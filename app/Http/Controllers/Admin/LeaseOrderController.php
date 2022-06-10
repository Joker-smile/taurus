<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Repositories\DeviceRepository;
use App\Repositories\OrderRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaseOrderController extends Controller
{
    private $order_repository;
    private $device_repository;

    public function __construct(OrderRepository $order_repository, DeviceRepository $device_repository)
    {
        $this->order_repository = $order_repository;
        $this->device_repository = $device_repository;
    }

    public function pendingList(Request $request)
    {
        $filters = $request->only(['rent_number', 'status', 'project_name', 'lessee_name',
            'construction_site', 'device_name', 'specification_model', 'rental_prices',
            'entry_times', 'lessor_id', 'lessee_id', 'limit', 'company_name']);
        $filters['type'] = 'lease';

        if (!Arr::get($filters, 'status')) {
            $filters['status'] = ['pending', 'review_failed', 'invalid'];
        }
        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name', 'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function distribute(Request $request)
    {
        $rules = [
            'device_id' => 'required|integer',
            'device_quantity' => 'integer',
            'rental_price' => 'required|string',
            'lump_sum_price' => 'required|string',
            'lessor_contract_number' => 'required|string',
            'lessee_contract_number' => 'required|string',
            'service_rate' => 'required|string',
            'release_time' => 'string',
            'id' => 'required|integer',
            'salesman' => 'string',
            'subsidiary' => 'string',
            'remark' => 'string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }
        $device = $this->device_repository->find($data['device_id']);
        $data['lessor_id'] = $device->user_id;
        $data['rental_price'] = $data['rental_price'] * 100;
        $data['lump_sum_price'] = $data['lump_sum_price'] * 100;
        $data['status'] = 'reviewing';

        $this->order_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function cancel(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Order::query()->where('id', $data['id'])
            ->where('type', 'lease')
            ->where('status', 'pending')
            ->update(['status' => 'invalid']);

        return renderSuccess();
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
        if ($order->type == 'lease' && $order->status == 'pending') {
            DB::transaction(function () use ($order) {
                $rent_order = $this->order_repository->find($order->rent_id);
                $rent_order->status = 'reviewing';
                $rent_order->update();
                $order->delete();
            });

            return renderSuccess();
        }

        return renderError(Code::FAILED, '该订单不能回退');
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'project_name', 'lessee_name', 'lessor_name',
            'construction_site', 'lessee_number', 'lessor_number', 'lessee_contract_number', 'lessor_contract_number',
            'entry_times', 'limit', 'company_name']);
        $filters['type'] = 'lease';

        if (!Arr::get($filters, 'status')) {
            $filters['status'] = ['reviewing', 'review_success'];
        }

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name', 'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
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
        $data['review_time'] = Carbon::now()->toDateTimeString();
        if (Cache::has('lease_order_' . $data['id'])) return renderError(Code::FAILED, '同一笔订单,请勿频繁提交审核');
        $order = $this->order_repository->update($data['id'], $data);
        if ($order && $data['status'] == 'review_success') {
            $order_data = $order->toArray();
            $order_data['lease_id'] = $order->id;
            $order_data['type'] = 'produce';
            $order_data['status'] = 'pending';
            $order_data['review_time'] = '';
            $data['review_id'] = 0;
            $produce_order = $this->order_repository->create($order_data);
            $produce_order->produce_number = createNumber('SC', $produce_order->id);
            $produce_order->save();
            $this->order_repository->push($order, 1);
        }

        if ($order && $data['status'] == 'review_failed') {
            DB::transaction(function () use ($order) {
                $order->status = 'pending';
                $order->update();
            });
        }
        Cache::put('lease_order_' . $order->id, 1, 10);

        return renderSuccess();
    }
}
