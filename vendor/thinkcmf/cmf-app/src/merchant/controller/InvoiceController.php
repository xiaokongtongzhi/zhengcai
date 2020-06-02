<?php
namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use app\admin\model\OrderModel;

class InvoiceController extends MerchantBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

   	//商品列表
    public function invoiced()
    {
        $param = $this->request->param();

       /* $type=$this->request->param('category',0,'intval');
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
        }*/
        $merchant_id = cmf_get_current_merchant_id();
        $orderModel=db('order');
    	$list=$orderModel->where("state=1")->where('merchant_id',$merchant_id)->order('id DESC')->paginate(100,false,['query'=>$param]);
        // var_dump($goodsModel->getLastSql());
		$page=$list->render();
       // $this->assign('keyword', $keyword ? $keyword : '');
        //$this->assign('category', $type ? $type : 0);
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
    	$this->assign('info',$info);
    	return $this->fetch();

    }

}