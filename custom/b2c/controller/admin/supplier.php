<?php
/**
 * @类说明:后台供应商管理控制器
 *
 */
class b2c_ctl_admin_supplier extends desktop_controller{

    var $workground = 'b2c.workground.goods';

    function index(){
        $supplier = $this->app->model('supplier');
        // $supplier->delete(['supplier_id'=>10]);
        $this->finder('b2c_mdl_supplier',array(
                'title'=>app::get('b2c')->_('商品供应商'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('添加供应商'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_supplier&act=create','target'=>'_blank'),
                ),
                'allow_detail_popup'=>true,
                'use_buildin_filter'=>true,
                'base_filter' =>array('for_comment_id' => 0),
                'use_view_tab'=>true,
            ));
    }

    function test(){
        $sms = kernel::single('b2c_tasks_sendsms');
        $params=[
            'data'=>['title'=>'发送验证8：','content'=>'尊敬的用户，您的验证码是：8888888；您可以用来进行找回密码的操作'],
            'mobile_number'=>'15221624337',
            'sendType'=>'notice'
        ];
        $sms->exec($params);
        // $this->page('admin/supplier/test.html');
    }


    function getCheckboxList(){
        $supplier = $this->app->model('supplier');
        $this->pagedata['checkboxList'] = $supplier->getList('supplier_id',null,0,-1);
        $this->page('admin/supplier/checkbox_list.html');
    }

    function create(){
        $objsupplier = $this->app->model('supplier');
        $this->pagedata['supplierInfo']['type'][$this->pagedata['type']['default']['type_id']] = 1;
        $attr = $objsupplier->get_supplier_attr();
        $this->pagedata['attr'] = $attr;
        $this->singlepage('admin/supplier/detail.html');
    }

    function save(){
        $this->begin('index.php?app=b2c&ctl=admin_supplier&act=index');
        $objsupplier = $this->app->model('supplier');
        $suppliername = $objsupplier->dump(array('supplier_name'=>$_POST['supplier_name'],'supplier_id'));
        if(empty($_POST['supplier_id']) && is_array($suppliername)){
             $this->end(false,app::get('b2c')->_('供应商名重复'));
        }
        $_POST['ordernum'] = intval( $_POST['ordernum'] );
        $data = $this->_preparegtype($_POST);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
            $olddata = app::get('b2c')->model('supplier')->dump($_POST['supplier_id']);
        }
        //更新首页缓存
        $index_url= kernel::single('site_router')->gen_url(array('app'=>'site','ctl'=>'default',));
        $page_key = 'SITE_PAGE_CACHE:' . $index_url;
        $cache_options['expires'] = 1;
        cachemgr::set($page_key, '', $cache_options);

        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        if($objsupplier->save($data)){
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.goods')){
                if(method_exists($obj_operatorlogs,'supplier_log')){
                    $obj_operatorlogs->supplier_log($_POST,$olddata);
                }
            }

            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            $this->end(true,app::get('b2c')->_('供应商保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('供应商保存失败'));
        }
    }

    function edit($supplier_id){
        $this->path[] = array('text'=>app::get('b2c')->_('商品供应商编辑'));
        $objsupplier = $this->app->model('supplier');
        $this->pagedata['supplierInfo'] = $objsupplier->dump($supplier_id);
        $attr = $objsupplier->get_supplier_attr($supplier_id);
        $this->pagedata['attr'] = $attr;
        $this->singlepage('admin/supplier/detail.html');
    }
    
    function _preparegtype($data){
        if(is_array($data)){
            $data['seo_info']['seo_title'] = $data['seo_title'];
            $data['seo_info']['seo_keywords'] = $data['seo_keywords'];
            $data['seo_info']['seo_description'] = $data['seo_description'];
            $data['seo_info'] = serialize($data['seo_info']);
        }
        return $data;
    }

}
