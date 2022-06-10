<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ProtocolRepository;
use App\Utils\Code;
use Illuminate\Http\Request;

class ProtocolController extends Controller
{
    private $protocol_repository;

    public function __construct(ProtocolRepository $protocol_repository)
    {
        $this->protocol_repository = $protocol_repository;
    }

    public function list(Request $request)
    {
        $list = $this->protocol_repository->get($request->all(), [], ['*'], 'id', 'asc');

        return renderSuccess($list);
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'key' => 'required|string',
            'value' => 'required|string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->protocol_repository->update($data['id'], $data);

        return renderSuccess();
    }
}
