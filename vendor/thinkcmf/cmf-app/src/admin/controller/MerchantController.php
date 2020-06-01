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
namespace app\admin\controller;

use app\admin\model\MerchantModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\db\Query;

/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '客户管理',
 * )
 */
class MerchantController extends AdminBaseController
{

    /**
     * 客户管理
     * @adminMenu(
     *     'name'   => '客户管理',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '客户管理',
     *     'param'  => ''
     * )
     * @throws \think\exception\DbException
     */
    public function index()
    {
        /*$content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }*/
        $Merchant = new MerchantModel();
        $param = $this->request->param();
        /**搜索条件**/
        $userLogin = trim($this->request->param('user_login'));
        $userEmail = trim($this->request->param('user_email'));

        $users = $Merchant
            ->where(['user_type'=>1])
            ->where(function (Query $query) use ($userLogin, $userEmail) {
                if ($userLogin) {
                    $query->where('user_login', 'like', "%$userLogin%");
                }

                if ($userEmail) {
                    $query->where('user_email', 'like', "%$userEmail%");
                }
            })
            ->order("id DESC")
            ->paginate(20,false,['query'=>$param]);
        // 获取分页显示
        $page = $users->render();
        $items = $users->items();
        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }
        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $items);
        return $this->fetch();
    }

    /**
     * 客户添加
     * @adminMenu(
     *     'name'   => '客户添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '客户添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 客户添加提交
     * @adminMenu(
     *     'name'   => '客户添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '客户添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
                $result = $this->validate($this->request->param(), 'Merchant.add');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                    $_POST['user_type'] = 1;
                    $_POST['create_time'] = time();
                    $_POST['start_time'] = strtotime($_POST['start_time']);
                    $_POST['end_time'] = strtotime($_POST['end_time']);
                    $_POST['province'] = '';
                    $_POST['city'] = '';
                    $_POST['area'] = '';
                    $arr = explode('/', $_POST['addr']);
                    $length = count($arr);
                    if($length == 1){
                        $_POST['province'] = $arr[0];
                    }elseif($length == 2){
                        $_POST['province'] = $arr[0];
                        $_POST['city'] = $arr[1];
                    }else{
                        $_POST['province'] = $arr[0];
                        $_POST['city'] = $arr[1];
                        $_POST['area'] = $arr[2];
                    }
                    unset($_POST['addr']);
                    //halt($_POST);
                    $Merchant = new MerchantModel();
                    $result     = $Merchant->insertGetId($_POST);
                    if ($result !== false) {
                        Db::name('RoleMerchant')->insert(["role_id" => 3, "merchant_id" => $result]);
                        $this->success("添加成功！", url("merchant/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }

        }
    }

    /**
     * 客户编辑
     * @adminMenu(
     *     'name'   => '客户编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '客户编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
       /* $content = hook_one('admin_user_edit_view');

        if (!empty($content)) {
            return $content;
        }*/
        $id    = $this->request->param('id', 0, 'intval');
        $user = DB::name('merchant')->where("id", $id)->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 客户编辑提交
     * @adminMenu(
     *     'name'   => '客户编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '客户编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {

                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                }
                $result = $this->validate($this->request->param(), 'Merchant.edit');
                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $_POST['start_time'] = strtotime($_POST['start_time']);
                    $_POST['end_time'] = strtotime($_POST['end_time']);
                    $_POST['province'] = '';
                    $_POST['city'] = '';
                    $_POST['area'] = '';
                    $arr = explode('/', $_POST['addr']);
                    $length = count($arr);
                    if($length == 1){
                        $_POST['province'] = $arr[0];
                    }elseif($length == 2){
                        $_POST['province'] = $arr[0];
                        $_POST['city'] = $arr[1];
                    }else{
                        $_POST['province'] = $arr[0];
                        $_POST['city'] = $arr[1];
                        $_POST['area'] = $arr[2];
                    }
                    unset($_POST['addr']);
                    $result = DB::name('merchant')->where('id',$_POST['id'])->update($_POST);
                    if ($result !== false) {
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }


        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where("id", $id)->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 停用客户
     * @adminMenu(
     *     'name'   => '停用客户',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用客户',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('merchant')->where(["id" => $id, "user_type" => 1])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("停用成功！", url("merchant/index"));
            } else {
                $this->error('停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('merchant')->where(["id" => $id, "user_type" => 1])->setField('user_status', '1');
            if ($result !== false) {
                $this->success("启用成功！", url("merchant/index"));
            } else {
                $this->error('启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }


    //删除
    public function delete(){
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 3])->delete();
            if ($result !== false) {
                $this->success("删除成功！", url("user/index"));
            } else {
                $this->error('删除失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    



}