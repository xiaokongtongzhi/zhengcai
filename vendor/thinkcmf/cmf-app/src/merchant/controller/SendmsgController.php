<?php

namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use app\merchant\model\OrderModel;
use think\Db;

class SendmsgController extends MerchantBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

   	//配置
    public function index()
    {
		$merchant_id = cmf_get_current_merchant_id();
		$data = db('merchant')->field('zsnameconfig,zspwd,zspwdconfig')->find($merchant_id);
		$this->assign('site_info', $data);
        return $this->fetch();
    }
	 public function indexpost()
    {
		$options = $this->request->param('options/a');
		$merchant_id = cmf_get_current_merchant_id();
		$options['zspwdconfig']=$this->getpwd($options['zsnameconfig'],$options['zspwd']);
		$res = db('merchant')->where('id',$merchant_id)->update($options);
		$this->success("保存成功",url('sendmsg/index'));
    }
	public function getpwd($name,$pwd){
		$username=$salt=$name;
		$password=$pwd;
		$rePassword=md5(md5($salt.$username.$password,true));
		return $rePassword;
	}

}