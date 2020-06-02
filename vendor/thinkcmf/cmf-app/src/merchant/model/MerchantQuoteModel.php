<?php

namespace app\merchant\model;

use think\Model;

class MerchantQuoteModel extends Model
{

	public function goods(){
		
		return $this->belongsTo('GoodsModel', 'goods_id');
		
	}


	public function add_quote($goods_id,$data){

		$this->insert([
			'merchant_id' => cmf_get_current_merchant_id(),
			'goods_id'	=> $goods_id,
			'type'	=> 1,
			'quote_price'	=> $data['sjjg'],
			'create_time'	=> time(),
			'zyjg'	=> $data['zyjg'],
			'goods_url'	=> $data['productlink']
		]);
	
	}

	public function add_part_quote($goods_id,$price,$parts_id=0){

		$this->insert([
			'merchant_id' => cmf_get_current_merchant_id(),
			'goods_id'	=> $goods_id,
			'type'	=> 2,
			'parts_id'	=> $parts_id,
			'quote_price'	=> $price,
			'create_time'	=> time()
		]);
	}

	public function add_service_quote($goods_id,$price,$service_id=0){

		$this->insert([
			'merchant_id' => cmf_get_current_merchant_id(),
			'goods_id'	=> $goods_id,
			'type'	=> 3,
			'service_id'	=> $service_id,
			'quote_price'	=> $price,
			'create_time'	=> time()
		]);
	}

	public function update_quote($id,$data){

		$this->where('id',$id)->update($data);
	}


}