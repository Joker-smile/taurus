<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->comment('财务发票审核过的订单id');
            $table->integer('lessee_id')->comment('承租方id');
            $table->integer('lessor_id')->comment('出租方id');
            $table->string('payment_number')->default("")->comment('付款编号');
            $table->string('collection_number')->default("")->comment('收款编号');
            $table->string('payer')->default("")->comment('付款单位');
            $table->string('bank_name')->default("")->comment('开户行');
            $table->string('account')->default("")->comment('账号');
            $table->integer('amount')->default(0)->comment('金额');
            $table->integer('surplus')->default(0)->comment('余额');
            $table->string('remark')->default("")->comment('摘要');
            $table->string('status')->default('pending')->comment('状态:pending:等待审核,reviewing:审核中,re-reviewing:复核中,review_success:审核通过,review_failed:审核失败');
            $table->integer('review_id')->default(0)->comment('审核人');
            $table->integer('re-review_id')->default(0)->comment('复核人');
            $table->integer('make_id')->default(0)->comment('制表人');
            $table->string('type')->default('receive')->comment('类型:receive:收款,payment:付款,bill_receive:应收账单,bill_payment:应付账单');
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
        Schema::dropIfExists('payments');
    }
}
