<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class syssupport_service_view_menu{
    function function_menu(){
        //$html[] = "<a href='index.php?ctl=shoprelation&act=index&p[0]=apply'>网店邻居</a>";
        $html[] = "<a href='index.php?app=syssupport&ctl=support&act=index'>".app::get('syssupport')->_('支持中心')."</a>";
        return $html;
    }
}
