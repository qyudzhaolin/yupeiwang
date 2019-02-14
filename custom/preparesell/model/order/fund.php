<?php
/**
 * #宇配网预售#
 * 预售订单款项
 * @auther LI
 */
class preparesell_mdl_order_fund extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

    //重写getList方法
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        if (!empty($datas)) {

            //预售加运费
            $order = app::get('b2c')->model('orders');
            $wheresOrder['order_id'] = $filter['order_id'];
            $aData = $order->getRow('cost_freight',$wheresOrder);

            //购买数量
            $orderItemModel = app::get('b2c')->model('order_items');
            $nums = $orderItemModel->getList('sum(nums) as nums',array('order_id'=>$filter['order_id']));
            $nums = (int)$nums[0]['nums'];


            $fundModel = app::get('preparesell')->model('preparesell_fund');
            $fundColumns = $fundModel->schema['columns'];
            foreach ($datas as $key => $o) {
                //支付结束时间(先处理)
                $datas[$key]['lefttime'] = $this->lefttime($o);

                $datas[$key]['begin_time_format']=date('Y-m-d H:i',$o['begin_time']);
                $datas[$key]['end_time_format']=date('Y-m-d H:i',$o['end_time']);
                $datas[$key]['end_time_format_left']=date('Y-m-d H:i:s',$o['end_time']);

                //款项名称
                if (isset($fundColumns['fund_name']['type'][$o['fund_name']])) {
                    $datas[$key]['fund_name_format'] = $fundColumns['fund_name']['type'][$o['fund_name']];
                }

                if ($nums) {
                    $datas[$key]['payment'] = round($o['payment'] * $nums, 2);
                    if ($o['fund_name']=='y') {
                        $datas[$key]['payment'] = $datas[$key]['payment']+$aData['cost_freight'];
                    }
                }

                //启用状态
                switch ($o['status']) {
                    case 'true':
                        $datas[$key]['status_format']='<span class="color-green">已开启</span>';
                        break;
                    case 'finish':
                        $datas[$key]['status_format']='<span class="">已完成</span>';
                        break;
                    default:
                        $datas[$key]['status_format']='<span class="color-gray">未开启</span>';
                        break;
                }

            }
        }
        return $datas;
    }


    //获取款项支付流水(查看预售订单详情页面使用)
    function getFundPayWater($order_id=0){
        $datas = [];
        $filter = [];
        $orderBy = ' order by of.begin_time asc';
        $groupBy = ' group by p.payment_id';
        $join = '';
        if (empty($order_id)) {
            return $datas;
        }
        $baseWhere[] = "p.payment_id !=''";
        $filter['order_id|nequal'] = $order_id;
        $fields = 'of.fund_name,p.money,p.pay_name,p.t_payed,p.t_begin,p.t_confirm,p.status pay_status';

        $join .= ' LEFT JOIN sdb_ectools_order_bills b ON b.rel_id = of.order_id';
        $join .= ' LEFT JOIN sdb_ectools_payments p ON (b.bill_id = p.payment_id and p.fund_id = of.fund_id and p.fund_type=of.fund_name)';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_preparesell_order_fund` of ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'of',$baseWhere)
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql(),0);
        if (empty($datas)) {
            return $datas;
        }


        $fundModel = app::get('preparesell')->model('preparesell_fund');
        $paymentsModel = app::get('ectools')->model('payments');
        $fundColumns = $fundModel->schema['columns'];
        $paymentsColumns = $paymentsModel->schema['columns'];
        foreach ($datas as $key => $o) {
            $datas[$key]['ctime']=date('Y/m/d H:i:s',$o['t_payed']);

            //款项名称
            if (isset($fundColumns['fund_name']['type'][$o['fund_name']])) {
                $datas[$key]['fund_name_format'] = $fundColumns['fund_name']['type'][$o['fund_name']];
            }

            //支付状态
            if (isset($paymentsColumns['status']['type'][$o['pay_status']])) {
                $datas[$key]['status_format'] = $paymentsColumns['status']['type'][$o['pay_status']];
            }

        }
        // ee($datas);
        return $datas;
    }


    //获取款项信息(包括该款项总支付金额，去付款详情页，去付款动作后台)
    function getOrderFund($order_fund_id=0){
        $fundRow = [];
        if (empty($order_fund_id)) {
            return $fundRow;
        }
        $fundWhere=['order_fund_id'=>$order_fund_id];
        $fundRow=$this->getRow('*',$fundWhere);

        if (!empty($fundRow)) {
            $nums = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$fundRow['order_id']));
            $nums = (int)$nums[0]['nums'];
            $fundRow['totalPayment'] = round($nums*$fundRow['payment'],2);
        }
        return $fundRow;
    }


    //款项支付状态
    function orderFundPaystatus($order_id=0,$order_fund_id=0){
        $data = ['status'=>0,'msg'=>'未支付'];

        $orderFundModel = app::get('preparesell')->model('order_fund');
        $fundRow=$orderFundModel->getOrderFund($order_fund_id);
        if (empty($fundRow)) {
            $data = ['status'=>1,'msg'=>'未发现订单款项信息!'];
            return $data;
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
            $wheres = [
                'payment_id' =>$bills_id,
                'fund_type'  =>$fundRow['fund_name'],
                'fund_id'    =>$fundRow['fund_id'],
                'status'     =>'succ',
            ];
            $paymentsRows = $obj_payments->getList('*',$wheres);
        }

        if (!empty($paymentsRows)) {
            $data = ['status'=>2,'msg'=>'该款项已经支付过了，无需再支付'];
        }

        return $data;
    }




    //获取款项总支付金额
    function getFundPayment($order_fund_id=0){
        $fundWhere=['order_fund_id'=>$order_fund_id];
        $fundRow=$this->getRow('*',$fundWhere);

        if (!empty($fundRow)) {
            $nums = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$fundRow['order_id']));
            $nums = (int)$nums[0]['nums'];
            $fundRow['payment'] = round($nums*$fundRow['payment'],2);
        }
        return $fundRow;
    }


    //获取订单款项，包括该款项总付款金额，payment字段即总付款金额，根据order_id
    function getFundRowByOrderId($order_id=0){
        $data = ['error'=>0,'msg'=>'','data'=>''];
        $prepareOrderModel = app::get('preparesell')->model('prepare_order');
        $fundStatus = $prepareOrderModel->fundStatus($order_id);
        // ee($fundStatus);
        if (!empty($fundStatus['order_fund_id'])) {
            $order_fund_id = $fundStatus['order_fund_id'];
            //预售订单款项#TODO#
            $orderFundModel = app::get('preparesell')->model('order_fund');
            $fundWhere=['order_fund_id'=>$order_fund_id];
            $fundRow=$orderFundModel->getFundPayment($fundWhere);
            if (empty($fundRow)) {
                return ['error'=>1,'msg'=>'没有发现该预售订单付款款项信息!请联系管理员...','data'=>''];
            }
            $data['data'] = $fundRow;
        }else{
            return ['error'=>1,'msg'=>'没有发现该预售订单付款款项信息!请联系管理员','data'=>''];
        }
        return $data;
    }


    //支付剩余时间
    function lefttime(&$row){
        $data = '';
        if ($row['status']=='true') {
            $data = $row['end_time'] - time();
        }
        return $data;
    }

}