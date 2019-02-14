<?php

class preparesell_preparesell_products{

    function __construct($app){
        $this->app = $app;
        $this->mdl_product = app::get('b2c')->model('products');
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->mdl_preparesell = app::get('preparesell')->model('preparesell');
        $this->mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $this->mdl_promotion_type = app::get('preparesell')->model('promotions_type');
    }


    /*
     *preparesell_goods列表页面中数据的处理
     *@$params preparesell_product 数据
     *
     */
    function getParams($params){
        $listpro = $this->_getProduct($params['product_id']);
        $listgoods = $this->_getGoods($listpro['goods_id']);
        foreach($listpro['spec_desc']['spec_private_value_id'] as $k=>$v){
            if($img = $listgoods['spec_desc'][$k][$v]['spec_goods_images']){
                $imgs = explode(',',$img);
                $num=count($imgs)-1;
                $listgoods['image_default_id']=$imgs[$num]?$imgs[$num]:$listgoods['image_default_id'];
            }

        }
        $spec_value = implode(" ",$listpro['spec_desc']['spec_value']);
        $listgoods['name'] = $listgoods['name']." ".$spec_value;
        $listgoods['prepare_id'] = $params['prepare_id'];
        $listgoods['status'] = $params['status']; 
        //款项的开始结束时间
        $preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $fund_name="y";
        $result=$preparesell_fund->getRow('begin_time,end_time',array('prepare_id' =>$params['prepare_id'],'fund_name'=>$fund_name));
        $listgoods['begin_time'] = $params['begin_time'];
        $listgoods['end_time'] = $params['end_time'];  
        $listgoods['fund_begin_time'] = $result['begin_time'];
        $listgoods['fund_end_time'] = $result['end_time']; 
        $listpro['price']=$params['promotion_price'];
        // $listpro['sales_price']=$params['sales_price'];
        $listpro['preparesell_typeid']=$params['type_id'];
        $listpro['begin_time']=$params['begin_time'];
        $listpro['end_time']=$params['end_time'];
        $listpro['initial_num']=$params['initial_num'];
        $listpro['prepare_num']=$params['prepare_num'];
        if($params['remind_time']){
            $listpro['remind_time']=strtotime("-".$params['remind_time']." minute",$params['begin_time']);
        }
        $remind = $params['remind_way'];
        $listpro['remind_way'] = $remind;
        if(in_array('email',$remind) && !in_array('sms',$remind)){
            $listpro['email_remind'] = true;
        }elseif(in_array('sms',$remind) && !in_array('email',$remind)){
            $listpro['sms_remind'] = true;
        }elseif(in_array('email',$remind) && in_array('sms',$remind)){
            $listpro['all_remind'] = true;
        }
        $listgoods['products'] = $listpro;
        return $listgoods;
    }



    function _getProduct($filter){
        $products="";
        if($filter){
            $products = $this->mdl_product->getRow("*",array('product_id'=>$filter));
        }
        return $products;
    }

    function _getGoods($filter){
        $goods="";
        if($filter){
            $goods = $this->mdl_goods->getRow('goods_id,name,bn,image_default_id,spec_desc,intro,cat_id,brand_id',array('goods_id'=>$filter));
            $goods['brand_name'] = app::get('b2c')->model('brand')->get_brand_name($goods['brand_id']);
            $goods['cat_name'] = app::get('b2c')->model('goods_cat')->get_cat_name($goods['cat_id']);
        }
        return $goods;

    }

    function getSpecialGoodsDetail($filter){
        $preparesell_goods = $this->mdl_preparesell_goods->getList('*',$filter);
        foreach($preparesell_goods as $key=>$value){
            $product = $this->mdl_product->getRow('*',array('product_id'=>$value['product_id']));
            $result[] = array_merge($value,$product);
        }
        return $result;
    }

    function getSpecialProduct($product_id){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['status']='true';
        $filter['product_id'] = $product_id;
        $product = $this->mdl_preparesell_goods->getRow('*',$filter);
        if($product){
            $product['type_id'] = $this->getTypename(array('type_id'=>$product['type_id']));
            return $product;
        }
        return false;
    }

    function getTypename($filter){
        $type_name = $this->mdl_promotion_type->getList("name",$filter);
        return $type_name[0]['name'];
    }

    function _get_protype(){

        $type_name = $this->mdl_promotion_type->getList("type_id,name");
        return $type_name;
    }

    function getStatus($filter){
        $preparesell = $this->mdl_preparesell->getRow("status",array('preparesell_id'=>$filter));
        return $preparesell['status'];
    }

    function getPrice($product_id,&$price){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';
        $list = $this->mdl_preparesell_goods->getRow('promotion_price,type_id',$filter);
        if($list){
            $price['price'] = $list['promotion_price'];
            $price['pricelabel'] = $this->getTypename(array('type_id'=>$list['type_id']));
        }
    }

    function getStore($product_id,&$store){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';

        $list = $this->mdl_preparesell_goods->getRow('*',$filter);
        if($list){
            $limit = $list['limit'];
            if($limit){
                $store['store'] = ($store['store'] < $limit) ? $store['store'] : $limit;
            }
        }
    }

    function check_store($product_id,$num,&$msg){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';
        $list = $this->mdl_preparesell_goods->getRow('*',$filter);
        $product = $this->_getProduct($product_id);
        if($list){
            if($list['limit']>0 && $list['limit'] < intval($num)){
                $msg = "购买超过限购量";
                return false;
            }
        }
        return true;
    }


    function ifSpecial($product_id){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';

        $list = $this->mdl_preparesell_goods->getRow('type_id',$filter);
        if($list){
            return "preparesell";
        }
        return false;
    }

    function check_preparesell_goods(&$products){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $products['product_id'];
        $filter['status'] = 'true';

        if($products){
            $preparesell_goods = $this->mdl_preparesell_goods->getRow('type_id,promotion_price',$filter);
            if($preparesell_goods){
                $products['price'] = $preparesell_goods['promotion_price'];
                $products['promotion_type'] = $this->getTypename(array('type_id'=>$preparesell_goods['type_id']));
            }
        }
    }
}
