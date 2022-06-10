<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RentOrderController extends Controller
{
    private $order_repository;

    public function __construct(OrderRepository $order_repository)
    {
        $this->order_repository = $order_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'project_name', 'lessee_name', 'construction_site', 'device_name',
            'specification_model', 'rental_prices', 'entry_times', 'lessor_id', 'lessee_id', 'limit', 'company_name']);
        $filters['type'] = 'rent';
        $filters['is_delete'] = 0;
        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name', 'maker:id,nick_name', 'reviewer:id,nick_name'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function create(Request $request)
    {
        $rules = [
            'lessee_id' => 'required|integer',
            'device_type_id' => 'required|integer',
            'device_group_id' => 'required|integer',
            'project_name' => 'required|string',
            'construction_site' => 'required|string',
            'construction_unit' => 'required|string',
            'construction_value' => 'required|string',
            'rental_price' => 'required|string',
            'rental_total' => 'required|string',
            'other_request' => 'string',
            'release_time' => 'required|string',
            'brand_name' => 'string',
            'device_name' => 'required|string',
            'specification_model' => 'string',
            'new_rate' => 'string',
            'entry_time' => 'string',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['type'] = 'rent';
        $data['make_id'] = currentAdmin()->id;
        $data['rental_price'] = $data['rental_price'] * 100;
        $data['rental_total'] = $data['rental_total'] * 100;
        $order = $this->order_repository->create($data);
        $order->rent_number = createNumber('FB', $order->id);
        $order->save();

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'lessee_id' => 'required|integer',
            'project_name' => 'required|string',
            'construction_site' => 'required|string',
            'construction_unit' => 'required|string',
            'construction_value' => 'required|string',
            'rental_price' => 'required|string',
            'rental_total' => 'required|string',
            'other_request' => 'string',
            'new_rate' => 'string',
            'release_time' => 'required|string',
            'brand_name' => 'string',
            'device_name' => 'required|string',
            'device_type_id' => 'required|integer',
            'device_group_id' => 'required|integer',
            'entry_time' => 'string',
            'specification_model' => 'string',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['rental_price'] = $data['rental_price'] * 100;
        $data['rental_total'] = $data['rental_total'] * 100;

        $this->order_repository->update($data['id'], $data);

        return renderSuccess();
    }

    public function submitReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Order::query()->where('id', $data['id'])
            ->where('type', 'rent')
            ->whereIn('status', ['pending', 'review_failed'])
            ->update(['status' => 'reviewing']);

        return renderSuccess();
    }

    public function cancelReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Order::query()->where('id', $data['id'])
            ->where('type', 'rent')
            ->where('status', 'reviewing')
            ->update(['status' => 'pending']);

        return renderSuccess();
    }

    public function review(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:review_success,review_failed',
            'review_message' => 'required_if:status,review_failed|string'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['review_id'] = currentAdmin()->id;
        $data['review_time'] = Carbon::now()->toDateTimeString();
        if (Cache::has('rent_order_' . $data['id'])) return renderError(Code::FAILED, '同一笔订单,请勿频繁提交审核');
        $order = $this->order_repository->update($data['id'], $data);
        if ($order && $data['status'] == 'review_success') {
            $order_data = $order->toArray();
            $order_data['rent_id'] = $order->id;
            $order_data['type'] = 'lease';
            $order_data['status'] = 'pending';
            $order_data['review_time'] = '';
            $order_data['review_id'] = 0;
            $lease_order = $this->order_repository->create($order_data);
            $lease_order->lessee_number = createNumber('SPZL', $lease_order->id);
            $lease_order->lessor_number = createNumber('CPZL', $lease_order->id);
            $lease_order->save();
        }
        Cache::put('rent_order_' . $order->id, 1, 10);

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

        Order::query()->where('id', $data['id'])
            ->where('type', 'rent')
            ->whereIn('status', ['pending', 'review_failed'])
            ->update(['is_delete' => 1]);

        return renderSuccess();
    }
}
