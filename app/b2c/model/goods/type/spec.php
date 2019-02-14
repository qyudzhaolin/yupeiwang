<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_goods_type_spec extends dbeav_model{
    var $has_many = array(
    );

    function get_type_spec($type_id){
        if (!$type_id) return array();
        return $this->getList('*',array('type_id'=>$type_id),0,-1,'ordernum ASC');
    }

    function save(&$sdf,$mustUpdate = null,$mustInsert = false){
        $data_list = $this->getList('*',array('type_id'=>$sdf['type_id']));
        $sdf['ordernum']=count($data_list)+1;
        $p_order=$sdf['ordernum'];
        $sql = 'UPDATE sdb_b2c_specification SET p_order='.$p_order.' where spec_id ='.$sdf['spec_id'];
        $update = $this->db->select($sql,true);
        return parent::save($sdf);
    }
}
