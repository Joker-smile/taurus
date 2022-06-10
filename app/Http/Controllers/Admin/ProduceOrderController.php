<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Repositories\OrderRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProduceOrderController extends Controller
{
    private $order_repository;

    public function __construct(OrderRepository $order_repository)
    {
        $this->order_repository = $order_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'project_name', 'lessee_name', 'construction_site',
            'produce_number', 'lessee_contract_number', 'specification_model', 'device_name',
            'date_range', 'lessor_id', 'lessee_id', 'review_id', 'limit', 'company_name', 'lessor_name']);
        $filters['type'] = 'produce';

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name', 'maker:id,nick_name', 'reviewer:id,nick_name'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function back(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $order = $this->order_repository->find($data['id']);
        if ($order->type == 'produce' && $order->status == 'pending') {
            DB::transaction(function () use ($order) {
                $lease_order = $this->order_repository->find($order->lease_id);
                $lease_order->status = 'reviewing';
                $lease_order->update();
                $order->delete();
            });

            return renderSuccess();
        }

        return renderError(Code::FAILED, '该订单不能回退');
    }

    public function update(Request $request)
    {
        $rules = [
            'produce_start_time' => 'required|string',
            'produce_end_time' => 'required|string',
            'construction_value' => 'required|string',
            'construction_unit' => 'required|string',
            'construction_images' => 'required|array',
            'construction_images.*' => 'required|url',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if (count($data['construction_images']) > 9 || count($data['construction_images']) < 2) {
            return renderError(Code::FAILED, '现场作业图片数量不对');
        }

        $data['status'] = 'reviewing';
        $this->order_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function review(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:review_success,review_failed',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['review_id'] = currentAdmin()->id;
        if (Cache::has('produce_order_' . $data['id'])) return renderError(Code::FAILED, '同一笔订单,请勿频繁提交审核');
        $order = $this->order_repository->update($data['id'], $data);
        $data['review_time'] = Carbon::now()->toDateTimeString();
        if ($order && $data['status'] == 'review_success') {
            $order_data = $order->toArray();
            $order_data['produce_id'] = $order->id;
            $order_data['type'] = 'settle';
            $order_data['status'] = 'pending';
            $order_data['review_time'] = '';
            $data['review_id'] = 0;
            $settle_order = $this->order_repository->create($order_data);
            $settle_order->lessee_settle_number = createNumber('GCYSK', $settle_order->id);
            $settle_order->lessor_settle_number = createNumber('GCYFK', $settle_order->id);
            $settle_order->save();
        }

        if ($order && $data['status'] == 'review_failed') {
            DB::transaction(function () use ($order) {
                $order->status = 'pending';
                $order->update();
            });
        }
        Cache::put('produce_order_' . $order->id, 1, 10);

        return renderSuccess();
    }

}
