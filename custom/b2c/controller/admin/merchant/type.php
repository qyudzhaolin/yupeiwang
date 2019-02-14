<?php
/**
 * @类说明:后台商户类型控制器
 *
 */
class b2c_ctl_admin_merchant_type extends desktop_controller{

    var $workground = 'b2c.workground.merchant_type';
    //企业认证  
    public function index(){
        $mdl_merchant_type = $this->app->model('merchant_type');
        $this->finder('b2c_mdl_merchant_type',array(
            'title'=>app::get('b2c')->_('企业认证列表'),
            'actions'=>[
                ['label'=>'添加商户类型','href'=>'index.php?app=b2c&ctl=admin_merchant_type&act=form','target'=>'dialog::{width:400,title:\'添加商户类型\'}'],
            ],
            'use_buildin_recycle'=>true,
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
        ));
    }

    //编辑
    function form($merchant_type_id){
        $this->path[] = array('text'=>app::get('b2c')->_('企业实名认证编辑'));
        $mdl_merchant_type = $this->app->model('merchant_type');
        $row = $mdl_merchant_type->dump($merchant_type_id);
        $this->pagedata['row'] = $row;
        $this->pagedata['finder_id'] = $_GET['finder_id'];
        $this->page('admin/merchant_type/form.html');
    }

    function save(){
        $this->begin('index.php?app=b2c&ctl=admin_merchant_type&act=index');
        $mdl_merchant_type = $this->app->model('merchant_type');
        $merchant_type_name = $mdl_merchant_type->dump(array('merchant_type'=>$_POST['merchant_type'],'merchant_type_id'));
        if(empty($_POST['merchant_type_id']) && is_array($merchant_type_name)){
             $this->end(false,app::get('b2c')->_('商户类型名重复'));
        }
        $_POST['ordernum'] = intval( $_POST['ordernum'] );
        $ctime = date('Y-m-d H:i:s');
        $fields = [
            'merchant_type_id'=> trim($_POST['merchant_type_id']),
            'merchant_type'=> trim($_POST['merchant_type']),
            'ordernum'=> trim($_POST['ordernum']),
            'ctime'=> $ctime,
        ];

        if (!empty($fields['merchant_type_id'])) {
            $fields['mtime'] = $ctime;
        }

        if($mdl_merchant_type->save($fields)){
            $this->end(true,app::get('b2c')->_('商户类型保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('商户类型保存失败'));
        }
    }


}
