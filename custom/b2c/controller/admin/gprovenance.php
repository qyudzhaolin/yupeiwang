<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
//*******************************************************************
//  原产地控制器
//  $ author zhaolin 
//*******************************************************************
class b2c_ctl_admin_gprovenance extends desktop_controller{
      var $workground = 'b2c_ctl_admin_gprovenance';
      public function __construct($app)
    {
      parent::__construct($app);
        $this->member_model = $this->app->model('gprovenance');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
             //TAB扩展
         $this->finder('b2c_mdl_gprovenance',array(
            'title'=>app::get('b2c')->_('商品原产地'),
            'actions' => array(
                array('label'=>'添加原产地','icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_gprovenance&act=add_page','target'=>'dialog::{title:\'添加原产地\', width:800, height:420}'),
            
            ),
            'allow_detail_popup' => true,
             'use_buildin_setcol'=> true,
        ));
     
    }
     
    function add_page(){
           $attr_model = $this->app->model('provenance_attr');
         $tmpdate =$attr_model->getList('*',null,0,-1,array('attr_order','asc'));
        #$t_num = count($tmpdate);
        foreach($tmpdate as $key=>$val){
            if($val['attr_type'] == "select" || $val['attr_type'] == "checkbox"){
                $val['attr_option'] = unserialize($val['attr_option']);
            }
            $n_tmpdate[$key] = $val;
        }
     
        $this->pagedata['attr'] = $n_tmpdate;
      
        $this->display('admin/gprovenance/add.html');
    }
    
    function add(){
        
        $this->begin('index.php?app=b2c&ctl=admin_gprovenance&act=index'); 
        $attr_model = $this->app->model('gprovenance');
        $flag = $attr_model->save($_POST);
        if($flag!=''){
            $this->end(true,app::get('b2c')->_('保存成功！'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败！'));
        }
    }
     function edit( $gprovenanceId ){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Progma: no-cache');
        $oSpec = $this->app->model('gprovenance');
           $attr = unserialize($this->app->getConf('provenance.attr'));
   
        $this->pagedata['attr'] = $attr;
        $this->pagedata['max_spec_value_num'] = MAX_SPEC_VALUE_NUM;
        $this->pagedata['gInfo'] = $oSpec->dump($gprovenanceId,$field = '*',$subSdf = null);
        $this->page('admin/gprovenance/edit.html');
    }
  
}

