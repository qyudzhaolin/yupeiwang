<?php
/**
 * @类说明:后台供应商表结构管理控制器
 *
 */
class b2c_ctl_admin_supplier_attr extends desktop_controller{

    var $workground = 'b2c.workground.member';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $attr_model = $this->app->model('supplier_attr');
        $datas =$attr_model->getList('*',null,0,-1,array('attr_order','asc'));
        #$t_num = count($datas);
        $tmp_datas=[];
        if (!empty($datas)) {
            foreach($datas as $key=>$val){
                if($val['attr_type'] == "select" || $val['attr_type'] == "checkbox"){
                    $val['attr_option'] = unserialize($val['attr_option']);
                }
                $tmp_datas[$key] = $val;
            }
        }
        $this->pagedata['tree'] = $tmp_datas;
        $this->pagedata['b2cs'] = '新增供应商注册项';
        $this->page('admin/supplier/attr_map.html');

    }

    //添加注册项页面
    function add_page(){
        $this->display('admin/supplier/attr_new.html');
    }

    //添加注册项动作
    function add(){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index'); 
        $attr_model = $this->app->model('supplier_attr');
        if($this->check_column($_POST['attr_column'])){
            $this->end(false,app::get('b2c')->_('该注册项字段名已存在'));
        }
        $flag = $attr_model->save($_POST);
        if($flag!=''){
            $this->end(true,app::get('b2c')->_('保存成功！'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败！'));
        }
    }


    function check_column($column){
        $member = $this->app->model('members');
        $metaColumn = $member->metaColumn;
        if(in_array($column,(array)$metaColumn)){
            return true;
        }
        else{
            return false;
        }
    }
    
    function edit_page($attr_id){
        $attr_model = $this->app->model('supplier_attr');
        $data = $attr_model->dump($attr_id);

        if($data['attr_option'] !=''){
            $data['attr_option'] = unserialize($data['attr_option']);
            $data['attr_optionNo1'] = $data['attr_option'][0];
            unset($data['attr_option'][0]);
        }
        $this->pagedata['memattr'] = $data;
        $this->page('admin/supplier/attr_edit.html');
    }
    
    function edit(){
        if(!$_POST['attr_required']) $_POST['attr_required']="false";
        if($_POST['attr_option'] !=''){
            $_POST['attr_option'] = serialize($_POST['attr_option']);
        }
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index'); 
        $attr_model = $this->app->model('supplier_attr');    
        if($attr_model->save($_POST)){
            $this->end(true,app::get('b2c')->_('编辑成功！'));
        }else{
           $this->end(false,app::get('b2c')->_('编辑失败！'));
        }
    }
    
    function remove($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end($attr_model->delete($attr_id),app::get('b2c')->_('选项删除成功'));
    }
    
    function show_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end( $attr_model->set_visibility($attr_id,true),app::get('b2c')->_('已设置显示状态'));
    }
    
    function hidden_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end( $attr_model->set_visibility($attr_id,false),app::get('b2c')->_('已设置关闭状态'));
    }
    
    function edit_show_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end( $attr_model->set_edit_visibility($attr_id,true),app::get('b2c')->_('已设置编辑状态'));
    }

    function edit_hidden_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end( $attr_model->set_edit_visibility($attr_id,false),app::get('b2c')->_('已设置编辑关闭状态'));
    }

    function save_order(){
        $this->begin('index.php?app=b2c&ctl=admin_supplier_attr&act=index');
        $attr_model = $this->app->model('supplier_attr');
        $this->end( $attr_model->update_order($_POST['attr_order']),app::get('b2c')->_('选项排序更改成功'));
    }


}
