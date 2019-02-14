<?php

class b2c_ctl_admin_contract_goods extends desktop_controller{


    function index(){
        $contract_goods = $this->app->model('contract_goods');
        $this->finder('b2c_mdl_contract',array(
                'title'=>app::get('b2c')->_('商品列表'),
                'allow_detail_popup'=>true,
                'use_buildin_filter'=>true,
                'base_filter' =>array('for_comment_id' => 0),
                'use_view_tab'=>true,
            ));
    }

}
