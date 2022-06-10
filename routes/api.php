<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\FeedBackController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'as' => 'api.',
], function () {
    Route::post('users/register', [LoginController::class, 'register'])->name('users.register');
    Route::post('users/login', [LoginController::class, 'login'])->name('users.login');
    Route::post('users/initFaceVerify', [LoginController::class, 'initFaceVerify'])->name('users.initFaceVerify');
    Route::post('users/describeFaceVerify', [LoginController::class, 'describeFaceVerify'])->name('users.describeFaceVerify');
    Route::post('users/sendSmsCode', [LoginController::class, 'sendSmsCode'])->name('users.sendSmsCode');
    Route::post('users/smsCodeValidate', [LoginController::class, 'smsCodeValidate'])->name('users.smsCodeValidate');
    Route::post('image/upload', [UploadController::class, 'upload'])->name('image.upload');
    Route::post('users/idCardIdentify', [LoginController::class, 'idCardIdentify'])->name('user.idCard.identify');
    Route::post('users/forgetPassword', [LoginController::class, 'forgetPassword'])->name('users.forgetPassword');
    Route::post('users/bankAccountAuth', [LoginController::class, 'bankAccountAuth'])->name('users.bankAccountAuth');
    Route::get('users/getProvinceCityArea', [UserController::class, 'getProvinceCityArea'])->name('users.getProvinceCityArea');

    Route::group([
        'middleware' => ['app.auth']
    ], function () {
        Route::post('users/logout', [LoginController::class, 'logout'])->name('users.logout');
        Route::get('users/info', [UserController::class, 'info'])->name('users.info');
        Route::post('users/update', [UserController::class, 'update'])->name('users.update');
        Route::post('users/companyInfoUpdate', [UserController::class, 'companyInfoUpdate'])->name('users.companyInfoUpdate');

        //设备相关
        Route::get('devices/list', [DeviceController::class, 'list'])->name('devices.list');
        Route::post('devices/create', [DeviceController::class, 'create'])->name('devices.create');
        Route::post('devices/update', [DeviceController::class, 'update'])->name('devices.update');
        Route::post('devices/delete', [DeviceController::class, 'delete'])->name('devices.delete');
        Route::get('deviceType/list', [DeviceController::class, 'deviceTypeList'])->name('devices.type.list');

        //问题反馈
        Route::get('feedback/list', [FeedbackController::class, 'list'])->name('feedback.list');
        Route::post('feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');

        //订单相关
        Route::get('orders/list', [OrderController::class, 'list'])->name('orders.list');

        //消息相关
        Route::get('messages/list', [MessageController::class, 'list'])->name('messages.list');
        Route::post('messages/delete', [MessageController::class, 'delete'])->name('messages.delete');
        Route::post('messages/read', [MessageController::class, 'read'])->name('messages.read');

    });

    //关于我
    Route::get('about/us', [SettingController::class, 'aboutUs'])->name('about.us');
    //免责声明
    Route::get('disclaimer/protocol', [SettingController::class, 'disclaimerProtocol'])->name('disclaimer.protocol');
    //隐私政策
    Route::get('privacy/policy', [SettingController::class, 'privacyPolicy'])->name('privacy.policy');
    //注销协议
    Route::get('cancel/protocol', [SettingController::class, 'cancelProtocol'])->name('cancel.protocol');
});
