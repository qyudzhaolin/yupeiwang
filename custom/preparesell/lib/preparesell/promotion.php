<?php

class preparesell_preparesell_promotion
{
	function __construct($app)
	{
		$this->app = $app;
		$this->mdl_product = app::get('b2c')->model('products');
	}

	function extend_filter($filter)
    {
        return array(
            'get_filter_by_class' => 'true',
            'filter_class'        => 'preparesell_preparesell_promotion',
            'filter_method'       => 'get_filter',
            'filter_params'       => json_encode($filter),
        );

    }

	//团购排他
    function get_filter($filter)
	{
        $filter = json_decode($filter, true);

        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $preparesell = $mdl_preparesell->getRow('promotion_pro', $filter);
        $promotion_pro = $preparesell['promotion_pro'];
    //    echo '<pre>';print_r($filter);exit;
        unset($preparesell['promotion_pro']);

        $filter = array_keys($promotion_pro);

        $mdl_preparesell = app::get('preparesell')->model('preparesell_goods');
		$mdl_preparesell = app::get('preparesell')->model('preparesell_goods');
		$mdl_gift = app::get('gift')->model('ref');
        $preparesell_product_id = $mdl_preparesell->getList('product_id',array('product_id|notin'=>$filter));
        $pre_product_id = $mdl_preparesell->getList('product_id');
        $gift_product_id = $mdl_gift->getList('product_id');
        $product_id=array_merge($pre_product_id,$gift_product_id,$preparesell_product_id);
		foreach ($product_id as $key => $value) {
			$products_id[]=$value['product_id'];
		}
		unset($filter['promotion']);
		$filter['product_id|notin'] = $products_id;
        return $filter;

	}

}
