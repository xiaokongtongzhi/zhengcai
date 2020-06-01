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

class GoodsController extends HomeBaseController
{


    public function index()
    {	
    	
    	set_time_limit(0);
    	ini_set('default_socket_timeout', 8000);
		$time = date('YmdHis',time());
		$start_time = date('YmdHis',time()-7200);
		$sprkkssj=$start_time;
		$sprkjssj=$time;
		//$sprkkssj='20191014000000';
		//$sprkjssj='20191015200000';
		$sets = array(
			'encoding'=>'utf-8',
		    'trace' => true, 
		    'keep_alive' => false,
		    //'connection_timeout' => 5000,
		    'cache_wsdl' => WSDL_CACHE_NONE,
		    //'compression'   => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
		);
		$client = new \SoapClient(config('config.zs_url'),$sets);

		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['sprkkssj']=$sprkkssj;
		 $m['sprkjssj']=$sprkjssj;
		 $m['pageNum']=1;
		 $m['pageSize']=50;
		 $mm=json_encode($m);
		 $res = $client->findSprkandParam(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 //$goodsModel = db('goods');
		 //var_dump($result);die;
		 if($result['resultFlag'] == 'Y'){
			 for($i=1;$i<$result['pagecount']+1;$i++){
			 	 // $in=array();
				 $m['username']=$this->zsnameconfig;
				 $m['pwd']=$this->zspwdconfig;
				 $m['sprkkssj']=$sprkkssj;
				 $m['sprkjssj']=$sprkjssj;
				 $m['pageNum']=$i;
				 $m['pageSize']=50;
				 $mm=json_encode($m);
				 $res = $client->findSprkandParam(array('in0'=>$mm));/* 调用方法 */
				 $result=json_decode($res->out,true);
				  //var_dump($result['pagecount']);
				 $data=$result['spList'];
				 $in=[];
				 // var_dump($data);
				 foreach ($data as $key=>$v){
				 	$arr = $v;
				 	$info = db('goods')->where('xhbh',$v['xhbh'])->count();
				 	//var_dump($info);
				 	if($info==0){
				 		$arr['parametersList'] = json_encode($v['parametersList']);
						$arr['create_time'] = time();
						array_push($in, $arr);
				 	}									
				}
				
				//var_dump(count($in));
				db("goods")->insertAll($in);
				//sleep(15);
			 }	
		}	 
		exit;
    }
	
	public function peijian(){
		set_time_limit(0);
    	ini_set('default_socket_timeout', 8000);
		 $client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 /*if(!empty($this->request->param("page"))){
			 $page=$this->request->param("page");
		 }else{
			 $page=1;
		 }		 */
		 //$num=50;
		 //for($i=701;$i<1001;$i++){
		 //$time = time();
		 //$start_time = $time-3600;
		 //$where[] = ['create_time',['>= time', $start_time], ['<= time', $time]];
		 $odata=db("pmmc")->order("id desc")->select()->toArray();
		 //var_dump($odata);die;
		 // echo json_encode($odata);die;
		 foreach($odata as $key=>$val){
			 $m['username']=$this->zsnameconfig;
			 $m['pwd']=$this->zspwdconfig;
			 $m['pmbh']=$val['pmbh'];
			 $m['pageNum']=1;
			 $m['pageSize']=50;
			 $mm=json_encode($m);
			 $res = $client->findPjByPmbh(array('in0'=>$mm));/* 调用方法 */
			 $result=json_decode($res->out,true);
			 // dump($result);//exit;
			 if(empty($result['accessoryList'])){
				 continue;
			 }else{
				 // dump($result);die;
				 echo "11--";
			 	$in=[];
			 	$data=$result['accessoryList'];
			 	foreach ($data as $key=>$v){
					$arr=array(
						'goods_id'=>$val['id'],
						'pmbh'   => $val['pmbh'],
						'pmmc'   => $val['pmmc'],
						'PJBH'   => $v['PJBH'],
						'PJMC'   => $v['PJMC'],
						'PJMS'   => $v['PJMS'],
						'accessoryListmx' => json_encode($v['accessoryListmx']),
						'create_time'=>time()
					);
					$isset_parts=db('goods_parts')->where(['pmbh'=>$val['pmbh'],'PJBH'=>$v['PJBH']])->count();
					if(empty($isset_parts)){
						array_push($in, $arr);
					}
				}
				db("goods_parts")->insertAll($in);	
			 }
			 //sleep(1);
		 }
		 //}		 
	}
	public function zhengzhifw(){
		set_time_limit(0);
    	ini_set('default_socket_timeout', 8000);
		 $client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		/*if(!empty($this->request->param("page"))){
			 $page=$this->request->param("page");
		 }else{
			 $page=1;
		 }	*/	 
		 //$num=50;
		 //for($i=501;$i<1001;$i++){
		 //$time = time();
		 //$start_time = $time-3600;
		 //$where[] = ['create_time',['>= time', $start_time], ['<= time', $time]];
		 $odata=db("pmmc")->order("id desc")->select()->toArray();
		 foreach($odata as $key=>$val){
			 $m['username']=$this->zsnameconfig;
			 $m['pwd']=$this->zspwdconfig;
			 $m['pmbh']=$val['pmbh'];
			 $m['pageNum']=1;
			 $m['pageSize']=50;
			 $mm=json_encode($m);
			 $res = $client->findFwByPmbh(array('in0'=>$mm));/* 调用方法 */
			 $result=json_decode($res->out,true);
			 // var_dump($result);
			 if(empty($result['serviceList'])){
				 continue;
			 }else{
			 	$in=[];
			 	$data=$result['serviceList'];
			 	foreach ($data as $key=>$v){
					$arr=array(
						'goods_id'=>$val['id'],
						'pmbh'   => $val['pmbh'],
						'pmmc'   => $val['pmmc'],
						'fwbh'   => $v['FWBH'],
						'fwmc'   => $v['FWMC'],
						'fwmx'   => $v['FWMX'],
						'zt'   => $v['ZT'],
						'create_time'=>time()
					);
					$isset_service=db('goods_service')->where(['pmbh'=>$val['pmbh'],'fwbh'=>$v['FWBH']])->count();
					if(empty($isset_service)){
						array_push($in, $arr);
					}
					// array_push($in, $arr);
				}
				db("goods_service")->insertAll($in);	 
			 }
			 //sleep(1);		 
		 }
		 //}		 
	}
	public function baojia(){
			$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
			 $m['username']=$this->zsnameconfig;
			 $m['pwd']=$this->zspwdconfig;
			 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';
			 $m['xhmc']='XG-H400ZA';
			 $m['pmbh']='000400010021000200030003';
			 $m['pmmc']='投影仪';
			 $m['ppbh']='40282a3e177bfb3f01177c90bddc00f3';
			 $m['ppmc']='夏普';
			 $m['lbbh']='00040001002100020003';
			 $m['lbmc']='扫描/投影';
			 $m['sjjg']=40;
			 $m['productlink']='http://xxhuizhidianzi.com/index.php?s=/goods/goodsinfo&goodsid=38';
			 $mm=json_encode($m);
			 $res = $client->execute(array('in0'=>$mm));/* 调用方法 */
			 $result=json_decode($res->out,true);
			 dump($result);exit;
	}
	public function peijianbaojia(){
			$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
			 $m['username']=$this->zsnameconfig;
			 $m['pwd']=$this->zspwdconfig;
			 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';
			 $m['xhmc']='XG-H400ZA';
			 $m['pmbh']='000400010021000200030003';
			 $m['pmmc']='投影仪';
			 $m['pjbh']='8080fd65d9e84852b80647e658544e38';//配件编号
			 $m['pjmc']='套餐A';//配件名称
			 $m['pjjg']='19.99';//配件价格
			// $m['bjyy']='';//修改报价原因
			 $m['productlink']='http://xxhuizhidianzi.com/index.php?s=/goods/goodsinfo&goodsid=38';
			 $mm=json_encode($m);
			 $res = $client->quotedpricePjByPjbh(array('in0'=>$mm));/* 调用方法 */
			 $result=json_decode($res->out,true);
			 dump($result);exit;
	}
	public function zhengzhifwbaojia(){
			$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
			 $m['username']=$this->zsnameconfig;
			 $m['pwd']=$this->zspwdconfig;
			 $m['xhbh']='77dfe90c84e34ee5abe28218cde84b0e';
			 $m['xhmc']='XG-H400ZA';
			 $m['pmbh']='000400010021000600010001';
			 $m['pmmc']='桌/椅/柜套装';
			 $m['fwbh']='667f7087727d444a8131327dabe0ab86';//服务编号
			 $m['fwmc']='上门安装服务';//服务名称
			 $m['fwjg']='19.99';//服务价格
			// $m['bjyy']='';//修改报价原因
			 $m['zt']='1';//状态
			 $mm=json_encode($m);
			 $res = $client->quotedpriceFwByFwbh(array('in0'=>$mm));/* 调用方法 */
			 $result=json_decode($res->out,true);
			 dump($result);exit;
	}
	
}

