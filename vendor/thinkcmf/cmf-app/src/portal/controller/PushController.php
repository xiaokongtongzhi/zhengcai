<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class PushController extends HomeBaseController
{

	//商品上/下架
    public function updown(){
		$client = new \SoapClient("http://222.143.21.205:8091/wsscservices_test/services/wsscWebService?wsdl",array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['xhbh']='WSCG000081';
		 $m['zt']='WSCG000081';
		 $m['xjyy']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->execSpDown(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
	}
	
	//推送商品唯一标识码
    public function tuisongnum(){
		$client = new \SoapClient("http://222.143.21.205:8091/wsscservices_test/services/wsscWebService?wsdl",array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['xhbh']='WSCG000081';
		 $m['wybs']='WSCG000081';
		 $m['pic']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->execTsWybs(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
	}
	//查询单个订单信息
    public function searchoneorder(){
		$client = new \SoapClient("http://222.143.21.205:8091/wsscservices_test/services/wsscWebService?wsdl",array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->findDdxxByddbh(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
	}
}

