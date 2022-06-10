<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('admin_id');
            $table->string('type', 11)->default('lessor')->comment('用户类型:lessor:出租方，lessee:承租方');
            $table->string('contract_number', 15)->comment('合同编号');
            $table->integer('sign_year')->comment('签订年度');
            $table->string('start_time')->comment('合同开始时间');
            $table->string('end_time')->comment('合同结束时间');
            $table->text('content')->nullable()->comment('合同内容');
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
        Schema::dropIfExists('contracts');
    }
}
