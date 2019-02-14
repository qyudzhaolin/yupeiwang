<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_gprovenance{
  var $detail_basic = '查看';
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_gprovenance&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['provenance_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑').'\', width:800, height:420}">'.app::get('b2c')->_('编辑').'</a>';
    }

      function detail_basic($gid){
        $render =  app::get('b2c')->render();
        $de= app::get('b2c')->model('gprovenance')->getList('*',array('provenance_id'=>$gid));
        $render->pagedata['ginfo'] = $de;
        return $render->fetch('admin/gprovenance/detail.html');
    }

}
