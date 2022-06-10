<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->default(1)->comment('1：功能需求，2：BUG，3：产品体验，4：其他');
            $table->integer('client_type')->default(1)->comment('1：安卓，2：IOS');
            $table->integer('user_id');
            $table->string('describe')->comment('问题描述');
            $table->text('images')->nullable()->comment('图片');
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
        Schema::dropIfExists('feedbacks');
    }
}
