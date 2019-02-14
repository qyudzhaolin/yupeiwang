<?php
/**
 *@说明：prove finder
 * 
 */
class b2c_finder_prove{
    var $column_view = '查看';
    public function __construct($app) {
        $this->app = $app;
    }

    function detail_view($prove_id){
        $objprove = $this->app->model('prove');
        $render = $this->app->render();
        $mdl_merchant_type = $this->app->model('merchant_type');
        $row = $objprove->getRow('*',['prove_id'=>$prove_id,'disabled'=>'false']);
        $upload = kernel::single('b2c_Think_upload');//文件上传类
        $images = ['store_img','license_img','id_front_img','id_back_img'];//字段类型
        $merchant_type_rows = $mdl_merchant_type->getListFormat('*',['disabled'=>'false'],0,-1,'ordernum ASC');
        // ee($row);
        if (!empty($row)) {
            $row['merchant_type_name'] = isset($merchant_type_rows[$row['merchant_type_id']]) ? $merchant_type_rows[$row['merchant_type_id']] : '';
            $row['status_name'] = $this->proveStatusArr[$row['status']];

            //生成图片
            foreach ($images as $image) {
                if (!empty($row[$image])) {
                    $tmpImage = $row[$image];
                    $row[$image] = [$upload->getImageSizes($tmpImage, $image, true)];
                    $row[$image]['thumbImages'] =  $upload->showUploadedThumbFiles($row[$image], $image, $row['prove_id'],false);
                }
            }
        }
        // ee($row);
        $render->pagedata['merchant_type_rows'] = $merchant_type_rows;
        //过滤条件
        if($row['conditions']) {
            if($row['c_template']) {
                $render->pagedata['conditions'] = kernel::single($row['c_template'])->tpl_name;
            }
        }
        $render->pagedata['row'] = $row;
        return $render->fetch('admin/prove/detail.html');
    }

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_prove&act=detail&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prove_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }


    var $column_editbutton = '操作';
    public function column_editbutton($row)
    {
        $titleFixed = $row['status'] == 'first' ? '初审' : '复审';
        $finder_id = $_GET['_finder']['finder_id'];
        return '<a href="index.php?app=b2c&ctl=admin_prove&act=check_page&_finder[finder_id]='.$finder_id.'&p[0]='.$row['prove_id'].'" target="dialog::{width:600,height:320,title:\'企业实名认证'. $titleFixed .'\'}" >'.app::get('b2c')->_('审核').'</a>';
    }

    var $column_uname = '用户名';
    public function column_uname($row){
        $this->userObject = kernel::single('b2c_user_object');
        $pam_member_info = $this->userObject->get_members_data(array('account'=>'login_account'),$row['member_id']);
        $this->pam_member_info[$row['member_id']] = $pam_member_info;
        $uname = kernel::single('weixin_wechat')->emoji_decode($pam_member_info['account']['local']);
        return $uname;
    }
}
