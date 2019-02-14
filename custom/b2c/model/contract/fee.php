<?php
/**
 * 说明：supplier model
 */
class b2c_mdl_contract_fee extends dbeav_model{
    var $defaultOrder = array('sorting',' ASC');

    function __construct($app){
        parent::__construct($app);
    }

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        foreach ($datas as $key=>$data) {
            $datas[$key]['isone_format'] = ($data['isone'] == 'true') ? '是' : '';
        }
        return $datas;
    }

}
