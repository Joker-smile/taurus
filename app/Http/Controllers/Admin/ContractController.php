<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ContractRepository;
use App\Utils\Code;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    private $contract_repository;

    public function __construct(ContractRepository $contract_repository)
    {
        $this->contract_repository = $contract_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['type', 'company_name', 'user_name', 'contract_number', 'sign_year', 'date_range', 'contract_times', 'limit']);

        $list = $this->contract_repository->paginate($filters, ['user:id,real_name,province,city,company_name', 'admin:id,nick_name'], $filters['limit'] ?? 15);

        return renderSuccess($list);
    }

    public function create(Request $request)
    {
        $rules = [
            'user_id' => 'required|integer',
            'type' => 'required|string|in:lessor,lessee',
            'contract_number' => 'required|string',
            'sign_year' => 'required|integer',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'content' => 'required|array',
            'content.*' => 'required|url',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if (count($data['content']) > 9) {
            return renderError(Code::FAILED, '内容图片最多只能九张');
        }

        $data['admin_id'] = currentAdmin()->id;

        $this->contract_repository->create($data);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'user_id' => 'integer',
            'contract_number' => 'string',
            'sign_year' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
            'content' => 'array',
            'content.*' => 'required_with:content|url',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->contract_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->contract_repository->delete($data['id']);

        return renderSuccess();
    }
}
