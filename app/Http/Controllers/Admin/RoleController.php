<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\RoleRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RoleController extends Controller
{
    private $role_repository;

    public function __construct(RoleRepository $role_repository)
    {
        $this->role_repository = $role_repository;
    }

    public function list(Request $request)
    {
        $filter = $request->all();
        $roles = $this->role_repository->paginate($filter, ['menus:id'], $request->input('limit') ?? 15, ['id', 'name', 'status']);
        foreach ($roles['list'] ?? [] as &$item) {
            $menus = $item->menus;
            $item->menu_ids = $menus ? $menus->pluck('id') : [];
            unset($item->menus);
        }

        return renderSuccess($roles);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string|unique:roles',
            'menu_ids' => 'string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $role = $this->role_repository->create($data);
        if ($menu_ids = Arr::get($data, 'menu_ids')) {
            $menu_ids = explode(',', $menu_ids);
            $role->menus()->sync($menu_ids);
        }

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'string|unique:roles,name,' . $request->input('id') . ',id',
            'menu_ids' => 'string',
            'status' => 'string|in:active,inactive'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['id'] == 1) {
            return renderError(Code::FAILED, '该角色不可更改');
        }

        $role = $this->role_repository->update($data['id'], $data);
        if ($menu_ids = Arr::get($data, 'menu_ids')) {
            $menu_ids = explode(',', $menu_ids);
            $role->menus()->sync($menu_ids);
        }

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        if ($id && $id == 1) {
            return renderError(Code::FAILED, '该角色不允许删除');
        }

        if ($id) {
            $role = $this->role_repository->find($id);
            $role->menus()->detach();
            $role->delete();
        }

        return renderSuccess();
    }
}
