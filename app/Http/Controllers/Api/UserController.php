<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Province;
use App\Repositories\UserRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function info()
    {
        $user = currentUser();

        return renderSuccess($user);
    }

    public function update(Request $request)
    {
        $rules = [
            'phone' => 'string|unique:users,phone,' . $request->input('id') . ',id',
            'id' => 'required|integer',
            'avatar' => 'string|url',
            'is_push_message' => 'integer|in:1,0'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $result = $this->user_repository->update($data['id'], $data);

        if ($result) return renderSuccess();

        return renderError(Code::UPDATE_FAILED);
    }

    public function companyInfoUpdate(Request $request)
    {
        $rules = [
            'business_license' => 'nullable|string|url',
            'company_name' => 'string|nullable',
            'credit_code' => 'string|nullable',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        $data['business_license'] = $data['business_license'] ? $data['business_license'] : '';
        $data['company_name'] = $data['company_name'] ? $data['company_name'] : '';
        $data['credit_code'] = $data['credit_code'] ? $data['credit_code'] : '';
        $user = currentUser();

        $result = $this->user_repository->update($user->id, $data);

        if ($result) return renderSuccess();

        return renderError(Code::UPDATE_FAILED);
    }

    public function getProvinceCityArea(Request $request)
    {
        $rules = [
            'type' => 'required|string|in:province,city,area',
            'province_code' => 'required_if:type,city|integer',
            'city_code' => 'required_if:type,area|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['type'] == 'province') {
            $list = Cache::rememberForever('province', function () {
                return Province::query()->get();
            });
        }

        if ($data['type'] == 'city') {
            $list = Cache::rememberForever('city' . $data['province_code'], function () use ($data) {
                return City::query()->where('province_code', $data['province_code'])->get();
            });
        }

        if ($data['type'] == 'area') {
            $list = Cache::rememberForever('area' . $data['city_code'], function () use ($data) {
                return area::query()->where('city_code', $data['city_code'])->get();
            });
        }

        return renderSuccess($list);
    }

}
