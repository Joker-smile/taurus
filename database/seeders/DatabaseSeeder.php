<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Department;
use App\Models\Menu;
use App\Models\Protocol;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Menu::truncate();
        Role::truncate();
        Admin::truncate();
        Department::truncate();
        Protocol::truncate();
        DB::table('role_menus')->where('id', '>', 0)->delete();

        $parent_menu = [
            'name' => '菜单栏管理',
            'path' => '/menu-management'
        ];
        $menus = [
            [
                'name' => '菜单栏列表',
                'path' => 'admin/menus/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '菜单栏创建',
                        'path' => 'admin/menus/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '菜单栏编辑',
                        'path' => 'admin/menus/update',
                        'sort' => 3

                    ],

                    [
                        'name' => '菜单栏删除',
                        'path' => 'admin/menus/delete',
                        'sort' => 4

                    ],
                ]
            ]
        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '人员角色',
            'path' => '/personnel-role'
        ];

        $menus = [
            [
                'name' => '管理员列表',
                'path' => 'admin/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '管理员创建',
                        'path' => 'admin/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '管理员编辑',
                        'path' => 'admin/update',
                        'sort' => 3
                    ],
                    [
                        'name' => '管理员删除',
                        'path' => 'admin/delete',
                        'sort' => 4
                    ],
                ]
            ],

            [
                'name' => '角色列表',
                'path' => 'admin/roles/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '角色创建',
                        'path' => 'admin/roles/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '角色编辑',
                        'path' => 'admin/roles/update',
                        'sort' => 3

                    ],

                    [
                        'name' => '角色删除',
                        'path' => 'admin/roles/delete',
                        'sort' => 4

                    ],
                ]
            ],

            [
                'name' => '部门列表',
                'path' => 'admin/departments/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '部门创建',
                        'path' => 'admin/departments/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '部门编辑',
                        'path' => 'admin/departments/update',
                        'sort' => 3

                    ],
                    [
                        'name' => '部门删除',
                        'path' => 'admin/departments/delete',
                        'sort' => 4

                    ],
                ]
            ],
        ];
        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '消息管理',
            'path' => '/message-manage'
        ];

        $menus = [

            [
                'name' => '消息列表',
                'path' => 'admin/systemMessages/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '消息创建',
                        'path' => 'admin/systemMessages/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '消息编辑',
                        'path' => 'admin/systemMessages/update',
                        'sort' => 3

                    ],

                    [
                        'name' => '消息删除',
                        'path' => 'admin/systemMessages/delete',
                        'sort' => 4

                    ],
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '系统设置',
            'path' => '/sym-config'
        ];

        $menus = [

            [
                'name' => '问题反馈',
                'path' => 'admin/feedback/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '反馈删除',
                        'path' => 'admin/feedback/delete',
                        'sort' => 2

                    ],

                    [
                        'name' => '协议管理',
                        'path' => 'admin/protocols/list',
                        'sort' => 1

                    ],

                    [
                        'name' => '协议更新',
                        'path' => 'admin/protocols/update',
                        'sort' => 2

                    ],
                ]
            ],

            [
                'name' => '协议管理',
                'path' => 'admin/protocols/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '协议更新',
                        'path' => 'admin/protocols/update',
                        'sort' => 2

                    ],
                ]
            ],

            [
                'name' => '密码修改',
                'path' => 'admin/updatePassword',
                'sort' => 1,
                'children' => [
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '设备管理',
            'path' => '/device-manage'
        ];

        $menus = [

            //类别相关
            [
                'name' => '类别列表',
                'path' => 'admin/deviceType/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '类别创建',
                        'path' => 'admin/deviceType/create',
                        'sort' => 2

                    ],

                    [
                        'name' => '类别更新',
                        'path' => 'admin/deviceType/update',
                        'sort' => 3

                    ],

                    [
                        'name' => '类别删除',
                        'path' => 'admin/deviceType/delete',
                        'sort' => 4

                    ],
                    //设备名称
                    [
                        'name' => '设备名称创建',
                        'path' => 'admin/deviceName/create',
                        'sort' => 5

                    ],
                    [
                        'name' => '设备名称删除',
                        'path' => 'admin/deviceName/delete',
                        'sort' => 6

                    ],
                    [
                        'name' => '设备名称编辑',
                        'path' => 'admin/deviceName/update',
                        'sort' => 7

                    ],

                    //组别
                    [
                        'name' => '组别删除',
                        'path' => 'admin/deviceGroup/delete',
                        'sort' => 8

                    ],
                    [
                        'name' => '组别创建',
                        'path' => 'admin/deviceGroup/create',
                        'sort' => 9

                    ],
                    [
                        'name' => '组别更新',
                        'path' => 'admin/deviceGroup/update',
                        'sort' => 10

                    ],
                ],
            ],

            //设备相关
            [
                'name' => '设备列表',
                'path' => 'admin/devices/list',
                'sort' => 11,
                'children' => [
                    [
                        'name' => '设备创建',
                        'path' => 'admin/devices/create',
                        'sort' => 12

                    ],
                    [
                        'name' => '设备更新',
                        'path' => 'admin/devices/update',
                        'sort' => 13

                    ],

                    [
                        'name' => '设备删除',
                        'path' => 'admin/devices/delete',
                        'sort' => 14

                    ],
                    [
                        'name' => '设备审核',
                        'path' => 'admin/devices/review',
                        'sort' => 15

                    ],
                ]
            ],
        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '会员列表',
            'path' => '/'
        ];

        $menus = [

            //出租方
            [
                'name' => '出租方会员',
                'path' => 'admin/lessor/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '出租方设备',
                        'path' => 'admin/lessor/devices',
                        'sort' => 2

                    ],
                    [
                        'name' => '出租方订单',
                        'path' => 'admin/lessor/orders',
                        'sort' => 3

                    ],
                    [
                        'name' => '出租方审核',
                        'path' => 'admin/lessor/statusChange',
                        'sort' => 4

                    ],
                    [
                        'name' => '出租方信息更新',
                        'path' => 'admin/lessor/update',
                        'sort' => 5

                    ],
                ]
            ],


            //承租方
            [
                'name' => '承租方会员',
                'path' => 'admin/lessee/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '承租方订单',
                        'path' => 'admin/lessee/orders',
                        'sort' => 3
                    ],
                    [
                        'name' => '承租方审核',
                        'path' => 'admin/lessee/statusChange',
                        'sort' => 4
                    ],
                    [
                        'name' => '承租方创建',
                        'path' => 'admin/lessee/create',
                        'sort' => 5
                    ],
                    [
                        'name' => '承租方信息更新',
                        'path' => 'admin/lessee/update',
                        'sort' => 6
                    ],
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        //供应链管理
        $parent_menu = [
            'name' => '供应链管理',
            'path' => '/supply-chain'
        ];

        $menus = [
            [
                'name' => '合同签订',
                'path' => 'admin/contracts/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '合同创建',
                        'path' => 'admin/contracts/create',
                        'sort' => 2

                    ],

                    [
                        'name' => '合同编辑',
                        'path' => 'admin/contracts/update',
                        'sort' => 3

                    ],

                    [
                        'name' => '合同删除',
                        'path' => 'admin/contracts/delete',
                        'sort' => 4

                    ],
                ]
            ],

            //求租信息
            [
                'name' => '求租信息',
                'path' => 'admin/rentOrders/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '求租信息创建',
                        'path' => 'admin/rentOrders/create',
                        'sort' => 2

                    ],
                    [
                        'name' => '求租信息编辑',
                        'path' => 'admin/rentOrders/update',
                        'sort' => 3

                    ],
                    [
                        'name' => '求租信息删除',
                        'path' => 'admin/rentOrders/delete',
                        'sort' => 4

                    ],
                    [
                        'name' => '求租信息提交审核',
                        'path' => 'admin/rentOrders/submitReview',
                        'sort' => 5

                    ],
                    [
                        'name' => '求租信息取消审核',
                        'path' => 'admin/rentOrders/cancelReview',
                        'sort' => 6

                    ],
                    [
                        'name' => '求租信息审核',
                        'path' => 'admin/rentOrders/review',
                        'sort' => 7

                    ],
                ]
            ],

            //租赁订单
            [
                'name' => '租赁订单待分配列表',
                'path' => 'admin/leaseOrders/pendingList',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '租赁订单分配',
                        'path' => 'admin/leaseOrders/distribute',
                        'sort' => 2
                    ],
                    [
                        'name' => '取消租赁订单',
                        'path' => 'admin/leaseOrders/cancel',
                        'sort' => 3
                    ],
                    [
                        'name' => '租赁订单回退',
                        'path' => 'admin/leaseOrders/back',
                        'sort' => 4
                    ],
                ]
            ],

            [
                'name' => '租赁订单列表',
                'path' => 'admin/leaseOrders/list',
                'sort' => 5,
                'children' => [
                    [
                        'name' => '租赁订单审核',
                        'path' => 'admin/leaseOrders/review',
                        'sort' => 6
                    ],
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '生产管理',
            'path' => '/product-manage'
        ];

        $menus = [
            [
                'name' => '生产订单列表',
                'path' => 'admin/produceOrders/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '订单回退',
                        'path' => 'admin/produceOrders/back',
                        'sort' => 2

                    ],

                    [
                        'name' => '完善作业',
                        'path' => 'admin/produceOrders/update',
                        'sort' => 3
                    ],

                    [
                        'name' => '订单审核',
                        'path' => 'admin/produceOrders/review',
                        'sort' => 4

                    ],
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '工程结算',
            'path' => '/project-settlement'
        ];

        $menus = [
            [
                'name' => '结算订单列表',
                'path' => 'admin/settleOrders/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '订单回退',
                        'path' => 'admin/settleOrders/back',
                        'sort' => 2

                    ],

                    [
                        'name' => '订单完善',
                        'path' => 'admin/settleOrders/update',
                        'sort' => 3
                    ],

                    [
                        'name' => '订单审核',
                        'path' => 'admin/settleOrders/review',
                        'sort' => 4
                    ],
                    [
                        'name' => '订单提交审核',
                        'path' => 'admin/settleOrders/submitReview',
                        'sort' => 4
                    ],

                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '财务管理',
            'path' => '/financial-manage'
        ];

        $menus = [

            //发票管理
            [
                'name' => '发票订单列表',
                'path' => 'admin/financeOrders/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '订单回退',
                        'path' => 'admin/financeOrders/back',
                        'sort' => 2

                    ],

                    [
                        'name' => '订单复核',
                        'path' => 'admin/financeOrders/reReview',
                        'sort' => 3
                    ],

                    [
                        'name' => '订单审核',
                        'path' => 'admin/financeOrders/review',
                        'sort' => 4

                    ],

                    [
                        'name' => '应收/付款导出',
                        'path' => 'admin/financeOrders/export',
                        'sort' => 5

                    ],
                ]
            ],


            //出纳管理
            [
                'name' => '出纳管理',
                'path' => 'admin/payments/list',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '创建收款单',
                        'path' => 'admin/payments/create',
                        'sort' => 2

                    ],

                    [
                        'name' => '收款单编辑',
                        'path' => 'admin/payments/update',
                        'sort' => 3
                    ],

                    [
                        'name' => '收款单删除',
                        'path' => 'admin/payments/delete',
                        'sort' => 4

                    ],
                    [
                        'name' => '收款单提交审核',
                        'path' => 'admin/payments/sublimeReview',
                        'sort' => 5
                    ],

                    [
                        'name' => '收款单审核',
                        'path' => 'admin/payments/review',
                        'sort' => 6

                    ],

                    [
                        'name' => '收款单复核',
                        'path' => 'admin/payments/reReview',
                        'sort' => 7
                    ],
                    [
                        'name' => '取消复核',
                        'path' => 'admin/payments/cancelReReview',
                        'sort' => 8
                    ],
                    [
                        'name' => '取消审核',
                        'path' => 'admin/payments/cancelReview',
                        'sort' => 9
                    ],

                    [
                        'name' => '收/付款单导出',
                        'path' => 'admin/payments/export',
                        'sort' => 10
                    ],
                ],
            ],
            [
                'name' => '账单查询',
                'path' => 'admin/payments/billList',
                'sort' => 8,
                'children' => [
                    [
                        'name' => '收/付账单导出',
                        'path' => 'admin/payments/billExport',
                        'sort' => 1
                    ],
                ]
            ],
        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        $parent_menu = [
            'name' => '各类报表',
            'path' => '/various-reports'
        ];

        $menus = [
            [
                'name' => '出租户汇总表',
                'path' => 'admin/report/lessors',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '导出',
                        'path' => 'admin/lessors/export',
                        'sort' => 2
                    ],
                ]
            ],

            [
                'name' => '承租户汇总表',
                'path' => 'admin/report/lessees',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '导出',
                        'path' => 'admin/lessees/export',
                        'sort' => 2
                    ],
                ]
            ],

            [
                'name' => '订单汇总表',
                'path' => 'admin/report/leaseOrders',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '出租方订单导出',
                        'path' => 'admin/lessorOrders/export',
                        'sort' => 2
                    ],
                    [
                        'name' => '承租方订单导出',
                        'path' => 'admin/lesseeOrders/export',
                        'sort' => 3
                    ],
                ]
            ],

            [
                'name' => '生产作业汇总表',
                'path' => 'admin/report/produceOrders',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '导出',
                        'path' => 'admin/produceOrders/export',
                        'sort' => 2
                    ],
                ]
            ],

            [
                'name' => '工程结算汇总表',
                'path' => 'admin/report/settleOrders',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '出租方订单导出',
                        'path' => 'admin/lesseeSettleOrder/export',
                        'sort' => 2
                    ],
                    [
                        'name' => '承租方订单导出',
                        'path' => 'admin/lessorSettleOrder/export',
                        'sort' => 3
                    ],
                ]
            ],
            [
                'name' => '业务员汇总表',
                'path' => 'admin/report/financeOrders',
                'sort' => 1,
                'children' => [
                    [
                        'name' => '导出',
                        'path' => 'admin/financeOrder/export',
                        'sort' => 2
                    ],
                ]
            ],

        ];

        $parent_menu = Menu::query()->create($parent_menu);
        foreach ($menus as $menu) {
            $menu['pid'] = $parent_menu->id;
            $childrens = $menu['children'];
            unset($menu['children']);
            $level_menu = Menu::query()->create($menu);
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children['pid'] = $level_menu->id;
                    Menu::query()->create($children);
                }
            }
        }

        //创建管理员角色等
        $role = Role::query()->create([
            'name' => '超级管理员'
        ]);
        $menu_ids = Menu::all()->pluck('id')->toArray();
        $role->menus()->sync($menu_ids);
        Department::query()->create([
            'depart_name' => '超级管理员',
            'position_name' => '超级管理员',
            'pid' => 1
        ]);

        Admin::query()->create(
            [
                'nick_name' => '超级管理员',
                'depart_id' => 1,
                'position_id' => 1,
                'role_id' => 1,
                'phone' => '18659546938',
                'password' => Hash::make('942jesus')
            ]
        );

        DB::table('protocols')->insert([
            [
                'key' => 'disclaimer_protocol',
                'value' => '<P>test</P>'
            ],

            [
                'key' => 'about_us',
                'value' => '<P>test</P>'
            ],

            [
                'key' => 'privacy_policy',
                'value' => '<P>test</P>'
            ],
            [
                'key' => 'cancel_protocol',
                'value' => '<P>注销协议</P>'
            ],
        ]);
    }
}
