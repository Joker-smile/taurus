<?php

namespace App\Http\Controllers\Admin;

use App\Excels\BoxWriter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Utils\Code;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceOrderController extends Controller
{
    private $order_repository;
    private $writer;
    private $payment_repository;

    public function __construct(OrderRepository $order_repository, BoxWriter $writer, PaymentRepository $payment_repository)
    {
        $this->order_repository = $order_repository;
        $this->writer = $writer;
        $this->payment_repository = $payment_repository;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'lessee_name', 'lessor_name',
            'date_range', 'lessor_finance_number', 'lessee_finance_number',
            'device_name', 'specification_model', 'lessor_id', 'lessee_id',
            'lessee_number', 'lessor_number', 'limit', 'company_name']);
        $filters['type'] = 'finance';

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name,bank_name,account_number', 'maker:id,nick_name', 'reviewer:id,nick_name'], $filters['limit'] ?? 15);
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
        if ($order->type == 'finance' && $order->status == 'reviewing') {
            DB::transaction(function () use ($order) {
                $settle_order = $this->order_repository->find($order->settle_id);
                $settle_order->status = 'reviewing';
                $settle_order->update();
                $order->delete();
            });

            return renderSuccess();
        }

        return renderError(Code::FAILED, '该订单不能回退');
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
        $order = $this->order_repository->find($data['id']);
        if ($order && $data['status'] == 'review_success') {
            $order->status = 're_reviewing';
            $order->review_time = Carbon::now()->toDateTimeString();
            $order->update();
        }

        if ($order && $data['status'] == 'review_failed') {
            $order->status = 'reviewing';
            $order->save();
        }

        return renderSuccess();
    }

    public function reReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:review_success,review_failed',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['re-review_id'] = currentAdmin()->id;
        if (Cache::has('finance_order_' . $data['id']) && $data['status'] == 'review_success') {
            return renderError(Code::FAILED, '同一笔订单,请勿频繁提交审核');
        }

        $order = $this->order_repository->update($data['id'], $data);
        if ($order && $data['status'] == 'review_failed') {
            $order->status = 'reviewing';
            $order->update();
        }

        if ($data['status'] == 'review_success') {
            $this->payment_repository->create([
                'order_id' => $order->id,
                'lessee_id' => $order->lessee_id,
                'lessor_id' => $order->lessor_id,
                'type' => 'bill_receive',
                'amount' => $order->lessee_total_price + $order->lessee_deduction_price,
                'surplus' => $order->lessee_total_price + $order->lessee_deduction_price
            ]);
            $this->payment_repository->create([
                'order_id' => $order->id,
                'lessee_id' => $order->lessee_id,
                'lessor_id' => $order->lessor_id,
                'type' => 'bill_payment',
                'amount' => $order->lessor_total_price + $order->lessor_deduction_price,
                'surplus' => $order->lessor_total_price + $order->lessor_deduction_price
            ]);
        }
        Cache::put('finance_order_' . $order->id, 1, 10);

        return renderSuccess();
    }

    public function export(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->all();
        $filters['type'] = 'finance';
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }
        $orders = $this->order_repository->get($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name,bank_name,account_number', 'maker:id,nick_name', 'reviewer:id,nick_name']);

        if (Arr::get($filters, 'order_type') == 'receive') {
            return $this->receive($orders);
        } else {
            return $this->payment($orders);
        }

    }

    protected function receive($orders): \Illuminate\Http\JsonResponse
    {
        $headers[] = ['应收款发票号', '订单状态', '订单编号', '承租方', '设备名称', '规格型号', '单位', '数量', '单价', '金额',
            '摘要', '奖扣款金额', '服务费率', '服务费', '应收款', '应收款(大写)', '创建日期', '制表人', '审核人', '审核日期', '复核人',
            '复核日期'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->lessee_finance_number,
                $this->status($order->status),
                $order->lessee_number,
                $order->lessee ? $order->lessee->company_name : '',
                $order->device_name,
                $order->specification_model,
                $order->construction_unit,
                $order->construction_value,
                format_money($order->lessee_rental_price),
                format_money($order->lessee_rent_amount),
                $order->lessee_deduction_description,
                format_money($order->lessee_deduction_price),
                $order->service_rate . '%',
                format_money($order->service_charge),
                format_money($order->lessee_total_price + $order->lessee_deduction_price),
                number2chinese(format_money($order->lessee_total_price + $order->lessee_deduction_price), true),
                (string)$order->created_at,
                $order->maker->nick_name,
                $order->reviewer->nick_name,
                $order->review_time,
                $order->reviewer->nick_name,
                (string)$order->updated_at
            ];
        }
        $name = time() . '_应收款发票导出';
        $this->excelExport($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    private function payment($orders)
    {
        $headers[] = ['应付款发票号', '订单状态', '订单编号', '出租方', '设备名称', '规格型号', '单位', '数量', '单价', '金额',
            '摘要', '奖扣款金额', '应收款', '应收款(大写)', '创建日期', '制表人', '审核人', '审核日期', '复核人', '复核日期'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->lessor_finance_number,
                $this->status($order->status),
                $order->lessor_number,
                $order->lessor ? $order->lessor->real_name : '',
                $order->device_name,
                $order->specification_model,
                $order->construction_unit,
                $order->construction_value,
                format_money($order->rental_price),
                format_money($order->lessor_total_price),
                $order->lessor_deduction_description,
                format_money($order->lessor_deduction_price),
                format_money($order->lessor_total_price + $order->lessor_deduction_price),
                number2chinese(format_money($order->lessor_total_price + $order->lessor_deduction_price), true),
                (string)$order->created_at,
                $order->maker->nick_name,
                $order->reviewer->nick_name,
                $order->review_time,
                $order->reviewer->nick_name,
                (string)$order->updated_at
            ];
        }
        $name = time() . '_应付款发票导出';
        $this->excelExport($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    private function excelExport($headers, $data, $name, $ext = 'xlsx')
    {
        $this->writer->start($name, $headers, 'app/public', $ext);
        $this->writer->addRows($data);
        $this->writer->save();
    }

    private function status($status)
    {
        switch ($status) {
            case 'reviewing':
                return '审核中';
            case're_reviewing':
                return '复核中';
            case 'review_success':
                return '审核通过';
            default:
                return '';
        }
    }
}
