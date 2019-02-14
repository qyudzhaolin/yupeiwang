<?php
/**
 * 说明：merchant_type model
 */
class b2c_mdl_merchant_type extends dbeav_model{

    function __construct($app){
        parent::__construct($app);
    }

     //重写getList方法
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
         $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);

         if (empty($datas)) {
             return $datas;
         }
         return $datas;
     }


    //重写getList方法(获取数据并且格式化)
    function getListFormat($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);

        if (empty($datas)) {
          return $datas;
        }

        $res = [];
        foreach ($datas as $key => $value) {
          $res[$value['merchant_type_id']] = $value['merchant_type'];
        }
        return $res;
    }

    //获取行
    function getRow($cols='*', $filter=array(), $orderType=null){
        $data = $this->getList($cols, $filter, 0, 1, $orderType);
        if($data){
            return $data['0'];
        }else{
            return $data;
        }
    }

    //进回收站前操作(有实名认证使用就不允许删除)
    function pre_recycle($datas){
        $falg = true;
        if(is_array($datas) && !empty($datas)){
            $obj_prove = app::get('b2c')->model('prove');

            foreach($datas as $data){
                $wheres=[];
                $wheres['disabled'] = 'false';
                $wheres['merchant_type_id|in'] = $data['merchant_type_id'];
                //之前添加的先删除
                if ($obj_prove->count($wheres)) {
                    $this->recycle_msg = '商户类型已被使用，无法删除！';
                    return false;
                }
            }
        }
        return $falg;
    }
}
