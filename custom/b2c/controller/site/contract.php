<?php
/**
 * 说明：静态页面控制器
 */
class b2c_ctl_site_contract extends b2c_frontpage{
    function __construct($app) {
        parent::__construct($app);
         $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('合约').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('合约').'_'.$shopname;
            $this->description = app::get('b2c')->_('合约').'_'.$shopname;
        }
        $this->app = $app;
        $this->verify_member();
        $this->_response->set_header('Cache-Control', 'no-store');
    }

    public function index(){
       
        $type_id = $this->_request->get_param(0);
        $GLOBALS['runtime']['path'] = $this->runtime_path($type_id);
        $this->pagedata['type_id']=$type_id;
         //获取会员登录信息
         $arrMember = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_id,cur',));
         $arrMember = $arrMember['members'];
         $contractModel = $this->app->model('contract');
         $groupBy = "GROUP BY  c.contract_id ";
        //查询条件
         if ($arrMember['member_id']) {
            $a="'off'";
            $b="'finish'";
         $whereStr='1 and c.member_id='.$arrMember['member_id'].' and c.state !='.$a.' and  c.state !='.$b.'';

            //查询
            if (!empty($_GET['input'])) {
            $contract_no = $_GET['input'];
            $whereStr .= " and c.contract_no LIKE '%{$contract_no}%'";
            unset($_GET['input']);
            }  
          //排序
           if (!empty($_GET['orderBy'])) {
            $orderBy = $_GET['orderBy'];
            unset($_GET['orderBy']);
            $this->pagedata['select'] = $orderBy;
            }  

                   //SQL
                   $join = '';
                   $fields = ' c.contract_no,c.state,c.contract_id,c.amount,c.begin_time,c.end_time,c.content,c.accounts,c.pay_status as cpay_status,c.ship_status,c.min_amount,c.min_num,ca.end_time as time,ca.step_id';
                   $join .= ' LEFT JOIN sdb_b2c_contract_account_step ca ON c.contract_id=ca.contract_id ';            
                   $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract` c ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$groupBy
                        .$orderBy;
            $results = $contractModel->db->select($sql);
            // ee($results);
          $this->pagedata['member_id'] = $arrMember['member_id'];
         }
         $this->pagedata['contract'] ['orderBy']=
           array(
            array(
                'label' =>'默认',
            ),
            array(
                'label' =>'按合同生效日顺序排列',
                'sql' => 'ORDER BY c.begin_time asc'
            ),
             array(
                'label' =>'按合同生效日倒序排列',
                'sql' => 'ORDER BY c.begin_time desc'
            ),
              array(
                'label' =>'按合同失效日顺序排列',
                'sql' => 'ORDER BY c.end_time asc'
            ),
               array(
                'label' =>'按合同失效日倒序排列',
                'sql' => 'ORDER BY c.end_time desc'
            ),
        );
            // ee(   $this->pagedata['contract']);
         
        if($results){
            foreach ($results as $key => $value) {
                //物流状态
                if($value['ship_status']=='seastore'){
                   $results[$key]['ship_status']="海外仓储";
                }elseif ($value['ship_status']=='interlogi') {
                   $results[$key]['ship_status']="国际物流";
                }elseif ($value['ship_status']=='clearance'){
                   $results[$key]['ship_status']="通关";
                }elseif ($value['ship_status']=='storage'){
                   $results[$key]['ship_status']="仓储";
                }elseif ($value['ship_status']=='citydist'){
                   $results[$key]['ship_status']="城配";
                }elseif ($value['ship_status']=='buy'){
                   $results[$key]['ship_status']="采购";
                }

                 $mdl_contract= app::get('b2c')->model('contract');
                 //付款状态;
                 $contract_id=$value['contract_id'];
                 $pay_status=$mdl_contract->getContractPayStatus($contract_id);

                 //结算阶段
                 $mdl_step = app::get('b2c')->model('contract_account_step');
                 $stepRow = $mdl_step->getRow('step_id,end_time',['step_id'=>$pay_status['data'],'state'=>'on']);
                 $results[$key]['pay_status'] = $pay_status['message'];
                 if($pay_status['message']){
                 $results[$key]['order_status']=0;
                   $time=$this->lefttime($stepRow);

                 }
                 //订单付款状态
                 if($pay_status['ispay']==1){
                 $results[$key]['order_status']=1;
                 }
                
                 if($time>=0){
                  //剩余时间
                 $results[$key]['time'] = $time;
                 }else{
                   //剩余时间
                 $results[$key]['time'] =0;
                 }
                

                //显示时间处理
                 $results[$key]['begin_time']=date('Y.m.d',$value['begin_time']);
                 $results[$key]['end_time']=date('Y.m.d',$value['end_time']);
            }
                $this->pagedata['contract_info'] = $results;
            
                    foreach ($results as $a =>$b) {  
                       if($b['order_status']==0){
                       $obligation[]=$results[$a];
                       }else if($b['order_status']==1){
                       $account_paid[]=$results[$a];
                       }
                   }

           
                $this->pagedata['obligation'] = $obligation;
                $this->pagedata['account_paid'] = $account_paid;

                $this->pagedata['members_id'] = $arrMember['member_id'];
                //合约页面
                $this->page('site/contract/contract.html');
        }else{
            if(!empty($_GET['is_query'])&&$_GET['is_query']==6){
                  //广告页
             $this->page('site/contract/contract.html');
            }else{
               $this->page('site/contract/advertisement.html');
            }
             
        }
    }
     //顶部导航路径
    function runtime_path($type_id,$product_id=null){
        $url = "#";
        $path = array(
            array(
                'type'=>"goodscat",
                'title'=>"首页",
                'link'=>kernel::base_url(1),

            ),
            array(
                'type'=>"contract",
                'title'=>'合约',
                'link'=>'./contract.html',
            ),
            array(
                'type'=>"contract",
                'title'=>'合约详情',
                'link'=>$url,
            ),
        );
        return $path;
    }

    //合约页面
    function contract(){
        $this->page('site/contract/contract.html');
    }
     //合约履约
     function performance($contract_id=0){
       
        $GLOBALS['runtime']['path'] = $this->runtime_path();

        $errUrl = ['app'=>'b2c','ctl'=>'site_contract','act'=>'index'];
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $payStatus = $mdl_contract->getContractPayStatus($contract_id);

        if ($payStatus['error']) {
            $this->splash('failed', $errUrl,$payStatus['message']);
        }
        $step_id = $payStatus['data'];
        //已经付款直接跳转到-查看合约页面
        if (isset($payStatus['ispay'])) {
            $this->pagedata['ispay'] = 1;
            $url = array('app'=>'b2c', ctl=>'site_member','act'=>'viewContract','arg0'=>$contract_id);
            $this->redirect($url);
        }
        if (empty($contract_id) || empty($step_id)) {
            $this->splash('failed', $errUrl,'无此合约或者结算阶段！');
        }
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $mdl_fee = app::get('b2c')->model('contract_fee');//费种
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
        //获取合约数据
        $data = $mdl_contract->getContractData($contract_id);
        if (empty($data)) {
            $this->splash('failed', $errUrl,'无此合约数据！');
        }
        // ee($data);
        $stepRow = $mdl_step->getRow('*',['step_id'=>$step_id,'state'=>'on']);
        if (empty($stepRow)) {
            $this->splash('failed', $errUrl,'无此合约的结算阶段数据！');
        }
        // ee($stepRow);

        //获取合约阶段付款列表
        $stepsPaymentRows = $mdl_contract->stepsPayment($contract_id,$step_id);
        if (empty($stepsPaymentRows)) {
            $this->splash('failed', $errUrl,'无此合约的结算阶段数据！');
        }
        $stepsPaymentRow = $stepsPaymentRows[0];
        if ($stepsPaymentRow['state'] == 'off') {
            $this->splash('failed', $errUrl,'此结算阶段尚未开启！');
        }elseif ($stepsPaymentRow['state'] == 'finish') {
            $this->splash('failed', $errUrl,'此结算阶段已经完成，请勿重复付款！');
        }

        //获取阶段付款总值以及表达式
        $this->pagedata['isout'] = $isout = false;
        $stepMoney = [];
        if ($stepsPaymentRow['fee_ids'] == '8') {
            // $stepMoney = $mdl_contract->stepMoneyCalculate($stepsPaymentRow['fee_ids'],$data['params'],$data['products']);
            $stepMoney['total']=$data['params'][1]['value'];//出库的总货值
            $this->pagedata['isout'] = $isout = true;//是出库阶段
            //是否含有临时附加费
            $extra = $stepsPaymentRow['extra'];
            $this->pagedata['extra'] = $extra;
            if ($extra > 0) {
               //临时附加费金额
               $temp_extra_charge = $data['params']['9'];            
               $this->pagedata['temp_extra_charge'] = $temp_extra_charge;

               //临时附加费是否已经提交过订单
               $Qwheres = [];
               $Qwheres['step_id']=$step_id;
               $Qwheres['extra']='1';
               $isPayedAxtra = $mdl_contract->getContractOrders($contract_id,$Qwheres);//是否已支付过了临时附加费
               if (!empty($isPayedAxtra)) {
                   $this->pagedata['isPayedAxtra'] = 1;
               }
            }

        }else{
            $stepMoney = $mdl_contract->stepMoneyCalculate($stepsPaymentRow['fee_ids'],$data['params']);
            //非出库的虚拟商品
            $mdl_products = $this->app->model('products');
            $virtualPro = $mdl_products->getRow('product_id,goods_id',['bn'=>'P5B7F9E59E830E']);
            if (empty($virtualPro)) {
                $this->splash('failed', $errUrl,'合约非出库阶段虚拟商品未设置！');
            }
            $virtualGoods= ['product_id'=>$virtualPro['product_id'],'goods_id'=>$virtualPro['goods_id']];
            $this->pagedata['virtualGoods'] = $virtualGoods;
        }

        // ee(sql());
        // ee($stepMoney);
        $this->pagedata['stepMoney'] = $stepMoney;
        $this->pagedata['stepRow'] = $stepRow;
        $this->pagedata['data'] = $data;
        $this->pagedata['step_id'] = $step_id;
        $this->pagedata['stepsPaymentRow'] = $stepsPaymentRow;
        $this->page('site/contract/performance.html');
    }

     //出库计算-site
     function getOutStoreExp(){
        //$this->app->member_id
        $params = $_GET;
        if (empty($params['contract_id'])) {
            $this->apiReturn(['error'=>1,'message'=>'非法的合约ID号！']);
        }

        if (empty($params['step_id'])) {
            $this->apiReturn(['error'=>1,'message'=>'非法的结算阶段ID号！']);
        }
        $member_id = $this->app->member_id;
        $contract_id = trim($params['contract_id']);
        $step_id = trim($params['step_id']);

        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $mdl_fee = app::get('b2c')->model('contract_fee');//费种
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
        //TODO

        //是否有某个出库批次待支付?如果有,禁止继续出库
        $readyPayOrderRows = $mdl_contract->getContractOrders($contract_id,['pay_status'=>'0']);
        if (!empty($readyPayOrderRows)) {
            $orderID = $readyPayOrderRows[0]['order_id'];
            $this->apiReturn(['error'=>1,'message'=>"您还有未支付的订单[{$orderID}]，暂不可出库！"]);
        }
        //获取合约数据
        $data = $mdl_contract->getContractData($contract_id,$member_id);
        if (empty($data)) {
            $this->apiReturn(['error'=>1,'message'=>'无此合约数据！']);
        }

        if (empty($data['products'])) {
            $this->apiReturn(['error'=>1,'message'=>'合约商品不能为空']);
        }
        $products = $data['products'];

        //合约商品已经出库完毕
        $leftRow = $mdl_contract_goods->count(['contract_id'=>$contract_id,'store_left|than'=>'0']);
        if (!$leftRow) {
            $this->apiReturn(['error'=>1,'message'=>'当前合约商品已经出库完毕！']);
        }

        if (empty($params['outStore'])) {
            $this->apiReturn(['error'=>13,'message'=>'未选择任何出库商品！']);
        }

        //合并出库到合约商品
        $outStore = $params['outStore'];
        $outTotalNum = 0;//出库商品总量
        foreach ($outStore as $key => $num) {
            if (empty($products[$key])) {
                $this->apiReturn(['error'=>1,'message'=>"没有此合约商品[{$key}]！"]);
            }
            $products[$key]['outNum'] = $num;
            $outTotalNum += $num;
        }

        //出库合法性检测(检测出库数量与剩余库存)
        $checkRes = $mdl_contract->storeLeftCheck($products);
        if ($checkRes['error']) {
            $this->apiReturn(['error'=>1,'message'=>$checkRes['message']]);
        }

        //是否含有临时附加费
        $extra = $params['extra'];
        // ee($products);
        $stepMoney = $mdl_contract->stepMoneyCalculate('8',$data['params'],$products,$extra);

        //最小出库数检测 && 最小出库金额检测
        // ee($data);
        $outCheckRes = $mdl_contract->outStoreCheck($data,$stepMoney,$step_id,$extra);
        if ($outCheckRes['error'] != 0) {
            $this->apiReturn(['error'=>1,'message'=>$outCheckRes['message']]);
        }

        $stepMoney['outNum'] = $outTotalNum;
        $this->apiReturn(['data'=>$stepMoney]);
    }


     //广告页
     function advertisement(){
        $this->page('site/static/advertisement.html');
    }

     //查看合约
     function viewContract(){
        $this->page('site/member/viewContract.html');
    }
      //剩余时间
    function lefttime(&$row){
        $time = '';
        if ($row) {
            $time = $row['end_time'] - time();
        }
        return $time;
    }
}
