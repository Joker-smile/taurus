<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DeviceResource;
use App\Http\Resources\Api\OrderResource;
use App\Repositories\DeviceRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Services\YongYouService;
use App\Utils\Code;
use Illuminate\Http\Request;

class LessorController extends Controller
{
    private $user_repository;
    private $device_repository;
    private $yong_you_service;

    public function __construct(UserRepository $user_repository, DeviceRepository $device_repository, YongYouService $yong_you_service)
    {
        $this->user_repository = $user_repository;
        $this->device_repository = $device_repository;
        $this->yong_you_service = $yong_you_service;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['real_name', 'phone', 'id_card_number', 'bank_name', 'status',
            'company_address', 'date_range', 'limit']);
        $filters['type'] = 'lessor';
        $list = $this->user_repository->paginate($filters, '', $filters['limit'] ?? 15, ['*']);

        return renderSuccess($list);
    }

    public function statusChange(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'review_message' => 'required_if:status,inactive|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->user_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'phone' => 'required|string|unique:users,id,' . $request->input('id') . '|min:11|max:11',
            'bank_name' => 'required|string',
            'account_number' => 'required|string|min:16',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $user = $this->user_repository->find($data['id']);
        $message = $this->yong_you_service->bankAccountAuth([
            'account_number' => $data['account_number'],
            'real_name' => $user->real_name,
            'id_card_number' => $user->id_card_number,
        ]);

        if (!$message) {
            $this->user_repository->update($data['id'], $data);
            return renderSuccess();
        }

        return renderError(Code::FAILED, $message);
    }

    public function devices(Request $request)
    {
        $rules = [
            'user_id' => 'required|integer',
            'status' => 'string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        $data['is_delete'] = 0;
        $devices = $this->device_repository->paginate($data, [
            'user:id,real_name,company_address',
            'reviewer:id,nick_name',
            'type:id,name',
            'group:id,name',
        ], $request->input('limit') ?? 15);
        $list = DeviceResource::collection($devices['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $devices['page_info']]);

        return renderSuccess($result);
    }

    public function orders(Request $request)
    {
        $rules = [
            'order_progress' => 'required|string|in:all,processing,finished,settle',
            'user_id' => 'required|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $filters)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['order_progress']['order_progress'] = $filters['order_progress'];
        $data['order_progress']['lessor_id'] = $filters['user_id'];
        $limit = $request->input('limit') ?? 15;
        $list = app(OrderRepository::class)->paginate($data, ['lessor:id,company_name', 'lessee:id,company_name',
            'payments'], $limit);
        $result = OrderResource::collection($list['list']);
        $result = array_merge(['list' => $result->toArray($request)], ['page_info' => $list['page_info']]);

        return renderSuccess($result);
    }
}
