<?php

namespace App\Http\Controllers\Admin;

use App\Excels\BoxWriter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PaymentResource;
use App\Models\Payment;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Utils\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    private $payment_repository;
    private $order_repository;
    private $writer;

    public function __construct(PaymentRepository $payment_repository,
                                OrderRepository $order_repository,
                                BoxWriter $writer)
    {
        $this->payment_repository = $payment_repository;
        $this->order_repository = $order_repository;
        $this->writer = $writer;
    }

    public function list(Request $request)
    {
        $filters = $request->only(['status', 'payment_number', 'collection_number', 'lessor_number',
            'lessee_number', 'payer', 'date_range', 'limit', 'type', 'company_name']);

        if (!Arr::get($filters, 'type')) {
            $filters['type'] = 'receive';
        }

        $payments = $this->payment_repository->paginate($filters, ['order:id,lessor_number,lessee_number', 'maker:id,nick_name',
            'reReviewer:id,nick_name', 'reviewer:id,nick_name', 'lessor:id,real_name', 'lessee:id,real_name,company_name'], $filters['limit'] ?? 15);
        $list = PaymentResource::collection($payments['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $payments['page_info']]);

        return renderSuccess($result);
    }

    public function create(Request $request)
    {
        $rules = [
            'order_id' => 'required|integer',
            'payer' => 'required|string',
            'bank_name' => 'required|string',
            'account' => 'required|string',
            'amount' => 'required|string',
            'remark' => 'required|string',
            'type' => 'required|string|in:receive,payment',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $order = $this->order_repository->with(['payments'])->find($data['order_id']);
        $amount = $order->payments()->where('type', $data['type'])->sum('amount');
        if ($data['type'] == 'receive') {
            $total = $order->lessee_total_price + $order->lessee_deduction_price;
        } else {
            $total = $order->lessor_total_price + $order->lessor_deduction_price;
        }

        $data['amount'] = $data['amount'] * 100;
        if (($amount + $data['amount']) > $total) {
            return renderError(Code::FAILED, '提交的金额不合法,订单需支付:' . format_money($total) . '元,已经支付:' . format_money($amount) . '元,还需要支付:' . format_money($total - $amount) . '元');
        }

        $data['lessee_id'] = $order->lessee_id;
        $data['lessor_id'] = $order->lessor_id;
        $data['surplus'] = $total - $data['amount'] - $amount;
        $data['make_id'] = currentAdmin()->id;
        $payment = $this->payment_repository->create($data);
        $payment->payment_number = createNumber('FK', $payment->id);
        $payment->collection_number = createNumber('SK', $payment->id);
        $payment->save();

        return renderSuccess();
    }

    public function update(Request $request)
    {
        $rules = [
            'order_id' => 'required|integer',
            'payer' => 'string',
            'bank_name' => 'string',
            'account' => 'string',
            'amount' => 'required|string',
            'remark' => 'string',
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $data['amount'] = $data['amount'] * 100;
        $payment = $this->payment_repository->find($data['id']);
        $order = $this->order_repository->with(['payments'])->find($data['order_id']);
        $payments = $order->payments()->where('type', $payment->type)->get();
        $payments = $payments->reject(function ($value) use ($data) {
            return $value->id == $data['id'];
        });

        $amount = $payments->sum('amount');
        if ($payment->type == 'receive') {
            $total = $order->lessee_total_price + $order->lessee_deduction_price;
        } else {
            $total = $order->lessor_total_price + $order->lessor_deduction_price;
        }

        if (($amount + $data['amount']) > $total) {
            return renderError(Code::FAILED, '提交的金额不合法,订单需支付:' . format_money($total) . '元,已经支付:' . format_money($amount) . '元,当前可填金额最大只能为:' . format_money($total - $amount) . '元');
        }
        $data['lessee_id'] = $order->lessee_id;
        $data['lessor_id'] = $order->lessor_id;
        $data['surplus'] = $total - $data['amount'] - $amount;
        $this->payment_repository->update($data['id'], $data);

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

        $this->payment_repository->delete($data['id']);

        return renderSuccess();
    }

    public function sublimeReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Payment::query()
            ->where('id', $data['id'])
            ->where('status', 'pending')
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

        Payment::query()->where('id', $data['id'])
            ->where('status', 'reviewing')
            ->update(['status' => 'pending', 'review_id' => 0]);

        return renderSuccess();
    }

    public function cancelReReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        Payment::query()->where('id', $data['id'])
            ->where('status', 're-reviewing')
            ->update(['status' => 'reviewing', 're-review_id' => 0]);

        return renderSuccess();
    }

    public function review(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:review_failed,re-reviewing'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['status'] == 'review_failed') {
            $data['status'] = 'pending';
        }

        Payment::query()
            ->where('id', $data['id'])
            ->where('status', 'reviewing')
            ->update([
                'status' => $data['status'],
                'review_id' => currentAdmin()->id
            ]);

        return renderSuccess();
    }

    public function reReview(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|string|in:review_failed,review_success'
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        if ($data['status'] == 'review_failed') {
            $data['status'] = 'reviewing';
        }

        if (Cache::has('payment_' . $data['id'])) {
            return renderError(Code::FAILED, '同一笔收/付款单,请勿频繁提交审核');
        }

        Payment::query()
            ->where('id', $data['id'])
            ->where('status', 're-reviewing')
            ->update([
                'status' => $data['status'],
                're-review_id' => currentAdmin()->id
            ]);

        $payment = $this->payment_repository->find($data['id']);
        if ($payment->type == 'receive') {
            $bill = Payment::query()->where('type', 'bill_receive')
                ->where('order_id', $payment->order_id)->first();
        } else {
            $bill = Payment::query()->where('type', 'bill_payment')
                ->where('order_id', $payment->order_id)->first();
        }

        if ($data['status'] == 'review_success' && $bill) {
            $bill->surplus -= $payment->amount;
            $bill->update();
        }

        $order = $payment->order;
        $amount = $order->payments()->sum('amount');
        if ($payment->type == 'payment') {
            if ($amount != ($order->lessor_total_price + $order->lessor_deduction_price)) {
                $this->order_repository->push($order, 3);
            } else {
                $this->order_repository->push($order, 4);
            }
        }

        Cache::put('payment_' . $payment->id, 1, 10);
        return renderSuccess();
    }

    public function billList(Request $request)
    {
        $filters = $request->only(['lessor_number', 'lessee_number', 'lessor_name',
            'date_range', 'type', 'limit', 'company_name', 'lessee_id', 'lessor_id']);
        if (!Arr::get($filters, 'type')) {
            $filters['type'] = 'bill_receive';
        }

        $payments = $this->payment_repository->paginate($filters, ['order:id,lessor_number,lessee_number', 'maker:id,nick_name',
            'reReviewer:id,nick_name', 'reviewer:id,nick_name', 'lessor:id,real_name', 'lessee:id,real_name,company_name'], $filters['limit'] ?? 15);
        $list = PaymentResource::collection($payments['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $payments['page_info']]);

        return renderSuccess($result);
    }

    public function cashierExport(Request $request)
    {
        $filters = $request->all();
        if (!Arr::get($filters, 'type')) {
            $filters['type'] = 'receive';
        }
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }
        $payments = $this->payment_repository->get($filters, ['order:id,lessor_number,lessee_number', 'maker:id,nick_name',
            'reReviewer:id,nick_name', 'reviewer:id,nick_name', 'lessor:id,real_name', 'lessee:id,real_name,company_name']);

        if ($filters['type'] == 'receive') {
            return $this->receive($payments);
        } else {
            return $this->payment($payments);
        }
    }

    private function receive($payments)
    {
        $headers[] = ['收款单号', '订单状态', '订单编号', '付款单位', '开户行', '账号', '摘要', '金额',
            '金额(大写)', '创建日期', '制表人', '审核人', '复核人'];
        $data = [];
        foreach ($payments as $payment) {
            $data[] = [
                $payment->collection_number,
                $this->status($payment->status),
                $payment->order ? $payment->order->lessee_number : '',
                $payment->payer,
                $payment->bank_name,
                $payment->account,
                $payment->remark,
                format_money($payment->amount),
                number2chinese(format_money($payment->amount), true),
                (string)$payment->created_at,
                $payment->maker ? $payment->maker->nick_name : '',
                $payment->reviewer ? $payment->reviewer->nick_name : '',
                $payment->reReviewer ? $payment->reReviewer->nick_name : '',
            ];
        }
        $name = time() . '_出纳收款导出';
        $this->excelExport($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    private function payment($payments)
    {
        $headers[] = ['付款单号', '订单状态', '订单编号', '付款单位', '开户行', '账号', '摘要', '金额',
            '金额(大写)', '创建日期', '制表人', '审核人', '复核人'];
        $data = [];
        foreach ($payments as $payment) {
            $data[] = [
                $payment->payment_number,
                $this->status($payment->status),
                $payment->order ? $payment->order->lessor_number : '',
                $payment->payer,
                $payment->bank_name,
                $payment->account,
                $payment->remark,
                format_money($payment->amount),
                number2chinese(format_money($payment->amount), true),
                (string)$payment->created_at,
                $payment->maker ? $payment->maker->nick_name : '',
                $payment->reviewer ? $payment->reviewer->nick_name : '',
                $payment->reReviewer ? $payment->reReviewer->nick_name : '',
            ];
        }
        $name = time() . '_出纳付款导出';
        $this->excelExport($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function billExport(Request $request)
    {
        $filters = $request->all();
        if (!Arr::get($filters, 'type')) {
            $filters['type'] = 'bill_receive';
        }
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }
        $payments = $this->payment_repository->get($filters, ['order:id,lessor_number,lessee_number', 'maker:id,nick_name',
            'reReviewer:id,nick_name', 'reviewer:id,nick_name', 'lessor:id,real_name', 'lessee:id,real_name,company_name']);

        if ($filters['type'] == 'bill_receive') {
            return $this->billReceive($payments);
        }

        return $this->billPayment($payments);
    }

    private function billReceive($payments)
    {
        $headers[] = ['关联订单编号', '交易对手(出租方)', '承租方', '应收金额', '已收金额', '余额', '日期'];
        $data = [];
        foreach ($payments as $payment) {
            $data[] = [
                $payment->order ? $payment->order->lessee_number : '',
                $payment->lessor ? $payment->lessor->real_name : '',
                $payment->lessee ? $payment->lessee->company_name : '',
                format_money($payment->amount),
                format_money($payment->amount - $payment->surplus),
                format_money($payment->surplus),
                (string)$payment->created_at,
            ];
        }
        $name = time() . '_应收账款导出';
        $this->excelExport($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    private function billPayment($payments)
    {
        $headers[] = ['关联订单编号', '交易对手(承租方)', '出租方', '应付金额', '已付金额', '余额', '日期'];

        $data = [];
        foreach ($payments as $payment) {
            $data[] = [
                $payment->order ? $payment->order->lessor_number : '',
                $payment->lessee ? $payment->lessee->company_name : '',
                $payment->lessor ? $payment->lessor->real_name : '',
                format_money($payment->amount),
                format_money($payment->amount - $payment->surplus),
                format_money($payment->surplus),
                (string)$payment->created_at,
            ];
        }
        $name = time() . '_应付账款导出';
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
        //pending:等待审核,reviewing:审核中,re-reviewing:复核中,review_success:审核通过,review_failed:审核失败
        switch ($status) {
            case 'pending':
                return '等待审核';
            case 'reviewing':
                return '审核中';
            case're-reviewing':
                return '复核中';
            case 'review_failed':
                return '审核失败';
            case 'review_success':
                return '审核通过';
            default:
                return '';
        }
    }
}
