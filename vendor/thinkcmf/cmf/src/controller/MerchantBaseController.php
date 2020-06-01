<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\Db;

class MerchantBaseController extends BaseController
{

    protected function initialize()
    {
        // 监听admin_init
        //hook('admin_init');
        parent::initialize();
        $sessionMerchantId = session('MERCHANT_ID');
        if (!empty($sessionMerchantId)) {
            $merchant = Db::name('merchant')->where('id', $sessionMerchantId)->find();
            if (!$this->checkAccess($sessionMerchantId)) {
                $this->error("您没有访问权限！");
            }
            $this->assign("merchant", $merchant);
        } else {
            if ($this->request->isPost()) {
               // echo '22222';die;
                $this->error("您还没有登录！", url("Merchant/public/login"));
            } else {
                //echo '333';die;
                return $this->redirect(url("Merchant/public/login"));
            }
        }

    }

    public function _initializeView()
    {
        $cmfAdminThemePath    = config('template.cmf_merchant_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_merchant_theme();
        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";
        $root = cmf_get_root();
        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }
        config('template.view_base', WEB_ROOT . "$themePath/");
        config('template.tpl_replace_string', $viewReplaceStr);
    }

    /**
     * 初始化后台菜单
     */
    public function initMenu()
    {
    }

    /**
     *  检查后台用户访问权限
     * @param int $userId 后台用户id
     * @return boolean 检查通过返回true
     */
    private function checkAccess($userId)
    {

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule       = $module . $controller . $action;

        $notRequire = ["adminIndexindex", "adminMainindex"];
        //var_dump(in_array($rule, $notRequire));
        if (!in_array($rule, $notRequire)) {
            return cmf_auth_check($userId);
        } else {
            return true;
        }
    }
	
	public function add_log($action){
		$merchant_id = cmf_get_current_merchant_id();
		$user_id = cmf_get_current_merchant_user_id();
		$data['user_id'] = $user_id;
		$data['merchant_id'] = $merchant_id;
		$data['action'] = $action;
		$data['create_time'] = time();
		$data['last_login_ip']   = get_client_ip(0, true);
		Db::name('action_log')->insert($data);
	}

}