<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('群组名称');
            $table->integer('status')->default(1)->comment('0:不启用,1:启用');
            $table->integer('type_id')->comment('类型id');
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
        Schema::dropIfExists('device_groups');
    }
}
