<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Repositories\OrderRepository;
use App\Utils\Code;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $order_repository;

    public function __construct(OrderRepository $order_repository)
    {
        $this->order_repository = $order_repository;
    }

    public function list(Request $request)
    {
        $rules = [
            'order_progress' => 'required|string|in:all,processing,finished,settle',
        ];

        if ($error = $this->formValidate($request, $rules, $filters)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['order_progress']['order_progress'] = $filters['order_progress'];
        $data['order_progress']['lessor_id'] = currentUser()->id;
        $limit = $request->input('limit') ?? 15;
        $list = $this->order_repository->paginate($data, ['lessor:id,real_name', 'lessee:id,company_name',
            'payments', 'device'], $limit);
        $result = OrderResource::collection($list['list']);
        $result = array_merge(['list' => $result->toArray($request)], ['page_info' => $list['page_info']]);

        return renderSuccess($result);
    }
}
