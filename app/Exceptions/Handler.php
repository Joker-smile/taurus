<?php

namespace App\Exceptions;

use App\Utils\Code;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
//    protected function prepareJsonResponse($request, \Throwable $e)
//    {
//        if (!$msg = $e->getMessage()) {
//            $msg = '未知错误';
//        }
//
//        if ($e instanceof NotFoundHttpException) {
//            $msg = '未找到资源';
//        }
//
//        return renderError(Code::FAILED, $msg);
//    }

    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }
}
