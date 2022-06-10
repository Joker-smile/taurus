<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nick_name')->comment('昵称');
            $table->string('phone', 15)->comment('账号');
            $table->string('avatar')->default('https://taurus-lease.oss-cn-shanghai.aliyuncs.com/idCards/16164810151278793835.jpg')->comment('头像');
            $table->string('password')->comment('密码');
            $table->integer('depart_id')->default(0)->comment('部门id');
            $table->integer('position_id')->default(0)->comment('职位id');
            $table->integer('role_id')->default(0)->comment('角色id');
            $table->string('status')->default('active')->comment('状态');
            $table->string('token')->default('')->comment('登录token');
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
        Schema::dropIfExists('admins');
    }
}
