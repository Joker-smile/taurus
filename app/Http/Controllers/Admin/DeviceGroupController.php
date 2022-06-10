<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Repositories\DeviceGroupRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeviceGroupController extends Controller
{
    private $device_group_repository;

    public function __construct(DeviceGroupRepository $device_group_repository)
    {
        $this->device_group_repository = $device_group_repository;
    }

    public function create(Request $request)
    {
        $rules = [
            'type_id' => 'required|integer',
            'name' => 'required|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $names = explode(',', $data['name']);
        foreach ($names as $name) {
            $group = DeviceGroup::query()->where('name', $name)->first();
            if ($group) {
                return renderError(Code::FAILED, $name . '已存在,不能重复添加');
            }

            $this->device_group_repository->create(['name' => $name, 'type_id' => $data['type_id']]);
        }

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'type_id' => 'integer',
            'id' => 'integer',
            'name' => 'string',
            'status' => 'integer|in:0,1'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if (Arr::get($data, 'type_id')) {
            Device::query()->where('group_id', $data['id'])->update(['type_id' => $data['type_id']]);
        }

        $this->device_group_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $device_count = Device::query()->where('group_id', $data['id'])->count();
        if ($device_count > 0) {
            return renderError(Code::FAILED, '删除失败,该组别存在用户设备');
        }

        $group = $this->device_group_repository->find($data['id']);
        $group->devices()->delete();
        $group->delete();

        return renderSuccess();
    }
}
