<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Department;
use App\Repositories\DepartmentRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    private $depart_repository;

    public function __construct(DepartmentRepository $depart_repository)
    {
        $this->depart_repository = $depart_repository;
    }

    public function list(Request $request)
    {
        $filter = $request->all();
        $filter['pid'] = 0;
        $limit = $filter['limit'] ?? 15;
        $list = $this->depart_repository->paginate($filter, ['position:id,pid,position_name,status,created_at'], $limit, ['id', 'depart_name', 'status', 'created_at']);

        return renderSuccess($list);
    }

    public function create(Request $request)
    {
        $rules = [
            'depart_name' => 'required_if:type,depart|string|unique:departments',
            'position_name' => 'required_if:type,position|string|unique:departments',
            'pid' => 'required_if:type,position|integer',
            'type' => 'required|string|in:depart,position',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['type'] == 'depart') {
            $data['pid'] = 0;
            $data['position_name'] = '';
        }
        $this->depart_repository->create($data);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'type' => 'required|string|in:depart,position',
            'depart_name' => 'required_if:type,depart|string|unique:departments,id,' . $request->input('id') . ',id',
            'position_name' => 'required_if:type,position|string|unique:departments,id,' . $request->input('id') . ',id',
            'pid' => 'required_if:type,position|integer',
            'status' => 'string|in:active,inactive',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['type'] == 'depart') {
            $data['pid'] = 0;
            $data['position_name'] = '';
        }

        $depart = $this->depart_repository->update($data['id'], $data);

        if ($depart->status != 'active' && $depart->pid == 0) {
            Department::query()->where('pid', $depart->id)->update(['status' => $depart->status]);
        }

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
        $this->depart_repository->delete($data['id']);
        Department::query()->where('pid', $data['id'])->delete();
        Admin::query()->where('depart_id', $data['id'])->update(['depart_id' => 0]);
        Admin::query()->where('position_id', $data['id'])->update(['position_id' => 0]);

        return renderSuccess();
    }

}
