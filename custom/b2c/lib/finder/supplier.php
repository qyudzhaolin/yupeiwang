<?php
/**
 *@说明：supplier finder
 * 
 */
class b2c_finder_supplier{
    var $column_view = '查看';
    public function __construct($app) {
        $this->app = $app;
    }

    function detail_view($id){
        $datas = $this->app->model('supplier')->dump($id);
        $render = $this->app->render();
        //过滤条件
        if($datas['conditions']) {
            if($datas['c_template']) {
                $render->pagedata['conditions'] = kernel::single($datas['c_template'])->tpl_name;
            }
        }

        //扩展字段属性值
        $attr = kernel::single('b2c_mdl_supplier')->get_supplier_attr($id);
        $render->pagedata['attr'] = $attr;

        $render->pagedata['datas'] = $datas;
        return $render->fetch('admin/supplier/finder/detail.html');
    }

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_supplier&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['supplier_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }
}
