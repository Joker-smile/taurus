<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MessageResource;
use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Utils\Code;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private $message_repository;

    public function __construct(MessageRepository $message_repository)
    {
        $this->message_repository = $message_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->validate([
            'type' => 'string|in:1,2'
        ]);
        $limit = $request->input('limit') ?? 15;
        $filters['user_id'] = currentUser()->id;
        $filters['is_delete'] = 0;
        $list = $this->message_repository->paginate($filters, ['order.payments'], $limit);
        $user_unread_message_count = Message::where('is_read', 0)->where('is_delete', 0)
            ->where('type', 1)
            ->where('user_id', currentUser()->id)
            ->count();
        $system_unread_message_count = Message::where('is_read', 0)->where('is_delete', 0)
            ->where('type', 2)
            ->where('user_id', currentUser()->id)
            ->count();
        $result = MessageResource::collection($list['list']);
        $result = array_merge(['list' => $result->toArray($request)], ['page_info' => $list['page_info']]);
        $result['user_unread_message_count'] = $user_unread_message_count;
        $result['system_unread_message_count'] = $system_unread_message_count;

        return renderSuccess($result);
    }

    public function delete(Request $request)
    {
        $rules = [
            'type' => 'required|in:all,part',
            'id' => 'required_if:type,part|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['type'] == 'all') {
            Message::query()->where('user_id', currentUser()->id)->update(['is_delete' => 1]);
        } else {
            $this->message_repository->update($data['id'], ['is_delete' => 1]);
        }

        return renderSuccess();
    }

    public function read(Request $request)
    {
        $rules = [
            'type' => 'required|in:all,part',
            'id' => 'required_if:type,part|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if ($data['type'] == 'all') {
            Message::query()->where('user_id', currentUser()->id)->update(['is_read' => 1]);
        } else {
            $this->message_repository->update($data['id'], ['is_read' => 1]);
        }

        return renderSuccess();
    }

    protected function orderStatus($message)
    {
        if ($message->type == 2 || !$message->order) {
            $message->status = '';

            return $message;
        }

        $message->status = 'processing';
        if ($message->order->type == 'settle') {
            $message->status = 'finished';
        }

        if ($message->order->type == 'finance') {
            $message->status = 'settling';
            $amount = $message->order->payments()->where('status', 'review_success')->where('type', 'payment')->sum('amount');
            if ($amount == $message->order->lessor_total_price) {
                $message->status = 'settled';
            }
        }

        return $message;
    }
}
