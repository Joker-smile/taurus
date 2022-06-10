<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\DeviceName;
use App\Repositories\DeviceNameRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeviceNameController extends Controller
{
    private $device_name_repository;

    public function __construct(DeviceNameRepository $device_name_repository)
    {
        $this->device_name_repository = $device_name_repository;
    }

    public function list()
    {
        $device_names = $this->device_name_repository->get([], [], ['id', 'name']);

        return renderSuccess($device_names);
    }

    public function create(Request $request)
    {
        $rules = [
            'group_id' => 'required|integer',
            'name' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $names = explode(',', $data['name']);
        foreach ($names as $name) {
            $device_name = DeviceName::query()->where('name', $name)->first();
            if ($device_name) {
                return renderError(Code::FAILED, $name . '已存在不能重复添加');
            }

            $this->device_name_repository->create(['name' => $name, 'group_id' => $data['group_id']]);
        }

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'group_id' => 'integer',
            'id' => 'required|integer',
            'name' => 'string',
            'status' => 'integer|in:0,1'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if (Arr::get($data, 'group_id')) {
            Device::query()->where('name', $data['name'])->update(['name' => $data['name']]);
        }

        $this->device_name_repository->update($data['id'], $data);

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

        $this->device_name_repository->delete($data['id']);

        return renderSuccess();
    }
}
