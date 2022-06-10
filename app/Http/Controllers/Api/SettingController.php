<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProtocolRepository;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private $protocol_repository;

    public function __construct(ProtocolRepository $protocol_repository)
    {
        $this->protocol_repository = $protocol_repository;
    }

    public function aboutUs()
    {
        $protocol = $this->protocol_repository->findBy('key', 'about_us');

        return renderSuccess($protocol->value ?? '');
    }

    public function disclaimerProtocol()
    {
        $protocol = $this->protocol_repository->findBy('key', 'disclaimer_protocol');

        return renderSuccess($protocol->value ?? '');
    }

    public function privacyPolicy(Request $request)
    {
        $type = $request->input('type');
        $protocol = $this->protocol_repository->findBy('key', 'privacy_policy');

        if ($type == 'html') {
            return view('privacy_policy', ['value' => $protocol->value ?? '']);
        } else {
            return renderSuccess($protocol->value ?? '');
        }
    }

    public function cancelProtocol()
    {
        $protocol = $this->protocol_repository->findBy('key', 'cancel_protocol');

        return renderSuccess($protocol->value ?? '');
    }
}
