<?php

namespace app\admin\validate;

use think\Validate;

class MerchantValidate extends Validate
{
    protected $rule = [
        'user_nickname' => 'require|unique:user,user_nickname',
        'user_login' => 'require|unique:user,user_login',
        'user_pass'  => 'require',
        'start_time'  => 'require',
        'end_time'  => 'require',
        'addr'      => 'require',
//        'user_email' => 'require|email|unique:user,user_email',
    ];
    protected $message = [
        'user_login.require' => '用户不能为空',
        'user_login.unique'  => '用户名已存在',
        'user_nickname.require' => '名称不能为空',
        'user_nickname.unique'  => '名称已存在',
        'user_pass.require'  => '密码不能为空',
        'start_time.require'    => '开始时间不能为空',
        'end_time.require'    => '到期时间不能为空',
        'addr.require'    => '选择地区不能为空',
    ];

    protected $scene = [
        'add'  => ['user_login','user_nickname', 'user_pass','start_time','end_time','addr'],
        'edit' => ['user_login','user_nickname','start_time','end_time','addr'],
    ];
}