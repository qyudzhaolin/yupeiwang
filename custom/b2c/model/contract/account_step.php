<?php
/**
 * 说明：account_step model
 */
class b2c_mdl_contract_account_step extends dbeav_model{
    var $defaultOrder = array('sorting',' ASC');

    function __construct($app){
        parent::__construct($app);
    }

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        return $datas;
    }

    /**
     * 说明：批量更新某条合约下的所有结算阶段
     * @params int $contract_id 合约id
     * @params array $data 阶段阶段数组
     * @return bool 
     */
    function updateBatchStep($contract_id=0,$data=[]){
        $res = false;
        if (empty($contract_id)) {
            return $res;
        }

        if (empty($data)) {
            return $res;
        }

        return $darestas;
    }

}
