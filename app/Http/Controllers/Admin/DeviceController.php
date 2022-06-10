<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DeviceResource;
use App\Models\DeviceName;
use App\Repositories\DeviceRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    private $device_repository;

    public function __construct(DeviceRepository $device_repository)
    {
        $this->device_repository = $device_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'lessor_name', 'brand_name', 'name', 'specification_model',
            'review_time', 'company_address', 'group_id', 'type_id', 'company_name']);
        $limit = $request->input('limit') ?? 15;
        $filters['is_delete'] = 0;
        $devices = $this->device_repository->paginate($filters, [
            'user:id,real_name,company_address,company_name',
            'reviewer:id,nick_name',
            'type:id,name',
            'group:id,name',
        ], $limit);
        $list = DeviceResource::collection($devices['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $devices['page_info']]);

        return renderSuccess($result);
    }

    public function create(Request $request)
    {
        $rules = [
            'type_id' => 'required|integer',
            'group_id' => 'required|integer',
            'name' => 'required|string',
            'specification_model' => 'string',
            'brand_name' => 'string',
            'license_number' => 'string',
            'new_rate' => 'string',
            'remark' => 'string',
            'images' => 'required|array',
            'images.*' => 'required|url',
            'user_id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if (count($data['images']) > 5) {
            return renderError(Code::FAILED, '图片不能超过5张');
        }

        $device = $this->device_repository->create($data);
        $device_name = DeviceName::query()->where('group_id', $data['group_id'])->where('name', $data['name'])->first();
        $device->number = deviceNumber($data['type_id']) . deviceNumber($data['group_id']) . deviceNumber($device_name->id) . deviceNumber1($device->id);
        $device->save();

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'type_id' => 'integer',
            'group_id' => 'integer',
            'name' => 'string',
            'specification_model' => 'string',
            'brand_name' => 'string',
            'license_number' => 'string',
            'new_rate' => 'string',
            'remark' => 'string',
            'images' => 'array',
            'images.*' => 'url',
            'id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if (count($data['images']) > 5) {
            return renderError(Code::FAILED, '图片不能超过5张');
        }

        $device_name = DeviceName::query()->where('group_id', $data['group_id'])->where('name', $data['name'])->first();
        $data['number'] = deviceNumber($data['type_id']) . deviceNumber($data['group_id']) . deviceNumber($device_name->id) . deviceNumber1($data['id']);
        $this->device_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->device_repository->update($data['id'], ['is_delete' => 1]);

        return renderSuccess();
    }

    public function review(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|in:success,failed',
            'review_message' => 'required_if:status,failed|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['review_time'] = Carbon::now();
        $data['review_admin_id'] = currentAdmin()->id;

        $this->device_repository->update($data['id'], $data);

        return renderSuccess();
    }
}
