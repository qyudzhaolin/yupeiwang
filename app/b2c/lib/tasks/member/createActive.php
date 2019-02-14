<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_member_createActive extends base_task_abstract implements base_interface_task{

    function exec($params=null){
        $member_id = $params['member_id'];
        $member_rpc_object = kernel::single("b2c_apiv_exchanges_request_member");
        $member_rpc_object->createActive($member_id);
    }
}


