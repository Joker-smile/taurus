<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SystemMessage;
use App\Models\Message;
use App\Models\User;
use App\Repositories\SystemMessageRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SystemMessageController extends Controller
{
    private $system_message_repository;

    public function __construct(SystemMessageRepository $system_message_repository)
    {
        $this->system_message_repository = $system_message_repository;
    }

    public function list(Request $request)
    {
        $filter = $request->all();
        $messages = $this->system_message_repository->paginate($filter, [], $filter['limit'] ?? 15);
        foreach ($messages['list'] as &$message) {
            $message->users = '';
            if ($message['user_ids'] != 0) {
                $user_ids = explode(',', $message['user_ids']);
                $message->users = User::query()->whereIn('id', $user_ids)->get();
            }
        }

        return renderSuccess($messages);
    }

    public function create(Request $request)
    {
        $rules = [
            'type' => 'required|integer|in:1,2',
            'user_ids' => 'string',
            'title' => 'required|string',
            'content' => 'required|string',
            'client_type' => 'required|integer|in:0,1,2',
            'image_url' => 'url',
            'push_time' => 'integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if (!Arr::get($data, 'push_time')) {
            $data['push_time'] = time() + 5;
        }
        $message = $this->system_message_repository->create($data);
        SystemMessage::dispatch($message)->delay(Carbon::createFromTimestamp($data['push_time']));

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'type' => 'required|integer|in:1,2',
            'user_ids' => 'string',
            'title' => 'required|string',
            'content' => 'required|string',
            'client_type' => 'required|integer|in:0,1,2',
            'image_url' => 'url',
            'push_time' => 'integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->system_message_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $this->system_message_repository->delete($id);
        }

        return renderSuccess();
    }

    public function count(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        $push_user_count = Message::where('system_message_id', $data['id'])->count();
        $read_count = Message::where('system_message_id', $data['id'])->where('is_read', 1)->count();
        $unread_count = Message::where('system_message_id', $data['id'])->where('is_read', 0)->count();

        return renderSuccess([
            'push_user_count' => $push_user_count,
            'read_count' => $read_count,
            'unread_count' => $unread_count
        ]);
    }
}
