<?php

namespace App\Http\Controllers\Admin;

use App\Excels\BoxWriter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    private $user_repository;
    private $writer;
    private $order_repository;

    public function __construct(UserRepository $user_repository,
                                BoxWriter $writer,
                                OrderRepository $order_repository)
    {
        $this->user_repository = $user_repository;
        $this->writer = $writer;
        $this->order_repository = $order_repository;
    }

    public function lessors(Request $request)
    {
        $filters = $request->only(['real_name', 'id_card_number', 'phone', 'bank_name', 'account_number',
            'company_address', 'credit_code', 'limit']);
        $filters['type'] = 'lessor';
        $lessors = $this->user_repository->paginate($filters, '', $filters['limit'] ?? 15, ['id', 'real_name', 'id_card_number', 'credit_code', 'phone', 'bank_name', 'account_number']);

        return renderSuccess($lessors);
    }

    public function lessorExport(Request $request)
    {
        $filters = $request->only(['real_name', 'id_card_number', 'phone', 'bank_name', 'account_number', 'company_address', 'credit_code', 'limit', 'ids']);
        $filters['type'] = 'lessor';
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $lessors = $this->user_repository->get($filters, '', ['id', 'real_name', 'id_card_number', 'credit_code', 'phone', 'bank_name', 'account_number']);
        $headers[] = ['出租户名称', '身份证号码', '营业执照号', '电话号码', '开户银行', '银行卡号'];
        $data = [];
        foreach ($lessors as $lessor) {
            $data[] = [
                $lessor->real_name,
                $lessor->id_card_number,
                $lessor->credit_code,
                $lessor->phone,
                $lessor->bank_name,
                $lessor->account_number,
            ];
        }
        $name = time() . '_出租会员导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function lessees(Request $request)
    {
        $filters = $request->only(['real_name', 'phone', 'company_name', 'credit_code', 'limit', 'company_address']);
        $filters['type'] = 'lessee';
        $lessors = $this->user_repository->paginate($filters, '', $filters['limit'] ?? 15, ['id', 'real_name', 'credit_code', 'phone', 'company_name']);

        return renderSuccess($lessors);
    }

    public function lesseeExport(Request $request)
    {
        $filters = $request->only(['real_name', 'phone', 'company_name', 'credit_code', 'ids']);
        $filters['type'] = 'lessee';
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $lessees = $this->user_repository->get($filters, '', ['id', 'real_name', 'credit_code', 'phone', 'company_name']);
        $headers[] = ['单位名称', '营业执照号', '联系人', '联系电话'];
        $data = [];
        foreach ($lessees as $lessee) {
            $data[] = [
                $lessee->company_name,
                $lessee->credit_code,
                $lessee->real_name,
                $lessee->phone,
            ];
        }
        $name = time() . '_承租会员导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function leaseOrders(Request $request)
    {
        $filters = $request->only(['status', 'project_name', 'lessor_name', 'lessee_name', 'construction_site',
            'lessor_number', 'lessee_number', 'lessor_contract_number', 'limit', 'company_address', 'company_name']);
        $filters['type'] = 'lease';

        if (!Arr::get($filters, 'status')) {
            $filters['status'] = ['reviewing', 'review_success'];
        }

        if (Arr::get($filters, 'lessor_number')) {
            $filters['id'] = numberToId($filters['lessor_number'], 'lease');
        }

        if (Arr::get($filters, 'lessee_number')) {
            $filters['id'] = numberToId($filters['lessee_number'], 'lease');
        }

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name,company_name', 'lessor:id,real_name', 'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function lessorOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'lease';

        if (!Arr::get($filters, 'status')) {
            $filters['status'] = ['reviewing', 'review_success'];
        }

        if (Arr::get($filters, 'lessor_number')) {
            $filters['id'] = numberToId($filters['lessor_number'], 'lease');
        }

        if (Arr::get($filters, 'lessee_number')) {
            $filters['id'] = numberToId($filters['lessee_number'], 'lease');
        }

        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $orders = $this->order_repository->get($filters, ['lessor']);
        $headers[] = ['订单号', '订单来源', '出租方', '合同编号', '工程名称', '施工地', '总单价', '总价'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->lessor_number,
                $order->salesman ?? '平台',
                $order->lessor ? $order->lessor->real_name : '',
                $order->lessor_contract_number,
                $order->project_name,
                $order->construction_site,
                format_money($order->lessor_total_unit_price),
                format_money($order->lessor_total_price),
            ];
        }
        $name = time() . '_出租方与平台订单导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function lesseeOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'lease';

        if (!Arr::get($filters, 'status')) {
            $filters['status'] = ['reviewing', 'review_success'];
        }

        if (Arr::get($filters, 'lessee_number')) {
            $filters['id'] = numberToId($filters['lessee_number'], 'lease');
        }
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $orders = $this->order_repository->get($filters, ['lessee']);
        $headers[] = ['订单来源', '施工方', '订单号', '合同编号', '单价', '金额', '服务费率%', '服务费', '总金额'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->salesman ?? '平台',
                $order->lessee ? $order->lessee->company_name : '',
                $order->lessee_number ?? '',
                $order->lessee_contract_number ?? '',
                format_money($order->lessee_rental_price),
                format_money($order->lessee_rent_amount),
                $order->service_rate . '%',
                format_money($order->service_charge),
                format_money($order->lessee_total_price)
            ];
        }
        $name = time() . '_承租方与平台订单导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function produceOrders(Request $request)
    {
        $filters = $request->only(['status', 'produce_number', 'lessee_name', 'lessor_name',
            'construction_site', 'lessee_number', 'project_name', 'limit', 'company_address', 'company_name']);
        $filters['type'] = 'produce';
        if (Arr::get($filters, 'produce_number')) {
            $filters['id'] = numberToId($filters['produce_number'], 'produce');
        }

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function produceOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'produce';
        if (Arr::get($filters, 'produce_number')) {
            $filters['id'] = numberToId($filters['produce_number'], 'produce');
        }
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $orders = $this->order_repository->get($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name']);
        $headers[] = ['生产管理单号', '订单号', '承租方', '出租方', '项目名称', '施工地', '实际作业量', '现场作业图'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->produce_number ?? '',
                $order->lessee_number ?? '',
                $order->lessee ? $order->lessee->company_name : '',
                $order->lessor ? $order->lessor->real_name : '',
                $order->project_name ?? '',
                $order->construction_site ?? '',
                $order->construction_value . $order->construction_unit,
                implode(';', $order->construction_images ?? []),
            ];
        }
        $name = time() . '_生产作业导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function settleOrders(Request $request)
    {
        $filters = $request->only(['status', 'lessee_settle_number', 'lessor_settle_number', 'lessee_name', 'lessor_name',
            'construction_site', 'project_name', 'limit', 'company_address', 'company_name']);
        $filters['type'] = 'settle';
        if (Arr::get($filters, 'settle_number')) {
            $filters['id'] = numberToId($filters['settle_number'], 'settle');
        }

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function lessorSettleOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'settle';
        if (Arr::get($filters, 'lessor_settle_number')) {
            $filters['id'] = numberToId($filters['lessor_settle_number'], 'settle');
        }
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $orders = $this->order_repository->get($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent']);

        $headers[] = ['结算单号', '出租方', '工程名称', '工程量', '施工地', '单价', '奖扣款', '服务费率', '服务费', '应付合计'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->lessor_settle_number,
                $order->lessor ? $order->lessor->real_name : '',
                $order->project_name,
                $order->construction_value . $order->construction_unit,
                $order->construction_site ?? '',
                format_money($order->rental_price),
                format_money($order->lessor_deduction_price),
                $order->service_rate . '%',
                format_money($order->service_charge),
                format_money($order->lessor_total_price + $order->lessor_deduction_price),
            ];
        }
        $name = time() . '_出租方与平台结算导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function lesseeSettleOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'settle';
        if (Arr::get($filters, 'lessee_settle_number')) {
            $filters['id'] = numberToId($filters['lessee_settle_number'], 'settle');
        }

        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }

        $orders = $this->order_repository->get($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent']);
        $headers[] = ['结算单号', '承租方', '工程名称', '工程量', '单价', '其他', '奖扣款', '租金金额', '服务费率', '服务费', '应收合计'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [$order->lessor_settle_number,
                $order->lessee ? $order->lessee->company_name : '',
                $order->project_name,
                $order->construction_value . $order->construction_unit,
                format_money($order->lessee_rental_price),
                $order->other_request,
                format_money($order->lessee_deduction_price),
                format_money($order->lessee_rent_amount),
                $order->service_rate . '%',
                format_money($order->service_charge),
                format_money($order->lessee_total_price + $order->lessee_deduction_price),];
        }

        $name = time() . '_承租方与平台结算导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    public function financeOrders(Request $request)
    {
        $filters = $request->only(['status', 'lessee_number', 'construction_site', 'project_name', 'limit', 'company_address', 'company_name', 'salesman']);
        $filters['type'] = 'finance';

        $orders = $this->order_repository->paginate($filters, ['lessee:id,real_name,company_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent'], $filters['limit'] ?? 15);
        $list = OrderResource::collection($orders['list']);
        $result = array_merge(['list' => $list->toArray($request)], ['page_info' => $orders['page_info']]);

        return renderSuccess($result);
    }

    public function financeOrderExport(Request $request)
    {
        $filters = $request->all();
        $filters['type'] = 'finance';
        if (Arr::get($filters, 'ids')) {
            $filters['id'] = explode(',', $filters['ids']);
        }
        $orders = $this->order_repository->get($filters, ['lessee:id,real_name,company_name', 'lessor:id,real_name',
            'maker:id,nick_name', 'reviewer:id,nick_name', 'rent']);

        $headers[] = ['业务员名称', '订单编号', '工程名称', '施工地', '施工量', '总单价', '金额'];
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->salesman ?? '平台',
                $order->lessee_number,
                $order->project_name,
                $order->construction_site,
                $order->construction_value . $order->construction_unit,
                format_money($order->lessee_rental_price),
                format_money($order->lessee_rent_amount + $order->lessee_deduction_price),
            ];
        }
        $name = time() . '_业务员业绩导出';
        $this->export($headers, $data, $name);
        return renderSuccess([
            'download_url' => Storage::disk('public')->url($name . ".xlsx")
        ]);
    }

    private function export($headers, $data, $name, $ext = 'xlsx')
    {
        $this->writer->start($name, $headers, 'app/public', $ext);
        $this->writer->addRows($data);
        $this->writer->save();
    }

}
