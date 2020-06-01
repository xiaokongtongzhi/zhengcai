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

class IndexController extends HomeBaseController
{

    // 首页
    public function index()
    {	
      echo "hello word!";exit;
		$catedata=db("goods")->field("lbbh,lbmc")->where(['quote_status'=>1])->group("lbbh")->limit(8)->select()->toArray();		
		$onedata=db("goods")->field("id,pic,xhmc,price,lbbh,lbmc")->where(['lbbh'=>'0081'])->where("quote_status=1 or price!='0.00'")->limit(10)->select()->toArray();//计算机
		$twodata=db("goods")->field("id,pic,xhmc,price,lbbh,lbmc")->where(['lbbh'=>'0085'])->where("quote_status=1 or price!='0.00'")->limit(10)->select()->toArray();//办公外设
		$threedata=db("goods")->field("id,pic,xhmc,price,lbbh,lbmc")->where(['lbbh'=>'00040001002100060001'])->where("quote_status=1 or price!='0.00'")->limit(10)->select()->toArray();//办公家具
		$data=db("goods")->field("id,lbbh,lbmc,xhmc,pic,price,quote_price")->where("quote_status=1 or price!='0.00'")->order("id desc")->limit(20)->select()->toArray();
		$this->assign("catedata",$catedata);
		$this->assign("onedata",$onedata);
		$this->assign("twodata",$twodata);
		$this->assign("threedata",$threedata);
		$this->assign("data",$data);
        return $this->fetch(":index");
    }
	//列表
	public function cate(){
		$lbbh=$this->request->param("lbbh");
		$catedata=db("goods")->field("lbbh,lbmc")->where(['quote_status'=>1])->group("lbbh")->select()->toArray();
		$data=db("goods")->field("id,lbbh,lbmc,xhmc,pic,price,quote_price")->where(['quote_status'=>1,'lbbh'=>$lbbh])->select()->toArray();
		//dump($data);exit;
		$this->assign("catedata",$catedata);
		$this->assign("data",$data);
        return $this->fetch(":cate");
		
	}
	//详情
	public function detail(){
		$id=$this->request->param("id");
		$data=db("goods")->field("*")->where(['quote_status'=>1,'id'=>$id])->find();
		$recommenddata=db("goods")->field("id,lbbh,lbmc,xhmc,pic,price,quote_price")->where(['quote_status'=>1])->order("id desc")->limit(6)->select()->toArray();
		$this->assign("data",$data);
		$this->assign("recommenddata",$recommenddata);
        return $this->fetch(":detail");
		
	}
}

