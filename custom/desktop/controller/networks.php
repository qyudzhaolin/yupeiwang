<?php
/**
 * @类说明:后台服务网点控制器
 *
 */
class desktop_ctl_networks extends desktop_controller{

    var $workground = 'desktop_ctl_system';
    var $certcheck = false;

    function index(){
        $mdl_networks = app::get('b2c')->model('networks');
        $this->finder('b2c_mdl_networks',array(
            'title'=>app::get('b2c')->_('服务网点列表'),
            'actions'=>[
                [
                    'label'=>'添加服务网点',
                    'href'=>'index.php?app=desktop&ctl=networks&act=form',
                    'target'=>'dialog::{width:800,title:\'设置网点\'}'
                ],
            ],
            'use_buildin_recycle'=>true,
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
        ));
    }


    //表单
    function form($networks_id){
        $mdl_networks = app::get('b2c')->model('networks');//网点
        $mdl_networksarea = app::get('b2c')->model('networksarea');//网点区域
        $row = $mdl_networks->dump($networks_id);

        //配送范围
        $this->pagedata['networksarea'] = [];
        if ($row) {
            $networksareas = $mdl_networksarea->getNetworksarea(['networks_id'=>$networks_id]);
            if($networksareas) {
                $tmpAreas=[];
                foreach ($networksareas as $networksarea) {
                    $tmpAreas[$networksarea['region_id']] = $networksarea['name'];
                }
                $this->pagedata['networksarea'] = $tmpAreas;
            }
        }
        $this->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        $this->pagedata['row'] = $row;
        $this->page('networks/form.html');
    }


    function save(){
        $datas  = $_POST;
        $this->begin('index.php?app=desktop&ctl=networks&act=index');
        $mdl_networks = app::get('b2c')->model('networks');//网点
        $mdl_networksarea = app::get('b2c')->model('networksarea');//网点区域
        $networks_name = $mdl_networks->dump(array('networks_name'=>$datas['networks_name'],'networks_id'));
        if(empty($datas['networks_id']) && is_array($networks_name)){
             $this->end(false,app::get('b2c')->_('网点名重复'));
        }
        $datas['ordernum'] = intval( $datas['ordernum'] );
        $networks_id = trim($datas['networks_id']);

        $ctime = date('Y-m-d H:i:s');
        $fields = [
            'networks_name' => trim($datas['networks_name']),
            'ordernum'      => trim($datas['ordernum']),
            'mtime'         => $ctime,
        ];

        if (empty($networks_id)) {
            $fields['ctime'] = $ctime;
            $networks_id = $mdl_networks->insert($fields);
            if(!$networks_id){
                $this->end(false,app::get('b2c')->_('新增网点失败'));
            }

            //网点必须选择
            if ($datas['networksarea'] == '') {
                $this->end(false,app::get('b2c')->_('必须选择所覆盖城市'));
            }
        }else{
            $res_networks = $mdl_networks->update($fields,['networks_id'=>$networks_id]);
            if(!$res_networks){
                $this->end(false,app::get('b2c')->_('修改网点失败'));
            }
        }

        //保存网点区域
        if ($datas['networksarea'] != '') {
            $networksareaDatas = [];
            $networksareaDatas['networksarea'] = isset($datas['networksarea']) ? $datas['networksarea'] : '';
            $networksareaDatas['networks_id'] = $networks_id ? $networks_id : '';
            // ee($networksareaDatas);
            $res_saveNetworksarea = $mdl_networksarea->saveNetworksarea($networksareaDatas);
            if(!$res_saveNetworksarea){
                $this->end(false,app::get('b2c')->_('网点区域保存失败'));
            }
        }

        $this->end(true,app::get('b2c')->_('网点设置成功'));
    }

}
