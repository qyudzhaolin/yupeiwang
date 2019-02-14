<?php
class b2c_mdl_analysis_adetails extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->analysis = app::get('ectools')->model('analysis');
    }

    function makeTimeRange($filter){
        $this->timeFrom = strtotime(trim($filter['time_from']));
        $this->timeTo = strtotime(trim($filter['time_to']));
        $addTimeArr = ['day'=>86400,'month'=>date('t', $this->timeTo)*86400,'year'=>31536000];
        $tmpReport = $filter['report'];
        if (in_array($tmpReport, array_keys($addTimeArr))) {
            $this->timeTo+=$addTimeArr[$tmpReport];
        }
    }


    function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
       
        $this->makeTimeRange($filter);
        $datas = [];
        $orderBy  = ' order by o.createtime desc';
        $groupBy = " group by od.item_id";//这里按商品统计
        $whereStr = '';

        $filter['promotion_type|noequal'] = 'bail';
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;
        //SQL
        $fields = ' o.order_id,o.createtime,o.pay_status,
                   o.cost_item,ROUND(o.final_amount,2)as final_amount,g.unit,
                   SUBSTRING_INDEX(SUBSTRING_INDEX(o.ship_area,'.'"/"'.', -2), '.'"/"'.', 1)as city,
                   ROUND(g.merchandisecost*od.nums,2) as merchandisecost , ROUND(g.packingcost*od.nums,2 )as packingcost,
                   ROUND((g.merchandisecost*od.nums+g.packingcost*od.nums),2) as costprice,
                   mp.login_account,o.mark_text,o.memo,ROUND(p.money,2)as money,p.pay_name,p.trade_no,p.bank_name,p.bank_no,
                   mp.member_id,o.payment,`o`.`status`,s.supplier_name,s.bn supplier_bn,od.bn,
                    od.name,od.nums,od.cost,od.price,od.amount,sum(od.amount) 
                    totalAmount,sum(od.nums) totalNums,o.order_refer';
        $join = ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
        $join .= ' LEFT JOIN sdb_b2c_products pd ON pd.product_id = od.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = od.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';
        $join .= ' LEFT JOIN sdb_ectools_order_bills b ON  b.rel_id = o.order_id';
        $join .= ' LEFT JOIN sdb_ectools_payments p ON  p.payment_id = b.bill_id';
        $join .=" LEFT JOIN sdb_pam_members mp on o.member_id = mp.member_id and mp.login_type = 'local'";
       
       
        if ($cols == 'getCount') {
            $fields = 'od.item_id _id';
        }

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_order_items` od ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'o')
                        . $whereStr
                        . $groupBy
                        . $orderBy;
           // var_dump($sql);   

        $datas = $this->db->selectLimit($sql,$limit,$offset);
        
        if ($cols == 'getCount') {
            return count($datas);
        }

        if (empty($datas)) {
            return $datas;
        }
        $ordersModel = app::get('b2c')->model('orders');
        $ordersColumns = $ordersModel->schema['columns'];
        $members = app::get('b2c')->model('members');
        foreach ($datas as $k=>$o) {
            $membersresult= $members->dump(array('member_id'=>$o['member_id']));
            $datas[$k]['supplier_name']= $membersresult['qiyemingcheng'];
            $datas[$k]['profit']= round($o['profit'], 2);
            $datas[$k]['transport']= 0;
            $datas[$k]['totalAmount']= round($o['totalAmount'], 2);
            //支付方式
            // if($o['payment']=='malipay'){
            // $datas[$k]['pay_name']="支付宝";
            // }else if($o['payment']=='wxqrpay'){
            // $datas[$k]['pay_name']="微信";   
            // }else if($o['payment']!="malipay"&&$o['payment']!="wxqrpay"){
            // $datas[$k]['pay_name']="银收"; 
            // }

            $opayment = app::get('ectools')->model('payments');
            $payment = $opayment->dump(array('pay_app_id'=>$o['payment']), 'pay_name', $subsdf=null);  
            $datas[$k]['pay_name']= $payment['pay_name'];
            //线上线下
            if($o['order_refer']=='local'){
             $datas[$k]['order_refer']="线上下单";
            }else if($o['order_refer']=='admin'){
             $datas[$k]['order_refer']="线下下单";
            }
            //订单状态
            if (isset($ordersColumns['status']['type'][$o['status']])) {
                $datas[$k]['status'] = $ordersColumns['status']['type'][$o['status']];
            }
             //付款状态

            $tmpArr1 = array(
            0 => app::get('b2c')->_('未付款'),
            1 => app::get('b2c')->_('已付款'),
            2 => app::get('b2c')->_('付款至担保方'),
            3 => app::get('b2c')->_('部分付款'),
            4 => app::get('b2c')->_('部分退款'),
            5 => app::get('b2c')->_('已退款'),
            );
         
            $datas[$k]['pay_status']= $tmpArr1[$o['pay_status']];    
           
             
        
            //支付方式
            // if (isset($ordersColumns['payment']['type'][$o['payment']])) {
            //     $datas[$k]['payment'] = $ordersColumns['payment']['type'][$o['payment']];
            // }
        }
        // ee(sql(),0);
        // ee($datas,0);
        return $datas;
    }

    public function count($filter=null){
        $totalnum = $this->getlist('getCount', $filter);
        return $totalnum;
    }


    public function get_schema(){
        $schema = [
            'columns' => [
                'order_id' => [
                    'label' => app::get('b2c')->_('订单号'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'1',
                ],
                'final_amount' => [
                    'label' => app::get('b2c')->_('销售额'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'2',

                ],
                'merchandisecost' => [
                    'label' => app::get('b2c')->_('商品成本'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'3',

                ],
                'packingcost' => [
                    'label' => app::get('b2c')->_('包装成本'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'4',

                ],
                'transport' => [
                    'label' => app::get('b2c')->_('运输成本'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'5',

                ],
                'costprice' => [
                    'label' => app::get('b2c')->_('成本合计'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'6',

                ],
                'supplier_name' => [
                    'label' => app::get('b2c')->_('客户'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'7',
                ],
                'createtime' => [
                    'label' => app::get('b2c')->_('订单日期'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'type' => 'time',
                    'order' =>'8',

                ],
                'pay_name' => [
                    'label' => app::get('b2c')->_('收款方式'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'9',
                ],

                'money' => [
                    'label' => app::get('b2c')->_('收款金额'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'10',
                ],
                
                'trade_no' => [
                    'label' => app::get('b2c')->_('支付宝/微信交易号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'11',

                ],

                'bank_name' => [
                    'label' => app::get('b2c')->_('付款银行'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'12',

                ],
              
                'bank_no' => [
                    'label' => app::get('b2c')->_('付款银行账号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'13',

                ],
                'order_refer' => [
                    'label' => app::get('b2c')->_('订单类型'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'14',

                ],
                'city' => [
                    'label' => app::get('b2c')->_('收货城市'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'15',

                ],
                'memo' => [
                    'label' => app::get('b2c')->_('订单备注'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'16',

                ],
                'pay_status' => [
                    'label' => app::get('b2c')->_('付款状态'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'17',

                ],
            ],
            'in_list' =>array(
                0 => 'order_id',
                1 => 'final_amount',
                2 => 'merchandisecost',
                3 => 'packingcost',
                4 => 'transport',
                5 => 'costprice',
                6 => 'supplier_name',
                7 => 'createtime',
                8 => 'pay_name',
                9 => 'money',
                10 => 'trade_no',
                11 => 'bank_name',
                12 => 'bank_no',
                13 => 'order_refer',
                14 => 'city',
                15 => 'memo',
                16 => 'pay_status',
           ),
            'default_in_list' =>array(
                0 => 'order_id',
                1 => 'final_amount',
                2 => 'merchandisecost',
                3 => 'packingcost',
                4 => 'transport',
                5 => 'costprice',
                6 => 'supplier_name',
                7 => 'createtime',
                8 => 'pay_name',
                9 => 'money',
                10 => 'trade_no',
                11 => 'bank_name',
                12 => 'bank_no',
                13 => 'order_refer',
                14 => 'city',
                15 => 'memo',
                16 => 'pay_status',
            ),
        ];
        return $schema;
   }
}
