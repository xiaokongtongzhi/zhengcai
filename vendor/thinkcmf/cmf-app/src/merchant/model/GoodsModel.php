<?php

namespace app\merchant\model;

use think\Model;

class GoodsModel extends Model
{

    public function parts(){
		
		return $this->hasMany('GoodsPartsModel','goods_id','id');

	}

	public function services(){
		
		return $this->hasMany('GoodsServiceModel','goods_id','id');
		
	}

}