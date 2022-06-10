<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('is_delete')->default(0)->comment('0:否，1:是');
            $table->integer('review_admin_id')->default(0)->comment('审核人');
            $table->string('number')->default("")->comment('编号');
            $table->integer('type_id')->comment('设备类别');
            $table->integer('group_id')->comment('设备组别');
            $table->string('name')->comment('设备名称');
            $table->string('specification_model')->default("")->comment('规格型号');
            $table->string('brand_name')->default("")->comment('品牌名称');
            $table->string('license_number')->default("")->comment('牌照号');
            $table->string('new_rate')->default("")->comment('成新率');
            $table->string('remark')->default("")->comment('备注');
            $table->string('review_message')->default("")->comment('审核信息');
            $table->string('status')->default('reviewing')->comment('状态');
            $table->text('images')->comment('图片');
            $table->string('review_time')->default("")->comment('审核时间');
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
        Schema::dropIfExists('devices');
    }
}
