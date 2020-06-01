<?php

namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use think\Db;

class PublicController extends MerchantBaseController
{
    public function initialize()
    {
    }

    /**
     * 后台登陆界面
     */
    public function login()
    {
       
        /*$loginAllowed = session("__LOGIN_BY_CMF_MERCHANT_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }*/
        $merchant_id = session('MERCHANT_ID');
        if (!empty($merchant_id)) {//已经登录
            return redirect(url("merchant/Index/index"));
        } else {
            session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
            $result = hook_one('admin_login');
            if (!empty($result)) {
                return $result;
            }
            return $this->fetch(":login");
        }
    }

    /**
     * 登录验证
     */
    public function doLogin()
    {
        if (hook_one('admin_custom_login_open')) {
            $this->error('您已经通过插件自定义后台登录！');
        }

        /*$loginAllowed = session("__LOGIN_BY_CMF_MERCHANT_PW__");
        if (empty($loginAllowed)) {
            $this->error('非法登录!', cmf_get_root() . '/');
        }*/

        $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        //验证码
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }

        $name = $this->request->param("username");
        if (empty($name)) {
            $this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
        }
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        if (strpos($name, "@") > 0) {//邮箱登陆
            $where['user_email'] = $name;
        } else {
            $where['user_login'] = $name;
        }

        $result = Db::name('merchant')->where($where)->find();

        if (!empty($result) && $result['user_type'] == 1) {
            if (cmf_compare_password($pass, $result['user_pass'])) {
                $groups = Db::name('RoleMerchant')
                    ->alias("a")
                    ->join('__ROLE__ b', 'a.role_id =b.id')
                    ->where(["merchant_id" => $result["id"], "status" => 1])
                    ->value("role_id");
                if ((empty($groups) || empty($result['user_status']))) {
                    $this->error(lang('USE_DISABLED'));
                }
                //登入成功页面跳转
                session('MERCHANT_ID', $result["id"]);
                session('name', $result["user_login"]);
                $result['last_login_ip']   = get_client_ip(0, true);
                $result['last_login_time'] = time();
                $token                     = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                Db::name('merchant')->update($result);
                cookie("merchant_username", $name, 3600 * 24 * 30);
                //session("__LOGIN_BY_CMF_MERCHANT_PW__", null);
                $this->success(lang('LOGIN_SUCCESS'), url("merchant/Index/index"));
            } else {
                $this->error(lang('PASSWORD_NOT_RIGHT'));
            }
        } else {
            $this->error(lang('USERNAME_NOT_EXIST'));
        }
    }

    /**
     * 后台管理员退出
     */
    public function logout()
    {
        session('MERCHANT_ID', null);
        return redirect(url('/', [], false, true));
    }
}