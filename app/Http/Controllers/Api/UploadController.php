<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use App\Utils\Code;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    private $upload_service;

    public function __construct(UploadService $upload_service)
    {
        $this->upload_service = $upload_service;
    }

    public function upload(Request $request)
    {
        $rules = [
            'type' => 'required|string|in:avatar,device,business_license,id_card,message,feedback,contract,produce',
            'image' => 'required|image',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $url = $this->upload_service->upload($data['image'], time() . rand(), $data['type']);

        return renderSuccess(['url' => $url]);
    }

}
