<?php
/**
 *@说明：networks finder
 * 
 */
class b2c_finder_networks{
    public function __construct($app) {
        $this->app = $app;
    }

    var $column_edit = '编辑';
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        return '<a href="index.php?app=desktop&ctl=networks&act=form&_finder[finder_id]='.$finder_id.'&p[0]='.$row['networks_id'].'" target="dialog::{width:800,title:\'设置网点\'}">编辑</a>';
    }

    //覆盖城市
    var $column_networksareas = "覆盖城市";
    function column_networksareas($row){
        $mdl_networksarea = app::get('b2c')->model('networksarea');//网点区域
        $networksareas = $mdl_networksarea->getNetworksareaFormat(['networks_id'=>$row['networks_id']]);
        if($networksareas){
            if (count($networksareas) > 1) {
                $networksareas = implode('<br>', $networksareas);
                return "<div class='networksareas'>{$networksareas}</div>";
            }else{
                $networksareas = $networksareas;
                return $networksareas[0];
            }
        }
        return '';
    }

}
