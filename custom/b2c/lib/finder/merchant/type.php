<?php
/**
 *@说明：merchant_type finder
 * 
 */
class b2c_finder_merchant_type{
    public function __construct($app) {
        $this->app = $app;
    }

    var $column_edit = '编辑';
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $merchant_type_id = $row['merchant_type_id'];
        return '<a class="editContract" merchant_type_id="' . $merchant_type_id . '" href="index.php?app=b2c&ctl=admin_merchant_type&act=form&_finder[finder_id]='.$finder_id.'&p[0]='.$merchant_type_id.'&finder_id='.$finder_id.'" target="dialog::{width:400,title:\'编辑商户类型'. $titleFixed .'\'}">'.app::get('b2c')->_('编辑').'</a>';
    }
}
