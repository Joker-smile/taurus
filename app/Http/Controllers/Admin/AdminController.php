<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\AdminRepository;
use App\Utils\CacheKeys;
use App\Utils\Code;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    const ADMIN_LOGIN_KEY = CacheKeys::ADMIN_LOGIN_KEY;

    private $admin_repository;

    public function __construct(AdminRepository $admin_repository)
    {
        $this->admin_repository = $admin_repository;
    }

    public function login(Request $request)
    {
        $rules = [
            'phone' => 'required|integer|min:11',
            'password' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        try {
            $admin = $this->admin_repository->with(['position:id,position_name', 'department:id,depart_name', 'role:id,name'])->findBy('phone', $data['phone']);
        } catch (ModelNotFoundException $e) {
            return renderError(Code::AUTH_IS_ERROR);
        }

        if (!Hash::check($data['password'], $admin->password) || $admin->is_delete == 1) return renderError(Code::AUTH_IS_ERROR);
        if ($admin->status != 'active') {
            return renderError(Code::FAILED, '您的账号已被冻结');
        }
        $token = md5(mt_rand(1000, 9999) . time() . 'taurus');  // 生成token
        Cache::put(self::ADMIN_LOGIN_KEY . $token, $admin->id, 3600 * 24);  // token绑定uid
        $result['admin_info'] = $admin;
        $result['token'] = $token;
//        Cache::forget(self::ADMIN_LOGIN_KEY . $admin->token);
//        $admin->token = $token;
//        $admin->update();

        return renderSuccess($result);
    }

    public function create(Request $request)
    {
        $rules = [
            'password' => 'required|string|min:8|confirmed',
            'nick_name' => 'required|string',
            'phone' => 'required|integer|unique:admins|min:11|max:11',
            'avatar' => 'url',
            'status' => 'required|string|in:active,inactive',
            'position_id' => 'required|integer',
            'depart_id' => 'required|integer',
            'role_id' => 'required|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data ['password'] = Hash::make($data['password']);

        $this->admin_repository->create($data);

        return renderSuccess(Code::SUCCESS);
    }

    public function update(Request $request)
    {
        $rules = [
            'password' => 'string|min:8|confirmed',
            'nick_name' => 'string',
            'avatar' => 'url',
            'position_id' => 'integer',
            'depart_id' => 'integer',
            'role_id' => 'integer',
            'status' => 'string|in:active,inactive',
            'phone' => 'required|string|unique:admins,id,' . $request->input('id') . '|min:11|max:11',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['id'] == 1) {
            return renderError(Code::FAILED, '该账号不可更改');
        }

        if (Arr::get($data, 'password')) {
            $data['password'] = Hash::make($data['password']);
        }

        $this->admin_repository->update($data['id'], $data);
        $current_admin = currentAdmin();

        //如果是修改自身密码，退出重新登录
        if ($current_admin->id == $data['id']) {
            $token = $request->header('token');
            Cache::forget(self::ADMIN_LOGIN_KEY . $token);
            return renderSuccess([], 0, '修改成功,请重新登录');
        }

        return renderSuccess([], 0, '修改成功');
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'password' => 'required|string|min:8|confirmed',
            'old_password' => 'required|string|min:8',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $admin = currentAdmin();
        if (!Hash::check($data['old_password'], $admin->password)) {
            return renderError(Code::FAILED, '旧密码不正确');
        }

        $data['password'] = Hash::make($data['password']);
        $this->admin_repository->update($admin->id, $data);
        $token = $request->header('token');
        Cache::forget(self::ADMIN_LOGIN_KEY . $token);

        return renderSuccess([], 0, '修改成功,请重新登录');
    }

    public function logout(Request $request)
    {
        if (!$token = $request->header('token')) return renderError(Code::TOKEN_IS_EMPTY);
        Cache::forget(self::ADMIN_LOGIN_KEY . $token);

        return renderSuccess();
    }

    public function list(Request $request)
    {
        $filter = $request->only(['nick_name', 'phone', 'status', 'depart_id', 'role_id', 'date_range']);
        $filter['is_delete'] = 0;
        $list = $this->admin_repository->paginate($filter, ['position:id,position_name', 'department:id,depart_name', 'role:id,name'], 15);

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

        if ($data['id'] == 1) {
            return renderError(Code::FAILED, '该账号不可删除');
        }

        $this->admin_repository->update($data['id'], ['is_delete' => 1]);

        return renderSuccess();
    }
}
