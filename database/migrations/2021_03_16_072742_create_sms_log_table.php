<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->default(1)->comment('类型:1:注册,2:忘记密码3:修改手机号码');
            $table->string('phone', 11)->comment('手机号码');
            $table->integer('sms_code')->comment('验证码');
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
        Schema::dropIfExists('sms_log');
    }
}
