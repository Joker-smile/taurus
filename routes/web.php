<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DeviceGroupController;
use App\Http\Controllers\Admin\DeviceNameController;
use App\Http\Controllers\Admin\DeviceTypeController;
use App\Http\Controllers\Admin\FeedBackController;
use App\Http\Controllers\Admin\FinanceOrderController;
use App\Http\Controllers\Admin\LeaseOrderController;
use App\Http\Controllers\Admin\LesseeController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProduceOrderController;
use App\Http\Controllers\Admin\ProtocolController;
use App\Http\Controllers\Admin\RentOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettleOrderController;
use App\Http\Controllers\Admin\SystemMessageController;
use App\Http\Controllers\Admin\LessorController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'as' => 'admin.',
    'prefix' => 'admin'
], function () {
    Route::post('login', [AdminController::class, 'login'])->name('login');
    Route::post('image/upload', [UploadController::class, 'upload'])->name('image.upload');
    Route::get('users/getProvinceCityArea', [UserController::class, 'getProvinceCityArea'])->name('users.getProvinceCityArea');
    Route::group([
        'middleware' => ['admin.auth']
    ], function () {

        //管理员相关
        Route::get('list', [AdminController::class, 'list'])->name('list');
        Route::post('create', [AdminController::class, 'create'])->name('create');
        Route::post('update', [AdminController::class, 'update'])->name('update');
        Route::post('logout', [AdminController::class, 'logout'])->name('logout');
        Route::post('delete', [AdminController::class, 'delete'])->name('delete');
        Route::post('updatePassword', [AdminController::class, 'updatePassword'])->name('updatePassword');

        //菜单相关
        Route::get('menus/list', [MenuController::class, 'list'])->name('menus.list');
        Route::post('menus/create', [MenuController::class, 'create'])->name('menus.create');
        Route::post('menus/update', [MenuController::class, 'update'])->name('menus.update');
        Route::post('menus/delete', [MenuController::class, 'delete'])->name('menus.delete');

        //角色相关
        Route::get('roles/list', [RoleController::class, 'list'])->name('roles.list');
        Route::post('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles/update', [RoleController::class, 'update'])->name('roles.update');
        Route::post('roles/delete', [RoleController::class, 'delete'])->name('roles.delete');
        Route::get('roles/getMenuByRoleId', [MenuController::class, 'getMenuByRoleId'])->name('menus.getMenuByRoleId');

        //部门相关
        Route::get('departments/list', [DepartmentController::class, 'list'])->name('departments.list');
        Route::post('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('departments/update', [DepartmentController::class, 'update'])->name('departments.update');
        Route::post('departments/delete', [DepartmentController::class, 'delete'])->name('departments.delete');

        //系统消息相关
        Route::get('systemMessages/list', [SystemMessageController::class, 'list'])->name('systemMessages.list');
        Route::post('systemMessages/create', [SystemMessageController::class, 'create'])->name('systemMessages.create');
        Route::post('systemMessages/update', [SystemMessageController::class, 'update'])->name('systemMessages.update');
        Route::post('systemMessages/delete', [SystemMessageController::class, 'delete'])->name('systemMessages.delete');
        Route::get('systemMessages/count', [SystemMessageController::class, 'count'])->name('systemMessages.count');

        //问题反馈相关
        Route::get('feedback/list', [FeedbackController::class, 'list'])->name('feedback.list');
        Route::post('feedback/delete', [FeedbackController::class, 'delete'])->name('feedback.delete');

        //协议相关
        Route::get('protocols/list', [ProtocolController::class, 'list'])->name('protocols.list');
        Route::post('protocols/update', [ProtocolController::class, 'update'])->name('protocols.update');

        //设备品牌相关
        Route::get('deviceType/list', [DeviceTypeController::class, 'list'])->name('deviceType.list');
        Route::post('deviceType/create', [DeviceTypeController::class, 'create'])->name('deviceType.create');
        Route::post('deviceType/update', [DeviceTypeController::class, 'update'])->name('deviceType.update');
        Route::post('deviceType/delete', [DeviceTypeController::class, 'delete'])->name('deviceType.delete');

        //设备组别相关
        Route::post('deviceGroup/create', [DeviceGroupController::class, 'create'])->name('deviceGroup.create');
        Route::post('deviceGroup/update', [DeviceGroupController::class, 'update'])->name('deviceGroup.update');
        Route::post('deviceGroup/delete', [DeviceGroupController::class, 'delete'])->name('deviceGroup.delete');

        //设备名称相关
        Route::post('deviceName/create', [DeviceNameController::class, 'create'])->name('deviceName.create');
        Route::get('deviceName/list', [DeviceNameController::class, 'list'])->name('deviceName.list');
        Route::post('deviceName/update', [DeviceNameController::class, 'update'])->name('deviceName.update');
        Route::post('deviceName/delete', [DeviceNameController::class, 'delete'])->name('deviceName.delete');

        //设备相关
        Route::get('devices/list', [DeviceController::class, 'list'])->name('devices.list');
        Route::post('devices/create', [DeviceController::class, 'create'])->name('devices.create');
        Route::post('devices/update', [DeviceController::class, 'update'])->name('devices.update');
        Route::post('devices/delete', [DeviceController::class, 'delete'])->name('devices.delete');
        Route::post('devices/review', [DeviceController::class, 'review'])->name('devices.review');

        //出租方用户相关
        Route::get('lessor/list', [LessorController::class, 'list'])->name('lessor.list');
        Route::get('lessor/devices', [LessorController::class, 'devices'])->name('lessor.orders');
        Route::get('lessor/orders', [LessorController::class, 'orders'])->name('lessor.devices');
        Route::post('lessor/statusChange', [LessorController::class, 'statusChange'])->name('lessor.statusChange');
        Route::post('lessor/update', [LessorController::class, 'update'])->name('lessor.update');

        //承租方用户相关
        Route::get('lessee/list', [LesseeController::class, 'list'])->name('lessee.list');
        Route::get('lessee/orders', [LesseeController::class, 'orders'])->name('lessee.devices');
        Route::post('lessee/statusChange', [LesseeController::class, 'statusChange'])->name('lessee.statusChange');
        Route::post('lessee/create', [LesseeController::class, 'create'])->name('lessee.create');
        Route::post('lessee/update', [LesseeController::class, 'update'])->name('lessee.update');

        //合同相关
        Route::get('contracts/list', [ContractController::class, 'list'])->name('contracts.list');
        Route::post('contracts/create', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('contracts/update', [ContractController::class, 'update'])->name('contracts.update');
        Route::post('contracts/delete', [ContractController::class, 'delete'])->name('contracts.delete');

        //求租信息
        Route::get('rentOrders/list', [RentOrderController::class, 'list'])->name('rentOrders.list');
        Route::post('rentOrders/create', [RentOrderController::class, 'create'])->name('rentOrders.create');
        Route::post('rentOrders/update', [RentOrderController::class, 'update'])->name('rentOrders.update');
        Route::post('rentOrders/delete', [RentOrderController::class, 'delete'])->name('rentOrders.delete');
        Route::post('rentOrders/submitReview', [RentOrderController::class, 'submitReview'])->name('rentOrders.submitReview');
        Route::post('rentOrders/cancelReview', [RentOrderController::class, 'cancelReview'])->name('rentOrders.cancelReview');
        Route::post('rentOrders/review', [RentOrderController::class, 'review'])->name('rentOrders.review');

        //租赁订单相关
        Route::get('leaseOrders/pendingList', [LeaseOrderController::class, 'pendingList'])->name('leaseOrders.pendingList');
        Route::post('leaseOrders/distribute', [LeaseOrderController::class, 'distribute'])->name('leaseOrders.distribute');
        Route::post('leaseOrders/cancel', [LeaseOrderController::class, 'cancel'])->name('leaseOrders.cancel');
        Route::post('leaseOrders/back', [LeaseOrderController::class, 'back'])->name('leaseOrders.back');
        Route::get('leaseOrders/list', [LeaseOrderController::class, 'list'])->name('leaseOrders.list');
        Route::post('leaseOrders/review', [LeaseOrderController::class, 'review'])->name('leaseOrders.review');

        //生产订单相关
        Route::get('produceOrders/list', [ProduceOrderController::class, 'list'])->name('produceOrders.list');
        Route::post('produceOrders/back', [ProduceOrderController::class, 'back'])->name('produceOrders.back');
        Route::post('produceOrders/update', [ProduceOrderController::class, 'update'])->name('produceOrders.update');
        Route::post('produceOrders/review', [ProduceOrderController::class, 'review'])->name('produceOrders.review');

        //结算订单相关
        Route::get('settleOrders/list', [SettleOrderController::class, 'list'])->name('settleOrders.list');
        Route::post('settleOrders/back', [SettleOrderController::class, 'back'])->name('settleOrders.back');
        Route::post('settleOrders/update', [SettleOrderController::class, 'update'])->name('settleOrders.update');
        Route::post('settleOrders/submitReview', [SettleOrderController::class, 'submitReview'])->name('settleOrders.submitReview');
        Route::post('settleOrders/review', [SettleOrderController::class, 'review'])->name('settleOrders.review');

        //财务发票订单相关
        Route::get('financeOrders/list', [FinanceOrderController::class, 'list'])->name('financeOrders.list');
        Route::post('financeOrders/back', [FinanceOrderController::class, 'back'])->name('financeOrders.back');
        Route::post('financeOrders/reReview', [FinanceOrderController::class, 'reReview'])->name('financeOrders.reReview');
        Route::post('financeOrders/review', [FinanceOrderController::class, 'review'])->name('financeOrders.review');
        Route::get('financeOrders/export', [FinanceOrderController::class, 'export'])->name('financeOrders.export');

        //出纳管理相关
        Route::get('payments/list', [PaymentController::class, 'list'])->name('payments.list');
        Route::post('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments/update', [PaymentController::class, 'update'])->name('payments.update');
        Route::post('payments/delete', [PaymentController::class, 'delete'])->name('payments.delete');
        Route::post('payments/sublimeReview', [PaymentController::class, 'sublimeReview'])->name('payments.sublimeReview');
        Route::post('payments/reReview', [PaymentController::class, 'reReview'])->name('payments.reReview');
        Route::post('payments/review', [PaymentController::class, 'review'])->name('payments.review');
        Route::post('payments/cancelReview', [PaymentController::class, 'cancelReview'])->name('payments.cancelReview');
        Route::post('payments/cancelReReview', [PaymentController::class, 'cancelReReview'])->name('payments.cancelReReview');
        Route::get('payments/billList', [PaymentController::class, 'billList'])->name('payments.billList');
        Route::get('payments/export', [PaymentController::class, 'cashierExport'])->name('payments.cashierExport');
        Route::get('payments/billExport', [PaymentController::class, 'billExport'])->name('payments.billExport');

        //各类报表:
        //出租方
        Route::get('report/lessors', [ReportController::class, 'lessors'])->name('report.lessors');
        Route::get('lessors/export', [ReportController::class, 'lessorExport'])->name('report.lessorExport');

        //承租方
        Route::get('report/lessees', [ReportController::class, 'lessees'])->name('report.lessees');
        Route::get('lessees/export', [ReportController::class, 'lesseeExport'])->name('report.lesseeExport');

        //订单汇总
        Route::get('report/leaseOrders', [ReportController::class, 'leaseOrders'])->name('report.leaseOrders');
        Route::get('lessorOrders/export', [ReportController::class, 'lessorOrderExport'])->name('report.lessorOrderExport');
        Route::get('lesseeOrders/export', [ReportController::class, 'lesseeOrderExport'])->name('report.lesseeOrderExport');

        //生产作业
        Route::get('report/produceOrders', [ReportController::class, 'produceOrders'])->name('report.produceOrders');
        Route::get('produceOrders/export', [ReportController::class, 'produceOrderExport'])->name('report.produceOrderExport');

        //结算订单汇总
        Route::get('report/settleOrders', [ReportController::class, 'settleOrders'])->name('report.settleOrders');
        Route::get('lesseeSettleOrder/export', [ReportController::class, 'lesseeSettleOrderExport'])->name('report.lesseeSettleOrderExport');
        Route::get('lessorSettleOrder/export', [ReportController::class, 'lessorSettleOrderExport'])->name('report.lessorSettleOrderExport');

        //业务员业绩汇总
        Route::get('report/financeOrders', [ReportController::class, 'financeOrders'])->name('report.financeOrders');
        Route::get('financeOrder/export', [ReportController::class, 'financeOrderExport'])->name('report.financeOrderExport');

    });
});
