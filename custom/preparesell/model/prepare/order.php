<?php
/**
 * #宇配网预售#
 * 预售订单
 * @auther LI
 */
class preparesell_mdl_prepare_order extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

    //保存预售订单款项
    function saveOrderFund($prepare_id=0,$order_id=0,$product_id=0){
        $wheres = [];
        if (empty($prepare_id)) {
            return false;
        }
        if (empty($order_id)) {
            return false;
        }
        $fundModel = app::get('preparesell')->model('preparesell_fund');
        $fundRows=$fundModel->getList('*',array('prepare_id'=>$prepare_id),0,-1,'begin_time asc');
        $fundModel->translateToPrice($fundRows,$prepare_id,$product_id);
        $wheres['prepare_id'] = $prepare_id;
        //之前添加的先删除
        if (empty($fundRows)) {
            return false;
        }

        //保存配送范围
        $ctime = time();
        $sqlArr = [];
        foreach ($fundRows as $row) {
            $order_id             = $order_id;
            $prepare_id           = $prepare_id;
            $fund_name            = $row['fund_name'];
            $payment              = $row['payment'];
            $begin_time           = $row['begin_time'];
            $end_time             = $row['end_time'];
            $time_out             = $row['time_out'];
            $status               = $row['status'];
            $fund_id              = $row['fund_id'];
            $ctime                = $ctime;
            $remind_time_send     = (int)$row['remind_time_send'];
            $remind_time_send_end = (int)$row['remind_time_send_end'];
            $sqlArr[] = "('{$order_id}','{$prepare_id}','{$fund_id}','{$fund_name}','{$payment}','{$begin_time}','{$end_time}','{$status}','{$time_out}',{$ctime},'{$remind_time_send}','{$remind_time_send_end}')";
        }

        //批量插入
        if (!empty($sqlArr)) {
            $sqlStr = implode(',', $sqlArr);
            $sql = "INSERT INTO `sdb_preparesell_order_fund` ( `order_id`,`prepare_id`,`fund_id`,`fund_name`,`payment` ,`begin_time` ,`end_time`  ,`status` ,`time_out` ,`ctime`,`remind_time_send`,`remind_time_send_end` ) VALUES " . $sqlStr;
            $this->db->exec($sql);
        }
        $insert_id = $this->db->lastinsertid();
        return $insert_id;
    }

    /**
     * 说明：预售订单款项付款状态[前端site会员中心预售使用]
     * @params int $order_id 订单id
     * @return array 返回预售订单状态数组信息，其中key由按钮使用，msg显示当前订单状态,order_fund_id款项id(待支付该款项)
     */
    function fundStatus($order_id=0){
        $data = [
            '0'      =>['key'=>'','msg'=>'','order_fund_id'=>''],
            '1'      =>['key'=>'','msg'=>'该预售订单没有款项信息','order_fund_id'=>''],
            'wait_y' =>['key'=>'wait_y','msg'=>'等待付款[预付款]','order_fund_id'=>''],
            'paid_y' =>['key'=>'paid_y','msg'=>'已付款[预付款]','order_fund_id'=>''],
            'wait_z' =>['key'=>'wait_z','msg'=>'等待付款[中期进度款]','order_fund_id'=>''],
            'paid_z' =>['key'=>'paid_z','msg'=>'已付款[中期进度款]','order_fund_id'=>''],
            'wait_w' =>['key'=>'wait_w','msg'=>'等待付款[尾款]','order_fund_id'=>''],
            'paid_w' =>['key'=>'paid_w','msg'=>'已付款[正在备货]','order_fund_id'=>'']
        ];
        if (empty($order_id)) {
            return $data['0'];
        }

        $prepareOrderModel = $this->app->model('prepare_order');
        $orderFundModel = $this->app->model('order_fund');//订单款项表

        //订单款项列表
        $tmpOrderFundRows = $orderFundModel->getList('*',['order_id'=>$order_id]);

        if (empty($tmpOrderFundRows)) {
            return $data['1'];
        }
        $orderFundRows = [];

        //处理格式[fund_name . fund_id=>$row]
        foreach ($tmpOrderFundRows as $o) {
           $orderFundRows[$o['fund_name'] . $o['fund_id']] = $o;

           //设置预售首款ID
           if ($o['fund_name'] == 'y') {
               $data['wait_y']['order_fund_id'] = $o['order_fund_id'];
           }
        }

        //支付单款项列表
        $paymentsRows = [];
        $obj_order_bills = app::get('ectools')->model('order_bills');
        $order_bills=$obj_order_bills->getList('*',['rel_id'=>$order_id,'bill_type'=>'payments','pay_object'=>'order']);
        if(!empty($order_bills)){
            $bills_id = [];
            $obj_payments = app::get('ectools')->model('payments');
            foreach($order_bills as $val){
                $bills_id[]=$val['bill_id'];
            }
            $tmpPaymentsRows = $obj_payments->getList('*',['payment_id'=>$bills_id,'status'=>'succ']);
        }

        //若还未付款[首款、中期、尾款都未付],直接提示支付预付款，此操作默认预付款必须是开启的
        if (empty($tmpPaymentsRows)) {
            return $data['wait_y'];
        }

        //处理格式[fund_name . fund_id=>$row]
        foreach ($tmpPaymentsRows as $p) {
           $paymentsRows[$p['fund_type'] . $p['fund_id']] = $p;
        }

        /*筛查各个款项支付情况[只看未支付情况,先收集，后集中判断]*/
        $topPay = $midPay = 0;//支付状态,特殊设置，防止没有中期情况
        $topFund = $midFund = 'false';//款项开启状态
        //检查首款支付情况
        foreach ($orderFundRows as $key => $row) {
            if ($row['fund_name'] == 'y') {
                $topFund = $row['status'];
                if (isset($paymentsRows[$key]) && $paymentsRows[$key]['status'] == 'succ') {
                    $topPay=true;
                    // if ($order_id == '180608130112510') {
                    //     ee($key,0);
                    //     ee($orderFundRows,0);
                    //     ee($paymentsRows,0);
                    // }
                }else{
                    //设置预售首款ID
                    $topPay = false;
                    $data['wait_y']['order_fund_id'] = $row['order_fund_id'];
                    break;
                }
            }
        }

        //检查中期进度款支付情况,这里还应该检测一种情况：就是没有中期情况(没有中期直接跳到尾款情况)
        foreach ($orderFundRows as $key => $row) {
            if ($row['fund_name'] == 'z') {
                $midFund = $row['status'];
                if (isset($paymentsRows[$key]) && $paymentsRows[$key]['status'] == 'succ') {
                    $midPay=true;
                }else{
                    $midPay=false;//当有两个以上中期进度款时候，只要有未付款的就设置false
                    //设置预售尾款ID
                    $data['wait_z']['order_fund_id'] = $row['order_fund_id'];
                    //因为若有多个中期进度款,也可能第二个已付款、第一个未付款，为了防止这种情况,此处逻辑应该是：发现有中期进度款未付款就检测完毕。
                    break;
                }
            }
        }

        //判断输出
        if (!$topPay && !$midPay) {
            return $data['wait_y'];
        }elseif ($topPay && ($midPay !== 0 && $midFund == 'false')) {
            return $data['paid_y'];
        }elseif ($topPay && !$midPay && in_array($midFund, ['true','finish'])) {
            return $data['wait_z'];
        }


        $tailPay = false;//支付状态
        $tailFund = 'false';//款项开启状态

        //检查尾款进度款支付情况
        foreach ($orderFundRows as $key => $row) {
            if ($row['fund_name'] == 'w') {
                $tailFund = $row['status'];
                if (isset($paymentsRows[$key]) && $paymentsRows[$key]['status'] == 'succ') {
                    $tailPay=true;
                }else{
                    $tailPay = false;
                    //设置预售尾款ID
                    $data['wait_w']['order_fund_id'] = $row['order_fund_id'];
                    break;
                }
            }
        }

        //判断输出
        if ($topPay && $midPay && in_array($midFund, ['true','finish']) && $tailFund == 'false') {
            return $data['paid_z'];
        }elseif(!$tailPay && $tailFund == 'true') {
            return $data['wait_w'];
        }elseif ($tailPay && in_array($tailFund, ['true','finish'])) {
            return $data['paid_w'];
        }

        //程序默认不返回任何状态
        return $data['0'];
    }

}