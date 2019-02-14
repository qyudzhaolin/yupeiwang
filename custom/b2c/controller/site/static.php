<?php
/**
 * 说明：静态页面控制器
 */
class b2c_ctl_site_static extends b2c_frontpage{
    function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->_response->set_header('Cache-Control', 'no-store');
    }


    //合约页面
    function contract(){
        $this->page('site/static/contract.html');
    }
     //合约履约
     function performance(){
        $this->page('site/static/performance.html');
    }
    //广告页 
     function advertisement(){
        $this->page('site/static/advertisement.html');
    }   
//  //公告页面
//   function advertisement(){
//      $this->page('site/static/news.html');
//  }  
}
