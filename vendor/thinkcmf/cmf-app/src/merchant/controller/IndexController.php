<?php

namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use think\Db;
use app\merchant\model\AdminMenuModel;

class IndexController extends MerchantBaseController
{

    public function initialize()
    {
        $adminSettings = cmf_get_option('admin_settings');
        if (empty($adminSettings['admin_password']) || $this->request->path() == $adminSettings['admin_password']) {
            $merchantId = cmf_get_current_merchant_id();
            if (empty($merchantId)) {
                session("__LOGIN_BY_CMF_MERCHANT_PW__", 1);//设置后台登录加密码
            }
        }
        parent::initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        $content = hook_one('admin_index_index_view');

        if (!empty($content)) {
            return $content;
        }

        $adminMenuModel = new AdminMenuModel();
        $menus          = cache('merchant_menus_' . cmf_get_current_merchant_id(), '', null, 'merchant_menus');
        if (empty($menus)) {
            $menus = $adminMenuModel->menuTree();
            cache('merchant_menus_' . cmf_get_current_merchant_id(), $menus, null, 'merchant_menus');
        }

        $this->assign("menus", $menus);

        $result = Db::name('AdminMenu')->order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $menusTmp = array();
        foreach ($result as $item){
            //去掉/ _ 全部小写。作为索引。
            $indexTmp = $item['app'].$item['controller'].$item['action'];
            $indexTmp = preg_replace("/[\\/|_]/","",$indexTmp);
            $indexTmp = strtolower($indexTmp);
            $menusTmp[$indexTmp] = $item;
        }
        $this->assign("menus_js_var",json_encode($menusTmp));

        $admin = Db::name("merchant")->where('id', cmf_get_current_merchant_id())->find();
        $this->assign('admin', $admin);

        return $this->fetch();
    }
}
