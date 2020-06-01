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

use cmf\controller\AdminBaseController;
use app\admin\model\OrderModel;

class OrderController extends AdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];	
	
   	//订单列表
    public function my_order()
    {
        $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',0];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
       // var_dump($where);
        $orderModel=new OrderModel();
    	$list=$orderModel->where($where)->order('id DESC')->paginate(20,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();

    }

    //订单详情
    public function info(){
      set_time_limit(0);
      ini_set('default_socket_timeout', 8000);
    	$id=$this->request->param('id',0,'intval');
    	if(empty($id)){
    		$this->error('参数错误');
    	}
    	$info=db('order')->field("ddbh,status")->where('id',$id)->find();
    	//dump($info);die;
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$info['ddbh'];
		 $mm=json_encode($m);
		 $res = $client->findDdxxByddbh(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 //dump($result);exit;
		 $wuliu=db('wuliu')->where('orderid',$id)->order('id DESC')->find();//获取物流
		 $qianshou=db('qianshou')->where('orderid',$id)->order('id DESC')->find();//获取订单签收时间		 
		 $orderlog=db('orderlog')->where('orderid',$id)->select();//获取记录
		 $data=$result['orderList'][0];
		 if($data['zt']==4&&$info['status']==0){
			 db('order')->where(['id'=>$id])->update(['status'=>11]);
		 }elseif($data['zt']==3&&$info['status']==0){
			 db('order')->where(['id'=>$id])->update(['status'=>1]);
		 }
		 db('order')->where(['id'=>$id])->update(['zt'=>$data['zt']]);
    	 $this->assign('data',$data);
         $this->assign('info',$info);
		 $this->assign('orderlog',$orderlog);
		 $this->assign('wuliu',$wuliu);
		 $this->assign('qianshou',$qianshou);
    	 return $this->fetch();

    }

    //确认订单
    public function queding(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $ddbh=db('order')->where('id',$id)->value('ddbh');
        $in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$ddbh;
		 $m['qrzt']=0;
		 $mm=json_encode($m);
		 $res = $client->execGysOrderQr(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 
		if($result['resultFlag']=="Y"){
			db('order')->where('id',$id)->update(['status'=>1,'zt'=>3]);
			db('orderlog')->insert(['orderid'=>$id,'catename'=>'待确认订单','name'=>'确定','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/my_order"));
		}else{
			 $this->error($result['resultMessage']);
		}    
    }
	//拒绝订单
    public function jujue(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $ddbh=db('order')->where('id',$id)->value('ddbh');
        $in=array();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$ddbh;
		 $m['qrzt']=1;
		 $mm=json_encode($m);
		 $res = $client->execGysOrderQr(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 
		if($result['resultFlag']=="Y"){
			db('order')->where('id',$id)->update(['status'=>10]);
			db('orderlog')->insert(['orderid'=>$id,'catename'=>'待确认订单','name'=>'拒绝','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/my_order"));
		}else{
			 $this->error($result['resultMessage']);
		} 
    }
   //待送货订单
   public function song(){
	  	$param = $this->request->param();

      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',1];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	   $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
   //物流信息
   public function wuliu(){
	   $id = $this->request->param("id");
	   $this->assign('id', $id);
	   return $this->fetch();
   }
   //物流信息
   public function wuliupost(){
		$data      = $this->request->param();

        $OrderModel = new OrderModel();
        $result    = $this->validate($data, 'Order');
        if ($result !== true) {
            $this->error($result);
        }
        $ddbh=$OrderModel->where(['id'=>$data['id']])->value("ddbh");
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$data['ddbh']=$ddbh;
		 $m['sfcd']=$data['sfcd'];
		 $m['fczddbh']=$data['fczddbh'];
		 $m['kdgs']=$data['kdgs'];
		 $m['kddh']=$data['kddh'];
		 $m['ms']=$data['ms'];
		 $m['kdsj']=date("YmdHis",strtotime($data['kdsj']));
		 $mm=json_encode($m);
		 $res = $client->exeLogistics(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 
		 $data['orderid']=$data['id'];
		 unset($data['id']);
		 $data['addtime']=time();
		 db("wuliu")->insertGetId($data);		 
		if($result['resultFlag']=="Y"){
			 $OrderModel->where(['id'=>$data['orderid']])->update(['status'=>2]);
			 db('orderlog')->insert(['orderid'=>$data['orderid'],'catename'=>'待送货订单','name'=>'物流发货','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/song"));
		}else{
			 db('orderlog')->insert(['orderid'=>$data['orderid'],'catename'=>'待送货订单','name'=>'物流发货失败','addtime'=>time()]);
			 $this->error($result['resultMessage']);
		}      
   }
   //获取合同
   public function hetong(){
	    $id      = $this->request->param("id");
       
        $mdata=db("order")->field("ddbh")->where(['id'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];				 
		 $mm=json_encode($m);
		 $res = $client->findOrderHt(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true); 
		 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待送货订单','name'=>'获取合同','addtime'=>time()]);
		$this->assign('data', $result);
	    return $this->fetch();   
	   
   }
   //验收
   public function yanshou(){
	    $id      = $this->request->param("id");
       
        $mdata=db("order")->field("ddbh")->where(['id'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];				 
		 $mm=json_encode($m);
		 $res = $client->findYsByOrder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);	 
		if($result['resultFlag']=="Y" && $result['yssj']!='' && $result['ysbz']!=''){
			db('order')->where('id',$id)->update(['zt'=>5]);
		}
		 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待收货订单','name'=>'查看验收','addtime'=>time()]);
		$this->assign('data', $result);
	    return $this->fetch();   
	   
   }
   //取消订单
   public function quxiaoorder(){
	    $id      = $this->request->param("id");
        $qxyy      = $this->request->param("qxyy");
        $mdata=db("order")->field("ddbh")->where(['id'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];	
		 $m['qxyy']=$qxyy;			 
		 $mm=json_encode($m);
		 $res = $client->execDsZfdd(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		//dump($result['["resultMessage"]']);exit;	 	 
		if($result['resultFlag']=="Y"){
			 $OrderModel = new OrderModel();
			 $OrderModel->where(['id'=>$id])->update(['status'=>11],['zt'=>8]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待送货订单','name'=>'取消订单','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/quxiaoorderlist")); 
		}else{
			 $OrderModel = new OrderModel();
			 $OrderModel->where(['id'=>$id])->update(['status'=>11]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待送货订单','name'=>'取消订单','addtime'=>time()]);
			 $this->error($result['resultMessage']);
		}	   
   }
   //已经取消订单
   public function quxiaoorderlist(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',11];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	   $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
	//待收货订单
   public function shouhuo(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',2];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	  $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
   //签收信息
   public function qianshou(){
	   $id = $this->request->param("id");
	   $this->assign('id', $id);
	   return $this->fetch();
   }
   //签收信息
   public function qianshoupost(){
	    $id      = $this->request->param("id");
        $shsj      = $this->request->param("shsj");
        $mdata=db("wuliu")->field("id,orderid,ddbh,sfcd,fczddbh")->where(['orderid'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];
		 $m['sfcd']=$mdata['sfcd'];
		 $m['fczddbh']=$mdata['fczddbh'];
		 $m['shsj']=date("YmdHis",strtotime($shsj));

		 $mm=json_encode($m);
		 $res = $client->execQssj(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 $mdata['wuliuid']=$mdata['id'];
		 unset($mdata['id']);
		 $mdata['shsj']=$shsj;
		 $mdata['addtime']=time();
		 db("qianshou")->insertGetId($mdata);		 
		if($result['resultFlag']=="Y"){
			 db('order')->where(['id'=>$mdata['orderid']])->update(['status'=>3]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待收货订单','name'=>'订单签收','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/shouhuo"));
		}else{
			 $this->error($result['resultMessage']);
		}      
   }
   //签收信息
   public function shibiema(){
	  // $id = $this->request->param("id");
	   $data['ddbh']      = $this->request->param("ddbh");
	   $data['xhbh']      = $this->request->param("xhbh");
	   $data['xhmc']      = $this->request->param("xhmc");
	   $preurl=$_SERVER['HTTP_REFERER'];
	   $this->assign('data', $data);
	   $this->assign('preurl', $preurl);
	   return $this->fetch();
   }
   //签收识别码信息
   public function shibiemapost(){
	   
	    //$id      = $this->request->param("id");
		$preurl      = $this->request->param("preurl");
        $wybs      = $this->request->param("wybs");
		$pic      = $this->request->param("pic");
		$pid = substr($pic,strrpos($pic,'/')+1,32);
		$pic="upload/".$pic;
		// $pic=$_SERVER['DOCUMENT_ROOT'].'/'.$pic;
		$ddbh      = $this->request->param("ddbh");
		$xhbh      = $this->request->param("xhbh");
		$groupid      = $this->request->param("groupid");
		$ms      = $this->request->param("ms");
        //$mdata=db("order")->field("id,ddbh,productList")->where(['id'=>$id])->find();
		//$aa=json_decode($mdata['productList'],true);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$ddbh;
		 $m['xhbh']=$xhbh;
		 $m['wybs']=$wybs;
		 $m['groupid']=$groupid;
		 $m['ms']=$ms;
		 $m['pid']=$pid;//date('YmdHis');
		 $m['pic']=$this->imgToBase64($pic);
		 //$m['pic']='';
		 //dump($m);exit;
		 $mm=json_encode($m);

		 $res = $client->execTsWybs(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);		 
				 
		if($result['resultFlag']=="Y"){	
			db("biaoshima")->insertGetId(['ddbh'=>$ddbh,'xhbh'=>$xhbh,'wybs'=>$wybs,'pid'=>$m['pid'],'pic'=>$m['pic'],'biaoshima_addtime'=>time()]);			
			 $this->success($result['resultMessage'], $preurl);
		}else{
			 $this->error($result['resultMessage']);
		}      
   }
   public function imgToBase64($img_file) {

		$img_base64 = '';
		 //dump(file_exists($img_file));exit;
		if (file_exists($img_file)) {
			$app_img_file = $img_file; // 图片路径
			$img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

			//echo '<pre>' . print_r($img_info, true) . '</pre><br>';
			$fp = fopen($app_img_file, "r"); // 图片是否可读权限
			
			if ($fp) {
				$filesize = filesize($app_img_file);
				$content = fread($fp, $filesize);
				$file_content = base64_encode($content); // base64编码
				switch ($img_info[2]) {           //判读图片类型
					case 1: $img_type = "gif";
						break;
					case 2: $img_type = "jpg";
						break;
					case 3: $img_type = "png";
						break;
				}

				$img_base64 =$file_content;//合成图片的base64编码

			}
			fclose($fp);
		}

		return $img_base64; //返回图片的base64
	}
   /* public function shibiemapost(){
	    $id      = $this->request->param("id");
        $wybs      = $this->request->param("wybs");
		$pic      = $this->request->param("pic");
        $mdata=db("order")->field("id,ddbh,productList")->where(['id'=>$id])->find();
		$aa=json_decode($mdata['productList'],true);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];
		 $m['xhbh']=$aa[0]['XHBH'];
		 $m['wybs']=$wybs;
		 $m['pic']=base64_encode($pic);		 
		 $mm=json_encode($m);
		 $res = $client->execTsWybs(array('in0'=>$mm));
		 $result=json_decode($res->out,true);		 
		 db("qianshou")->where(['orderid'=>$mdata['id']])->update(['wybs'=>$wybs,'pic'=>$m['pic'],'biaoshima_addtime'=>time()]);			 
		if($result['resultFlag']=="Y"){
			db('order')->where(['id'=>$mdata['id']])->update(['status'=>3]);
			 $this->success($result['resultMessage'], url("order/shouhuo"));
		}else{
			 $this->error($result['resultMessage']);
		}      
   }*/
	//待开票订单
   public function kaipiao(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',3];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	   $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
   //发票
   public function fapiao(){
	   $id = $this->request->param("id");
	   $this->assign('id', $id);
	   return $this->fetch();
   }
   //发票时间
   public function fapiaopost(){
	    $id      = $this->request->param("id");
        $fpkjsj      = $this->request->param("fpkjsj");
        $mdata=db("qianshou")->field("id,orderid,ddbh")->where(['orderid'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];
		 $m['fpkjsj']=date("YmdHis",strtotime($fpkjsj));		 
		 $mm=json_encode($m);
		 $res = $client->execFpkjsjByOrder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 db("qianshou")->where(['id'=>$mdata['id']])->update(['fpkjsj'=>$fpkjsj,'fapiao_addtime'=>time()]);		 
		if($result['resultFlag']=="Y"){
			 db('order')->where(['id'=>$mdata['orderid']])->update(['state'=>1]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待开票订单','name'=>'开具发票','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/kaipiao"));
		}else{
			 $this->error($result['resultMessage']);
		}      
   }
   //收发票
   public function shoufapiao(){
	   $id = $this->request->param("id");
	   $this->assign('id', $id);
	   return $this->fetch();
   }
   //收发票时间
   public function shoufapiaopost(){
	    $id      = $this->request->param("id");
        $fpsdsj      = $this->request->param("fpsdsj");
        $mdata=db("qianshou")->field("id,orderid,ddbh")->where(['orderid'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];
		 $m['fpsdsj']=date("YmdHis",strtotime($fpsdsj));
		 $mm=json_encode($m);
		 $res = $client->execfpsdsjByorder(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 db("qianshou")->where(['id'=>$mdata['id']])->update(['fpsdsj'=>$fpsdsj,'shoudao_addtime'=>time()]);		 
		if($result['resultFlag']=="Y"){
			 db('order')->where(['id'=>$mdata['orderid']])->update(['status'=>4]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待开票订单','name'=>'收到发票','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/kaipiao"));
		}else{
			 $this->error($result['resultMessage']);
		}      
   }
   //待收款订单
   public function daishoukuan(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',4];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }
		
        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	  $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
    //收款情况
   public function shoukuandetail(){
	   $id = $this->request->param("id");
	   $this->assign('id', $id);
	   return $this->fetch();
   }
   //收款情况
   public function shoukuandetailpost(){
	    $id      = $this->request->param("id");
        $skbz      = $this->request->param("skbz");
		$skje      = $this->request->param("skje");
		$sksj      = $this->request->param("sksj");
		$bz      = $this->request->param("bz");
        $mdata=db("qianshou")->field("id,orderid,ddbh")->where(['orderid'=>$id])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
		 $m['ddbh']=$mdata['ddbh'];
		 $m['skbz']=$skbz;
		 $m['skje']=$skje;
		 $m['bz']=$bz;
		 $m['sksj']=date("YmdHis",strtotime($sksj));
		 //print_r($m);exit;
		 $mm=json_encode($m);
		 $res = $client->execSkqk(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 db("qianshou")->where(['id'=>$mdata['id']])->update(['skbz'=>$skbz,'skje'=>$skje,'sksj'=>$sksj,'shoukuanaddtime'=>time()]);		 
		if($result['resultFlag']=="Y"){
			 db('order')->where(['id'=>$mdata['orderid']])->update(['status'=>5]);
			 db('orderlog')->insert(['orderid'=>$id,'catename'=>'待收款订单','name'=>'收款情况','addtime'=>time()]);
			 $this->success($result['resultMessage'], url("order/daishoukuan"));
		}else{
			 $this->error($result['resultMessage']);
		}      
   }
   
	//已完成订单
   public function over(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',5];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	   $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
      	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
   //已拒绝订单
   public function refuse_order(){
	   $param = $this->request->param();      
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $tel=$this->request->param('tel','');
        $tel=trim($tel);
        // var_dump($param);
        $where[]=['status','eq',10];
        if(!empty($keyword)){
           $where[]=['xflxrxm','like','%'.$keyword.'%'];
        }
        if(!empty($tel)){
           $where[]=['xfdh','like','%'.$tel.'%'];
        }

        $startTime = empty($param['start_time']) ? 0 : date('YmdHis',strtotime($param['start_time']));
        $endTime   = empty($param['end_time']) ? 0 : date('YmdHis',strtotime($param['end_time']));
        if (!empty($startTime) && !empty($endTime)) {
            $where[] = ['cjrq',['>=', $startTime], ['<=', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where[] = ['cjrq','>=', $startTime];
            }
            if (!empty($endTime)) {
                $where[] = ['cjrq','<=', $endTime];
            }
        }
	   $orderModel=new OrderModel();
       $list=$orderModel->where($where)->order('id DESC')->paginate(100,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       	$this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('tel', isset($param['tel']) ? $param['tel'] : '');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
   }
	public function getcurorder(){
		
		set_time_limit(0);
    	ini_set('default_socket_timeout', 8000);
		$in=array();
		$time = date('YmdHis',time());
		$start_time = date('YmdHis',time()-3600*24*7);
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
		 //var_dump($client);die;
		 $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
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
				 $m['username']=$this->zsnameconfig;
				 $m['pwd']=$this->zspwdconfig;
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
				 		array_push($in, $v);
				 	}else{
				 		db('order')->where(['ddbh'=>$v['ddbh']])->update(['status'=>0]);
				 	}
				}
				//var_dump(count($in));
				db("order")->insertAll($in);
				//sleep(1);
				}
			 }
		 }
		  $this->success("获取完毕",url('admin/order/my_order'));
	}
}