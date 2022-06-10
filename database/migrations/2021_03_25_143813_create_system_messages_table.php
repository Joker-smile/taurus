<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->default(1)->comment('消息类型:1:通知,2:营销');
            $table->integer('client_type')->default(1)->comment('消息类型:1:安卓,2:ios,0:全部');
            $table->integer('user_ids')->default(0)->comment('发送对象:0:全部');
            $table->string('status', 15)->default('pending')->comment('状态:pending:发送中，success:发送成功,failed:发送失败');
            $table->string('title')->default("")->comment('标题');
            $table->string('content')->default("")->comment('内容');
            $table->string('image_url')->default("")->comment('图片');
            $table->integer('push_time')->default(0)->comment("推送时间");
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
        Schema::dropIfExists('system_messages');
    }
}
