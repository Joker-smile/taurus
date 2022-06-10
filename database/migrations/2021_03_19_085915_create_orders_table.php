<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_id')->default(0)->index()->comment('审核人');
            $table->integer('re-review_id')->default(0)->index()->comment('复审核人');
            $table->integer('device_id')->default(0)->index()->comment('设备id');
            $table->integer('make_id')->index()->comment('制表人');
            $table->integer('rent_id')->index()->default(0)->comment('求租订单id');
            $table->integer('lease_id')->index()->default(0)->comment('租赁订单id');
            $table->integer('produce_id')->index()->default(0)->comment('生产订单id');
            $table->integer('settle_id')->index()->default(0)->comment('结算订单id');
            $table->integer('lessee_id')->index()->default(0)->comment('承租方id');
            $table->integer('lessor_id')->index()->default(0)->comment('出租方id');
            $table->integer('device_type_id')->index()->default(0)->comment('设备类型id');
            $table->integer('device_group_id')->index()->default(0)->comment('设备组别id');
            $table->string('project_name')->comment('工程名称');
            $table->string('construction_site')->comment('施工地');
            $table->string('lessee_contract_number')->default("")->comment('承租方合同编号');
            $table->string('lessor_contract_number')->default("")->comment('出租方合同编号');
            $table->float('construction_value')->default(0)->comment('施工量');
            $table->float('service_rate')->default(0)->comment('服务费率');
            $table->string('construction_unit')->comment('施工单位');
            $table->integer('rental_price')->default(0)->comment('租赁单价');
            $table->integer('rental_total')->default(0)->comment('租赁价');
            $table->integer('lump_sum_price')->default(0)->comment('包干单价');
            $table->string('other_request')->default('')->comment('其他要求');
            $table->string('new_rate')->default('')->comment('成新率');
            $table->integer('lessor_deduction_price')->default(0)->comment('出租方扣款金额');
            $table->integer('lessee_deduction_price')->default(0)->comment('承租方扣款金额');
            $table->string('lessor_deduction_description')->default("")->comment('出租方扣款描述');
            $table->string('lessee_deduction_description')->default("")->comment('承租方扣款描述');
            $table->string('type')->default('rent')->comment('类型:rent:求租订单,lease:租赁订单,produce:生产订单,settle:结算订单,finance:财务订单');
            $table->string('status')->default('pending')->comment('状态:pending:等待发布/分配,reviewing:审核中,
            review_success:审核通过,re-reviewing:复核中,review_failed:审核失败,invalid:无效');
            $table->string('release_time')->default("")->comment('发布时间');
            $table->string('review_time')->default("")->comment('审核时间');
            $table->string('entry_time')->default("")->comment('进场时间');
            $table->string('produce_start_time')->default("")->default("")->comment('设备作业开始时间');
            $table->string('produce_end_time')->default("")->default("")->comment('设备作业结束时间');
            $table->string('brand_name')->default('')->comment('品牌名称');
            $table->string('device_name')->comment('设备名称');
            $table->string('specification_model')->default("")->comment('规格型号');
            $table->integer('is_delete')->default(0)->comment('是否删除');
            $table->string('salesman')->default("")->default('业务员名称');
            $table->integer('device_quantity')->default(1)->comment('设备数量');
            $table->text('construction_images')->nullable()->comment('施工图片');
            $table->string('review_message')->default("")->comment('审核信息');
            $table->string('remark')->default("")->comment('备注');

            $table->string('rent_number')->index()->default("")->comment('求租编号');
            $table->string('lessee_number')->index()->default("")->comment('承租方租赁编号');
            $table->string('lessor_number')->index()->default("")->comment('出租方租赁编号');
            $table->string('produce_number')->index()->default("")->comment('生产编号');
            $table->string('lessee_settle_number')->index()->default("")->comment('承租方结算编号');
            $table->string('lessor_settle_number')->index()->default("")->comment('出租方结算编号');
            $table->string('lessee_finance_number')->index()->default("")->comment('承租方应收款编号');
            $table->string('lessor_finance_number')->index()->default("")->comment('出租方应付款编号');
            $table->string('subsidiary')->default("")->comment('子公司');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
