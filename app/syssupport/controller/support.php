<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syssupport_ctl_support extends desktop_controller{

    function index(){



//        var_dump(kernel::single('syssupport_notice')->getNoticeIframeUrl());

        $pagedata['url']     = kernel::single('syssupport_auth')->getUrl();
        $pagedata['params']  = kernel::single('syssupport_auth_code')->getParams();
        $pagedata['code']    = kernel::single('syssupport_auth_code')->encode($pagedata['params'], 3600*24*7);
        $pagedata['product'] = kernel::single('syssupport_auth')->getProduct();
        $pagedata['checked'] = kernel::single('syssupport_auth')->checkParams($pagedata['params']);
        $this->pagedata = $pagedata;

        return $this->page('support.html');
    }
}

