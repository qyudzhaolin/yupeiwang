<?php
/**
 * @类说明:后台保证金控制器
 *
 */
class b2c_ctl_admin_bond extends desktop_controller{

    var $workground = 'b2c.workground.bond';
    //保证金  
    public function index(){
        $mdl_bond = $this->app->model('bond');
        $this->finder('b2c_mdl_bond',array(
            'title'=>app::get('b2c')->_('保证金列表'),
            'actions'=>[
                // ['label'=>'初审','href'=>'index.php?app=b2c&ctl=admin_bond&act=add_rule','target'=>'_blank'],
            ],
            'use_buildin_recycle'=>false,//删除
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
        ));
    }

}
