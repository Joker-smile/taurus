<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 验证
     * @param Request $request
     * @param array $rules
     * @param array $formData
     * @return mixed|string|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function formValidate(Request $request, array $rules, &$formData = [])
    {
        $validator = Validator::make($request->all(), $rules);
        $error = $validator->errors()->first();
        if ($error) return $error;
        $formData = $this->validate($request, $rules);

        return null;
    }
}
