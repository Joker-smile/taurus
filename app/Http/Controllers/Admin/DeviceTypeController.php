<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\DeviceName;
use App\Models\Devicetype;
use App\Repositories\DeviceTypeRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeviceTypeController extends Controller
{
    private $device_type_repository;

    public function __construct(DeviceTypeRepository $device_type_repository)
    {
        $this->device_type_repository = $device_type_repository;
    }

    public function list()
    {
        $list = $this->device_type_repository->get([], ['groups', 'groups.devices'], ['id', 'name', 'status']);
        $data['type_count'] = Devicetype::count();
        $data['group_count'] = DeviceGroup::count();
        $data['device_count'] = DeviceName::count();

        return renderSuccess(['list' => $list, 'count' => $data]);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        $names = explode(',', $data['name']);
        foreach ($names as $name) {
            $type = DeviceType::query()->where('name', $name)->first();
            if ($type) {
                return renderError(Code::FAILED, $name . '已存在不能重复添加');
            }

            $this->device_type_repository->create(['name' => $name]);
        }

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'string',
            'status' => 'integer|in:0,1',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->device_type_repository->update($data['id'], $data);

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

        $device_count = Device::query()->where('type_id', $data['id'])->count();
        if ($device_count > 0) {
            return renderError(Code::FAILED, '删除失败,该类别存在用户设备');
        }

        $type = $this->device_type_repository->find($data['id']);
        $groups = $type->groups;
        foreach ($groups as $group) {
            DeviceName::where('group_id', $group->id)->delete();
            $group->delete();
        }
        $type->delete();

        return renderSuccess();
    }
}
