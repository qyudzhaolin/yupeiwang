<?php
/**
 * 说明：prove model
 */
class b2c_mdl_prove extends dbeav_model{
    var $defaultOrder = array('ordernum',' DESC');

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }


    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        // $mdl_merchant_type = $this->app->model('merchant_type');
        // $merchant_type_rows = $mdl_merchant_type->getList('*',['disabled'=>'false'],0,-1,'ordernum ASC');
        // if (!empty($datas)) {
        //     foreach ($datas as $key => $data) {
        //         $datas[$key]['merchant_type'] = isset($merchant_type_rows[$data['merchant_type_id']]) ? $merchant_type_rows[$data['merchant_type_id']] : '';
        //     }
        // }
        // ee($datas);
        // ee(sql());
        return $datas;
    }

    //验证手机号
    function is_mobile($str){
        return preg_match('/^1[34578]\d{9}$/', $str);
    }

}
