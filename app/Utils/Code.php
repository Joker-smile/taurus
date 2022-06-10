<?php

namespace App\Utils;

class Code
{
    const SUCCESS = 0;// 成功
    const FAILED = 1;// 失败
    const PARAM_IS_ERROR = 10002;// 参数为空
    const UPDATE_FAILED = 10045;// 修改失败
    const SAVE_FAILED = 10049;// 保存失败
    const AUTH_IS_ERROR = 10401;// 账号或密码错误
    const TOKEN_IS_EMPTY = 10402;// token 为空
    const TOKEN_IS_EXPIRE = 10403;// token 过期
    const IS_NOT_AUTH = 10404;// 操作未权限


    protected const MESSAGE_CN = [
        '0' => 'success',
        '1' => 'error',
        '10012' => '未找到更多数据',
        '10041' => '添加失败',
        '10042' => '添加数据已存在',
        '10045' => '修改失败',
        '10046' => '修改数据已存在',
        '10049' => '保存失败',
        '10050' => '保存数据已存在',
        '10401' => '账号或密码出错',
        '10402' => '认证参数不能为空',
        '10403' => '登录过期',
        '10404' => '操作未授权',
    ];

    public static function getMessage($code)
    {
        $messageList = self::MESSAGE_CN;
        return $messageList[$code] ?? 'ERROR';
    }
}
