<?php

namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use app\merchant\model\GoodsModel;
use app\merchant\model\MerchantQuoteModel;
use app\admin\model\OfferLogModel;
use app\admin\model\OfferPartLogModel;
use app\admin\model\OfferServiceLogModel;
use think\Db;

class GoodsController extends MerchantBaseController
{
    
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

   	//商品列表
    public function index()
    {
        $param = $this->request->param();  

        $type=$this->request->param('category',0,'intval');
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        // var_dump($param);
        $where=[];
		$where[]=['isdel','eq',0];
        if(!empty($keyword)){

            if(empty($type)){
                $where[]=['xhbh|xhmc|ppmc|lbmc','like','%'.$keyword.'%'];
            }else if($type==1){
                $where[]=['xhmc','like','%'.$keyword.'%'];
            }else if($type==2){
                $where[]=['ppmc','like','%'.$keyword.'%'];
            }else if($type==3){
                $where[]=['lbmc','like','%'.$keyword.'%'];
            }
        }
        $goodsModel=new GoodsModel();
    	$list=$goodsModel->relation('parts,services')->where($where)->order('id DESC')->paginate(50,false,['query'=>$param]);

        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
        $this->assign('keyword', $keyword ? $keyword : '');
        $this->assign('category', $type ? $type : 0);
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();

    }

    //商品详情
    public function info(){

    	$id=$this->request->param('id',0,'intval');
    	if(empty($id)){
    		$this->error('参数错误');
    	}
        $type=$this->request->param('type',0,'intval');
        $this->assign('type',$type);
    	$info=db('goods')->where('id',$id)->find();
      //print_r($info);exit;
    	if(empty($info['goods_url'])&&$info['price']!='0.00'){
    		$info['goods_url']='http://'.$_SERVER['HTTP_HOST'].'/portal/index/detail/id/'.$id.'.html';
    	}
    	$this->assign('info',$info);
    	return $this->fetch();

    }

