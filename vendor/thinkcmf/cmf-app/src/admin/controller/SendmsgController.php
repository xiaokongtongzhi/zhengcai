<?php

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\OrderModel;

class SendmsgController extends AdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

   	//配置
    public function index()
    {
		$this->assign('site_info', cmf_get_option('site_info'));
        return $this->fetch();
    }
	 public function indexpost()
    {
		$options = $this->request->param('options/a');
		$options['zspwdconfig']=$this->getpwd($options['zsnameconfig'],$options['zspwd']);
		
        cmf_set_option('site_info', $options);
		$this->success("保存成功",url('sendmsg/index'));
    }
	public function getpwd($name,$pwd){
		$username=$salt=$name;
		$password=$pwd;
		$rePassword=md5(md5($salt.$username.$password,true));
		return $rePassword;
	}

}