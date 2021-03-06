<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 支付单创建的具体实现逻辑
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_create
{
	/**
	 * @var app object
	 */
	public $app;
	
	/**
	 * 构造方法
	 * @param object app
	 * @return null
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	/**
	 * 支付单创建
	 * @param array sdf
	 * @param string message
	 * @return boolean success or failure
	 */
	public function generate(&$sdf, &$msg='')
	{
		// 创建订单是和中心的交互
		$is_payed = false;		            			
	  
		// 获得的支持变量的信息
		$objMath = kernel::single('ectools_math');		
		$payment_cfgs = $this->app->model('payment_cfgs');
		$arrPyMethod = $payment_cfgs->getPaymentInfo($sdf['pay_app_id']);            
		
		$class_name = "";
		$obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
		foreach ($obj_app_plugins as $obj_app)
		{
			$app_class_name = get_class($obj_app);
			$arr_class_name = explode('_', $app_class_name);
			if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
			{
				if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
			else
			{
				if ($app_class_name == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
		}
		$strPaymnet = $this->app->getConf($class_name);
		$arrPayment = unserialize($strPaymnet);
		$objCur = $this->app->model('currency');
		$aCur = $objCur->getDefault();
		$objModelPay = $this->app->model('payments');
		
		$account = $sdf['shopName'] ? $sdf['shopName'] : $sdf['account'];
		$account = $account ? $account : $arrPyMethod['app_display_name'];
		$bank = ($arrPyMethod['app_key'] == 'deposit' || $arrPyMethod['app_key'] == 'offline') ? $this->app->getConf("system.shopname") : $arrPyMethod['app_display_name'];
		$bank = $sdf['bank'] ? $sdf['bank'] : $bank;
		
		if ($sdf['pay_object'] == 'order')
		{
			$currency = $sdf['currency'] ? $sdf['currency'] : $aCur['cur_code'];
			$money = $sdf['money'];
			$pay_fee = $arrPayment['setting']['pay_fee'];//支付费率        
			$paycost = $sdf['payinfo']['cost_payment'];
			//$cur_money = $objCur->get_cur_money($sdf['money'], $currency);
			$cur_money = $sdf['cur_money'];
		}
		else
		{
			$currency = $aCur['cur_code'];
			$money = $sdf['money'];
			$paycost = 0;
			$cur_money = $money;
		}         
		
		$pay_type = ($arrPyMethod['app_pay_type'] == 'true') ? 'online' : 'offline';
		$pay_type = $sdf['pay_type'] ? $sdf['pay_type'] : $pay_type;	
		
		$time = time();
		
          
          $pay_account = $sdf['pay_account'];
          if( !$pay_account )
            {
              if( $sdf['member_id'] ){
                $login_name = kernel::single('b2c_user_object')->get_member_name(null,$sdf['member_id']); 
                if($login_name){
                  $pay_account =  $login_name;
                }
              }
              else {
                $pay_account = app::get('ectools')->_('非会员顾客');
              }
            }
		$paymentArr = array(
			'payment_id' => $sdf['payment_id'],
			'account' => $account ? $account : $bank,
			'member_id' => ($sdf['member_id']) ? $sdf['member_id'] : '0',
			'bank' => $bank,
			'pay_account' => $pay_account,
			'currency' => $currency,
			'money' => $money,
			'paycost' => $paycost,
			'cur_money' => $cur_money,
			'pay_type' => $pay_type,
			'pay_app_id' => $sdf['pay_app_id'],
			'pay_name' => $arrPyMethod['app_display_name'],
			'pay_ver' => $arrPyMethod['app_version'],
			'op_id' => ($sdf['op_id']) ? $sdf['op_id'] : ($sdf['member_id'] ? $sdf['member_id'] : '0'),
			'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_HOST'],
			't_begin' => $time,
			't_payed' => $time,
			't_confirm' => $time,
			'status' => $sdf['status'],
			'trade_no' => '',
			'memo' => (!$sdf['memo']) ? '' : $sdf['memo'],
			'return_url' => $sdf['return_url'],
			'orders' => array(
				array(
					'rel_id' => $sdf['rel_id'],
					'bill_type' => 'payments',
					'pay_object' => $sdf['pay_object'],
					'bill_id' => $sdf['payment_id'],
					'money' => $money,
				)
			)
		);

		//#TODO##宇配网预售#
		if (isset($sdf['fund_type'])) {
			$paymentArr['fund_type'] = $sdf['fund_type'];
			if($sdf['fund_type']=='y'){
             $a=$paymentArr['orders'];
			//预售加运费
             $order = app::get('b2c')->model('orders');
             $order_status['order_id'] = $a[0]['rel_id'];
             $order_status['promotion_type'] ='prepare';
             $order_status['status'] = 'active';
             $aData = $order->getRow('cost_freight',$order_status);
             $paymentArr['money'] =$aData['cost_freight']+$paymentArr['money'];
             $paymentArr['cur_money'] =$aData['cost_freight']+$paymentArr['cur_money'];
			}
			
		}
	
		if(isset($sdf['bank_name'])){
            $paymentArr['bank_name'] = $sdf['bank_name'];
		}
		if(isset($sdf['bank_no'])){
            $paymentArr['bank_no'] = $sdf['bank_no'];
		}
		//#TODO#
		if (isset($sdf['fund_id'])) {
			$paymentArr['fund_id'] = $sdf['fund_id'];
		}
		
		$sdf = $paymentArr;
		
		$is_save = $objModelPay->save($paymentArr,null,true);
		
		if ($is_save)
		{
			return true;
		}
		else
		{
			$msg = app::get('ectools')->_('支付单生成失败！');
			return false;
		}
	}
}
