<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class  b2c_ctl_admin_analysis extends desktop_controller
{
    function __construct($app){
        parent::__construct($app);
        $this->analysis = app::get('ectools')->model('analysis');
    }

    //概况
    public function index(){
        $html = kernel::single('b2c_analysis_index')
                    ->set_service('b2c_analysis_shopsale')
                    ->set_extra_view(array('ectools'=>'analysis/index_view.html'))
                    ->set_params($_POST)
                    ->fetch();
        // ee($_POST,0);//TEST
        // $this->pagedata['report'] = $_POST['report'] ? $_POST['report'] : 'day';
        $this->pagedata['_PAGE_CONTENT'] = $html;
        $this->page();
    }

    //账款统计
    public function sale(){
        kernel::single('b2c_analysis_accounts')->set_extra_view(array('ectools'=>'analysis/index_view.html'))->set_params($_POST)->display();
        

    }


    //商品统计
    public function goods(){
        kernel::single('b2c_analysis_goods')->set_extra_view(array('ectools'=>'analysis/goods_view.html'))->set_params($_POST)->display();
    }

    public function shopsale(){
        kernel::single('b2c_analysis_shopsale')->set_params($_POST)->display();
        kernel::single('b2c_analysis_shopsale')->set_extra_view(array('ectools'=>'analysis/shopsale.html'))->set_params($_POST)->display();
    }

    public function productsale(){
        kernel::single('b2c_analysis_productsale')->set_params($_POST)->display();
    }

    //客户统计
    public function member(){
        kernel::single('b2c_analysis_member')->set_extra_view(array('ectools'=>'analysis/index_view.html'))->set_params($_POST)->display();
    }
      //供应商统计
      public function customer(){
        kernel::single('b2c_analysis_supplier')->set_extra_view(array('ectools'=>'supplier/supplier_view.html'))->set_params($_POST)->display();
    }
    //账款统计主表
    public  function accountdetails(){
        kernel::single('b2c_analysis_adetails')->set_extra_view(array('ectools'=>'accountdetails/accountdetails_view.html'))->set_params($_POST)->display(); 
    }
    //账款统计子表
    public  function accountdetailssublist(){
        kernel::single('b2c_analysis_accountdetailssublist')->set_extra_view(array('ectools'=>'accountdetails/accountdetails_view.html'))->set_params($_POST)->display(); 
    }
}