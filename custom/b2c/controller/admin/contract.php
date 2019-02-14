<?php

class b2c_ctl_admin_contract extends desktop_controller{


    function index(){
        $contract = $this->app->model('contract');
        $this->finder('b2c_mdl_contract',array(
                'title'=>app::get('b2c')->_('合约列表'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('添加合约'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_contract&act=add_rule','target'=>'_blank'),
                ),
                'allow_detail_popup'=>true,
                'use_buildin_filter'=>true,
                'base_filter' =>array('for_comment_id' => 0),
                'use_view_tab'=>true,
            ));
    }

    function add_rule(){
        //费种
        $oFee = $this->app->model('contract_fee');
        $feeRows = $oFee->getList();
        $this->pagedata['feeRows'] = $feeRows;
        $this->singlepage('admin/contract/contract.html');
    }


    //获取结算参数html
    function get_account_params_html(){
        $params = $_GET['param'];
        // ee($params);
        //费种
        $oParams = $this->app->model('contract_account_params');
        $rows = $oParams->getList('*',[],0,-1,'sorting asc');
        foreach ($rows as $key => $value) {
            $rows[$key]['fix'] = in_array($value['no'], ['point','deposit_rate']) ? '%' : '';
        }
        $this->pagedata['rows'] = $rows;
        $this->pagedata['params'] = $params;
        exit($this->fetch('admin/contract/account/params.html'));

    }

    //获取费种html
    function get_fee_html(){
        //费种
        $oFee = $this->app->model('contract_fee');
        $feeRows = $oFee->getList();
        $this->pagedata['feeRows'] = $feeRows;

        $data = [];
        if (!empty($_POST['data'])) {
            $data = json_decode(trim($_POST['data']),true);
            // ee($data);
            $data['fee_ids'] = explode(',', $data['fee_ids']);
        }
        $this->pagedata['stepRow'] = $_POST['data'];
        $this->pagedata['data'] = $data;
        // $this->singlepage('admin/contract/fee/fee.html');
        exit($this->fetch('admin/contract/fee/fee.html'));
    }


    //获取费种return json
    function get_fee_data(){
        //费种
        $oFee = $this->app->model('contract_fee');
        $datas = $oFee->getList();
        $this->apiReturn(['data'=>$datas]);
    }

    //操作
    function setpage($contract_id){
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $data = $mdl_contract->getContractData($contract_id);
        if (empty($data)) {
            $this->splash('failed', 'index.php?ctl=admin_contract&act=index','无此合约！');
        }

        $contractSchema = $mdl_contract->get_schema();
        $contract_state_arr = $contractSchema['columns']['state']['type'];//结算阶段状态

        //物流状态
        $ship_status = [];
        $contractSchema = $mdl_contract->get_schema();
        $ship_status_arr = $contractSchema['columns']['ship_status']['type'];//物流状态
        foreach ($ship_status_arr as $key => $value) {
            if (empty($key)) {
                unset($ship_status[$key]);
                continue;
                // $value = '----无----';
            }
            $ship_status[] = ['id'=>$key,'name'=>$value];
        }

        //阶段状态
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $stepSchema = $mdl_step->get_schema();
        $step_state_arr = $stepSchema['columns']['state']['type'];//结算阶段状态

        //重新处理阶段名称
        foreach ($step_state_arr as $k => $v) {
            if ($k == 'finish') {
                unset($step_state_arr[$k]);
            }
            if ($k == 'off') {
                $step_state_arr[$k] = '待开启';
            }
        }
        $accountStepRows = $mdl_step->getList('*',['contract_id'=>$contract_id],0,-1,'end_time');
        foreach ($accountStepRows as $key => $stepRow) {
            $accountStepRows[$key]['end_time'] = date('Y-m-d H:i',$stepRow['end_time']);
        }

        //写入阶段付款状态
        $mdl_contract->stepsPayStatus($accountStepRows,$contract_id);//注意第一个参数为引用

        //编辑阶段地址
        $finder_id = $_GET['finder_id'];
        $editStepUrl = "index.php?app=b2c&ctl=admin_contract&act=edit&_finder[finder_id]={$finder_id}&p[0]={$contract_id}&finder_id={$finder_id}";

        // ee($accountStepRows);
        $this->pagedata['data'] = $data;
        $this->pagedata['editStepUrl'] = $editStepUrl;
        $this->pagedata['ship_status'] = $ship_status;
        $this->pagedata['accountStepRows'] = $accountStepRows;
        $this->pagedata['step_state_arr'] = $step_state_arr;
        $this->pagedata['contract_state_arr'] = $contract_state_arr;
        $this->pagedata['params'] = $data['params'];//结算参数
        $this->pagedata['step'] = $data['step'];//结算阶段
        $this->pagedata['products'] = $data['products'];
        $this->pagedata['product_ids']= $data['product_ids'];//商品列表数据
        // ee($this->pagedata);
        $this->page('admin/contract/setpage.html');
    }

    //操作
    function toset(){
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段

        $this->begin();
        $data = $_POST;
        // ee($data);
        if (empty($data['contract_id'])) {
            $this->end(false,'非法提交!');
        }
        $contract_id = $data['contract_id'];
        $base = [];
        $contractRow = $mdl_contract->getContractData($contract_id);

        //更新合约基本信息-付款状态
        if (!empty($data['pay_status'])) {
            $base['pay_status'] = $data['pay_status'];
        }

        //更新合约基本信息-物流状态
        if (!empty($data['ship_status']) && $data['ship_status'] != $contractRow['ship_status']) {
            $base['ship_status'] = $data['ship_status'];

            $mdl_contract_ship_status = app::get('b2c')->model('contract_ship_status');//合约物流状态
            $ship_status_data = [
                'contract_id'=>$contract_id,
                'ship_status'=>$data['ship_status'],
                'ctime'=>date('Y-m-d H:i:s'),
            ];
            $mdl_contract_ship_status->insert($ship_status_data);

        }
        //更新合约基本信息-合约处理
        if (!empty($data['state'])) {
            $base['state'] = $data['state'];
        }
        $contract_result = $mdl_contract->update($base,['contract_id'=>$contract_id]);

        //更新结算阶段
        // ee($data['step']);
        if (!empty($data['step'])) {
            //阶段开启验证(不能同时开启多个阶段)
            $stepOpenCheckRes = $mdl_contract->stepOpenCheck($data['step']);
            if ($stepOpenCheckRes['error']) {
                $this->end(false,$stepOpenCheckRes['message']);
            }

            foreach ($data['step'] as $step) {
                if (empty($step['state'])) continue;
                $stepRow = $mdl_step->getRow('*',['step_id'=>$step['step_id']]);
                $updateStepResult  = $mdl_step->update(['state'=>$step['state']],['step_id'=>$step['step_id']]);

                //操作日志-阶段开启或者关闭
                if (!empty($stepRow) && !empty($step['state']) && $step['state'] != $stepRow['state']) {
                    $step_state_fix = '开启';
                    if($step['state'] == 'off'){
                        $step_state_fix = '关闭';
                    }
                    $step_state_name =  "阶段{$step_state_fix}：" . $stepRow['title'];
                    $mdl_contract->saveDologs($contract_id,'succ','do',$step_state_name,$this->user->user_id,$this->user->user_data['name']);
                }
            }
        }

        //操作日志-合约物流状态更新
        if ($data['ship_status'] != $contractRow['ship_status']) {
            $schemaRow = $mdl_contract->get_schema();
            $ship_status_arr = $schemaRow['columns']['ship_status']['type'];//物流状态
            $ship_status = isset($ship_status_arr[$data['ship_status']]) ? $ship_status_arr[$data['ship_status']] : '';
            $mdl_contract->saveDologs($contract_id,'succ','ship_update',$ship_status,$this->user->user_id,$this->user->user_data['name']);
        }


        //操作日志-合约状态更新
        if ($data['state'] != $contractRow['state']) {
            $schemaRow = $mdl_contract->get_schema();
            $state_arr = $schemaRow['columns']['state']['type'];//合约状态
            $state = isset($state_arr[$data['state']]) ? $state_arr[$data['state']] : '';
            $state = '合约状态：' . $state;
            $mdl_contract->saveDologs($contract_id,'succ','do',$state,$this->user->user_id,$this->user->user_data['name']);
        }


        $this->end(true,'保存成功');
    }

    //编辑
    function edit($contract_id){
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $data = $mdl_contract->getContractData($contract_id);
        if (empty($data)) {
            $this->splash('failed', 'index.php?ctl=admin_contract&act=index','无此合约！');
        }

        //获取已付款阶段,设置此阶段付款状态为已付款(payed=1)
        $mdl_contract->stepsPayStatus($data['step'],$contract_id);//注意第一个参数为引用
        // ee($data);

        $this->pagedata['data'] = $data;
        $this->pagedata['params'] = $data['params'];//结算参数
        $this->pagedata['step'] = $data['step'];//结算阶段
        $this->pagedata['products'] = $data['products'];
        $this->pagedata['product_ids']= $data['product_ids'];//商品列表数据
        // ee($this->pagedata);
        $this->singlepage('admin/contract/contract.html');
    }


    function save_rule(){
        $this->begin();
        // ee($_POST);
        $data = $this->_contractData($_POST);
        // ee($data);
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $ctime = date('Y-m-d H:i:s');
        $base    = $data['base'];//基础数据
        $product = $data['product'];//合约商品
        $params  = $data['params'];//结算参数
        $step    = $data['step'];//结算阶段

        $base['params'] = serialize($params);
        // ee($base);

        $contract_id = $base['contract_id'];

        if (empty($base['contract_id'])) {
            //添加基本信息
            $contract_id = $mdl_contract->insert($base);

            /*添加合约商品,规则:合约一旦开启商品就不允许修改*/
            foreach ($product as $proKey => $proVal) {
                $product[$proKey]['contract_id'] = $contract_id;
                $product[$proKey]['bn']          = $proVal['gbn'] . $base['contract_no'];//生成合约商品货号，规则：商品编号+合同号
                $product[$proKey]['num']         = $proVal['num'] ? $proVal['num'] : 0;
                $product[$proKey]['store_left']  = $proVal['num'] ? $proVal['num'] : 0;//库存结余
                $product[$proKey]['price']       = $proVal['price'] ? $proVal['price'] : 0;
                unset($product[$proKey]['gbn']);
            }
            $result_goods = $mdl_contract_goods->insertAll($product,['table'=>'sdb_b2c_contract_goods']);
            if(!$result_goods){
                $this->end(false,'保存合约商品时失败，请联系管理员！' );
            }

            //操作日志-创建合约
            $mdl_contract->saveDologs($contract_id,'succ','creates','创建',$this->user->user_id,$this->user->user_data['name']);
        }else{
            //判断合约是否存在
            $row = $mdl_contract->getRow('*',['contract_id'=>$contract_id]);
            if (!$row) {
                $this->end(false,'合约不存在！' );
            }
            //更新合约基本信息
            unset($base['contract_id']);
            $contract_result = $mdl_contract->update($base,['contract_id'=>$contract_id]);

            $wheres['contract_id'] = $contract_id;
            //操作日志-合约更新
            $mdl_contract->saveDologs($contract_id,'succ','edit','更新',$this->user->user_id,$this->user->user_data['name']);
        }

        //阶段开启验证(不能同时开启多个阶段)
        $stepOpenCheckRes = $mdl_contract->stepOpenCheck($step);
        if ($stepOpenCheckRes['error']) {
            $this->end(false,$stepOpenCheckRes['message']);
        }

        /*添加或者修改结算阶段*/
        if (!empty($step)) {
            $result_step = true;
            foreach ($step as $stepKey => $stepVal) {
                $tdata = $stepVal;
                $step_id = $stepVal['step_id'];
                if (isset($stepVal['end_time'])) {
                    $tdata['end_time'] = empty($stepVal['end_time']) ? '0' : strtotime($stepVal['end_time']);
                }

                if ($stepVal['step_id'] > 0) {
                    $stepRow = $mdl_step->getRow('*',['step_id'=>$step_id]);
                    $result_step = $mdl_step->update($tdata,['step_id'=>$step_id]);

                    //操作日志-阶段开启或者关闭
                    if (!empty($stepRow) && !empty($stepVal['state']) && $stepVal['state'] != $stepRow['state']) {
                        $step_state_fix = '开启';
                        if($stepVal['state'] == 'off'){
                            $step_state_fix = '关闭';
                        }
                        $step_state_name =  "阶段{$step_state_fix}：" . $stepRow['title'];
                        $mdl_contract->saveDologs($contract_id,'succ','do',$step_state_name,$this->user->user_id,$this->user->user_data['name']);
                    }

                }else{
                    $tdata['contract_id'] = $contract_id;
                    $tdata['ctime'] = $ctime;
                    // ee($tdata);
                    //出库阶段($fee_ids=8)只允许添加一个
                    if ($tdata['fee_ids'] == '8' && $mdl_step->count(['contract_id'=>$contract_id,'fee_ids'=>'8'])) {
                        $this->end(false,'只能添加一个分批出库阶段！' );
                    }
                    $result_step = $mdl_step->insert($tdata);
                }
                if (!$result_step) break;
            }

            if(!$result_step){
                $this->end(false,'保存结算阶段时失败，请联系管理员！' );
            }
        }

        $this->end();
    }

    //删除结算阶段
    function deleteStep(){
        $step_id = trim($_GET['step_id']);
        $res = ['error'=>0,'message'=>'操作成功！'];
        if (empty($step_id)) {
            $this->apiReturn(['error'=>1,'message'=>'非法的阶段id！']);
        }
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $wheres = ['step_id'=>$step_id];
        $delResult = $mdl_step->remove($wheres);
        if ($delResult) {
            $this->apiReturn($res);
        }
        $this->apiReturn(['error'=>1,'message'=>'操作失败！']);
    }


    //出库的总货值计算
    function totalAmountCalc(){
        $res = ['error'=>0,'message'=>'操作成功！'];
        $products = $_POST['data']['product'];
        if (empty($products)) {
            $this->apiReturn(['error'=>1,'message'=>'没有合约商品！']);
        }

        $amount = 0;
        foreach ($products as $key => $product) {
            $amount += $product['num'] * $product['price'];
        }
        $amount = round($amount,2);
        $this->apiReturn(['error'=>0,'message'=>'操作成功！','data'=>$amount]);
    }

    function _contractData($param){
        #合约相关数据整理
        $ctime = date('Y-m-d H:i:s');
        $data = $param['data'];
        $base = $param['data']['base'];
        $base['content'] = $base['content'] ? $base['content'] : '';//服务内容
        $base['accounts'] = $base['accounts'] ? $base['accounts'] : '';//结算方式
        $base['begin_time'] = $base['begin_time'] ? strtotime($base['begin_time']) : 0;//生效日
        $base['end_time'] = $base['end_time'] ? strtotime($base['end_time']) : 0;//失效日
        $base['state'] = $base['state'] ? $base['state'] : 'off';//启用状态

        if($base['begin_time'] >= $base['end_time']){
            $this->end(false,'失效日不能小于或等于生效日！' );
        }

        if(!is_numeric($base['min_amount'])){
            $this->end(false,'最小出库金额只能为数字！' );
        }

        if(!is_numeric($base['min_num'])){
            $this->end(false,'最小出库数只能为整数！' );
        }
        $base['min_num'] = $base['min_num'] ? (int)$base['min_num'] : 0;//最小出库数

        if (empty($data['params'])) {
            $this->end(false,'结算参数没有填写！' );
        }

        if (empty($base['contract_id']) && empty($data['step'])) {
            $this->end(false,'结算阶段没有填写！' );
        }

        if (empty($base['contract_id']) && empty($data['product'])) {
            $this->end(false,'合约商品必须选择！' );
        }
        $params  = $data['params'];//结算参数
        //总货值必须填写
        if (empty($data['params']) || empty($params['1'])) {
            $this->end(false,'总货值必须填写！' );
        }
        //总货值
        $base['amount'] = $params['1'];

        //根据会员账户创建member_id
        $mdl_pam_members = app::get('pam')->model('members');
        $memberRows = $mdl_pam_members->getList('member_id',array('login_account' => $base['login_name']));
        if(!$memberRows){
            $this->end(false,'客户不存在！' );
        }
        if(count($memberRows) > 1){
            $this->end(false,'您输入的客户名不完整,请重写输入！' );
        }
        $base['member_id'] = $memberRows[0]['member_id'];

        //修改与创建时间
        $base['mtime'] = $ctime;
        if (empty($base['contract_id'])) {
            $base['ctime'] = $ctime;
        }

        // if(!$this->_checkpro($base,$msg)){
        //    $this->end(false,$msg);
        // }

        #合约商品数据整理
        // foreach($base['product'] as $key=>$value){

        // }
        $data['base'] = $base;
        // ee($data);
        return $data;
    }


    function _checkpro($data,&$msg){
        $pro_mdl = app::get('b2c')->model('special_goods');
        $filter['end_time|bthan'] = $data['end_time'];
        $filter['status'] = "true";
        $filter['product_id'] = $data['contract_pro'];
        $filter['special_id|noequal'] = $data['special_id'];

        $checkdata = $pro_mdl->getList('product_id',$filter);
        if($data['status']=='true' && $checkdata){
            $msg = "以下货品ID参加的其他活动还没有结束:";
            foreach($checkdata as $val){
                $msg .= $val['product_id']." ";
            }
            return false;
        }
        return true;
    }

}
