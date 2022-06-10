<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id')->default(0);
            $table->integer('client_type')->default(0)->comment('客户端类型:1:安卓,2:ios');
            $table->string('type', 11)->default('lessor')->comment('用户类型:lessor:出租方，lessee:承租方');
            $table->string('phone')->unique()->comment('手机号码');
            $table->string('password')->comment('密码');
            $table->string('real_name', 15)->comment('真实姓名');
            $table->string('id_card_number', 18)->default(0)->comment('身份证号码');
            $table->string('id_card_face')->default("")->comment('身份证正面');
            $table->string('id_card_back')->default("")->comment('身份证反面');
            $table->string('certify_id', 50)->default("")->comment('实人认证唯一标识');
            $table->string('bank_name', 15)->default("")->comment('开户行');
            $table->string('account_number', 50)->default("")->comment('银行卡号');
            $table->string('bank_license', 50)->default("")->comment('开户行许可证');
            $table->string('business_license')->default("")->comment('营业执照');
            $table->string('company_name')->default("")->comment('单位名称');
            $table->string('credit_code')->default("")->comment('信用代码');
            $table->string('company_address')->default("")->comment('公司所在区域');
            $table->string('province')->default("")->comment('省');
            $table->string('city')->default("")->comment('市');
            $table->string('area')->default("")->comment('区');
            $table->string('address')->default("")->comment('详细地址');
            $table->string('invoicing_phone')->default("")->comment('开票电话');
            $table->integer('company_address_code')->default(0)->comment('公司所在区域编码');
            $table->string('register_capital')->default("")->comment('注册资本');
            $table->string('employees')->default("")->comment('从业人数');
            $table->string('status', 15)->default('active')->comment('状态');
            $table->string('avatar')->default('http://misu-yhq.oss-cn-shenzhen.aliyuncs.com/dlg/app/default_avatar.png')->comment('头像');
            $table->string('device_token', 150)->default("")->comment('设备token');
            $table->integer('is_push_message')->default(1)->unsigned()->comment('是否推送消息');
            $table->string('review_message')->default("")->comment('审核信息');
            $table->string('remark')->default("")->comment('标记/其他要求');
            $table->timestamps();
        });
        $init = file_get_contents(__DIR__ . '/init.sql');
        DB::unprepared($init);

        $area = file_get_contents(__DIR__ . '/area.sql');
        DB::unprepared($area);

        $city = file_get_contents(__DIR__ . '/city.sql');
        DB::unprepared($city);

        $province = file_get_contents(__DIR__ . '/province.sql');
        DB::unprepared($province);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
