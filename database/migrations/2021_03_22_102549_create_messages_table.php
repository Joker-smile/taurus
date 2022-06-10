<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->default(1)->comment('消息类型:1:用户2:系统');
            $table->integer('user_id');
            $table->integer('system_message_id')->default(0)->comment("对应的系统消息id,用户消息为0");
            $table->string('title')->default("")->comment('标题');
            $table->string('content')->default("")->comment('内容');
            $table->string('image_url')->default("")->comment('图片');
            $table->integer('order_id')->default(0)->comment('用户消息对应的订单id');
            $table->text('order_data')->nullable()->comment('用户消息对应的订单');
            $table->integer('is_read')->default(0)->comment('是否已读');
            $table->integer('is_delete')->default(0)->comment('是否删除');
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
        Schema::dropIfExists('messages');
    }
}
