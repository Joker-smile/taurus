<?php

namespace App\Http\Controllers\Api;

use App\Models\Area;
use App\Models\SmsLog;
use App\Models\User;
use App\Repositories\SmsLogRepository;
use App\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Services\AliYunService;
use App\Services\YongYouService;
use App\Utils\CacheKeys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Utils\Code;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    const USER_LOGIN_KEY = CacheKeys::USER_LOGIN_KEY;

    private $user_repository;
    private $ali_yun_service;
    private $sms_log_repository;
    private $yong_you_service;

    public function __construct(UserRepository $user_repository,
                                AliYunService $ali_yun_service,
                                SmsLogRepository $sms_log_repository,
                                YongYouService $yong_you_service)
    {
        $this->user_repository = $user_repository;
        $this->ali_yun_service = $ali_yun_service;
        $this->sms_log_repository = $sms_log_repository;
        $this->yong_you_service = $yong_you_service;
    }

    public function register(Request $request)
    {
        $rules = [
            'phone' => 'required|string|unique:users|min:11|max:11',
            'password' => 'required|string|min:8',
            'real_name' => 'required|string|max:10',
            'id_card_number' => 'required|string|unique:users|min:18|max:20',
            'id_card_face' => 'required|url',
            'id_card_back' => 'required|url',
            'certify_id' => 'required|string|max:50',
            'bank_name' => 'required|string',
            'account_number' => 'required|string|min:16',
            'device_token' => 'required|string|max:150',
            'province' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['company_address'] = $data['province'] . $data['area'];
        $area = Area::query()->where('name', $data['area'])->first();
        $data['company_address_code'] = $area ? $area->area_code : 0;
        $data['password'] = Hash::make($data['password']);
        $data['client_type'] = $request->input('client_type');
        $user = $this->user_repository->create($data);
        $token = md5(mt_rand(1000, 9999) . time() . 'joker');  // 生成token
        Cache::put(self::USER_LOGIN_KEY . $token, $user['id'], 3600 * 24 * 15);  // token绑定uid
        $result['user_info'] = $this->user_repository->find($user->id);
        $result['token'] = $token;

        return renderSuccess($result);
    }

    public function login(Request $request)
    {
        $rules = [
            'phone' => 'required|string|min:11|max:11',
            'password' => 'required|string|min:8',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        try {
            $user = $this->user_repository->findBy('phone', $data['phone']);
        } catch (ModelNotFoundException $e) {
            return renderError(Code::AUTH_IS_ERROR);
        }

        if ($user->status != 'active') {
            return renderError(Code::FAILED, '账号异常,请联系客服,原因:' . $user->review_message);
        }

        if (!Hash::check($data['password'], $user->password)) return renderError(Code::AUTH_IS_ERROR);
        $token = md5(mt_rand(1000, 9999) . time() . 'joker');  // 生成token
        Cache::put(self::USER_LOGIN_KEY . $token, $user['id'], 3600 * 24 * 15);  // token绑定uid
        if ($device_token = $request->input('device_token') ?? '') {
            $user->device_token = $device_token;
            $user->client_type = $request->input('client_type');
            $user->save();
        }
        $result['user_info'] = $user;
        $result['token'] = $token;

        return renderSuccess($result);
    }

    public function sendSmsCode(Request $request)
    {
        $rules = [
            'phone' => 'required|string|min:11',
            'type' => 'required|integer|in:1,2,3'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['type'] == 1) {
            $user = User::query()->where('phone', $data['phone'])->first();
            if ($user) return renderError(Code::FAILED, '该手机号码已经被注册,请前往登录');
        }

        $code = rand(1000, 9999);
        $today_sms_log_count = SmsLog::query()->where('phone', $data['phone'])
            ->where('type', $data['type'])
            ->where('created_at', '>', Carbon::now()->format('Y-m-d') . ' 00:00:00')
            ->where('created_at', '<', Carbon::now()->format('Y-m-d') . ' 23:59:59')
            ->count();
        if ($today_sms_log_count > 5) {
            return renderError(Code::FAILED, '今日验证码发送已达上限，请明天再来');
        }

        if (Cache::get($data['phone'])) {
            return renderError(Code::FAILED, '发送验证码太频繁，请稍后再试');
        }

        $this->sms_log_repository->create(['phone' => $data['phone'], 'sms_code' => $code, 'type' => $data['type']]);
        Cache::add($data['phone'], 1, 60);
        $template = config('sms.forget_password_template');
        if ($data['type'] == 1) {
            $template = config('sms.register_template');
        }

        if ($data['type'] == 3) {
            $template = config('sms.edit_phone_template');
        }

        $result = $this->ali_yun_service->sendSms($data['phone'], $template, $code, '', $message);
        if (!$result) {
            return renderError(Code::FAILED, $message);
        }

        return renderSuccess([], Code::SUCCESS, '发送成功');
    }

    public function smsCodeValidate(Request $request)
    {
        $rules = [
            'phone' => 'required|string|min:11',
            'sms_code' => 'required|integer|min:4',
            'type' => 'required|integer|in:1,2,3'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $sms_code = SmsLog::query()->where('type', $data['type'])
            ->where('phone', $data['phone'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->where('created_at', '<', Carbon::now())
            ->latest()->first();
        if ($sms_code && $sms_code->sms_code == $data['sms_code']) {
            return renderSuccess([], Code::SUCCESS, '验证成功');
        }

        return renderError(Code::FAILED, '验证码错误或者过期');
    }

    public function forgetPassword(Request $request)
    {
        $rules = [
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
            'sms_code' => 'required|integer|min:4',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $user = User::query()->where('phone', $data['phone'])->first();

        if (!$user) {
            return renderError(Code::FAILED, '用户不存在');
        }

        $sms_code = SmsLog::query()->where('type', 2)
            ->where('phone', $data['phone'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->where('created_at', '<', Carbon::now())
            ->latest()->first();
        if ($sms_code && $sms_code->sms_code == $data['sms_code']) {
            $data['password'] = Hash::make($data['password']);
            $data['client_type'] = $request->input('client_type');
            $this->user_repository->update($user->id, $data);

            return renderSuccess();
        }

        return renderError(Code::FAILED, '验证码错误或者过期');

    }

    public function logout(Request $request)
    {
        if (!$token = $request->header('token')) return renderError(Code::TOKEN_IS_EMPTY);
        Cache::forget(self::USER_LOGIN_KEY . $token);

        return renderSuccess();
    }

    public function initFaceVerify(Request $request)
    {
        $rules = [
            'meta_info' => 'required|string',
            'real_name' => 'required|string|max:10',
            'id_card_number' => 'required|string|min:18|max:20',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Log::info('DATA:' . json_encode($data));
        $certify_id = $this->ali_yun_service->initFaceVerify($data, $message);

        if (!$certify_id) {
            return renderError(Code::FAILED, $message);
        }

        return renderSuccess(['certify_id' => $certify_id]);
    }

    public function describeFaceVerify(Request $request)
    {
        $rules = [
            'certify_id' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $result = $this->ali_yun_service->describeFaceVerify($data, $message);

        if (!$result) {
            return renderError(Code::FAILED, $message);
        }

        return renderSuccess($result, Code::SUCCESS, $message);
    }

    public function idCardIdentify(Request $request)
    {
        $rules = [
            'id_card_face' => 'required|url',
            'id_card_back' => 'required|url',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        try {
            $face_result = $this->ali_yun_service->idCardIdentify($data['id_card_face'], 'face');
        } catch (\Exception $e) {
            Log::error('身份证正面照片不合法原因:' . $e->getMessage());
            return renderError(Code::FAILED, '身份证正面照片不合法');
        }
        $face_result = $face_result->frontResult->toMap();

        try {
            $this->ali_yun_service->idCardIdentify($data['id_card_back'], 'back');
        } catch (\Exception $e) {
            Log::error('身份证背面照片不合法原因:' . $e->getMessage());
            return renderError(Code::FAILED, '身份证背面照片不合法');
        }

        if (Arr::get($face_result, 'IDNumber')) {
            $user = User::query()->where('id_card_number', $face_result['IDNumber'])->first();
            if ($user) return renderError(Code::FAILED, '该身份证号码已经被注册,请前往登录');
        }

        return renderSuccess(['id_card_number' => $face_result['IDNumber'] ?? '', 'real_name' => $face_result['Name'] ?? '']);
    }

    public function bankAccountAuth(Request $request)
    {
        $rules = [
            'account_number' => 'required|string',
            'real_name' => 'required|string',
            'id_card_number' => 'required|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $message = $this->yong_you_service->bankAccountAuth($data);
        if (!$message) {
            return renderSuccess();
        }

        return renderError(Code::FAILED, $message);
    }

}
