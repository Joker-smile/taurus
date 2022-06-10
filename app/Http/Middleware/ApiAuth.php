<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Utils\CacheKeys;
use App\Utils\Code;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiAuth
{
    /**
     * @return \Illuminate\Http\JsonResponse|Closure
     */
    public static function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        if (!$token) return renderError(Code::TOKEN_IS_EMPTY);
        $user_id = Cache::get(CacheKeys::USER_LOGIN_KEY . $token);
        if (!$user_id) return renderError(Code::TOKEN_IS_EXPIRE);
        $user = User::query()->find($user_id);
        if ($user->status != 'active') {
            Cache::forget(CacheKeys::USER_LOGIN_KEY . $token);
            return renderError(Code::FAILED, '用户已被拉黑,原因:' . $user->review_message ?? '');
        }

        return $next($request);
    }
}
