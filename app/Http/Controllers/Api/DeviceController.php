<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DeviceResource;
use App\Models\DeviceName;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceTypeRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeviceController extends Controller
{
    private $device_repository;
    private $device_type_repository;

    public function __construct(DeviceRepository $device_repository, DeviceTypeRepository $device_type_repository)
    {
        $this->device_repository = $device_repository;
        $this->device_type_repository = $device_type_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->validate([
            'status' => 'string|in:reviewing,success,failed',
        ]);

        $filters['is_delete'] = 0;
        $filters['user_id'] = currentUser()->id;
        $limit = $request->input('limit') ?? 15;
        $devices = $this->device_repository->paginate($filters, [
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
            'specification_model' => 'required|string',
            'brand_name' => 'string',
            'license_number' => 'string',
            'new_rate' => 'string',
            'remark' => 'string',
            'images' => 'required|array',
            'images.*' => 'required|url',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if (count($data['images']) > 5) {
            return renderError(Code::FAILED, '图片不能超过5张');
        }

        $data['user_id'] = currentUser()->id;
        $device = $this->device_repository->create($data);
        $device_name = DeviceName::query()->where('group_id', $data['group_id'])->where('name', $data['name'])->first();
        $device->number = deviceNumber($data['type_id']) . deviceNumber($data['group_id']) . deviceNumber($device_name->id) . deviceNumber1($device->id);
        $device->save();

        if (!$device) return renderError(Code::SAVE_FAILED);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'type_id' => 'required|integer',
            'group_id' => 'required|integer',
            'name' => 'required|string',
            'specification_model' => 'required|string',
            'brand_name' => 'string',
            'license_number' => 'string',
            'new_rate' => 'string',
            'remark' => 'string',
            'images' => 'required|array',
            'images.*' => 'required|url',
            'id' => 'required|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        if (count($data['images']) > 5) {
            return renderError(Code::FAILED, '图片不能超过5张');
        }

        if (!Arr::get($data, 'brand_name')) {
            $data['brand_name'] = '其它';
        }

        $data['status'] = 'reviewing';
        $device_name = DeviceName::query()->where('group_id', $data['group_id'])->where('name', $data['name'])->first();
        $data['number'] = deviceNumber($data['type_id']) . deviceNumber($data['group_id']) . deviceNumber($device_name->id) . deviceNumber1($data['id']);
        $result = $this->device_repository->update($data['id'], $data);
        if (!$result) return renderError(Code::UPDATE_FAILED);

        return renderSuccess();
    }

    public function deviceTypeList()
    {
        $list = $this->device_type_repository->get(['status' => 1, 'groups' => 1], ['groups.devices' => function ($q) {
            return $q->where('status', 1);
        }], ['id', 'name']);

        foreach ($list as $key => $type) {
            foreach ($type->groups as $k => $group) {
                if ($group->devices->isEmpty()) {
                    $type->groups->pull($k);
                }
            }
            if ($type->groups->isEmpty()) {
                $list->pull($key);
            }
        }
        $list = array_values($list->toArray());

        return renderSuccess($list);
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
}
