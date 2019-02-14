<?php
/**
 * @类说明:后台企业认证控制器
 *
 */
class b2c_ctl_admin_prove extends desktop_controller{

    var $workground = 'b2c.workground.prove';
    //企业认证  
    public function index(){
        // ee($this);
        $mdl_prove = $this->app->model('prove');
        $this->finder('b2c_mdl_prove',array(
            'title'=>app::get('b2c')->_('企业认证列表'),
            'actions'=>[
                // ['label'=>'初审','href'=>'index.php?app=b2c&ctl=admin_prove&act=add_rule','target'=>'_blank'],
            ],
            'use_buildin_recycle'=>false,
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
        ));
    }


    function _views(){
        $mdl_prove = $this->app->model('prove');
        $tags = [];

        $tags[0] = [
            'label'=>'待初审',
            'filter'=>['status'=>'first','disabled'=>'false']
        ];

        // if($this->has_permission('prove_review')){
        // }

        $tags[1] = [
            'label'=>'待复审',
            'filter'=>['status'=>'review','disabled'=>'false']
        ];

        $tags[2] = [
            'label'=>'审核通过',
            'filter'=>['status'=>'pass','disabled'=>'false']
        ];

        $tags[3] = [
            'label'=>'审核不通过',
            'filter'=>['status'=>'nopass','disabled'=>'false']
        ];

        foreach ($tags as $key => $value) {
            //网点限制
            $regionIds = kernel::single('b2c_access')->checkNetlimit($this->user);
            if (!empty($regionIds)) {
                $value['filter']['region_id|in'] = $regionIds;
            }

            //生成tags所需参数
            $sub_menu[]=[
                'label'=>app::get('b2c')->_($value['label']),
                'optional'=>true,
                'filter'=>$value['filter'],
                // 'addon'=>$mdl_prove->count($value['filter']),
                'href'=>'index.php?app=b2c&ctl=admin_prove&act=index&view='.$key
            ];
        }
        return $sub_menu;
    }

    //编辑
    function detail($prove_id){
        $objprove = $this->app->model('prove');
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
        $this->pagedata['row'] = $row;
        $this->pagedata['merchant_type_rows'] = $merchant_type_rows;
        $this->pagedata['title'] = '编辑企业实名认证';
        $this->singlepage('admin/prove/form.html');
    }


    //编辑
    function save(){
        //字段验证(is为1是必填项)
        $checkFields = [
            'area'             =>['is'=>1, 'name'=>'商户所在区域'],
            'addr'             =>['is'=>1, 'name'=>'街道详细地址'],
            'inviter_mobile'   =>['is'=>1, 'name'=>'邀请人手机号'],
            'linkman'          =>['is'=>1, 'name'=>'联系人'],
            'merchant_type_id' =>['is'=>1, 'name'=>'商户类型'],
            'merchantName'     =>['is'=>1, 'name'=>'商户名称'],
            'mobile'           =>['is'=>1, 'name'=>'手机号'],
        ];
        $mdl_prove = app::get('b2c')->model('prove');
        $this->begin();
        $datas = $_POST;
        // ee($datas);
        if (empty($datas['prove_id'])) {
            $this->end(false,'非法提交!');
        }
        $prove_id = $datas['prove_id'];
        $row = $mdl_prove->dump($prove_id);
        if (empty($row)) {
            $this->end(false,'暂无数据!');
        }

        $fields = [];
        $ctime = date('Y-m-d H:i:s');
        //开始验证必填
        foreach ($checkFields as $key => $value) {
            if ($value['is'] === 1 && empty($datas[$key])) {
                $this->end(false,$value['name'] . '必须');
            }

            //整合保存数据
            $fields[$key] = $datas[$key];
        }
        $area = explode(':', $datas['area']);
        $fields['region_id'] = end($area);

        // if (!$mdl_prove->is_mobile($fields['inviter_mobile'])) {
        //     $this->end(false,'邀请人手机号格式错误');
        // }

        if (!$mdl_prove->is_mobile($fields['mobile'])) {
            $this->end(false,'手机号格式错误');
        }

        if ($row['status'] == 'first') {
            $fields['op_name_first'] = $this->user->user_data['account']['login_name'];

        }

        if ($row['status'] == 'review') {
            $fields['op_name_review'] = $this->user->user_data['account']['login_name'];
        }

        $prove_result = $mdl_prove->update($fields,['prove_id'=>$prove_id]);
        if ($prove_result) {
            $this->end(true,'操作成功');
        }
        $this->end(false,'操作失败!');
    }

    //审核页面
    function check_page($prove_id){
        $objprove = $this->app->model('prove');
        $row = $objprove->dump($prove_id);
        $this->pagedata['row'] = $row;
        $this->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        $this->page('admin/prove/check.html');
    }

    //操作
    function toset(){
        $mdl_prove = app::get('b2c')->model('prove');
        $this->begin();
        $data = $_POST;
        // ee($data);
        if (empty($data['prove_id'])) {
            $this->end(false,'非法提交!');
        }
        $prove_id = $data['prove_id'];
        $row = $mdl_prove->dump($prove_id);
        if (empty($row)) {
            $this->end(false,'暂无数据!');
        }

        $fields = [];
        $ctime = date('Y-m-d H:i:s');
        $fields['status'] = $data['status'];
        if ($row['status'] == 'first') {
            $fields['first_time'] = $ctime;
            $fields['op_name_first'] = $this->user->user_data['account']['login_name'];
            $fields['content_first'] = $data['content_review'];
        }

        if ($row['status'] == 'review') {
            $fields['review_time'] = $ctime;
            $fields['op_name_review'] = $this->user->user_data['account']['login_name'];
            $fields['content_review'] = $data['content_review'];
        }

        $prove_result = $mdl_prove->update($fields,['prove_id'=>$prove_id]);
        if ($prove_result) {
            $this->end(true,'操作成功');
        }
        $this->end(false,'操作失败!');
    }



}
