<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\model;

use think\Model;

class UserModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];

    /**
     * 检测用户
     * @param array $wxuser 用户信息基本信息
     * @return int
     */
    public function checkUser($openId,$pid) {
        $uid=0; //默认uid是0
        $user=$this->where(['user_type'=>2,'openid'=>$openId])->find();
        if (isset($user) && !empty($user['id'])){
            $this->where(['id'=>$user['id']])->setField('last_login_time',time());
            $uid=$user['id'];
        }else{
            //用户不存在，则注册用户
            $udata=array(
                'user_type'=>2,
                'openid'=>$openId,
                'sex'=>0,	//性别
                'birthday'=>time(),
                'last_login_time'=>time(),
                'create_time'=>time(),
                'user_status'=>1,
                'user_login'=>'wx'.andy_rand_user(6),
                'user_pass'=>cmf_password('123456'),	//系统默认普通用户初始密码123456
                'user_email' => '',	//邮箱
                'user_nickname'=>'wx'.andy_rand_user(6),	//微信昵称
                'avatar'=>'',	    //微信头像
                'pid'=>$pid,        //邀请人id
            );
            $uid=$this->insertGetId($udata);
        }
        return $uid;
    }
    
    /**
     * 注册
     */
    public function doReg($user){
        $result = $this->where('user_login', $user['user_login'])->find();
        if (empty($result)) {
            $data   = [
                'user_login'      => $user['user_login'],
                'mobile'          => $user['user_login'],
                'user_pass'       => cmf_password('123456'),
                'last_login_time' => time(),
                'user_status'     => 1
            ];
            $this->allowField(true)->save($data,['id' => $user['id']]);
            return 0;
        }
        return 1;
    }
    
    /**
     * 修改用户资料
     * @param array $data 数据
     * @return $this
     */
    public function portalEditUser($data)
    {
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();
        return $this;
    }
}