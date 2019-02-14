<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_mdl_gprovenance extends dbeav_model{
    // var $has_tag = true;
    function __construct($app){
        parent::__construct($app);
        
        // $this->use_meta();  //member中的扩展属性将通过meta系统进行存储
    }


     function brand2json($return=false){
        @set_time_limit(600);
        $contents=$this->db->select('select provenance_id,gsource_area from  sdb_b2c_gprovenance  ORDER BY provenance_id desc');
        if($return){
            base_kvstore::instance('b2c_goods')->store('goods_gprovenance.data',$contents);
            return $contents;
        }else{
            return base_kvstore::instance('b2c_goods')->store('goods_gprovenance.data',$contents);
        }
    }
     function getAll(){
        if(base_kvstore::instance('b2c_goods')->fetch('goods_gprovenance.data', $contents) !== false){

            if(!is_array($contents)){
                if(($result=json_decode($contents,true))){
                    return json_decode($contents,true);
                }else{
                    return $this->brand2json(true);
                }
            }else{
                    return $contents;
            }
        }else{
            return $this->brand2json(true);
        }
    }
     
	function get_gsource_area($brand_id){
		$list = $this->getRow("gsource_area",array('provenance_id'=>$provenance_id));
		return $list['gsource_area'];
	}

    //根据商品id获取原产地
    function getAreaByGoodsId($goods_id=0){
        if (empty($goods_id)) {
            return '';
        }
        $mdl_goods = $this->app->model('goods');
        $goodsRow = $mdl_goods->getRow('provenance_id',['goods_id'=>$goods_id]);
        if (empty($goodsRow)) {
            return '';
        }
        $provenance_id = $goodsRow['provenance_id'];
        $areaRow = $this->getRow('provenance_id,gsource_area area',['provenance_id'=>$provenance_id]);
        return $areaRow;
    }

    //获取原产国名称-并且格式化
    function get_country_format($is_get_hot=false){
        $rows = [];
        $wheres = ['is_country'=>'true'];
        //如果只获取热门推荐的国家
        if ($is_get_hot) {
            $wheres['is_hot'] = 'true';
        }
        $lists = $this->getList('provenance_id id,gcountry_of_origin country', $wheres);
        foreach ($lists as $key => $value) {
            $rows[$value['id']] = $value['country'];
        }
        $rows = array_unique($rows);
        return $rows;
    }
}