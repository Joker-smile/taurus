<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettleOrderController extends Controller
{
    private $order_repository;

    public function __construct(OrderRepository $order_repository)
    {
        $this->order_repository = $order_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'project_name', 'lessee_name', 'lessor_name',
            'lessee_settle_number', 'date_range', 'produce_number', 'lessor_number', 'lessee_number',
            'lessor_settle_number', 'lessor_id', 'lessee_id', 'limit', 'company_name']);
        $filters['type'] = 'settle';

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
        if ($order->type == 'settle' && $order->status == 'pending') {
            DB::transaction(function () use ($order) {
                $produce_order = $this->order_repository->find($order->produce_id);
                $produce_order->status = 'reviewing';
                $produce_order->update();
                $order->delete();
            });

            return renderSuccess();
        }

        return renderError(Code::FAILED, '该订单不能回退');
    }

    public function update(Request $request)
    {
        $rules = [
            'lessor_deduction_price' => 'string',
            'lessor_deduction_description' => 'string',
            'lessee_deduction_price' => 'string',
            'lessee_deduction_description' => 'string',
            'id' => 'required|integer',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if (Arr::get($data, 'lessor_deduction_price')) {
            $data['lessor_deduction_price'] = $data['lessor_deduction_price'] * 100;
        }

        if (Arr::get($data, 'lessee_deduction_price')) {
            $data['lessee_deduction_price'] = $data['lessee_deduction_price'] * 100;
        }

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
            ->where('type', 'settle')
            ->where('status', 'pending')
            ->update(['status' => 'reviewing']);

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
        $data['review_time'] = Carbon::now()->toDateTimeString();
        if (Cache::has('settle_order_' . $data['id'])) return renderError(Code::FAILED, '同一笔订单,请勿频繁提交审核');
        $order = $this->order_repository->update($data['id'], $data);
        if ($order && $data['status'] == 'review_success') {
            $order_data = $order->toArray();
            $order_data['settle_id'] = $order->id;
            $order_data['type'] = 'finance';
            $order_data['status'] = 'reviewing';
            $order_data['review_time'] = '';
            $data['review_id'] = 0;
            $finance_order = $this->order_repository->create($order_data);
            $finance_order->lessee_finance_number = createNumber('YSKFP', $finance_order->id);
            $finance_order->lessor_finance_number = createNumber('YFKFP', $finance_order->id);
            $this->order_repository->push($finance_order, 2);
            $finance_order->save();
        }

        if ($order && $data['status'] == 'review_failed') {
            DB::transaction(function () use ($order) {
                $order->status = 'pending';
                $order->update();
            });
        }
        Cache::put('settle_order_' . $order->id, 1, 10);

        return renderSuccess();
    }
}
