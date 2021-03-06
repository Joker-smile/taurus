<?php

return [
    'initFaceVerify' => [
        401 => '参数非法',
        402 => '应用配置不存在',
        404 => '认证场景配置不存在',
        410 => '未开通服务',
        411 => 'RAM无权限',
        412 => '欠费中',
        414 => '设备类型不支持',
        415 => 'SDK版本不支持',
        416 => '系统版本不支持',
        417 => '无法使用刷脸服务',
        418 => '刷脸失败次数过多',
        500 => '系统错误',
    ],

    'describeFaceVerify' => [
        200 => '认证通过',
        201 => '姓名和身份证不一致',
        202 => '查询不到身份信息',
        203 => '查询不到照片或照片不可用',
        204 => '人脸比对不一致',
        205 => '活体检测存在风险',
        206 => '业务策略限制',
        207 => '人脸与身份证人脸比对不一致',
        210 => '认证通过',
        500 => '系统错误',
    ]
];
