<?php
/**
 * 说明：contract_goods model
 */
class b2c_mdl_contract_goods extends dbeav_model{
    var $defaultOrder = array('sorting',' ASC');

    function __construct($app){
        parent::__construct($app);
    }

    /**
     * 重写getlist方法
     */
    public function getList2($cols='*',$filter=array(),$start=0,$limit=-1,$orderType=null){
        $arr_product = parent::getList($cols,$filter,$start,$limit,$orderType);
        return $arr_product;
    }

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = [];
        // $datas = parent::getList($cols,$filter,$offset,$limit,$orderType);
        //获取合约商品
        $groupBy = ' group by p.product_id';
        $join = '';
        $orderBy = ' order by null';
        $fields = 'p.product_id,`p`.`name`,g.bn,p.weight,p.unit,commodity_specification as spec';
        $fields .= ',g.commodity_property attr,o.gsource_area area,s.shortname,cp.price,cp.num,cp.storehouse';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = p.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_gprovenance o ON o.provenance_id = g.provenance_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';
        $join .= ' LEFT JOIN sdb_b2c_contract_goods cp ON cp.product_id = p.product_id';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_products` p ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'cp')
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,$limit,$offset);
        if (empty($datas)) {
            return $datas;
        }
        // ee(sql());

        $obj_extends_service = kernel::servicelist('b2c.api_goods_extend_actions');
        if ($obj_extends_service)
        {
            foreach ($obj_extends_service as $obj)
            {
                $obj->extend_get_product_list($datas);
            }
        }
        // ee($datas);
        return $datas;
    }

}
