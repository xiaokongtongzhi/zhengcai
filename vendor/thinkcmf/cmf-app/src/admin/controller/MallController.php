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
use app\admin\model\GoodsModel;
use app\admin\model\OfferLogModel;
use app\admin\model\OfferPartLogModel;
use app\admin\model\OfferServiceLogModel;

class MallController extends AdminBaseController
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
        }
        $goodsModel=db('goods');
        $list=$goodsModel->where('quote_status',1)->where($where)->order('id DESC')->paginate(20,false,['query'=>$param])->each(function($item,$key){
            $isset_parts=db('goods_parts')->where('goods_id',$item['id'])->count();
            if($isset_parts){
                $item['parts'] = 1;
            }else{
                $item['parts'] = 0;
            }
            $isset_service=db('goods_service')->where('goods_id',$item['id'])->count();
            if($isset_service){
                $item['service'] = 1;
            }else{
                $item['service'] = 0;
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
	public function add(){
		$pmmcdata=db('pmmc')->select()->toArray();
		$lbmcdata=db('lbmc')->select()->toArray();
		$this->assign('pmmcdata', $pmmcdata);
		$this->assign('lbmcdata', $lbmcdata);
		return $this->fetch();
	}
	public function addpost(){
		$param = $this->request->param();
		$param['post']['pmmc']=db("pmmc")->where(['pmbh'=>$param['post']['pmbh']])->value("pmmc");
		$param['post']['lbmc']=db("lbmc")->where(['lbbh'=>$param['post']['lbbh']])->value("lbmc");
		$param['post']['quote_status']=1;
		$param['post']['zt']=2;
		$goodsModel=db('goods');
		$res = $goodsModel->insertGetId($param['post']);
		if($res){
			$this->success("保存成功");
		}else{
			$this->error("保存失败");
		}	
	}
	public function edit(){
		$id = $this->request->param('id', 0, 'intval');
		$post=db('goods')->where(['id'=>$id])->find();
		$pmmcdata=db('pmmc')->select()->toArray();
		$lbmcdata=db('lbmc')->select()->toArray();
		
		$this->assign('id', $id);
		$this->assign('post', $post);
		$this->assign('pmmcdata', $pmmcdata);
		$this->assign('lbmcdata', $lbmcdata);
		return $this->fetch();	
	}
	public function editpost(){
		$id = $this->request->param('id', 0, 'intval');
		$param = $this->request->param();
		$param['post']['pmmc']=db("pmmc")->where(['pmbh'=>$param['post']['pmbh']])->value("pmmc");
		$param['post']['lbmc']=db("lbmc")->where(['lbbh'=>$param['post']['lbbh']])->value("lbmc");
		$goodsModel=db('goods');
		$res = $goodsModel->where(['id'=>$id])->update($param['post']);
		if($res){
			$this->success("保存成功");
		}else{
			$this->error("保存失败");
		}
	}
	//本地商城上架
	public function localup(){
		$id = $this->request->param('id', 0, 'intval');
		//$param = $this->request->param();
		$goodsModel=db('goods');
		$res = $goodsModel->where(['id'=>$id])->update(['zt'=>2]);
		if($res){
			$this->success("上架成功");
		}else{
			$this->error("上架失败");
		}
	}
	//本地商城下架
	public function localdown(){
		$id = $this->request->param('id', 0, 'intval');
		//$param = $this->request->param();
		$goodsModel=db('goods');
		$res = $goodsModel->where(['id'=>$id])->update(['zt'=>1]);
		if($res){
			$this->success("下架成功");
		}else{
			$this->error("下架失败");
		}
	}
	//上架
	public function shangjia(){
		$id = $this->request->param('id', 0, 'intval');
		$post=db('goods')->where(['id'=>$id])->find();
		$this->assign('id', $id);
		$this->assign('post', $post);
		return $this->fetch();		
	}
	public function shangjiapost(){
		$id = $this->request->param('id', 0, 'intval');
		$param = $this->request->param();
		$goodsModel=db('goods');
		$res = $goodsModel->where(['id'=>$id])->update($param['post']);
		if($res){
			$this->success("保存成功");
		}else{
			$this->error("保存失败");
		}
	}
	//下架
	public function xiajia(){
		
		$param = $this->request->param();
		$goodsModel=db('goods');
		$goods = $goodsModel->find($param['id']);
		$client = new \SoapClient("http://222.143.21.205:8091/wsscservices_test/services/wsscWebService?wsdl",array('encoding'=>'utf-8'));
        $m['username']=$this->zsnameconfig;
		 $m['pwd']=$this->zspwdconfig;
        $m['xhbh']=$goods['xhbh'];
        $m['zt']=2;
        $m['xjyy']='暂时不需要';
        $mm=json_encode($m);
        $res = $client->execSpDown(array('in0'=>$mm));/* 调用方法 */
        $result=json_decode($res->out,true);
		if($result['resultFlag']=='Y'){
            $goodsModel->where('id',$param['id'])->update(['zt'=>1]);
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
		$client = new \SoapClient("http://222.143.21.205:8091/wsscservices_test/services/wsscWebService?wsdl",array('encoding'=>'utf-8'));
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

}