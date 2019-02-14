<?php
/**
 * Created by PhpStorm.
 * User: songjiang
 * Date: 2018/1/16
 * Time: 15:57
 */
class b2c_refund_refund {
    public function refundPay($params)
    {
        $objMdlPayments = app::get('ectools')->model('payments');
        $objMdlbills = app::get('ectools')->model('order_bills');
        $paymoney = $objMdlbills->getList('money,bill_id',array('rel_id'=>$params['order_id']));     
        $paymentData = $objMdlPayments->getRow('payment_id,status,money,pay_app_id',array('payment_id'=>$params['payment_id']));
        //logger::info('$paymentData:'.json_encode($paymentData));
        $refundarr = array();
        foreach ($paymoney as $v ){
            $refundarr[$v['bill_id']]=$v['money'];
        }

     
        if(!$paymentData || $paymentData['status'] != 'succ')
        {
            throw new Exception('请检查订单对应的支付单号是否存在且已支付成功，否则不能退款');
        }


        if (count($refundarr)>1){
            logger::info('预售商品两次退款：'.json_encode($refundarr));
            foreach ($refundarr as $k=>$va){
                $sdf = [
                    'trade_no' => $k,
                    'refund_fee' => $va,
                    'refund_id' => $params['refund_id']++,
                    'total_fee' => $params['money'],
                    'payment_id' => $k,
                    'pay_app_id' => $paymentData['pay_app_id'],
                    'type' => 'refund', //此参数一定不能少，判断是否是退款操作
                    'pay_type' => 'online',
                ];
                logger::info('退款参数：'.json_encode($sdf));
                $result = $this->generate($sdf);

                if(!$result)
                {
                    for ($i=0;$i<3;$i++){
                        $result = $this->generate($sdf);
                        if ($result){
                            break;
                        }
                    }
                    if (!$result){
                        return false;
                        break;
                    }
                }
            }

        }else{
            $sdf = [
                'trade_no' => $paymentData['payment_id'],
                'refund_fee' => $params['money'],
                'refund_id' => $params['refund_id'],
                'total_fee' => $params['money'],
                'payment_id' => $paymentData['payment_id'],
                'pay_app_id' => $paymentData['pay_app_id'],
                'type' => 'refund', //此参数一定不能少，判断是否是退款操作
                'pay_type' => 'online',
            ];
            logger::info('普通商品退款参数：'.json_encode($sdf));
            $result = $this->generate($sdf);
            if(!$result) {
                return false;
            }
        }
        $objMdlRefunds = app::get('ectools')->model('refunds');
        switch ($result['status'])
        {
            case 'succ':
            case 'progress':
                $isUpdatedPay = $objMdlRefunds->update(['status'=>$result['status']], ['refund_id'=>$result['refund_id']]);
                break;
            case 'failed':
                $isUpdatedPay = $objMdlRefunds->update(['status'=>'failed'], ['refund_id'=>$result['refund_id']]);
                break;
        }
        return $result;
    }
    /**
     * 请求第三方支付网关
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate($sdf)
    {
        // 异常处理
        if (!isset($sdf) || !$sdf || !is_array($sdf))
        {
            throw new \Exception(app::get('ectools')->_('支付单信息不能为空！'));
        }
        if (!$sdf['type'])
        {
            throw new \Exception(app::get('ectools')->_('支付类型是付款还是退款不能为空'));
        }

        // 支付方式的处理
        $str_app = "";
        $pay_app_id = ($sdf['pay_app_id']) ? $sdf['pay_app_id'] : $sdf['pay_type'];
        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");

        foreach ($obj_app_plugins as $obj_app)
        {


            $app_class_name = get_class($obj_app);
            $arr_class_name = explode('_', $app_class_name);
            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
            {
                if ($arr_class_name[count($arr_class_name)-1] == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
            else
            {
                if ($app_class_name == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
        }
        $pay_app_ins = new $str_app();
        $is_payed = true;
        switch($sdf['pay_type'])
        {
            case "recharge":
            case "online":
                if($sdf['type']=='refund')
                {
                    if(!method_exists( $pay_app_ins, 'dorefund' ) )
                    {
                        throw new \Exception(app::get('ectools')->_('原支付方式不支持原路返回！请换线下退款方式！'));
                    }
                    logger::info("第三方退款请求信息：".var_export($sdf,1));
                    $is_payed = $pay_app_ins->dorefund($sdf);

                }
                else
                {
                    logger::info("支付请求信息：".var_export($sdf,1));
                    $is_payed = $pay_app_ins->dopay($sdf);
                }
                break;
            default:
                $is_payed = false;
                throw new \LogicException(app::get('ectools')->_('请求支付网关失败！'));
                break;
        }
        return $is_payed;
    }




















}
