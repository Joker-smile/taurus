<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Menu;
use App\Utils\CacheKeys;
use App\Utils\Code;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminAuth
{

    /**
     * @return \Illuminate\Http\JsonResponse|Closure
     */
    public static function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        if (!$token) return renderError(Code::TOKEN_IS_EMPTY);
        $admin_id = Cache::get(CacheKeys::ADMIN_LOGIN_KEY . $token);
        if (!$admin_id) return renderError(Code::TOKEN_IS_EXPIRE);
        $admin = Admin::where('is_delete', 0)->with(['role:id,status'])->find($admin_id);
        if (!$admin) {
            return renderError(Code::IS_NOT_AUTH);
        }
        if ($admin->status != 'active') {
            return renderError(Code::FAILED, '用户已被拉黑');
        }

        //权限判断
        $path = $request->path();
        $whitelist = config('permissions.whitelist');

        //是否在白名单内
        if (in_array($path, $whitelist)) {
            return $next($request);
        }

        $menu = Menu::with('roles:id')->where('path', $path)->first();
        //不存在该菜单
        if (!$menu) {
            return renderError(Code::IS_NOT_AUTH);
        }

        $role_ids = $menu->roles->pluck('id')->toArray();
        //该菜单未分配
        if (!$role_ids) {
            return renderError(Code::IS_NOT_AUTH);
        }

        //当前用户的职位没有角色
        if (!$role = $admin->role) {
            return renderError(Code::IS_NOT_AUTH);
        }

        //角色没有启用
        if ($role->status != 'active') {
            return renderError(Code::IS_NOT_AUTH);
        }

        //当前用户的角色没有操作的权限
        if (!in_array($role->id, $role_ids)) {
            return renderError(Code::IS_NOT_AUTH);
        }

        return $next($request);
    }
}
