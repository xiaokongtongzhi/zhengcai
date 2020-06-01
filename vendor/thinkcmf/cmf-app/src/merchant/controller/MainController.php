<?php

namespace app\merchant\controller;

use cmf\controller\MerchantBaseController;
use think\Db;
use app\admin\model\Menu;

class MainController extends MerchantBaseController
{

    /**
     *  后台欢迎页
     */
    public function index()
    {
        //商品统计
		$goodscount=db("goods")->count();
		$goods['total']=$goodscount;
		$weibaojiacount=db("goods")->where(['quote_status'=>0])->count();
		$goods['weibaojia']=$weibaojiacount;
		$yibaojiacount=db("goods")->where(['quote_status'=>1])->count();
		$goods['yibaojia']=$yibaojiacount;
		$cexiaocount=db("offer_log")->where(['status'=>2])->count();
		$goods['cexiao']=$cexiaocount;
		$ordercount=db("order")->count();		
		$order['total']=$ordercount;
		$daiquerencount=db("order")->where(['status'=>0])->count();//待送货
		$order['daiqueren']=$daiquerencount;
		$daisonghuocount=db("order")->where(['status'=>1])->count();//待送货
		$order['daisonghuo']=$daisonghuocount;
		$daishouhuocount=db("order")->where(['status'=>2])->count();//待收货
		$order['daishouhuo']=$daishouhuocount;
		$daikaipiaocount=db("order")->where(['status'=>3])->count();//待开票
		$order['daikaipiao']=$daikaipiaocount;
		$daishoukuancount=db("order")->where(['status'=>4])->count();//待收款
		$order['daishoukuan']=$daishoukuancount;
		$yiwanchengcount=db("order")->where(['status'=>5])->count();//已完成
		$order['yiwancheng']=$yiwanchengcount;
		$yijujuecount=db("order")->where(['status'=>10])->count();//已拒绝
		$order['yijujue']=$yijujuecount;
		$kaipiao['daikaipiao']=$order['daikaipiao'];
		$yikaipiaocount=db("order")->where(['state'=>1])->count();//已拒绝
		$kaipiao['yikaipiao']=$yikaipiaocount;
		$yisurecount=db("order")->where("status>0 and status!=10")->count();//已完成
		$tongji['yisure']=$yisurecount;
		$yisuremoney=db("order")->where("status>0 and status!=10")->sum('ddze');//已完成
		$tongji['yisuremoney']=$yisuremoney;
		$premonth=$this->getDateInfo(1);//上个月
		$daymonth=$this->getDateInfo(4);//最近30天
		$orderdaymonth=$this->getDateInfo(5);//最近30天
		$premonthyisurecount=db("order")->where("status>0 and status!=10 and cjrq>$premonth[firstday] and cjrq<$premonth[lastday]")->count();//上个月已完成
		$tongji['premonth_yisure']=$premonthyisurecount;
		$premonthyisuremoney=db("order")->where("status>0 and status!=10 and cjrq>$premonth[firstday] and cjrq<$premonth[lastday]")->sum('ddze');//上个月已完成
		$tongji['premonth_yisuremoney']=$premonthyisuremoney;
		$daymonthyisurecount=db("order")->where("status>0 and status!=10 and cjrq>$daymonth[firstday] and cjrq<$daymonth[lastday]")->count();//最近30天已完成
		$tongji['daymonth_yisure']=$daymonthyisurecount;
		$daymonthyisuremoney=db("order")->where("status>0 and status!=10 and cjrq>$daymonth[firstday] and cjrq<$daymonth[lastday]")->sum('ddze');//最近30天已完成
		$tongji['daymonth_yisuremoney']=$daymonthyisuremoney;
		//print_r($tongji);
		
		for($i=30;$i>0;$i--){
			$riqi[]=date('m-d', strtotime('-'.$i.' day'));
			$first=date('Ymd000000', strtotime('-'.$i.' day'));
			$last=date('Ymd000000', strtotime('-'.($i-1).' day'));
			$daycount=db("order")->where("status>0 and status!=10 and cjrq>$first and cjrq<=$last")->count();//已完成
			$num[]=$daycount;
			$daycountmoney=db("order")->where("status>0 and status!=10 and cjrq>$first and cjrq<=$last")->sum('ddze');//已完成
			$money[]=$daycountmoney;
		}
		$turiqi=json_encode($riqi);
		$tunum=json_encode($num);
		$tumoney=json_encode($money);
		//$turiqi="[".$turiqi."]";
		//echo $turiqi;
		$allorder=db("order")->where("status>0 and status!=10")->select()->toArray();
		foreach($allorder as $k=>$v){
			$a=json_decode($v['productList'],true);
			foreach($a as $b){
				$new[]=$b['PPMC'].$b['XHMC'];
			}
		}
		//print_r($new);
		$new=array_count_values($new);
		//print_r($new);
		$new=array_slice($new,0,10);
		$this->assign("new",$new);
		$this->assign("turiqi",$turiqi);
		$this->assign("tunum",$tunum);
		$this->assign("tumoney",$tumoney);
		$this->assign("goods",$goods);
		$this->assign("order",$order);
		$this->assign("kaipiao",$kaipiao);
		$this->assign("tongji",$tongji);
        return $this->fetch();
    }
	public function getDateInfo($type)
	{
	  $data = array(
		array(
		  'firstday' => date('Ym01000000', strtotime('-1 month')),
		  'lastday' => date('Ymt000000', strtotime('-1 month')),
		),
		array(
		  'firstday' => date('Ym01000000', strtotime(date("Y-m-d"))),
		  'lastday' => date('Ymd000000', strtotime((date('Ym01', strtotime(date("Y-m-d")))) . " +1 month -1 day")),
		),
		array(
		  'firstday' => date('Ymd000000', strtotime("-15 day")),
		  'lastday' => date('Ymd000000', strtotime('-1 day')),
		),
		array(
		  'firstday' => date('Ymd000000', strtotime("-30 day")),
		  'lastday' => date('Ymd000000', strtotime('-1 day')),
		),
		array(
		  'firstday' => date('m-d', strtotime("-30 day")),
		  'lastday' => date('m-d', strtotime('-1 day')),
		),
	  );
	  return is_null($type) ? $data : $data[$type-1];
	}
    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

}
