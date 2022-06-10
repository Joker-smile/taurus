<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class LesseeController extends Controller
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['credit_code', 'bank_license', 'bank_name', 'account_number',
            'company_name', 'phone', 'company_address', 'date_range', 'limit', 'status', 'admin_id']);
        $filters['type'] = 'lessee';
        $list = $this->user_repository->paginate($filters, ['admin:id,nick_name'], $filters['limit'] ?? 15, ['*']);

        return renderSuccess($list);
    }

    public function create(Request $request)
    {
        $rules = [
            'phone' => 'required|string|unique:users|min:11|max:11',
            'real_name' => 'required|string|max:150',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'company_name' => 'required|string|unique:users|max:150',
            'province' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string|max:255',
            'invoicing_phone' => 'required|string|max:30',
            'area' => 'required|string',
            'business_license' => 'required|url|max:255',
            'credit_code' => 'required|string|max:255',
            'bank_license' => 'required|string|max:50',
            'avatar' => 'url',
            'register_capital' => 'string|max:255',
            'employees' => 'string|max:255',
            'remark' => 'string|max:255',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['company_address'] = $data['province'] . $data['area'];
        $data['type'] = 'lessee';
        $data['admin_id'] = currentAdmin()->id;
        $data['password'] = Hash::make('123456');
        $this->user_repository->create($data);

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'phone' => 'required|string|unique:users,id,' . $request->input('id') . '|min:11|max:11',
            'real_name' => 'required|string|max:150',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'company_name' => 'required|string|max:150|unique:users,id,' . $request->input('id'),
            'province' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
            'address' => 'required|string|max:255',
            'invoicing_phone' => 'required|string|max:30',
            'business_license' => 'required|url|max:255',
            'credit_code' => 'required|string|max:255',
            'bank_license' => 'required|string|max:50',
            'avatar' => 'url',
            'register_capital' => 'string|max:255',
            'employees' => 'string|max:255',
            'remark' => 'string|max:255',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['company_address'] = $data['province'] . $data['area'];
        $this->user_repository->update($data['id'], $data);

        return renderSuccess();
    }


    public function statusChange(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'review_message' => 'required_if:status,inactive|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $this->user_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function orders(Request $request)
    {
        $rules = [
            'user_id' => 'required|integer',
            'type' => 'required|in:rent,lease,produce,settle',
            'status' => 'string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $filters['lessee_id'] = $data['user_id'];
        $filters['type'] = $data['type'];
        if (Arr::get($data, 'status')) {
            $filters['status'] = $data['status'];
        }
        $filters['is_delete'] = 0;
        $orders = app(OrderRepository::class)->paginate($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

}
