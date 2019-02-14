<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_tools extends b2c_frontpage{

    function __construct($app) {
        parent::__construct($app);

        $this->app = $app;
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
    }

    function selRegion()
    {
        $arrGet = $this->_request->get_get();
        $path = $arrGet['path'];
        $depth = $arrGet['depth'];

        //header('Content-type: text/html;charset=utf8');
        $local = kernel::single('ectools_regions_select');
        $ectools = app::get('ectools');
        $ret = $local->get_area_select($ectools,$path,array('depth'=>$depth));
        if($ret){
            echo '&nbsp;-&nbsp;'.$ret;exit;
        }else{
            echo '';exit;
        }
    }

    function history(){
        $this->path[] = array('title'=>app::get('b2c')->_('历史记录'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_tools', 'act'=>'history','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;
        $this->title= app::get('b2c')->_('浏览过的商品');
        $this->page('site/tools/history.html');
    }


    function products(){
        $objGoods  = $this->app->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $filter = array();
        foreach(explode(',',$_POST['goods']) as $gid){
            $filter['goods_id'][] = $gid;
         }

        $aProduct = $objGoods->getList('*,find_in_set(goods_id,"'.utils::addslashes_array($_POST['goods']).'") as rank',$filter,0,-1,array('rank','asc'));
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        if(!$member_id){
            $this->pagedata['login'] = 'nologin';
        }
        $view = $this->app->getConf('gallery.default_view');
        if($view=='index') $view='list';

        if(is_array($aProduct) && count($aProduct) > 0){
            $objProduct = $this->app->model('products');
            if($this->app->getConf('site.show_mark_price')=='true'){
                $setting['mktprice'] = $this->app->getConf('site.show_mark_price');
                if(isset($aProduct)){
                    foreach($aProduct as $pk=>$pv){
                        if(empty($aProduct[$pk]['mktprice']))
                        $aProduct[$pk]['mktprice'] = $objProduct->getRealMkt($pv['price']);
                    }
                }
            }else{
                $setting['mktprice'] = 0;
            }
            $setting['saveprice'] = $this->app->getConf('site.save_price');
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            //spec_desc
            $siteMember = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_lv_id'));
            $this->site_member_lv_id = $siteMember['members']['member_lv_id'];
            $oGoodsLv = $this->app->model('goods_lv_price');
            $oMlv = $this->app->model('member_lv');
            $mlv = $oMlv->db_dump( $this->site_member_lv_id,'dis_count' );

            foreach ($aProduct as $key=>&$val) {
                $temp = $objProduct->getList('product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
                $aProduct[$key]['spec_desc'] = unserialize($val['spec_desc']);
                if( $this->site_member_lv_id ){
                    $tmpGoods = array();
                    foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$this->site_member_lv_id ) ) as $k => $v ){
                        $tmpGoods[$v['product_id']] = $v['price'];
                    }
                    foreach( $temp as &$tv ){
                        $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                    }
                    $val['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$val['price'] ));
                }
                $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                if($promotion_price){
                    if($promotion_price['price']) {
                        $val['timebuyprice'] = $promotion_price['price'];
                    }
                    else {
                        $val['timebuyprice'] = $val['price'];
                    }
                    $val['show_button'] = $promotion_price['show_button'];
                    $val['timebuy_over'] = $promotion_price['timebuy_over'];
                }
                $val['spec_desc_info'] = $temp;
                $aProduct[$key]['product_id'] = $temp[0]['product_id'];
                if(empty($val['image_default_id']))
                $aProduct[$key]['image_default_id'] = $imageDefault['S']['default_image'];

            }
            $this->pagedata['products'] = &$aProduct;
        }

        $this->page('site/gallery/type/'.$view.'.html',true);
    }

    public function count_digist()
    {
        if ($_POST['data'] || $_POST['point']){
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');

            $obj_math = kernel::single('ectools_math');
            if($_POST['point']){
                $arr_data[0] = $_POST['point']['rate'];
                $arr_data[1] = floor($_POST['point']['score']);
                $_method = 'number_multiple';
            }else{
                $arr_data = json_decode($_POST['data'], 1);
                $_method = $_POST['_method'];
            }
            try{
                echo $obj_math->$_method($arr_data);exit;
            }catch(Exception $e)
            {
                echo $e->message();exit;
            }
        }
    }

	public function send_orders()
	{
		if (!$_POST['order_id']){
			echo '{failed:"'.app::get('b2c')->_('发送订单号不存在！').'",msg:"'.app::get('b2c')->_('发送订单号不存在！').'"}';exit;
		}

		$order_id = $_POST['order_id'];
		$objOrder = $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf = $objOrder->dump($order_id, '*', $subsdf);

		/** 开始发送 **/
		if (!$sdf){
			echo '{failed:"'.app::get('b2c')->_('发送订单不存在！').'",msg:"'.app::get('b2c')->_('发送订单不存在！').'"}';exit;
		}

        system_queue::instance()->publish('b2c_tasks_matrix_sendorders', 'b2c_tasks_matrix_sendorders', $sdf);

		echo '{success:"'.app::get('b2c')->_('成功！').'",msg:"'.app::get('b2c')->_('成功！').'"}';exit;
	}

	public function send_payments()
	{
		if (!$_POST['payment_id']){
			echo '{failed:"'.app::get('b2c')->_('发送支付号不存在！').'",msg:"'.app::get('b2c')->_('发送支付号不存在！').'"}';exit;
		}

		$app_ectools = app::get('ectools');
        $oPayment = $app_ectools->model('payments');
        $subsdf = array('orders'=>array('*'));
        $sdf_payment = $oPayment->dump($_POST['payment_id'], '*', $subsdf);

		/** 开始发送 **/
		if (!$sdf_payment){
			echo '{failed:"'.app::get('b2c')->_('发送支付单不存在！').'",msg:"'.app::get('b2c')->_('发送支付单不存在！').'"}';exit;
		}

        system_queue::instance()->publish('b2c_tasks_matrix_sendpayments', 'b2c_tasks_matrix_sendpayments', $sdf_payment);

		echo '{success:"'.app::get('b2c')->_('成功！').'",msg:"'.app::get('b2c')->_('成功！').'"}';exit;
	}


    function getareas(){
        //配送地区
        $regionsModel = app::get('b2c')->model('shiparea');
        $cityData = $regionsModel->getGoodsRegions();
        $this->apiReturn(['data'=>$cityData]);
    }

    //配送范围数据，wap端使用,弃用
    function getwapareas_old(){
        //配送地区
        // $shipareaModel = app::get('b2c')->model('shiparea');
        // $cityData = $shipareaModel->getwapareas(true);
        // // ee($cityData);
        // $this->apiReturn(['data'=>$cityData]);
    }

    //配送范围数据，wap端使用
    function getwapareas(){
        //配送地区
        $shipareaModel = app::get('b2c')->model('shiparea');
        $cityData = $shipareaModel->getwapareas();
        $this->apiReturn(['data'=>$cityData]);
    }

    function getgoodsids(){
        //配送地区
        $regionsModel = app::get('b2c')->model('shiparea');
        $data = $regionsModel->getGoodsIdsByregionId(2182);
    }

    //显示标记
    function mark(){
        $limit = $_GET['num'] ? $_GET['num'] : 10;
        $model = $this->app->model('supplier');
        $sql = "select * from sdb_datas_log order by id desc limit {$limit}";
        $datas = $model->db->select($sql);
        if (!empty($datas)) {
            foreach ($datas as &$data) {
                if ($data['isobj'] == '1') {
                    $data['content'] = unserialize($data['content']);
                }
            }
        }
        $this->pagedata['datas'] = $datas;
        $this->display('site/tools/mark.html');
    }


    //购物车商品种数
    function getCartGoods(){
        $goodsNum = 0;
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        if (empty($member_id)) {
            $this->apiReturn(['data'=>$goodsNum]);
        }
        $mCartObject = app::get('b2c')->model('cart_objects');
        $goodsNum = $mCartObject->parent_count(['member_id'=>$member_id,'obj_type'=>'goods','buy_type'=>'']);
        $this->apiReturn(['data'=>$goodsNum]);
    }

    //测试定时任务
    function testtask($id=''){
        if($id != 'ypw2018online000') return;
        $ctime = date('Y-m-d H:i:s');
        // mark('task：' . $ctime);
        // ee('success!!------' . $ctime);
    }

    //启用状态
    public  function  preparesell_fund_state($id=''){
        if ($id != 'ypw2018online000') return;
        header("Content-type: text/html; charset=utf-8");
        $step = 1;
        echo $step++ . '.自动处理[<b style="color:green">已启动</b>]...<br>';
        $mdb = app::get('preparesell')->model('preparesell');
        $mdl_preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $mdl_preparesell_order_fund = app::get('preparesell')->model('order_fund');
        $mdl_preparesell_order= app::get('b2c')->model('orders');
        $nowtime= time();

        //----------------------------------1.处理过期----------------------------------------------
        //[1.1]处理过期预售活动->设置prepare_goods为fasle
        //注意：暂时只设置预售goods过期，而不设置活动false
        echo $step++ . ".正在处理过期活动...<br>";
        $sqlexpire = "SELECT prepare_id id FROM  sdb_preparesell_preparesell as p WHERE `status`!='false' AND {$nowtime} > p.end_time";
        $rows = $mdb->db->select($sqlexpire);
        // ee(sql());

        $ids = [];//prepare_id ids
        $setNum = 0;
        if($rows){
            foreach ($rows as $row) {
                 $ids[$row['id']] = $row['id'];
            }

            $setNum = count($ids);
            $ids = implode(',', $ids);
            //设置预售商品过期
            $mdb->db->exec("UPDATE sdb_preparesell_preparesell_goods SET `status`='false' WHERE `status`!='false' AND prepare_id IN ({$ids})");
            //设置预售活动过期
            $mdb->db->exec("UPDATE sdb_preparesell_preparesell SET `status`='false' WHERE `status`!='false' AND prepare_id IN ({$ids})");
        }
        echo $step++ . ".本次共<b>作废</b>{$setNum}个过期的预售商品.........<br>";
        //1.1END


        //[1.2]预售订单款项过期处理(逻辑：某个过期的&&未完成的订单款项在payments里面状态没有succ就设置该订单dead)
        echo $step++ . ".正在处理过期预售订单款项...<br>";
        $fields = 'of.order_fund_id,of.order_id,GROUP_CONCAT(`p`.`status`) AS status_pay,`of`.`status` as status_fund';
        $join = "SELECT {$fields} FROM `sdb_preparesell_order_fund` of";
        $join .= ' LEFT JOIN sdb_b2c_orders o ON o.order_id = of.order_id';
        $join .= ' LEFT JOIN sdb_ectools_order_bills b ON b.rel_id = of.order_id';
        $join .= ' LEFT JOIN sdb_ectools_payments p ON (b.bill_id = p.payment_id and p.fund_id = of.fund_id and p.fund_type=of.fund_name)';
        $join .= " WHERE of.end_time < {$nowtime} AND `of`.`status` !='finish' AND `o`.`status`!='dead' GROUP BY order_fund_id";
        $rows = $mdb->db->select($join);
        // ee(sql());

        $ids = [];//orderIds
        $setNum = 0;
        if(!empty($rows)){
            foreach ($rows as $row) {
                $paymentsArr = explode(',', $row['status_pay']);
                if (!in_array('succ', $paymentsArr)) {
                    $ids[] = $row['order_id'];
                }
            }
            // ee($ids);
             //设置订单过期
            if(!empty($ids)){
                $setNum = count($ids);
                $ids = implode(',', $ids);
                $order_fund = $mdb->db->exec("UPDATE sdb_b2c_orders set `status`='dead' where `status`!='dead' AND   promotion_type='prepare' and order_id in ({$ids})");
            }
        }
        echo $step++ . ".本次根据过期的订单款项共<b>作废</b>{$setNum}个订单.........<br>";
        //1.2END


        //[1.3]过期预售款项处理->设置order_goods 为false
        echo $step++ . ".正在处理过期预售款项...<br>";
        $fields = 'f.prepare_id id';
        $sqlExp = "SELECT {$fields} FROM `sdb_preparesell_preparesell_fund` f";
        $sqlExp .= ' INNER JOIN sdb_preparesell_preparesell_goods pg ON pg.prepare_id = f.prepare_id';
        $sqlExp .= " WHERE f.fund_name='y' AND f.end_time < {$nowtime} AND `pg`.`status` !='false' GROUP BY f.prepare_id";
        $rows = $mdb->db->select($sqlExp);
        // ee(sql());
        $ids = [];//order_fund_id ids
        $setNum = 0;
        if($rows){
            foreach ($rows as $row) {
                 $ids[$row['id']] = $row['id'];
            }
            $setNum = count($ids);
            $ids = implode(',', $ids);
            //设置预售商品关闭
            $mdb->db->exec("UPDATE sdb_preparesell_preparesell_goods SET `status`='false' WHERE `status`='true' AND prepare_id IN ({$ids})");
        }
        echo $step++ . ".本次根据过期的预售款项共<b>作废</b>{$setNum}个预售商品.........<br><br>";

        //1.3END

        //----------------------------------1.处理过期 END----------------------------------------------


        //----------------------------------2.处理到期START----------------------------------------------
        //[2.1]处理到期预售活动->设置prepare_goods为true && 设置preparesell为true
        echo $step++ . ".正在处理到期活动...<br>";
        $sqlAct = "SELECT prepare_id id FROM  sdb_preparesell_preparesell as p WHERE `status`='false' AND p.begin_time < {$nowtime} AND {$nowtime} < p.end_time";
        $rows = $mdb->db->select($sqlAct);
        $ids = [];//prepare_id ids
        $setNum = 0;
        if($rows){
            foreach ($rows as $row) {
                 $ids[$row['id']] = $row['id'];
            }

            $setNum = count($ids);
            $ids = implode(',', $ids);
            //设置预售商品到期
            $mdb->db->exec("UPDATE sdb_preparesell_preparesell_goods SET `status`='true' WHERE `status`='false' AND prepare_id IN ({$ids})");
            //设置预售活动到期
            $mdb->db->exec("UPDATE sdb_preparesell_preparesell SET `status`='true' WHERE `status`='false' AND prepare_id IN ({$ids})");
        }
        echo $step++ . ".本次共<b>开启</b>{$setNum}个预售活动商品.........<br>";
        //2.1END

        //[2.1]处理到期订单款项->设置prder_fund 为true
        echo $step++ . ".正在处理到期订单款项...<br>";
        $sqlAct = "SELECT order_fund_id id FROM  sdb_preparesell_order_fund as p WHERE `status`='false' AND p.begin_time < {$nowtime} AND {$nowtime} < p.end_time";
        $rows = $mdb->db->select($sqlAct);
        // ee(sql(),0);
        $ids = [];//order_fund_id ids
        $setNum = 0;
        if($rows){
            foreach ($rows as $row) {
                 $ids[$row['id']] = $row['id'];
            }
            $setNum = count($ids);
            $ids = implode(',', $ids);
            //设置预售订单款项到期
            $mdb->db->exec("UPDATE sdb_preparesell_order_fund SET `status`='true' WHERE `status`='false' AND order_fund_id IN ({$ids})");
        }
        echo $step++ . ".本次共<b>开启</b>{$setNum}个订单款项.........<br><br>";
        //2.1END
        //----------------------------------2.处理到期 END----------------------------------------------


        //----------------------------------3.处理已完成 START----------------------------------------------
        //[3.1]处理已完成订单款项->设置order_fund 为finish
        echo $step++ . ".正在处理已完成订单款项...<br>";
        $fields = 'of.order_fund_id id,of.order_id,`p`.`status` AS status_pay';
        $sqlFinish = "SELECT {$fields} FROM `sdb_preparesell_order_fund` of";
        // $sqlFinish .= ' INNER JOIN sdb_b2c_orders o ON o.order_id = of.order_id';
        $sqlFinish .= ' INNER JOIN sdb_ectools_order_bills b ON b.rel_id = of.order_id';
        $sqlFinish .= ' INNER JOIN sdb_ectools_payments p ON (b.bill_id = p.payment_id and p.fund_id = of.fund_id and p.fund_type=of.fund_name)';
        $sqlFinish .= " WHERE `of`.`status` !='finish' AND `p`.`status`='succ' GROUP BY order_fund_id";
        $rows = $mdb->db->select($sqlFinish);
        // ee(sql());
        $ids = [];//order_fund_id ids
        $setNum = 0;
        if($rows){
            foreach ($rows as $row) {
                 $ids[$row['id']] = $row['id'];
            }
            $setNum = count($ids);
            $ids = implode(',', $ids);
            //设置预售订单款项为已完成
            $mdb->db->exec("UPDATE sdb_preparesell_order_fund SET `status`='finish' WHERE `status`!='finish' AND order_fund_id IN ({$ids})");
        }
        echo $step++ . ".本次共设置{$setNum}个订单款项为<b>已完成</b>状态.........<br><br>";
        //3.1END
        //----------------------------------3.处理已完成 END----------------------------------------------
        exit($step++ . '.自动处理[<b style="color:green">已完成</b>]');
    }




          //短信提醒
    public function preparesell_fund_send(){

        echo "------款项短信发送------";
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $mdl_preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $mdl_preparesell_order_fund = app::get('preparesell')->model('order_fund');
        $mdl_preparesell_order= app::get('b2c')->model('orders');
        $mdl_member_addrs= app::get('b2c')->model('member_addrs');
        $nowtime= strtotime(date("Y-m-d H:i:s", time()));
        $_POST['status']=1;
        //会员手机号查询
        $sqlphone='select  GROUP_CONCAT(DISTINCT mobile) as mobiles from sdb_b2c_member_addrs ';
        $phone= $mdl_member_addrs->db->select($sqlphone);
           if($phone){
                $datas = $phone[0]['mobiles'];
            }
         $sql = "select * from sdb_b2c_orders as o,
                 sdb_preparesell_prepare_order  as po,
                 sdb_preparesell_order_fund  as pf
                 where po.order_id=o.order_id 
                 and po.order_id=pf.order_id 
                 and pf.status=true 
                 and pf.is_send_start=0 
                 and pf.remind_time_send=".$nowtime ;
        $result = $mdl_preparesell_order->db->select($sql);
        if($result){
           foreach ($result as $key => $value) {
               if($value['fund_name']=='y'){
                   //执行发送短信
                      $_POST['content']='预售预付款开始支付' . $key;
                      $_POST['mobile_number']=json_encode($datas);
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                    if($attr==1){
                        //填写发送记录
                      $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='y' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                    }
               }elseif ($value['fund_name']=='z') {
                    $sqlzhifu="select * from  sdb_ectools_payments as ep,
                              sdb_ectools_order_bills as eo
                              where  eo.bill_type='payments' 
                              and eo.bill_id=ep.payment_id
                              and status='succ' 
                              and eo.rel_id=
                              ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售中期进度款待支付';
                  if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                  if($attr==1){
                          $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='z' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                      }
                     
                   }
               }elseif ($value['fund_name']=='w') {
                      $sqlzhifu="select * from  sdb_ectools_payments as ep,
                                sdb_ectools_order_bills as eo
                                where  eo.bill_type='payments' 
                                and eo.bill_id=ep.payment_id
                                and status='succ' 
                                and eo.rel_id=
                                ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售尾款待支付';
                      if(!empty($value['ship_mobile'])){
                           $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                            
                      }
                      if($attr==1){
                         $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='w' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                         
                      }
                     
                   }
                      
               }
           }
           
        }
        
            //结束提醒
           $sqlend = "select * from sdb_b2c_orders as o,
                 sdb_preparesell_prepare_order  as po,
                 sdb_preparesell_order_fund  as pf
                 where po.order_id=o.order_id 
                 and po.order_id=pf.order_id 
                 and pf.status=true 
                 and pf.is_send_start=0 
                 and pf.remind_time_send_end=".$nowtime ;
        $result = $mdl_preparesell_order->db->select($sqlend);
        if($result){
           foreach ($result as $key => $value) {
               if($value['fund_name']=='y'){
                   //执行发送短信
                      $_POST['content']='预售预付款开始支付';
                      $_POST['mobile_number']=$value['ship_mobile'];
                    
                      if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                    if($attr==1){
                        //填写发送记录
                      $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='y' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                    }
               }elseif ($value['fund_name']=='z') {
                    $sqlzhifu="select * from  sdb_ectools_payments as ep,
                              sdb_ectools_order_bills as eo
                              where  eo.bill_type='payments' 
                              and eo.bill_id=ep.payment_id
                              and status='succ' 
                              and eo.rel_id=
                              ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信

                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售中期进度款待支付';
                    
                      if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                      if($attr==1){
                          $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='z' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                      }
                     
                   }
               }elseif ($value['fund_name']=='w') {
                      $sqlzhifu="select * from  sdb_ectools_payments as ep,
                                sdb_ectools_order_bills as eo
                                where  eo.bill_type='payments' 
                                and eo.bill_id=ep.payment_id
                                and status='succ' 
                                and eo.rel_id=
                                ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售尾款待支付';
                      if(!empty($value['ship_mobile'])){
                           $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                      if($attr==1){
                         $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='w' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                         
                      }
                     
                   }
                      
               }
           }
            
        }
        
    }





}
