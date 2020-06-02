<?php

namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;

class OrderController extends HomeBaseController
{
	//查询订单
    public function search()
    {	
		set_time_limit(0);
    	ini_set('default_socket_timeout', 8000);
		$in=array();
		$time = date('YmdHis',time());
		$start_time = date('YmdHis',time()-3600);
		//var_dump($start_time);
		$sprkkssj=$start_time;
		$sprkjssj=$time;
		//$sprkkssj="20160101000000";
		//$sprkjssj="20191014000000";
		$sets = array(
			'encoding'=>'utf-8',
		    'trace' => true, 
		    'keep_alive' => false,
		    'connection_timeout' => 5000,
		    'cache_wsdl' => WSDL_CACHE_NONE,
		    'compression'   => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
		);
		
		$client = new \SoapClient(config('config.zs_url'),$sets);
		$m_data = db('merchant')->field('id,zsnameconfig,zspwdconfig')->where('end_time','>',time())->where('user_status',1)->select()->toArray();
		foreach ($m_data as $key => $val) {
			$m['username']=$val['zsnameconfig'];
			$m['pwd']=$val['zspwdconfig'];
			$m['kssj']=$sprkkssj;
			$m['jssj']=$sprkjssj;
			$m['zt']=2;
			$m['pageNum']=1;
			$m['pageSize']=50;
			$mm=json_encode($m);
			$res = $client->findOrder(array('in0'=>$mm));/* 调用方法 */
			$result=json_decode($res->out,true);
			//var_dump($res);die;
			if($result['resultFlag'] == 'Y'){
				$pagecount=ceil($result['count']/$m['pageSize']);
				for($i=1;$i<$pagecount+1;$i++){
					$in=array();
					$m['username']=$val['zsnameconfig'];
					$m['pwd']=$val['zspwdconfig'];
					$m['kssj']=$sprkkssj;
					$m['jssj']=$sprkjssj;
					$m['zt']=2;
					$m['pageNum']=$i;
					$m['pageSize']=50;
					$mm=json_encode($m);
					$res = $client->findOrder(array('in0'=>$mm));/* 调用方法 */
					$result=json_decode($res->out,true);
					 //var_dump($result);
					if($result['resultFlag'] == 'Y'){
					  //var_dump($result['pagecount']);
					$data=$result['orderList'];
					 // var_dump($data);
					foreach ($data as $key=>$v){
					 	$info = db('order')->where(['ddbh'=>$v['ddbh']])->find();
					 	if(empty($info)){
					 		$v['productList']=json_encode($v['productList']);
					 		$v['merchant_id'] = $val['id'];
					 		array_push($in, $v);
					 	}
					}
					//var_dump(count($in));
					db("order")->insertAll($in);
					//sleep(1);
					}
				}
			}
		}
		exit;
    }
	//确认订单
	 public function sure()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['qrzt']=0;
		 $mm=json_encode($m);
		 $res = $client->execGysOrderQr(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//物流信息
	 public function wuliu()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['sfcd']=0;
		 $m['fczddbh']='WSCG000081';
		 $m['kdgs']=0;
		 $m['kddh']='WSCG000081';
		 $m['ms']=0;
		 $m['kdsj']=0;
		 $mm=json_encode($m);
		 $res = $client->exeLogistics(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//订单签收
	 public function qianshou()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['sfcd']=0;
		 $m['fczddbh']='WSCG000081';
		 $m['shsj']=0;		
		 $mm=json_encode($m);
		 $res = $client->execQssj(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//订单发票开具
	 public function fapiao()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['fpkjsj']=0;
		 $mm=json_encode($m);
		 $res = $client->execFpkjsjByOrder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//订单发票收到
	 public function shoufapiao()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['fpsdsj']=0;
		 $mm=json_encode($m);
		 $res = $client->execfpsdsjByorder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//订单合同获取
	 public function hetonghuoqu()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->findOrderHt(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//订单收款情况
	public function shoukuan()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['skbz']='WSCG000081';
		 $m['skje']='WSCG000081';
		 $m['sksj']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->execSkqk(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//取消订单信息
	public function quxiaoorder()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['qxyy']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->execDsZfdd(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//获取验收时间及验收方式
	public function yanshou()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['ddbh']='WSCG000081';
		 $m['qxyy']='WSCG000081';
		 $mm=json_encode($m);
		 $res = $client->findYsByOrder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//商品报价
	 public function spbj()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';
		 $mm=json_encode($m);
		 $res = $client->findSpByXhbh(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//商品配件服务报价
	 public function pjfwbj()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';
		 $mm=json_encode($m);
		 $res = $client->findSpSfbj(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		exit;
    }
	//撤销报价
	 public function cexiaobj()
    {	
		$in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=7223;
		 $m['pwd']='737a51608fb3297de8b5189cd64fcc3e';
		 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';			
		 $mm=json_encode($m);
		 $res = $client->qxShByXhbh(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 var_dump($result); 	 
		 exit;
    }
	
	public function getpwd(){
		$username=$salt="7223";
		$password="ff8080814a1353ac014a139496110049";
		$rePassword=md5(md5($salt.$username.$password,true));
		dump($rePassword);
		exit;
	}
}

