<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_index_goods(&$setting,&$render){
    $_return = false;
    switch ($setting['selector']) {
    case 'filter':
        parse_str($setting['goods_filter'],$goodsFilter);
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
        $goodsFilter['goodsNum'] = $setting['limit'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter,'wap');
        // ee(sql());
        break;

    case 'select':

        $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
        $goodsFilter['goodsNum'] = $setting['limit'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter,'wap');

        foreach (json_decode($setting['goods_select_linkobj'],1) as $obj) {
            if(trim($obj['pic'])!=""){
                $_return['goodsRows'][$obj['id']]['goodsPicL'] =
                    $_return['goodsRows'][$obj['id']]['goodsPicM'] =
                    $_return['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
            }
            if(trim($obj['nice'])!=""){
                $_return['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
            }
        }

        break;

    }
    $gids = array_keys($_return['goodsRows']);

    //商品标签
    if(is_array($_return['goodsRows'])){
        foreach($_return['goodsRows'] as $apk=>$apv){
            $_return['goodsRows'][$apk]['goods_id'] = $apv['goodsId'];

            //原产地
            $_return['goodsRows'][$apk]['areaName'] = '';
            if (!empty($apv['areaId'])) {
                $mdl_area = app::get('b2c')->model('gprovenance');
                $area = $mdl_area->getRow('gsource_area as areaName',['provenance_id'=>$apv['areaId']]);
                $_return['goodsRows'][$apk]['areaName'] = $area['areaName'];
            }

            //获取一级分类
            if (!empty($apv['catId']) && !isset($_return['levelOneId'])) {
                $mdl_cat = app::get('b2c')->model('goods_cat');
                $levelOneId = $mdl_cat->getCateLevelIdByCatId($apv['catId']);
                $_return['levelOneId'] = $levelOneId;
            }
        }
    }
    foreach( kernel::servicelist('tags_special.add') as $services ) {
        if ( is_object($services)) {
            if ( method_exists( $services, 'add') ) {
                $services->add( $gids, $_return['goodsRows'] );
            }
        }
    }

    if(!$gids||count($gids)<1)return $_return;
    $mdl_product = app::get('b2c')->model('products');
    $products = $mdl_product ->getList('product_id,sales_price, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true','is_default'=>'true'));
    // ee(sql(),0);
    foreach ($products as $product) {

        $_return['goodsRows'][$product['goods_id']]['products'][] = $product;
    }
    // ee($_return);
    // ee($setting,0);
    return $_return;
}
?>
