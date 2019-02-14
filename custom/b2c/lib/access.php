<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_access{
    
    function test(){
        #$data = array('订单确认小组','订单删除小组');
        echo "<input type='checkbox' value='订单确认小组' name='order1'>".app::get('b2c')->_('订单确认小组')."</input><br/>"; 
        echo "<input type='checkbox' value='订单删除小组' name='order2'>".app::get('b2c')->_('订单删除小组')."</input><br/>"; 
        echo "<input type='checkbox' value='订单创建小组' name='order3'>".app::get('b2c')->_('订单创建小组')."</input><br/>"; 
    }

    //网点限制(返回所有区域ID集合)说明：$user参数为$this->user,有数据默认返回数组
    public function checkNetlimit($user,$type='array'){
        $regionIds = [];
        // $userData = $this->user->user_data;
        $userData = $user->user_data;
        if (getConfig('openet') && !empty($user) && !empty($userData) && $userData['super'] != '1') {
            $mdl_networksarea = app::get('b2c')->model('networksarea');//网点区域
            $regionIds = $mdl_networksarea->getRegionIdsByNetworksIds($userData['networks']);
            if (!empty($regionIds) && $type == 'string') {
                return implode(',', $regionIds);
            }
            return $regionIds;
        }
        return $regionIds;
    }
}
?>