    //配置信息
    public function parts(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $type=$this->request->param('type',0,'intval');
        $this->assign('type',$type);
        $pmbh=db('goods')->where('id',$id)->value('pmbh');
        $parts=db('goods_parts')->where('pmbh',$pmbh)->paginate(10);
        $page=$parts->render();
        $this->assign('parts',$parts);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //配件明细
    public function parts_info(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $type=$this->request->param('type',0,'intval');
        $this->assign('type',$type);
        $info=db('goods_parts')->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
	
	//配件报价记录
    public function parts_offer(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
		$offerPartModel= new OfferPartLogModel(); 
        $list=$offerPartModel->where('goods_part_id',$id)->order('id desc')->paginate(20);
		$page=$list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }
	//服务报价记录
    public function service_offer(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
		$offerServicetModel= new OfferServiceLogModel(); 
        $list=$offerServicetModel->where('goods_service_id',$id)->order('id desc')->paginate(20);
		$page=$list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }
    //增值服务信息
    public function service(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $type=$this->request->param('type',0,'intval');
        $this->assign('type',$type);

        $pmbh=db('goods')->where('id',$id)->value('pmbh');
        $service=db('goods_service')->where('pmbh',$pmbh)->paginate(10);
        $page=$service->render();
        $this->assign('service',$service);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //增值服务详情
    public function service_info(){
        $id=$this->request->param('id');
        if(empty($id)){
            $this->error('参数错误');
        }
        $type=$this->request->param('type',0,'intval');
        $this->assign('type',$type);
        $info=db('goods_service')->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

	//商品报价列表
    public function goods_quote()
    {
        $param = $this->request->param();

        $type=$this->request->param('category',0,'intval');
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        // var_dump($param);
        $where=[];
        if(!empty($keyword)){

            if(empty($type)){
                $where[]=['xhmc|ppmc|lbmc','like','%'.$keyword.'%'];
            }else if($type==1){
                $where[]=['xhmc','like','%'.$keyword.'%'];
            }else if($type==2){
                $where[]=['ppmc','like','%'.$keyword.'%'];
            }else if($type==3){
                $where[]=['lbmc','like','%'.$keyword.'%'];
            }
        }else{
            $where['id']=0;
        }
        $goodsModel= new goodsModel();
        $list=$goodsModel->with('parts,services')->where($where)->order('id DESC')->paginate(50,false,['query'=>$param]);
        // var_dump($goodsModel->getLastSql());
        $page=$list->render();
        $this->assign('keyword', $keyword ? $keyword : '');
        $this->assign('category', $type ? $type : 0);
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();

    }


    //商品报价提交
    public function goods_quote_post(){

        $id=$this->request->param('id',0,'intval');
        $price=$this->request->param('price');
        $zyjg=$this->request->param('zyjg');
        $jgsfyy=$this->request->param('jgsfyy');
        $fwcn=$this->request->param('fwcn');
		$goods_url=$this->request->param('goods_url');
        if(empty($id)){
            $this->error('参数错误');
        }
		if(empty($goods_url)){
            $this->error('请输入商品地址');
        }
        if(empty($price)){
            $this->error('请输入价格');
        }
        if(empty($zyjg)){
            $this->error('请输入自有价格');
        }
		if(!is_numeric($price)){
			$this->error('价格只能是数字，不能含有字母符号');
		}
        $goodsModel = new goodsModel();
        $goods=$goodsModel->where('id',$id)->find();
        if(floatval($price)>floatval($goods['quote_price']) && empty($jgsfyy)){
            $this->error('请输入价格上浮原因');
        }
        $client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
         $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
         $m['xhbh']=$goods['xhbh'];
         $m['xhmc']=$goods['xhmc'];
         $m['pmbh']=$goods['pmbh'];
         $m['pmmc']=$goods['pmmc'];
         $m['ppbh']=$goods['ppbh'];
         $m['ppmc']=$goods['ppmc'];
         $m['lbbh']=$goods['lbbh'];
         $m['lbmc']=$goods['lbmc'];
         $m['sjjg']=$price;
         $m['zyjg']=$zyjg;
         $m['jgsfyy']=$jgsfyy;
         if(!empty($fwcn)){
            $m['fwcn']=$fwcn;
         }
         //$m['productlink']='http://xxhuizhidianzi.com/index.php?s=/goods/goodsinfo&goodsid=38';
		 $m['productlink']= $goods_url;
          //var_dump($m);
         $mm=json_encode($m);
         $res = $client->execute(array('in0'=>$mm));/* 调用方法 */
         $result=json_decode($res->out,true);
         if($result['resultFlag']=='Y'){
			$status = 0;
			if(isset($result['price'])){
				//db('goods')->where('id',$id)->update(['quote_status'=>1,'quote_price'=>$price,'zyjg'=>$zyjg,'goods_url'=>$goods_url]);
                $merchant_id = cmf_get_current_merchant_id();
                $MerchantQuote = new MerchantQuoteModel();
                $is_exit = $MerchantQuote->where('merchant_id',$merchant_id)->where('goods_id',$id)->where('type',0)->find();
                if($is_exit){
                    $map['quote_price'] = $price;
                    $map['zyjg'] = $zyjg;
                    $map['goods_url'] = $goods_url;
                    $MerchantQuote->update_quote($is_exit['id'],$map);
                }else{
                    $MerchantQuote->add_quote($id,$m);
                }
			}else{
				$status = 1;
			}
			db('offer_log')->insert([
                'merchant_id'   => $merchant_id,
				'goods_id'		=> $id,
				'price'			=> $price,
				'status'		=> $status,
				'create_time'	=> time(),
			]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
    }

    //配件报价提交
    public function parts_quote_post(){

        $id=$this->request->param('id',0,'intval');
        $price=$this->request->param('price');
        $jgsfyy=$this->request->param('jgsfyy');
        if(empty($id)){
            $this->error('参数错误');
        }
        if(empty($price)){
            $this->error('请输入价格');
        }
		if(!is_numeric($price)){
			$this->error('价格只能是数字，不能含有字母符号');
		}
        $parts=db('goods_parts')->where('id',$id)->find();
		$goodsb=db('goods')->field("xhbh,xhmc")->where(['id'=>$parts['goods_id']])->find();
        /*if(floatval($price)>floatval($parts['quote_price']) && empty($jgsfyy)){
            $this->error('请输入价格上浮原因');
        }*/

        $client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		$m['pwd']=$this->zspwdconfig;  
        $m['pmbh']=$parts['pmbh'];
        $m['pmmc']=$parts['pmmc'];
		$m['xhbh']=$goodsb['xhbh'];
        $m['xhmc']=$goodsb['xhmc'];
        $m['pjbh']=$parts['PJBH'];//配件编号
        $m['pjmc']=$parts['PJMC'];//配件名称
        $m['pjjg']=$price;//配件价格
        $m['bjyy']=$jgsfyy;//修改报价原因
        //$m['productlink']='http://xxhuizhidianzi.com/index.php?s=/goods/goodsinfo&goodsid=38';
        $mm=json_encode($m);
        $res = $client->quotedpricePjByPjbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
        if($result['resultFlag']=='Y'){
            ///db('goods_parts')->where('id',$id)->update(['quote_status'=>1,'quote_price'=>$price]);
            $merchant_id = cmf_get_current_merchant_id();
            $MerchantQuote = new MerchantQuoteModel();
            $is_exit = $MerchantQuote->where('merchant_id',$merchant_id)->where('parts_id',$id)->find();
            if($is_exit){
                $map['quote_price'] = $price;
                $MerchantQuote->update_quote($is_exit['id'],$map);
            }else{
                $MerchantQuote->add_part_quote($parts['goods_id'],$price,$id);
            }
			db('offer_part_log')->insert([
                'merchant_id'   => $merchant_id,
				'goods_part_id'	=> $id,
				'price'			=> $price,
				'status'		=> 0,
				'create_time'	=> time(),
			]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }

    }


    //增值服务报价提交
    public function service_quote_post(){
		
		$id=$this->request->param('id',0,'intval');
        $price=$this->request->param('price');
        $jgsfyy=$this->request->param('jgsfyy');
        if(empty($id)){
            $this->error('参数错误');
        }

        if(empty($price)){
            $this->error('请输入价格');
        }
		if(!is_numeric($price)){
			$this->error('价格只能是数字，不能含有字母符号');
		}
        $service=db('goods_service')->where('id',$id)->find();
		$goodsb=db('goods')->field("xhbh,xhmc")->where(['id'=>$service['goods_id']])->find();
        if(floatval($price)>floatval($service['quote_price']) && empty($jgsfyy)){
            $this->error('请输入价格上浮原因');
        }

        $client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;         
         $m['pmbh']=$service['pmbh'];
         $m['pmmc']=$service['pmmc'];
		 $m['xhbh']=$goodsb['xhbh'];
         $m['xhmc']=$goodsb['xhmc'];
         $m['fwbh']=$service['fwbh'];//服务编号
         $m['fwmc']=$service['fwmc'];//服务名称
         $m['fwjg']=$price;//服务价格
         $m['bjyy']=$jgsfyy;//修改服务报价原因
		 $m['zt']=$service['zt'];//修改服务报价原因
         $mm=json_encode($m);
         $res = $client->quotedpriceFwByFwbh(array('in0'=>$mm));/* 调用方法 */
         $result=json_decode($res->out,true);
		// dump($result);exit;
         if($result['resultFlag']=='Y'){
            //db('goods_service')->where('id',$id)->update(['quote_status'=>1,'quote_price'=>$price]);
            $merchant_id = cmf_get_current_merchant_id();
            $MerchantQuote = new MerchantQuoteModel();
            $is_exit = $MerchantQuote->where('merchant_id',$merchant_id)->where('service_id',$id)->find();
            if($is_exit){
                $map['quote_price'] = $price;
                $MerchantQuote->update_quote($is_exit['id'],$map);
            }else{
                $MerchantQuote->add_service_quote($service['goods_id'],$price,$id);
            }
			db('offer_service_log')->insert([
                'merchant_id'   => $merchant_id,
				'goods_service_id'	=> $id,
				'price'			=> $price,
				'status'		=> 0,
				'create_time'	=> time(),
			]);
            $this->success($result['resultMessage']);
         }else{
            $this->error($result['resultMessage']);
         }  
    }
    //报价商品
    public function quote_goods(){
        $merchant_id = cmf_get_current_merchant_id();
        $param = $this->request->param();
        $type=$this->request->param('category',0,'intval');
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        $where=[];
        if(!empty($keyword)){
            if(empty($type)){
                $where[]=['xhmc|ppmc|lbmc','like','%'.$keyword.'%'];
            }else if($type==1){
                $where[]=['xhmc','like','%'.$keyword.'%'];
            }else if($type==2){
                $where[]=['ppmc','like','%'.$keyword.'%'];
            }else if($type==3){
                $where[]=['lbmc','like','%'.$keyword.'%'];
            }
            $goodsModel= new goodsModel();
            $goods_ids = $goodsModel->where($where)->column('goods_id');
            if($goods_ids){
                $where[]=['goods_id','in',$goods_ids];
            } 
        }
        $MerchantQuote = new MerchantQuoteModel();
        $list=$MerchantQuote->where('merchant_id',$merchant_id)->with('goods')->order('id DESC')->paginate(50,false,['query'=>$param])->each(function($item,$key){
            $isset_parts=db('goods_parts')->where('goods_id',$item['goods']['id'])->count();
            if($isset_parts){
                $item['goods']['parts'] = 1;
            }else{
                $item['goods']['parts'] = 0;
            }
            $isset_service=db('goods_service')->where('goods_id',$item['goods']['id'])->count();
            if($isset_service){
                $item['goods']['service'] = 1;
            }else{
                $item['goods']['service'] = 0;
            }
            return $item;
        });
        // var_dump($goodsModel->getLastSql());
        $page=$list->render();
        $this->assign('keyword', $keyword ? $keyword : '');
        $this->assign('category', $type ? $type : 0);
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }
	
	//报价记录
	public function offer_log(){
		$param = $this->request->param();
        $merchant_id = cmf_get_current_merchant_id();
		$offerModel= new OfferLogModel();
		$offerData = $offerModel->where('merchant_id',$merchant_id)->where('goods_id',$param['id'])->order('id desc')->paginate(20);
		$page=$offerData->render();
		$this->assign('list', $offerData);
        $this->assign('page', $page);
        return $this->fetch();
	}
	
	//上架
	public function shangjia(){
		$param = $this->request->param();
		$goodsModel=db('goods');
		$goods = $goodsModel->find($param['id']);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		$m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
        $m['zt']=1;
        $m['xjyy']='暂时不需要';
        $mm=json_encode($m);
        $res = $client->execSpDown(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $MerchantQuote = new MerchantQuoteModel();
            $MerchantQuote->where('merchant_id',$merchant_id)->where('goods_id',$param['id'])->update(['zt'=>2]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	
	//下架
	public function xiajia(){
		
		$id = $this->request->param("id");
		$this->assign('id', $id);
        return $this->fetch();
	}
	//下架
	public function xiajiapost(){
		
		$param = $this->request->param();
		$goodsModel=db('goods');
		$goods = $goodsModel->find($param['id']);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
        $m['zt']=2;
        $m['xjyy']=$param['xjyy'];
        $mm=json_encode($m);
        $res = $client->execSpDown(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
        $MerchantQuote = new MerchantQuoteModel();
		if($result['resultFlag']=='Y'){
            $MerchantQuote->where('merchant_id',$merchant_id)->where('goods_id',$param['id'])->update(['zt'=>1]);
            $this->success($result['resultMessage']);
        }else{
			$MerchantQuote->where('merchant_id',$merchant_id)->where('goods_id',$param['id'])->update(['zt'=>1]);
            $this->error($result['resultMessage']);
        }
	}
	//配件上架
	public function peijianshangjia(){
		$param = $this->request->param();
		$goods_parts=db('goods_parts');
		$partdata = $goods_parts->find($param['id']);
		$goods=db("goods")->field("xhbh")->where(['id'=>$partdata['goods_id']])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
		$m['pjbh']=$partdata['PJBH'];
        $m['zt']=1;
        $m['xjyy']='暂时不需要';
        $mm=json_encode($m);
        $res = $client->delPjbjByPjbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $goods_parts->where('id',$param['id'])->update(['zt'=>1]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	//配件下架
	public function peijianxiajia(){
		$id = $this->request->param("id");
		$this->assign('id', $id);
        return $this->fetch();
		
	}
	//配件下架
	public function peijianxiajiapost(){
		
		$param = $this->request->param();
		$goods_parts=db('goods_parts');
		$partdata = $goods_parts->find($param['id']);
		$goods=db("goods")->field("xhbh")->where(['id'=>$partdata['goods_id']])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		$m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
		$m['pjbh']=$partdata['PJBH'];
        $m['zt']=2;
        $m['xjyy']=$param['xjyy'];
        $mm=json_encode($m);
        $res = $client->delPjbjByPjbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $goods_parts->where('id',$param['id'])->update(['zt'=>2]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	//增值服务上架
	public function zzfwshangjia(){
		$param = $this->request->param();
		$goods_service=db('goods_service');
		$servicedata = $goods_service->find($param['id']);
		$goods=db("goods")->field("xhbh")->where(['id'=>$servicedata['goods_id']])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
		$m['fwbh']=$servicedata['fwbh'];
        $m['zt']=1;
        $m['xjyy']='暂时不需要';
        $mm=json_encode($m);
        $res = $client->delFwbjByFwbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $goods_service->where('id',$param['id'])->update(['shangjia_zt'=>1]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	//增值服务下架
	public function zzfwxiajia(){
		$id = $this->request->param("id");
		$this->assign('id', $id);
        return $this->fetch();
	}
	//增值服务下架
	public function zzfwxiajiapost(){
		$param = $this->request->param();
		$goods_service=db('goods_service');
		$servicedata = $goods_service->find($param['id']);
		$goods=db("goods")->field("xhbh")->where(['id'=>$servicedata['goods_id']])->find();
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
		$m['fwbh']=$servicedata['fwbh'];
        $m['zt']=2;
        $m['xjyy']=$param['xjyy'];
        $mm=json_encode($m);
        $res = $client->delFwbjByFwbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $goods_service->where('id',$param['id'])->update(['shangjia_zt'=>2]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	//撤销报价
	public function chexiao(){
		$param = $this->request->param();
		$goodsModel=db('goods');
		$goods = $goodsModel->find($param['goods_id']);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
        $mm=json_encode($m);
        $res = $client->qxShByXhbh(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            //$goodsModel->where('id',$param['id'])->update(['quote_status'=>0]);
			$offerModel= new OfferLogModel();
			$offerModel->where('id',$param['id'])->update(['status'=>2]);
            $this->success($result['resultMessage']);
        }else{
            $this->error($result['resultMessage']);
        }
	}
	//报价记录审核通过
	public function baojiasucess(){
		$param = $this->request->param();
		$offerModel= new OfferLogModel();
		$offerdata=$offerModel->where('id',$param['id'])->find();
		$offerModel->where('id',$param['id'])->update(['status'=>0]);
		$info=db('goods')->where(['id'=>$param['goods_id']])->update(['quote_price'=>$offerdata['price']]);
		if($info){
			$this->success('操作成功');
		}else{
			$this->success('操作失败');
		}	
	}
	//商品假删除
	public function deletegoods(){
		$param = $this->request->param();
		$info=db('goods')->where(['id'=>$param['id']])->update(['isdel'=>1]);
		if($info){
			$this->success('删除成功');
		}else{
			$this->success('删除失败');
		}
	}
	//商品获取
	public function goods_get(){
        return $this->fetch();
	}
	public function goods_getdetail_post(){
		//echo "111";exit;
		$list=array();
		$xhbh=$this->request->param('xhbh','');
		set_time_limit(0);
		ini_set('default_socket_timeout', 8000);
		
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
		 $m['xhbh']=$xhbh;
		 
		 $mm=json_encode($m);
		 $res = $client->findSpByXhbh(array('in0'=>$mm));/* 调用方法 */
		 $result=json_decode($res->out,true);
		 ///print_R($result);exit;
		 if($result['resultFlag'] == 'Y'){	
		 	$aaa=db('goods')->where('xhbh',$xhbh)->find();
		 	if($aaa){
		 		if(!empty($result['zt'])){
		 			//$info = db('goods')->where('xhbh',$xhbh)->update(['zt'=>$result['zt'],'spzt'=>$result['spzt']]);
					$info = db('goods')->where('xhbh',$xhbh)->update(['spzt'=>$result['spzt']]);
		 		}
		 		if(!empty($result['price'])){
		 			$info = db('goods')->where('xhbh',$xhbh)->update(['quote_price'=>$result['price'],'spzt'=>$result['spzt']]);
		 		}
		 		db('goods')->where('xhbh',$xhbh)->update(['pmmc'=>$result['pmmc'],'xhmc'=>$result['xhmc'],'pmbh'=>$result['pmbh'],'ppbh'=>$result['ppbh'],'ppmc'=>$result['ppmc'],'lbbh'=>$result['lbbh'],'lbmc'=>$result['lbmc']]);
		 		if(!empty($result['accessoryList'])){
		 			foreach ($result['accessoryList'] as $k=>$v){
		 					db('goods_parts')->where('PJBH',$v['PJBH'])->update(['PJMC'=>$v['PJMC']]);
		 			}
		 		}
		 		if(!empty($result['serviceList'])){
		 			foreach ($result['serviceList'] as $k=>$v){
		 					db('goods_service')->where('FWBH',$v['FWBH'])->update(['FWMC'=>$v['FWMC']]);
		 			}
		 		}
		 	}
							
		 }	 
		$this->assign("result",$result);
		return $this->fetch();	
	}
	public function goods_get_post(){
		$starttime=$this->request->param('starttime','');
		$endtime=date('Y-m-d',strtotime($starttime)+3600*24);
		$list=array();
	
		set_time_limit(0);
		ini_set('default_socket_timeout', 8000);
		$time = date('YmdHis',strtotime($endtime));
		$start_time = date('YmdHis',strtotime($starttime));
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
				 $list=$result['spList'];
				 $in=[];
				 //print_r($list);exit;
				 foreach ($list as $key=>$v){
					$arr = $v;
					$info = db('goods')->where('xhbh',$v['xhbh'])->count();
					//var_dump($info);
					if($info==0){
						$arr['parametersList'] = json_encode($v['parametersList']);
						$arr['create_time'] = time();
						array_push($in, $arr);
					}else{
						//db('goods')->where('xhbh',$v['xhbh'])->update(['status'=>0]);
					}									
				}
				
				//var_dump(count($in));
				db("goods")->insertAll($in);
				//sleep(15);
			 }	
		}	 
		//print_r($list);	exit;
		$this->assign("list",$list);
		return $this->fetch();
		
	}
	public function findSpSfbj(){
		$id = $this->request->param("id");
		$goodsModel=db('goods');
		$goods = $goodsModel->find($id);
		$client = new \SoapClient(config('config.zs_url'),array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		$m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
        $mm=json_encode($m);
        $res = $client->findSpSfbj(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		//print_R($result);exit;
		if($result['resultFlag']=='N'){
			$result['spjg']='无';
			$result['sprksj']='无';
		}
		$this->assign('result', $result);
        return $this->fetch();
        
	}
     //导出商品
    public function export(){
       /* ini_set("memory_limit", "2000M");
        set_time_limit(0);
       */
        //$where = "";
       /* if(!empty($request['name']) && $request['name']!=-1){
            $where['o.username'] = ['like', "%$request[name]%"];
        }
        if(!empty($request['title']) && $request['title']!=-1 ){
            $where['s.title'] = ['like', "%$request[title]%"];
        }
        if(!empty($request['school']) && $request['school']!=-1){
            $where['s.school'] = ['like', "%$request[school]%"];
        }
        if(!empty($request['order_sn']) && $request['order_sn']!=-1){
            $where['o.transaction_id'] = $request['order_sn'];
        }
       */
        $param = $this->request->param();

        $type=$this->request->param('category',0,'intval');
        $keyword=$this->request->param('keyword','');
        $keyword=trim($keyword);
        // var_dump($param);
        $where=[];
        if(!empty($keyword) && $keyword !=-1){

            if($type==-1){
                $where[]=['xhmc|ppmc|lbmc','like','%'.$keyword.'%'];
            }else if($type==1){
                $where[]=['xhmc','like','%'.$keyword.'%'];
            }else if($type==2){
                $where[]=['ppmc','like','%'.$keyword.'%'];
            }else if($type==3){
                $where[]=['lbmc','like','%'.$keyword.'%'];
            }
        }
        
        $goodsModel=db('goods');
        $list=$goodsModel->where($where)->field('xhmc,pmmc,ppmc,lbmc')->order('id DESC')->select()->toArray();

        $title = [' 型号名称','品目名称','品牌名称','类别名称'];
        $now = date('YmdHis',time()).".xls";
        $this->csv_export($list,$title,$now);
    }

    //导出商品
    function csv_export($data = array(), $headlist = array(), $fileName) {
        ini_set('memory_limit','2000M');
        ini_set('max_execution_time',0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=".$fileName);
        $fp=fopen('php://output','w');
        fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));//转码 防止乱码(比如微信昵称(乱七八糟的))
        fputcsv($fp,$headlist);
        $index = 0;
        $new_item = [];
        foreach ($data as $k=>$item) {
            if($index==1000){
                $index=0;
                ob_flush();
                flush();
            }
            $index++;
           /* $new_item['title'] = $item['title'];
            $new_item['school'] = $item['school'];
            $new_item['name'] = $item['name'];
            $new_item['sex'] = $item['sex'];
            $new_item['grade'] = $item['grade'];
            $new_item['class'] = $item['class'];
            $new_item['tel'] = $item['tel'];
            $new_item['size'] = $item['size'];
            $new_item['size2'] = $item['size2'];
            $new_item['size3'] = $item['size3'];
            $new_item['size4'] = $item['size4'];
            $new_item['num'] = $item['num'];
            $new_item['total_money'] = $item['total_money'];
            $new_item['pay_money'] = $item['pay_money'];
            $new_item['addtime'] = date('Y-m-d H:i:s', $item["addtime"]);
            $new_item['transaction_id'] = html_entity_decode("&nbsp;".$item['transaction_id']);*/
            

            //$new_item['openid'] = $item['openid'];
            //var_dump($new_item);die;
            fputcsv($fp,$item);
        }
        ob_flush();
        flush();
        ob_end_clean();
        die();
    }
	
}