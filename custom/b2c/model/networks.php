<?php
/**
 * 说明：networks model
 */
class b2c_mdl_networks extends dbeav_model{
    var $defaultOrder = array('ordernum',' ASC');

    function __construct($app){
        parent::__construct($app);
    }

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        // ee($datas);
        return $datas;
    }


    
    function getListFormat($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = [];
        $rows = parent::getList($cols, $filter, $offset, $limit, $orderType);
        if (empty($rows)) {
            return $rows;
        }
        foreach ($rows as $key => $row) {
            $datas[$row['networks_id']] = $row['networks_name'];
        }
        return $datas;
     }


    //进回收站前操作(先删除网点区域)
    function pre_recycle($datas){
        $falg = true;
        if(is_array($datas) && !empty($datas)){
            $mdl_networksarea = app::get('b2c')->model('networksarea');//网点区域
            $mdl_users = app::get('desktop')->model('users');//后台用户
            foreach($datas as $data){
                $networks_id = $data['networks_id'];
                $users_networks = $mdl_users->count(['filter_sql'=>"find_in_set('{$networks_id}', networks)"]);
                if ($users_networks) {
                    $this->recycle_msg = '该网点已经绑定操作人员，无法删除！';
                    return false;
                }


                $wheres=[];
                $wheres['networks_id|in'] = $networks_id;
                //之前添加的先删除
                if ($mdl_networksarea->count($wheres)) {
                    $res = $mdl_networksarea->remove($wheres);
                    if (!$res) {
                        $this->recycle_msg = '删除网点区域失败！';
                        return false;
                    }
                }
            }
        }
        return $falg;
    }

}
