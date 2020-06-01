<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'kdgs' => 'require',
        'kddh'  => 'require',
		'ms' => 'require',
        'kdsj'  => 'require',
		'sfcd'  => 'require',
    ];

    protected $message = [
        'kdgs.require' => '快递公司不能为空',
        'kddh.require'  => '快递单号不能为空',
		'ms.require' => '描述不能为空',
        'kdsj.require'  => '快递时间不能为空',
		'sfcd.require'  => '是否拆单不能为空',
    ];

}