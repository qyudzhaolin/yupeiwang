<?php
/**
 *@说明：contract_goods finder
 * 
 */
class b2c_finder_contract_goods{
    var $column_name = '商品名称1';
    public function __construct($app) {
        $this->app = $app;
    }

    function column_name($row){
        return $row['name'];
    }

    var $column_xxx = 'xxxx';
    function column_xxx(){
        return 'xxxx';
    }

    var $column_bn = '商品编号';
    function column_bn($row){
        return $row['bn'];
    }


    var $column_weight = '重量(kg)';
    function column_weight($row){
        return $row['weight'];
    }

    var $column_unit = '计量单位';
    function column_unit($row){
        return $row['unit'];
    }


    var $column_spec = '商品规格';
    function column_spec($row){
        return $row['spec'];
    }


    var $column_attr = '商品属性';
    function column_attr($row){
        return $row['attr'];
    }


    var $column_area = '原产地';
    function column_area($row){
        return $row['area'];
    }

    var $column_shortname = '供应商';
    function column_shortname($row){
        return $row['shortname'];
    }
}
