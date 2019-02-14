<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_order extends wap_frontpage{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        $shopname = app::get('wap')->getConf('wap.name');
        if(isset($sysconfig)){
            $this->title = app::get('b2c')->_('订单').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('订单_').'_'.$shopname;
            $this->description = app::get('b2c')->_('订单_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('b2c')->_('订单中心');
        $this->objMath = kernel::single("ectools_math");
    }

    public function create()
    {
        //$this->app->model('coupons')->deleteCart($arrMember['member_id']);
        /**
         * 取到扩展参数,用来判断是否是团购立即购买，团购则不判断登录（无注册购买情况下）
         */
        $arr_args = func_get_args();
        $arr_args = array(
            'get' => $arr_args,
            'post' => $_POST,
        );
       
        $groupbuy = (array)json_decode($arr_args['post']['extends_args']);
        // 判断顾客登录方式.
        #$login_type = $this->app->getConf('site.login_type');
        $arrMember = $this->get_current_member();
        // checkout url
        $this->begin(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'checkout'));
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $this->mCart = $this->app->model('cart');
        if(isset($_POST['isfastbuy']) && $_POST['isfastbuy']){
            $is_fastbuy = true;
            $fastbuy_filter['is_fastbuy'] = $is_fastbuy;
        }else{
            $is_fastbuy = false;
            $fastbuy_filter['is_fastbuy'] = $is_fastbuy;
        }
        $fastbuy_filter['apply_platform'] = '2';
        $aCart = $this->mCart->get_objects($fastbuy_filter);
        // ee($aCart);
        //模拟购物车数据
        if($_POST['order_type']=='bail'){
        $aCart =Array
        (
            object=> Array
                (

                    goods => Array
                        (
                            0 => Array
                                (
                                    obj_ident =>'goods_921_1523',
                                    obj_type =>'goods',
                                    obj_items => Array
                                        (
                                            products => Array
                                                (
                                                    0 => Array
                                                        (
                                                            bn => 'YPW00479GYS002',
                                                            price => Array
                                                                (
                                                                    price => '300',
                                                                    cost => '300',
                                                                    member_lv_price => '300',
                                                                    buy_price => '300',
                                                                ),

                                                            product_id => '1523',
                                                            goods_id => '1079',
                                                            goods_type => 'normal',
                                                            name => '保证金',
                                                            consume_score => '0',
                                                            gain_score => '0',
                                                            type_setting => Array
                                                                (
                                                                    use_brand => 0,
                                                                    use_props => 1,
                                                                ),

                                                            type_id => 11,
                                                            cat_id => 34,
                                                            min_buy => 0,
                                                            spec_info => 0,
                                                            spec_desc => 0,
                                                            weight => 0.000,
                                                            quantity => 1,
                                                            params => 0,
                                                            floatstore => 0,
                                                            store => 9994,
                                                            package_scale => 1.00,
                                                            package_unit =>0,
                                                            package_use =>'0',
                                                            unit => '盒',
                                                            sales_price => 0.000,
                                                            gunit => '盒',
                                                            storehouse_id => 0,
                                                            owner_code => '',
                                                            sku_code =>'',
                                                            is_yp_store => 0,
                                                            unitEn => '',
                                                            default_image => Array
                                                                (
                                                                    thumbnail => '8913c4832317f7efe1e750f823780efb',
                                                                ),

                                                            json_price => Array
                                                                (
                                                                    price => 300,
                                                                    cost => 300,
                                                                    member_lv_price => 300,
                                                                    buy_price => 300,
                                                                ),

                                                            thumbnail => '8913c4832317f7efe1e750f823780efb',
                                                            new_name => '保证金',
                                                            subtotal => 300,
                                                        ),

                                                ),

                                        ),

                                    quantity => 1,
                                    params => Array
                                        (
                                            goods_id => 1079,
                                            product_id => 1523,
                                            adjunct => Array
                                                (
                                                ),
                                            extends_params => 0,
                                        ),

                                    subtotal_consume_score => 0.00,
                                    subtotal_gain_score => 0.00,
                                    subtotal => 300,
                                    subtotal_price => 300,
                                    subtotal_weight => 0.00,
                                    discount_amount => 0,
                                    store => Array
                                        (
                                            real => 9994,
                                            less => 1,
                                            store => 9994,
                                            name => '保证金',
                                        ),

                                    subtotal_prefilter_after => 300,
                                    discount_amount_prefilter => 0.00,
                                    item_quantity_count => 0,
                                    min_buy => Array
                                        (
                                            goods_id => 1079,
                                            min_buy =>0, 
                                            name =>'保证金',
                                        ),

                                ),

                        ),

                ),
            subtotal_consume_score => 0,
            subtotal_gain_score => 0,
            subtotal => 300,
            subtotal_price => 300,
            subtotal_discount => 0,
            items_quantity => 1,
            items_count => 1,
            subtotal_weight => 0,
            discount_amount_prefilter => 0,
            discount_amount_order => 0,
            discount_amount => 0,
            subtotal_prefilter_after => 300,
            goods_min_buy => Array
                (
                    1079 => Array
                        (
                            info => Array
                                (
                                    goods_id => 1079,
                                    min_buy => 0,
                                    name => '保证金',
                                ),

                            real_quantity=> 1,
                        ),

                ),

            items_quantity_widgets => 1,
            items_count_widgets => 1,
        );
 
        }
      
        $aCart['apply_platform'] = $fastbuy_filter['apply_platform'];
        //判断B类优惠券是否已使用
        $checkCodeB_use = @app::get('b2c')->model('coupons')->checkCodeB_use($aCart['object']['coupon']);
        if($checkCodeB_use===false){
            $db->rollback();
            $msg = app::get('b2c')->_("优惠券正在使用中……");
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'index')),true,true);
        }
        //当有活动时，在生成订单前做一个当前购买数量与实际库存的判断
        if( isset($aCart['cart_status'] )){

            $this->end(false,app::get('b2c')->_($aCart['cart_error_html']),$this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'index')),true,true);

        }

        // 校验购物车是否为空
        if ($this->mCart->is_empty($aCart))
        {
            $this->end(false,app::get('b2c')->_('操作失败，购物车为空！'),$this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'index')),true,true);
        }
        // 校验购物车有没有发生变化
        $md5_cart_info = $_POST['md5_cart_info'];
        if (!defined("STRESS_TESTING") && $md5_cart_info != kernel::single("b2c_cart_objects")->md5_cart_objects($is_fastbuy)){
            $this->end(false,app::get('b2c')->_('购物车内容发生变化，请重新结算！'),$this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'checkout')),true,true);
        }

        $msg = "";

        //判断团购限制数量
        if( app::get('starbuy')->is_actived() ){
            $starbuy_special_count = kernel::single('starbuy_special_count');
            $starbuy_tmp = $aCart['object']['goods'];
            foreach($starbuy_tmp as $starbuy_goods)
            {
                foreach($starbuy_goods['obj_items']['products'] as $starbuy_product)
                {
                    $fmt_check_products[$starbuy_product['product_id']] = $starbuy_goods['quantity'];
                }
            }
            if($fmt_check_products != null){
                $special_goods = $starbuy_special_count->get_special_products($fmt_check_products);
                if($special_goods != null)
                {
                    foreach($special_goods as $tmp_special_goods)
                    {
                        if( $starbuy_special_count->add_count($arrMember['member_id'],$tmp_special_goods['product_id'],$fmt_check_products[$tmp_special_goods['product_id']]) == false)
                        {
                            $db->rollback();
                            $this->end(false,app::get('b2c')->_('您的购物车中有商品数量超出了可购买数量，请检查购物车。'), null,true,true);
                        }
                    }
                }
            }
        }

        if(!$_POST['address']){
            $msg .= app::get('b2c')->_("请先确认收货地址")."\n";
            $this->end(false, $msg,'',true,true);
        }else{
            $address = json_decode($_POST['address'],true);
            unset($_POST['address']);
            unset($_POST['purchase']);
            $addr = $this->app->model('member_addrs')->getList('*',array('addr_id'=>$address['addr_id'],'member_id'=>$arrMember['member_id']));
            if($this->app->getConf('site.checkout.zipcode.required.open') == 'true' && empty($addr[0]['zip']) ) {
                $this->end(false,app::get('b2c')->_('收货地址不完整，请填写邮编'),null,true,true);
            }
            $ship_area_name = app::get('ectools')->model('regions')->change_regions_data($addr[0]['area']);
            $_POST['delivery']['addr_id'] = $addr[0]['addr_id'];
            $_POST['delivery']['ship_area'] = $addr[0]['area'];
            $_POST['delivery']['ship_addr'] = $ship_area_name.$addr[0]['addr'];//$addr[0]['addr'];
            $_POST['delivery']['ship_zip'] = $addr[0]['zip'];
            $_POST['delivery']['ship_name'] = $addr[0]['name'];
            $_POST['delivery']['ship_mobile'] = $addr[0]['mobile'];
            $_POST['delivery']['ship_tel'] = $addr[0]['tel'];
            if($this->app->getConf('site.checkout.receivermore.open') == 'true'){
                $_POST['delivery']['day'] = $addr[0]['day'];
                $_POST['delivery']['time'] = $addr[0]['time'];
            }else{
                $_POST['delivery']['day'] = app::get('b2c')->_('任意时间');
                $_POST['delivery']['time'] = app::get('b2c')->_('任意时间段');
            }
        }
        if(!$_POST['shipping']){
            $msg .= app::get('b2c')->_("请先确认配送方式")."\n";
            $this->end(false, $msg, '',true,true);
        }else{
            $shipping = json_decode($_POST['shipping'],true);
            unset($_POST['shipping']);
            $_POST['delivery']['shipping_id'] = $shipping['id'];
            $_POST['delivery']['is_protect'][$shipping['id']] = $_POST['is_protect'];
        }
    if($_POST['order_type']!='bail'){
        if(!$_POST['payment']){
            $msg .= app::get('b2c')->_("请先确认支付方式")."\n";
            $this->end(false, $msg, '',true,true);
        }else{
            $payment_id = json_decode($_POST['payment']['pay_app_id'],true);
            $_POST['payment']['pay_app_id'] = $payment_id['pay_app_id'];
        }

        if($_POST['point']['score']){
            $_POST['payment']['dis_point'] = $_POST['point']['score'];
        }

        if (!$_POST['delivery']['ship_area'] ||  !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] ||  (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || !$_POST['delivery']['shipping_id'] || !$_POST['payment']['pay_app_id'])
        {
            if (!$_POST['delivery']['ship_area'] )
            {
                $msg .= app::get('b2c')->_("收货地区不能为空！")."\n";
            }

            if (!$_POST['delivery']['ship_addr'])
            {
                $msg .= app::get('b2c')->_("收货地址不能为空！")."\n";
            }

            if (!$_POST['delivery']['ship_name'])
            {
                $msg .= app::get('b2c')->_("收货人姓名不能为空！")."\n";
            }

            /*
            if (!$_POST['delivery']['ship_email'] && !$arrMember['member_id'])
            {
                $msg .= app::get('b2c')->_("Email不能为空！")."<br />";
            }
            */

            if (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel'])
            {
                $msg .= app::get('b2c')->_("手机或电话必填其一！")."\n";
            }

            if (!$_POST['delivery']['shipping_id'])
            {
                $msg .= app::get('b2c')->_("配送方式不能为空！")."\n";
            }

            if (!$_POST['payment']['pay_app_id'])
            {
                $msg .= app::get('b2c')->_("支付方式不能为空！")."\n";
            }

            if (strpos($msg, '\n') !== false)
            {
                $msg = substr($msg, 0, strlen($msg) - 2);
            }
            eval("\$msg = \"$msg\";");
            $this->end(false, $msg, '',true,true);
        }
     }
        $obj_dlytype = $this->app->model('dlytype');
        if ($_POST['payment']['pay_app_id'] == '-1')
        {
            $arr_dlytype = $obj_dlytype->dump($_POST['delivery']['shipping_id'], '*');
            if ($arr_dlytype['has_cod'] == 'false')
            {
                $this->end(false, $this->app->_("ship_method_consistent_error"),'',true,true);
            }
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);
        $order = $this->app->model('orders');
        $_POST['order_id'] = $order_id = $order->gen_id();
        $_POST['member_id'] = $arrMember['member_id'] ? $arrMember['member_id'] : 0;
        $order_data = array();
        $obj_order_create = kernel::single("b2c_order_create");
        // 加入订单能否生成的判断
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if ($obj_checkorder)
        {
            if (!$obj_checkorder->check_create($aCart, $_POST['delivery']['ship_area'], $message))
                $this->end(false, $message);
        }
        $order_data = $obj_order_create->generate($_POST,'',$msg,$aCart);
        $obj_checkproducts = kernel::servicelist('b2c_order_check_products');
        if ($obj_checkproducts)
        {
        	foreach($obj_checkproducts as $obj_check){
	            if (!$obj_check->check_products($order_data, $messages))
	                $this->end(false, $messages);
        	}
        }
		if (!$order_data || !$order_data['order_objects'])
		{
			$db->rollback();
			$this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'checkout')),true,true);
		}
        if($this->from_weixin){
            $order_data['source'] = 'weixin'; //订单来源
        }else{
            $order_data['source'] = 'wap'; //订单来源
        }
        if($_COOKIE['penker'] == 'true'){
            $order_data['source'] = 'penker';
            $order_data['guide_identity'] = $_COOKIE['guide_identity'];
        }
         //给订单表加上预售标识
        $order_data['promotion_type']=$_POST['promotion_type'];
        //kernel::single('penker_service_order')->create($order_data);
        //exit;
      
        //给订单加保证金类型
        if($_POST['order_type']=='bail'){
        $order_data['promotion_type']='bail';
        }
        $result = $obj_order_create->save($order_data, $msg);
         //同时在预售订单表添加条数据245-255行
        if($_POST['promotion_type']=='prepare')
        {   $date=array(
                'order_id'=>$order_data['order_id'],
                'member_id'=>$arrMember['member_id'] ? $arrMember['member_id'] : 0,
                'email'=>$arrMember['email'],
                'product_id'=>$order_data['order_objects'][0]['order_items'][0]['products']['product_id'],
                'name'=>$order_data['order_objects'][0]['order_items'][0]['name'],
            );
            kernel::service('prepare_order')->sava_prepare_order($date);
        }
		if ($result)
		{
			// 发票高级配置埋点
			foreach( kernel::servicelist('invoice_setting') as $services ) {
				if ( is_object($services) ) {
					if ( method_exists($services, 'saveInvoiceData') ) {
						$services->saveInvoiceData($_POST['order_id'],$_POST['payment']);
					}
				}
			}
			// 与中心交互
			$is_need_rpc = false;
			$obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
			foreach ($obj_rpc_obj_rpc_request_service as $obj)
			{
				if ($obj && method_exists($obj, 'rpc_judge_send'))
				{
					if ($obj instanceof b2c_api_rpc_notify_interface)
						$is_need_rpc = $obj->rpc_judge_send($order_data);
				}

				if ($is_need_rpc) break;
			}

			if ($is_need_rpc)
			{
              /*
				$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');

				if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
				{
					if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
						$obj_rpc_request_service->rpc_caller_request($order_data,'create');
				}
				else
				{
					$obj_order_create->rpc_caller_request($order_data);
                    }*/
              //新的版本控制api
              $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
              $obj_apiv->rpc_caller_request($order_data, 'ordercreate');
			}
		}

        // 取到日志模块
        if ($arrMember['member_id'])
        {
            $obj_members = $this->app->model('members');
            $arrPams['pam_account']['login_name'] = $arrMember['uname'];
        }

        // remark create
        $obj_order_create = kernel::single("b2c_order_remark");
        $arr_remark = array(
            'order_bn' => $order_id,
            'mark_text' => $_POST['memo'],
            'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'mark_type' => 'b0',
        );

        $log_text = "";
        if ($result)
        {
			$log_text[] = array(
				'txt_key'=>'订单创建成功！',
				'data'=>array(
				),
			);
			$log_text = serialize($log_text);
        }
        else
        {
			$log_text[] = array(
				'txt_key'=>'订单创建失败！',
				'data'=>array(
				),
			);
			$log_text = serialize($log_text);
        }
        $orderLog = $this->app->model("order_log");
        $sdf_order_log = array(
            'rel_id' => $order_id,
            'op_id' => $arrMember['member_id'],
            'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'creates',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );

        $log_id = $orderLog->save($sdf_order_log);

        if ($result)
        {
            foreach(kernel::servicelist('b2c_save_post_om') as $object)
            {
                $object->set_arr($order_id, 'order');
            }

            // 设定优惠券不可以使用
            if (isset($aCart['object']['coupon']) && $aCart['object']['coupon'])
            {
                $coupon_status = in_array($_POST['payment']['pay_app_id'],array('-1')) ? 'true':'busy';
                $obj_coupon = kernel::single("b2c_coupon_mem");
                foreach ($aCart['object']['coupon'] as $coupons)
                {
                    if($coupons['used'])
                        $obj_coupon->use_c($coupons['coupon'], $arrMember['member_id'], $order_id,$coupon_status);
                }
            }

            // 订单成功后清除购物车的的信息
            $this->cart_model = $this->app->model('cart_objects');
            $this->cart_model->remove_object('',null,$mag,$is_fastbuy);

            // 生成cookie有效性的验证信息
            setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$order_id));
            setcookie("purchase[addr][usable]", "", time() - 3600, kernel::base_url().'/');
            setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
            setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
            setcookie("checkout_b2c_goods_buy_info", "", time() - 3600, kernel::base_url().'/');

            // 得到物流公司名称
            if ($order_data['order_objects'])
            {
                $itemNum = 0;
                $good_id = "";
                $goods_name = "";
                foreach ($order_data['order_objects'] as $arr_objects)
                {
                    if ($arr_objects['order_items'])
                    {
                        if ($arr_objects['obj_type'] == 'goods')
                        {
                            $obj_goods = $this->app->model('goods');
                            $good_id = $arr_objects['order_items'][0]['goods_id'];
                            $obj_goods->updateRank($good_id, 'buy_count',$arr_objects['order_items'][0]['quantity']);
                            $arr_goods = $obj_goods->parent_getList('image_default_id',array('goods_id'=>$good_id));
                            $arr_goods = $arr_goods[0];
                        }

                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            $itemNum = $this->objMath->number_plus(array($itemNum, $arr_items['quantity']));
                            if ($arr_objects['obj_type'] == 'goods')
                            {
                                if ($arr_items['item_type'] == 'product')
                                    $goods_name .= $arr_items['name'] . ($arr_items['products']['spec_info'] ? '(' . $arr_items['products']['spec_info'] . ')' : '') . '(' . $arr_items['quantity'] . ')';
                            }
                        }
                    }
                }
                $arr_dlytype = $obj_dlytype->dump($order_data['shipping']['shipping_id'], 'dt_name');
                $arr_updates = array(
                    'order_id' => $order_id,
                    'total_amount' => $order_data['total_amount'],
                    'shipping_id' => $arr_dlytype['dt_name'],
                    'ship_mobile' => $order_data['consignee']['mobile'],
                    'ship_tel' => $order_data['consignee']['telephone'],
                    'ship_addr' => $order_data['consignee']['addr'],
                    'ship_email' => $order_data['consignee']['email'] ? $order_data['consignee']['email'] : '',
                    'ship_zip' => $order_data['consignee']['zip'],
                    'ship_name' => $order_data['consignee']['name'],
                    'member_id' => $order_data['member_id'] ? $order_data['member_id'] : 0,
                    'uname' => (!$order_data['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                    'itemnum' => count($order_data['order_objects']),
                    'goods_id' => $good_id,
                    'goods_url' => kernel::base_url(1).kernel::url_prefix().$this->gen_url(array('app'=>'b2c','ctl'=>'wap_product','act'=>'index','arg0'=>$good_id)),
                    'thumbnail_pic' => base_storager::image_path($arr_goods['image_default_id']),
                    'goods_name' => $goods_name,
                    'ship_status' => '',
                    'pay_status' => 'Nopay',
                    'is_frontend' => true,
                );

                $cancelorder_obj = kernel::servicelist("cancelorder");
                foreach($cancelorder_obj as $obj_cancelorder){
                    $obj_cancelorder->cancel($order_id,$msg);
                }

                $order->fireEvent('create', $arr_updates, $order_data['member_id']);
            }

            $db->commit($transaction_status);

            /** 订单创建结束后执行的方法 **/
            $odr_create_service = kernel::servicelist('b2c_order.create');
            $arr_order_create_after = array();
            if ($odr_create_service)
            {
                foreach ($odr_create_service as $odr_ser)
                {
                    if(!is_object($odr_ser)) continue;

                    if( method_exists($odr_ser,'get_order') )
                        $index = $odr_ser->get_order();
                    else $index = 10;

                    while(true) {
                        if( !isset($arr_order_create_after[$index]) )break;
                        $index++;
                    }
                    $arr_order_create_after[$index] = $odr_ser;
                }
            }
            ksort($arr_order_create_after);
            if ($arr_order_create_after)
            {
                foreach ($arr_order_create_after as $obj)
                {
                    $obj->generate($order_data);
                }
            }
            /** end **/
        }
        else
        {
            $db->rollback();
        }

        if ($result)
        {
            $order_num = $order->count(array('member_id' => $order_data['member_id']));
            $obj_mem = $this->app->model('members');
            $obj_mem->update(array('order_num'=>$order_num), array('member_id'=>$order_data['member_id']));

			/** 订单金额为0 **/
			if ($order_data['cur_amount'] == '0')
			{
				// 模拟支付流程
				$objPay = kernel::single("ectools_pay");
				$sdf = array(
					'payment_id' => $objPay->get_payment_id($order_data['order_id']),
					'order_id' => $order_data['order_id'],
					'rel_id' => $order_data['order_id'],
					'op_id' => $order_data['member_id'],
					'pay_app_id' => $order_data['payinfo']['pay_app_id'],
					'currency' => $order_data['currency'],
					'payinfo' => array(
						'cost_payment' => $order_data['payinfo']['cost_payment'],
					),
					'pay_object' => 'order',
					'member_id' => $order_data['member_id'],
					'op_name' => $this->user->user_data['account']['login_name'],
					'status' => 'ready',
					'cur_money' => $order_data['cur_amount'],
					'money' => $order_data['total_amount'],
				);
				$is_payed = $objPay->gopay($sdf, $msg);
				if (!$is_payed){
					$msg = app::get('b2c')->_('订单自动支付失败！');
					$this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'checkout')));
				}

				$obj_pay_lists = kernel::servicelist("order.pay_finish");
				$is_payed = false;
				foreach ($obj_pay_lists as $order_pay_service_object)
				{
					$is_payed = $order_pay_service_object->order_pay_finish($sdf, 'succ', 'font',$msg);
				}
                $obj_coupon = kernel::single("b2c_coupon_order");
                if( $obj_coupon ){
                    $obj_coupon->use_c($order_id);
                }
			}
            if($_COOKIE['penker'] == 'true'){
                kernel::single('penker_service_order')->create($order_data);
            }
          
            if($_POST['order_type']=='bail'){
                $this->apiReturn(['status'=>'1','order_id'=>$order_id,'succ'=>'保证金定单创建成功']);
              }else{
                $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'index','arg0'=>$order_id,'arg1'=>'true')),'',true); 
             }
             
        }
        else
            
            if($_POST['order_type']=='bail'){
                  $this->apiReturn(['status'=>'0','failed'=>"保证金订单创建失败"]);
                }else{
                   $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'wap_cart','act'=>'checkout')),true,true); 
            }
          
           
    }

    function select_payment(){
        if($_POST['payment']['currency']){
            $sdf['cur'] = $_POST['payment']['currency'];
        }
        //预售排除线下和货到付款
        if(!empty($_POST['no_offline'])&&$_POST['no_offline']=='prepare')
        {
            $sdf['no_offline']='true';
            $this->pagedata['has_cod'] = 'false';
        }
        if($_POST['shipping']['shipping_id']){
            $has_cod = app::get('b2c')->model('dlytype')->getList('has_cod',array('dt_id'=>$_POST['shipping']['shipping_id']));
            #$this->pagedata['has_cod'] = $has_cod[0]['has_cod'] =='true' ? 'true' : 'false';
        }
        $obj_payment_select = new ectools_payment_select();
        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
        $this->pagedata['current_currency'] = $sdf['cur'] ? $sdf['cur'] : '';
        $this->pagedata['app_id'] = 'b2c';//$app_id;
        $this->pagedata['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
        $this->pagedata['payment_html'] = $obj_payment_select->select_pay_method($this, $sdf, false,false,array('iscommon','iswap'),'wap/cart/checkout/select_currency.html');
        echo $this->fetch('wap/order/select_payment.html');
        exit;
    }

    function payment_change(){
        $objOrders = $this->app->model('orders');
        $objPay = kernel::single('ectools_pay');
        $objMath = kernel::single('ectools_math');
        $payments = $_POST['payment'];
        $order_id = $_POST['order_id'];
        $currency = $payments['currency'];
        $pay = json_decode($payments['pay_app_id'],true);
        if($pay['pay_app_id'] == -1){
            return true;
        }
        $arrOrders = $objOrders->dump($order_id,'*');
        if ($arrOrders['payinfo']['pay_app_id'] != $pay['pay_app_id'] || $arrOrders['currency'] != $currency)
        {
            $class_name = "";
            $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
            foreach ($obj_app_plugins as $obj_app)
            {
                $app_class_name = get_class($obj_app);
                $arr_class_name = explode('_', $app_class_name);
                if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
                {
                    if ($arr_class_name[count($arr_class_name)-1] == $pay['pay_app_id'])
                    {
                        $pay_app_ins = $obj_app;
                        $class_name = $app_class_name;
                    }
                }
                else
                {
                    if ($app_class_name == $pay['pay_app_id'])
                    {
                        $pay_app_ins = $obj_app;
                        $class_name = $app_class_name;
                    }
                }
            }
            $strPayment = app::get('ectools')->getConf($class_name);
            $arrPayment = unserialize($strPayment);
            $getcur = app::get('ectools')->model('currency')->getcur($currency);
            $arrOrders['currency'] = $getcur['cur_code'];
            $arrOrders['cur_rate'] = $getcur['cur_rate'];
        }

        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])),$arrOrders['payed'])), $arrPayment['setting']['pay_fee']));
        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

        // 更新订单支付信息
        $arr_updates = array(
            'order_id' => $order_id,
            'payinfo' => array(
                            'pay_app_id' => $pay['pay_app_id'],
                            'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                        ),
            'currency' => $arrOrders['currency'],
            'cur_rate' => $arrOrders['cur_rate'],
            'total_amount' => $total_amount,
            'cur_amount' => $cur_money,
        );
        $objOrders->save($arr_updates);
    }

    //删除订单
    public function dodelete(){
        $order_id = trim($_GET['order_id']);
        $arrMember = kernel::single('b2c_user_object')->get_current_member(); //member_id,uname
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order = $mdl_order->getRow('*', array('order_id'=>$order_id));

        if($sdf_order['member_id'] != $arrMember['member_id'])
        {
            $db->rollback();
            $this->apiReturn(['error'=>1,'message'=>'请勿删除别人的订单']);
        }

        if($sdf_order['status'] != 'dead')
        {
            // $db->rollback();
            // $this->apiReturn(['error'=>1,'message'=>'请勿删除未取消的订单']);
        }

        $sdf_order['displayonsite'] = 'false';
        if($mdl_order->save($sdf_order))
        {
            $db->commit($transaction_status);
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'orders'));
            $this->apiReturn(['error'=>0,'message'=>'删除成功','data'=>$url]);

        }else{
            $db->rollback();
            $this->apiReturn(['error'=>1,'message'=>'删除失败']);
        }
    }


    //扫码订单

    public function scan($order_id){
        $this->pagedata['title']='扫码结果';
        // $order_id='181113115104804';
       //根据订单号判断是否是货到付款
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order = $mdl_order->getRow('*', array('order_id'=>$order_id));
          //订单状态
                           $tmpArr1 = array(
                            0 => app::get('b2c')->_('未付款'),
                            1 => app::get('b2c')->_('已付款'),
                            2 => app::get('b2c')->_('付款至担保方'),
                            3 => app::get('b2c')->_('部分付款'),
                            4 => app::get('b2c')->_('部分退款'),
                            5 => app::get('b2c')->_('已退款'),
                            );
         //发货状态
                           $tmpArr2 = array(
                            0 => app::get('b2c')->_('未发货'),
                            1 => app::get('b2c')->_('已发货'),
                            2 => app::get('b2c')->_('部分发货'),
                            3 => app::get('b2c')->_('部分退货'),
                            4 => app::get('b2c')->_('已退货'),
                            ); 
                             
          //收货转态 
           $tmpArr3 = array(
                            0 => app::get('b2c')->_('未收货'),
                            1 => app::get('b2c')->_('已收货'),
                            );                                            
        if($sdf_order['payment_offline']=='-1'){
        //货到付款
          if($sdf_order){
                    $sdf_order['pay_status']= $tmpArr1[$sdf_order['pay_status']];
                    $sdf_order['ship_status']= $tmpArr2[$sdf_order['ship_status']]; 
                    $sdf_order['received_status']= $tmpArr3[$sdf_order['received_status']];
                    $sdf_order['payment']="货到付款";  # code...

              //原来订单支付逻辑
            // $order_id='181204172829584';
            $objOrder = $this->app->model('orders');
        $sdf = $objOrder->dump($order_id);
        //当支付方式为微信支付js接口时，获取openid
        if( $sdf['payinfo']['pay_app_id'] == 'wxpayjsapi' )
        {

            $return_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'index','args'=>array($order_id),'full'=>1));
            if(!kernel::single('weixin_openid')->check($return_url, $msg))
                  $this->splash('failed', 'back',  $msg);

         $wxpayjsapi_conf = app::get('ectools')->getConf('weixin_payment_plugin_wxpayjsapi');
         $wxpayjsapi_conf = unserialize($wxpayjsapi_conf);
         if(!$_GET['code'])
         {
             $return_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'index','args'=>array($order_id),'full'=>1));
             $appId_to_get_code = trim($wxpayjsapi_conf['setting']['appId']);
             kernel::single('weixin_wechat')->get_code($appId_to_get_code, $return_url);
         }else{
             $code = $_GET['code'];
             $openid = kernel::single('weixin_wechat')->get_openid_by_code($wxpayjsapi_conf['setting']['appId'], $wxpayjsapi_conf['setting']['Appsecret'], $code);
             if($openid == null)
                 $this->splash('failed', 'back',  app::get('b2c')->_('获取openid失败'));
         }
        }
        //获取openid结束

        if(!$sdf){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }

        if($sdf['pay_status'] == '1' || $sdf['pay_status'] == '2'){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'result_pay','arg0'=>$order_id,'arg1'=>'true'));
            header('Location:'.$url);
            exit;
        }

        $member_money = app::get('b2c')->model('members')->getList('advance',array('member_id'=>$sdf['member_id']));
        $this->pagedata['deposit_money'] = $member_money[0]['advance'] ? $member_money[0]['advance'] : 0;

        // // 校验订单的会员有效性.
        // $is_verified = ($this->_check_verify_member($sdf['member_id'])) ? $this->_check_verify_member($sdf['member_id']) : false;

        // // 校验订单的有效性.
        // if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified){
        //     $this->begin();
        //     $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'wap','ctl'=>'default','act'=>'index')));
        // }

        $sdf['cur_money'] = $this->objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));

        $this->pagedata['order'] = $sdf;
        if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit'){
            $deposit = app::get('ectools')->model('payment_cfgs')->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
            $this->pagedata['order']['deposit_status'] = empty($deposit['pay_status'])?0:$deposit['pay_status'];
            $MemberData = app::get('b2c')->model('members')->getRow('*',array('member_id'=>$this->pagedata['order']['member_id']));
            $this->pagedata['order']['pay_password'] = $MemberData['pay_password'];
        }else{
            $this->pagedata['order']['deposit_status'] = 0;
        }
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$order_id));
        $this->pagedata['order']['quantity'] = $order_quantity[0]['nums'];

        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency'],array('iscommon','iswap'));
        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        $pay_online = false;
        foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
        {

            //判断是否有在线支付方式
            if(!$pay_online && $arrPayments['app_id'] != 'deposit' && $arrPayments['app_pay_type'] == 'true'){
                $pay_online = true;
            }

            if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
            {
                $this->pagedata['order']['payinfo']['pay_name'] = $arrPayments['app_display_name'];
                $this->pagedata['order']['payinfo']['pay_des'] = $arrPayments['app_des'];
                $this->pagedata['order']['payinfo']['platform'] = $arrPayments['app_platform'];
                $arrPayments['cur_money'] = $this->objMath->formatNumber($this->pagedata['order']['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
            }else{
                $arrPayments['cur_money'] = $this->pagedata['order']['cur_money'];
                if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit'){
                    $temp_cur_money = $this->objMath->number_minus(array($arrPayments['cur_money'],$this->pagedata['deposit_money']));
                    $arrPayments['cur_money'] = $temp_cur_money ? $temp_cur_money : 0;
                }
                $cur_discount = $this->objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));

                if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
                {
                    if ($this->pagedata['order']['cur_money'] > 0)
                        $cost_payments_rate = $this->objMath->number_div(array($arrPayments['cur_money'], $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']))));
                    else
                        $cost_payments_rate = 0;

                    $cost_payment = $this->objMath->number_multiple(array($this->objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }
                else
                {
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $cost_payment = $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }

                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['cur_money'] = $this->objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit'){
                    $arrPayments['cur_money'] = $this->objMath->number_div(array($arrPayments['cur_money'],$this->pagedata['order']['cur_rate']));
                    $payed = $this->objMath->number_div(array($this->pagedata['order']['payed'],$this->pagedata['order']['cur_rate']));
                    $arrPayments['total_amount'] = $this->objMath->number_plus(array($arrPayments['cur_money'],$payed,$this->pagedata['deposit_money']));
                }
            }
       }

        //将订单金额转换为基准货币值
        $this->pagedata['order']['cur_money'] = $this->objMath->number_div(array($this->pagedata['order']['cur_money'],$this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['payed'] = $this->objMath->number_div(array($this->pagedata['order']['payed'],$this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['total_amount'] = $this->objMath->number_plus(array($this->pagedata['order']['cur_money'],$this->pagedata['order']['payed']));
        //end

        // if ($this->pagedata['order']['payinfo']['pay_app_id'] == '-1'){
        //     $this->pagedata['order']['payinfo']['pay_name'] = app::get('b2c')->_('货到付款');
        // }

        $this->pagedata['combination_pay'] =  'false';
        if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit'){
            $this->pagedata['combination_pay'] = $pay_online ? app::get('b2c')->getConf('site.combination.pay') : 'false';
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];

        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'result_pay'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'dopayment','arg0'=>'order'));
        $this->pagedata['form_check'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'pay_password'));
        if(isset($openid) && $openid != null )
        {
            $this->pagedata['form_action'] = $this->pagedata['form_action'].'?openid='.$openid;
        }
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html)
        {
            foreach ($obj_order_payment_html as $obj)
            {
                $obj->gen_data($this, $app_id);
            }
        }
        $this->pagedata['newOrder'] = $newOrder;

        //中金快捷支付获取用户银行卡
        if($this->pagedata['order']['payinfo']['pay_app_id']=='cpcnquick'){
            $cards = app::get('b2c')->model('member_bankcard')->getList('*',array('member_id'=>$this->pagedata['order']['member_id'],'effective'=>'1'));
            foreach ($cards as $k=>$v){
                $h = substr($v['AccountNumber'],0,4);
                $f = substr($v['AccountNumber'],-4);
                $v['AccountNumber'] = $h.'***********'.$f;
                $cards[$k]=$v;
            }
            $this->pagedata['order']['payinfo']['bankcards'] = $cards;
        }


         //预售信息
        $preparesell_is_actived = app::get('preparesell')->getConf('app_is_actived');
        if($preparesell_is_actived == 'true'){
            $orderdetail=app::get('b2c')->model('order_items');
            $product_id=$orderdetail->getRow('product_id',array('order_id'=>$order_id));
            $preparesell=app::get('preparesell')->model('preparesell_goods');
            if(is_object($preparesell))
            {
                $prepare=$preparesell->getRow('*',array('product_id'=>$product_id));
                if($this->pagedata['order']['promotion_type']=='prepare')
                {   $prepare['nowtime']=time();
                $this->pagedata['prepare']=$prepare;
                }
            }
        }

        $this->pagedata['promotion_type'] = $this->pagedata['order']['promotion_type'];
        $this->set_tmpl('order_index');

        //判断是否开启当前的支付方式
        $this->pagedata['payment_app_status'] = false;
        $payment_app_id = $this->pagedata['order']['payinfo']['pay_app_id'];
        if($payment_app_id == -1)
            $this->pagedata['payment_app_status'] = false;
        else
            foreach($this->pagedata['payments'] as $payment)
            {
                if( $payment_app_id == $payment['app_id'] )
                    $this->pagedata['payment_app_status'] = true;


            }
      
                // $a=$this->pagedata['payments'];


                // foreach ($a as $key => $value) {
                //   //   if( $value['app_id']!='malipay'){
                //   // unset($a[$key]);

                //   //   }

                //     $type = array("malipay", "wxpay");
                //      if((in_array( $value['app_id'], $type,true)==false)){
                //         unset($a[$key]);

                //     }
                //     # code...
                // }
                //        $this->pagedata['payments']=$a;
             if($payment_app_id == -1){
               $payinfo= Array
                (
                    pay_app_id =>'malipay',
                    cost_payment =>$cost_payment,
                    pay_name =>'手机支付宝',
                    pay_des =>'',
                    platform =>'iswap'
                );
              
                 
                $this->pagedata['order']['payinfo'] = $payinfo; 
             }
                
                  // ee($this->pagedata);
                 $this->pagedata['payment_app_status'] = 1;
                $this->pagedata['order_info'] = $sdf_order;
                $this->page('wap/order/scanpay.html');
            }
         
        }else{ 

               $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap','act'=>'index'));
             if($sdf_order['pay_status']!=1){
                $msg="此订单不是完成支付订单，无法收货确认";

                $this->splash('failed', $url,  $msg);exit();
              }

              if($sdf_order['received_status']==1){
                $msg="此订单已完成收货确认，不可再次确认";
                $this->splash('failed',  $url,  $msg);exit();
              }

        //非货到付款
        $sql = "select o.order_id,o.total_amount,p.money,p.pay_app_id,o.pay_status,o.ship_status,
               o.received_status,
               o.ship_mobile from
             sdb_b2c_orders as o,sdb_ectools_order_bills as b,sdb_ectools_payments as p
             WHERE   b.rel_id = o.order_id and p.payment_id = b.bill_id 
             and o.order_id=".$order_id;
        $result = $mdl_order->db->select($sql);
            if($result){
                foreach ($result as $k => $v) {
                    $result[$k]['pay_status']= $tmpArr1[$v['pay_status']];
                    $result[$k]['ship_status']= $tmpArr2[$v['ship_status']]; 
                    $result[$k]['received_status']= $tmpArr3[$v['received_status']];
                     //支付方式
                    $opayment = app::get('ectools')->model('payments');
                    $payment = $opayment->dump(array('pay_app_id'=>$v['pay_app_id']), 'pay_name', $subsdf=null);  
                    $result[$k]['payment']= $payment['pay_name'];  # code...
                }
               
                $this->pagedata['order_info'] = $result;

                $this->page('wap/order/scan.html');

            }
        }
   

    }

       //短信发送验证码
    public function send_vcode_sms($ship_mobile){
        $mobile = $_POST['uname'];
        $type = $_POST['type']; //激活activation
        if(!preg_match("/^1[34578]{1}[0-9]{9}$/",$mobile) ){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,'','',true);
        }
       if($ship_mobile!=$mobile){

           $msg = app::get('b2c')->_('该手机号与本订单的会员手机号不符合');
            $this->splash('failed',null,$msg,'','',true);
       }
        $userVcode = kernel::single('b2c_user_vcode');
        if($mobile){
            $vcode = $userVcode->set_vcode($mobile,$type,$msg);
        }
        if($vcode){
            //发送验证码 发送短信
            //logger::info('vcode:'.$vcode);
            $data['vcode'] = $vcode;
            if($type == 'signup')
            {
                $type = 'signup-mobile';
            }
            if( !$userVcode->send_sms($type,(string)$mobile,$data) ){
                $msg = app::get('b2c')->_('发送失败');
                $this->splash('failed',null,$msg,'','',true);
            }
        }else{
            $this->splash('failed',null,$msg,'','',true);
        }
    }
    //收货确认
     public function confirm_receipt($order_id){
    
    
       $order_id=$_POST['order_id'];
        $url = $_SERVER['HTTP_HOST'];

        if(!preg_match("/^1[34578]{1}[0-9]{9}$/",$_POST['login_mobile']) ){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,'','',true);
        }
         

       if($_POST['login_mobile']){
           $_POST['send_type']=$_POST['login_mobile'];
        }
        if($_POST['login_verification']){
           $_POST['mobilevcode']=$_POST['login_verification'];   
        }
    
       


       $userVcode = kernel::single('b2c_user_vcode');
        if( !$vcodeData = $userVcode->verify($_POST['mobilevcode'],$_POST['send_type'],'activation')){
            $msg = app::get('b2c')->_('验证码错误');
            $this->splash('failed',null,$msg,'','',true);exit;
        }
        $_POST['key'] = $userVcode->get_vcode_key($_POST['send_type'],'activation');
        $_POST['key'] = md5($vcodeData['vcode'].$_POST['key']);
        $_POST['account'] = $_POST['login_mobile'];
        $userVcode = kernel::single('b2c_user_vcode');
        $vcodeData = $userVcode->get_vcode($_POST['account'],'activation');
        $key = $userVcode->get_vcode_key($_POST['account'],'activation');
        if($_POST['account'] !=$vcodeData['account']  || $_POST['key'] != md5($vcodeData['vcode'].$key) ){
            $msg = app::get('b2c')->_('页面已过期');
            $this->splash('failed',null,$msg,'','',true);exit;
        }
     
            //修改收货状态
         $mdl_order = app::get('b2c')->model('orders');
            if($order_id){
                $received_status="'1'";
                $pay_status="'1'";
                $ship_status="'1'";
                $ishave=$mdl_order->getRow('order_id',array('order_id'=>$order_id,'pay_status'=>1,'ship_status'=>1));
              if($ishave){
                $result =$mdl_order->db->exec("UPDATE sdb_b2c_orders set received_status=".$received_status." where order_id =".$order_id." and pay_status=".$pay_status." and ship_status=".$ship_status.""); 
                  $this->apiReturn(['status'=>1,'url'=>$url]);
              }else{
                     $msg = app::get('b2c')->_('此订单必须已经支付，且已发货状态才能订单确认');
                     $this->splash('failed',null,$msg,'','',true);;
              }
                
        }
          
    }



    public function scan_confirm($order_id){
       //根据订单号判断是否是货到付款
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order = $mdl_order->getRow('payment', array('order_id'=>$order_id));
          //订单状态
                           $tmpArr1 = array(
                            0 => app::get('b2c')->_('未付款'),
                            1 => app::get('b2c')->_('已付款'),
                            2 => app::get('b2c')->_('付款至担保方'),
                            3 => app::get('b2c')->_('部分付款'),
                            4 => app::get('b2c')->_('部分退款'),
                            5 => app::get('b2c')->_('已退款'),
                            );
         //发货状态
                           $tmpArr2 = array(
                            0 => app::get('b2c')->_('未发货'),
                            1 => app::get('b2c')->_('已发货'),
                            2 => app::get('b2c')->_('部分发货'),
                            3 => app::get('b2c')->_('部分退货'),
                            4 => app::get('b2c')->_('已退货'),
                            );  
          //收货转态 
           $tmpArr3 = array(
                            0 => app::get('b2c')->_('未收货'),
                            1 => app::get('b2c')->_('已收货'),
                            );                                   
        if($sdf_order['payment']=='-1'){
        $this->page('wap/order/scanpay.html');
        }else{ 
        $sql = "select o.order_id,o.total_amount,p.money,p.pay_app_id,o.pay_status,o.ship_status,
              o.received_status,   
              o.ship_mobile from
             sdb_b2c_orders as o,sdb_ectools_order_bills as b,sdb_ectools_payments as p
             WHERE   b.rel_id = o.order_id and p.payment_id = b.bill_id 
             and o.order_id=".$order_id;
        $result = $mdl_order->db->select($sql);
            if($result){
                foreach ($result as $k => $v) {
                    $result[$k]['pay_status']= $tmpArr1[$v['pay_status']];
                    $result[$k]['ship_status']= $tmpArr2[$v['ship_status']]; 
                    $result[$k]['received_status']= $tmpArr3[$v['received_status']];
                     //支付方式
                    $opayment = app::get('ectools')->model('payments');
                    $payment = $opayment->dump(array('pay_app_id'=>$v['pay_app_id']), 'pay_name', $subsdf=null);  
                    $result[$k]['payment']= $payment['pay_name'];  # code...
                }
               
                $this->pagedata['order_info'] = $result;
                $this->page('wap/order/scan_confirm.html');

            }
        }
   


    }

    function select_paymentwap(){
        if($_POST['payment']['currency']){
            $sdf['cur'] = $_POST['payment']['currency'];
        }
        //预售排除线下和货到付款
        if(!empty($_POST['no_offline'])&&$_POST['no_offline']=='prepare')
        {
            $sdf['no_offline']='true';
            $this->pagedata['has_cod'] = 'false';
        }
        if($_POST['shipping']['shipping_id']){
            $has_cod = app::get('b2c')->model('dlytype')->getList('has_cod',array('dt_id'=>$_POST['shipping']['shipping_id']));
            #$this->pagedata['has_cod'] = $has_cod[0]['has_cod'] =='true' ? 'true' : 'false';
        }
        $obj_payment_select = new ectools_payment_select();
        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
        $this->pagedata['current_currency'] = $sdf['cur'] ? $sdf['cur'] : '';
        $this->pagedata['app_id'] = 'b2c';//$app_id;
        $this->pagedata['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
       
        $this->pagedata['payment_html'] = $obj_payment_select->select_pay_method($this, $sdf, false,false,array('iscommon','iswap'),'wap/cart/checkout/select_currency.html');
       
        echo $this->fetch('wap/order/select_paymentwap.html');
        exit;
    }
   


    //生成保证金订单
    public function baid_order(){
      
        $a=$_POST;
        //模拟参数
        $_POST=Array
            (
                isfastbuy => 'true',
                purchase => Array
                    (
                        addr_id =>'37',
                    ),

                md5_cart_info =>kernel::single("b2c_cart_objects")->md5_cart_objects($isfastbuy),
                extends_args =>'{"get":"Array","post":"Array"}',
                address=> '{"addr_id":37,"area":23}',
                shipping=>'{"id":3,"has_cod":"true","dt_name":"宇配网配送","money":"8"}',
                payment => Array
                    (
                        pay_app_id =>'{"pay_app_id":"malipay","payment_name":"手机支付宝"}',
                        currency => 'CNY',
                        tax_type => 'false',
                    ),

            );


   
        $_POST['payment']['pay_app_id']=$a['payment'];
        $_POST['order_type']=$a['order_type'];
        $_POST['order_money']=$a['order_money'];

        $this->create();
     
    }
}

