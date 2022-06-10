<?php

namespace App\Http\Middleware;

use App\Utils\Code;
use Closure;
use Illuminate\Http\Request;

class DataDecrypt
{
    /**
     * @return \Illuminate\Http\JsonResponse|Closure
     */
    public static function handle(Request $request, Closure $next)
    {
//        $data = $request->input('data');
        $client_type = $request->input('client_type');
        if (!in_array($client_type, [1, 2])) return renderError(Code::FAILED, '客户端不合法');
//        if (!$data) return $next($request);
//        $data = !empty(privateDecrypt($data)) ? privateDecrypt($data) : [];
//        $request->merge($data);

        return $next($request);
    }
}
