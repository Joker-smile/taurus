<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\MenuRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    private $menu_repository;

    public function __construct(MenuRepository $menu_repository)
    {
        $this->menu_repository = $menu_repository;
    }

    public function list()
    {
        $menus = $this->menu_repository->get(['pid' => 0], ['roles:id,name', 'allChildren'], ['id', 'name', 'path', 'sort', 'pid'], 'sort', 'asc');
        $this->dataHandle($menus);

        return renderSuccess($menus);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'path' => 'string|unique:menus',
            'pid' => 'required|integer|min:0',
            'sort' => 'required|integer|min:0'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->menu_repository->create($data);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'string',
            'path' => 'string|unique:menus,path,' . $request->input('id') . ',id',
            'pid' => 'integer|min:0',
            'sort' => 'integer|min:0'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->menu_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $menu = $this->menu_repository->find($id);
            $menu->roles()->detach();
            $menu->delete();
        }

        return renderSuccess();
    }

    public function getMenuByRoleId(Request $request)
    {
        $role_id = $request->input('role_id');
        $menus = [];
        if ($role_id) {
            $menu_ids = DB::table('role_menus')->where('role_id', $role_id)->pluck('menu_id');
            $menus = $this->menu_repository->whereIn(['id' => $menu_ids])->all(['id', 'name', 'path']);
        }

        return renderSuccess($menus);
    }

    protected function dataHandle($menus)
    {
        foreach ($menus as &$menu) {
            $roles = $menu->roles;
            $roles = $roles->pluck('name') ?? [];
            unset($menu->roles);
            $menu->roles = $roles;
            if ($menu = $menu->allChildren) {
                $this->dataHandle($menu);
            }
        }
    }
}
