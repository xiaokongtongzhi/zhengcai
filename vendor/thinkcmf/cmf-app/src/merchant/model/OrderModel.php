<?php

namespace app\merchant\model;

use think\Model;

class OrderModel extends Model
{
	public function getDeliverycityAttr($value){
		return db('region')->where('qydm',$value)->value('qymc');
	}

	public function getDeliverycountyAttr($value){
		return db('region')->where('qydm',$value)->value('qymc');
	}

}