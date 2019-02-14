<?php
class b2c_mdl_analysis_sale extends dbeav_model{
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

    //收款额
    public function get_pay_money($filter=null){
        $sql = 'SELECT sum(P.money) as amount FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_payments as P ON B.bill_id=P.payment_id '.
            'where pay_object=\'order\' and bill_type=\'payments\' and P.t_payed >='.intval($filter['time_from']).' and P.t_payed <='.intval($filter['time_to']).' and P.status=\'succ\'';
        $row = $this->db->select($sql);
        return $row[0]['amount'];
    }

    //退款额
    public function get_refund_money($filter=null){
        $sql = 'SELECT sum(R.money) as amount FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_refunds as R ON B.bill_id=R.refund_id '.
            'where pay_object=\'order\' and bill_type=\'refunds\' and R.t_payed >='.intval($filter['time_from']).' and R.t_payed <='.intval($filter['time_to']).' and R.status=\'succ\'';
        $row = $this->db->select($sql);
        return $row[0]['amount'];
    }


    function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $ordersModel = app::get('b2c')->model('orders');
        $ordersColumns = $ordersModel->schema['columns'];
        $this->makeTimeRange($filter);
        $orderBy  = ' order by o.createtime desc';
        $groupBy = ' group by o.order_id';
        $fields = '';
        $join = '';
        $whereStr = '';
        $baseWhere = [];
        $filter['promotion_type|noequal'] = 'bail';
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;

        //用户名
        if (!empty($filter['login_account'])) {
            $login_account = $filter['login_account'];
            $baseWhere[] = "pm.login_account LIKE '%{$login_account}%'";
            unset($filter['login_account']);
        }
        $whereStr .= $this->_filter($filter, 'o', $baseWhere);

        //SQL
        $fields .= 'o.order_id,o.payment,o.createtime,`o`.`status`,o.ship_status,o.cost_item,o.final_amount,o.confirm';
        $fields .= ',o.ship_area,o.ship_addr,o.ship_name,o.weight,o.tostr,o.itemnum,o.ip,o.ship_zip,o.ship_tel';
        $fields .= ',o.ship_email,o.ship_time,o.ship_mobile,o.is_tax,o.tax_type,o.tax_content,o.cost_tax,o.tax_company';
        $fields .= ',o.tax_code,o.tax_bank,o.bank_account,o.tax_addr,o.tax_remark,o.is_protect';
        $fields .= ',o.cost_protect,o.cost_payment,o.currency,o.cur_rate,o.score_u,o.memo,o.cost_freight,o.soOrderNum';
        $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNums,sum((ifnull(od.price,0)-ifnull(od.cost,0))*od.nums) as profit';
        $fields .= ',sum(od.cost*od.nums) as totalCost,d.logi_no';
        $fields .= ',o.pay_status,o.ship_status,`o`.`status`,o.ship_status,o.received_status,o.shipping';
        $fields .= ',od.item_id,od.cost,od.price,od.amount,od.nums,pm.login_account';
        $fields .= ',o.promotion_type,group_concat(DISTINCT p.pay_app_id) as payments';
        $join   .= ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
        $join   .= " LEFT JOIN sdb_pam_members pm ON o.member_id = pm.member_id and pm.login_type='local'";

        //连表用于判断混合支付方式
        $join .= ' LEFT JOIN sdb_ectools_order_bills b ON  b.rel_id = o.order_id';
        $join .= " LEFT JOIN sdb_ectools_payments p ON  p.payment_id = b.bill_id and `p`.`status`='succ'";

        $join   .= ' LEFT JOIN sdb_b2c_delivery d ON d.order_id = o.order_id';

        if ($cols == 'getCount') {
            $fields = 'od.item_id _id';
        }

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_order_items` od ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,$limit,$offset);

        if ($cols == 'getCount') {
            return count($datas);
        }

        //支付方式配置
        $payment =[
            'period'  =>'账期支付',//账期支付
            //线上支付
            'online'  =>'线上支付',
            'cpcn'    =>'线上支付[网银]',
            'wxqrpay' =>'线上支付[微信]',
            'alipay'  =>'线上支付[支付宝]',

            'offline' =>'线下支付',//线下支付
            'mixed'   =>'混合支付',//混合支付
        ];

        foreach ($datas as $k=>$o) {
            $datas[$k]['profit']= round($o['profit'], 2);
            $datas[$k]['totalCost']= round($o['totalCost'], 2);

            //订单状态
            if (isset($ordersColumns['status']['type'][$o['status']])) {
                $datas[$k]['status'] = $ordersColumns['status']['type'][$o['status']];
            }

            //付款状态
            if (isset($ordersColumns['pay_status']['type'][$o['pay_status']])) {
                $datas[$k]['pay_status'] = $ordersColumns['pay_status']['type'][$o['pay_status']];
            }

            //发货状态
            if (isset($ordersColumns['ship_status']['type'][$o['ship_status']])) {
                $datas[$k]['ship_status'] = $ordersColumns['ship_status']['type'][$o['ship_status']];
            }

            //收货状态
            if (isset($ordersColumns['received_status']['type'][$o['received_status']])) {
                $datas[$k]['received_status'] = $ordersColumns['received_status']['type'][$o['received_status']];
            }

            //支付方式
            $datas[$k]['payment'] = isset($payment[$o['payment']]) ? $payment[$o['payment']] : '其他';

            //满足此条件为混合支付
            $payments = explode(',', $o['payments']);
            if($o['promotion_type'] == 'prepare' && count($payments) > 1){
                $datas[$k]['payment'] = $payment['mixed'];
            }

            //配送方式
            if (isset($ordersColumns['shipping']['type'][$o['shipping']])) {
                $datas[$k]['shipping'] = $ordersColumns['shipping']['type'][$o['shipping']];
            }

            //是否要开发票
            $datas[$k]['is_tax'] = $o['is_tax']=='true' ? '是' : '否';

            //发票类型
            if (isset($ordersColumns['tax_type']['type'][$o['tax_type']])) {
                $datas[$k]['tax_type'] = $ordersColumns['tax_type']['type'][$o['tax_type']];
            }
            //是否有保价费
            $datas[$k]['is_protect'] = $o['is_protect']=='true' ? '是' : '否';
        }
        // ee($whereStr,0);
        // ee(sql());
        // ee($datas);
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
                'createtime' => [
                    'label' => app::get('b2c')->_('下单时间'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'type' => 'time',
                    'order' =>'2',

                ],
                'final_amount' => [
                    'label' => app::get('b2c')->_('订单总额'),
                    'default_in_list' => true,
                    'order' =>'3',

                ],
                'totalCost' => [
                    'label' => app::get('b2c')->_('成本价'),
                    'default_in_list' => true,
                    'order' =>'4',

                ],
                'profit' => [
                    'label' => app::get('b2c')->_('利润'),
                    'default_in_list' => true,
                    'order' =>'5',

                ],

                'status' => [
                    'label' => app::get('b2c')->_('订单状态'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'6',
                ],

                'pay_status' => [
                    'label' => app::get('b2c')->_('付款状态'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'8',

                ],
                'ship_status' => [
                    'label' => app::get('b2c')->_('发货状态'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'9',

                ],
                'received_status' => [
                    'label' => app::get('b2c')->_('收货状态'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'10',

                ],

                'payment' => [
                    'label' => app::get('b2c')->_('支付方式'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'11',

                ],
                'shipping' => [
                    'label' => app::get('b2c')->_('配送方式'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'12',

                ],
                'login_account' => [
                    'label' => app::get('b2c')->_('会员用户名'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'13',

                ],
                'confirm' => [
                    'label' => app::get('b2c')->_('确认状态'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'14',
                ],
                'ship_area' => [
                    'label' => app::get('b2c')->_('收货地区'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'15',
                ],
                'ship_addr' => [
                    'label' => app::get('b2c')->_('收货地址'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'16',
                ],
                'ship_name' => [
                    'label' => app::get('b2c')->_('收货人'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'17',
                ],
                'weight' => [
                    'label' => app::get('b2c')->_('订单总重量'),
                    'default_in_list' => true,
                    'order' =>'18',
                ],
                'tostr' => [
                    'label' => app::get('b2c')->_('订单文字描述'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'19',
                ],
                'itemnum' => [
                    'label' => app::get('b2c')->_('订单子订单数量'),
                    'default_in_list' => true,
                    'order' =>'20',
                ],
                'ip' => [
                    'label' => app::get('b2c')->_('IP地址'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'21',
                ],
                'ship_zip' => [
                    'label' => app::get('b2c')->_('收货人邮编'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'22',
                ],
                'ship_tel' => [
                    'label' => app::get('b2c')->_('收货电话'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'23',
                ],
                'ship_email' => [
                    'label' => app::get('b2c')->_('收货人email'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'24',
                ],
                'ship_time' => [
                    'label' => app::get('b2c')->_('配送时间'),
                    'default_in_list' => true,
                    'order' =>'25',
                ],
                'ship_mobile' => [
                    'label' => app::get('b2c')->_('收货人手机'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'26',
                ],
                'is_tax' => [
                    'label' => app::get('b2c')->_('是否要开发票'),
                    'default_in_list' => true,
                    'order' =>'27',
                ],
                'tax_type' => [
                    'label' => app::get('b2c')->_('发票类型'),
                    'default_in_list' => true,
                    'order' =>'28',
                ],
                'tax_content' => [
                    'label' => app::get('b2c')->_('发票内容'),
                    'default_in_list' => true,
                    'order' =>'29',
                ],
                'cost_tax' => [
                    'label' => app::get('b2c')->_('订单税率'),
                    'default_in_list' => true,
                    'order' =>'30',
                ],
                'tax_company' => [
                    'label' => app::get('b2c')->_('发票抬头'),
                    'default_in_list' => true,
                    'order' =>'31',
                ],
                'tax_code' => [
                    'label' => app::get('b2c')->_('税号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'32',
                ],
                'tax_bank' => [
                    'label' => app::get('b2c')->_('开户行'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'33',
                ],
                'bank_account' => [
                    'label' => app::get('b2c')->_('银行账号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'34',
                ],
                'tax_addr' => [
                    'label' => app::get('b2c')->_('开票地址'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'35',
                ],
                'tax_remark' => [
                    'label' => app::get('b2c')->_('备注信息'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'36',
                ],
                'is_protect' => [
                    'label' => app::get('b2c')->_('是否有保价费'),
                    'default_in_list' => true,
                    'order' =>'37',
                ],
                'cost_protect' => [
                    'label' => app::get('b2c')->_('保价费'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'38',
                ],
                'cost_payment' => [
                    'label' => app::get('b2c')->_('支付费用'),
                    'default_in_list' => true,
                    'order' =>'39',
                ],
                'currency' => [
                    'label' => app::get('b2c')->_('订单支付货币'),
                    'default_in_list' => true,
                    'order' =>'40',
                ],
                'cur_rate' => [
                    'label' => app::get('b2c')->_('订单支付货币汇率'),
                    'default_in_list' => true,
                    'order' =>'41',
                ],
                'score_u' => [
                    'label' => app::get('b2c')->_('订单使用积分'),
                    'default_in_list' => true,
                    'order' =>'42',
                ],
                'memo' => [
                    'label' => app::get('b2c')->_('订单附言'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'43',
                ],
                'cost_freight' => [
                    'label' => app::get('b2c')->_('配送费用'),
                    'default_in_list' => true,
                    'order' =>'44',
                ],
                'soOrderNum' => [
                    'label' => app::get('b2c')->_('出库单号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'45',
                ],
                'logi_no' => [
                    'label' => app::get('b2c')->_('物流单号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'46',
                ],
            ],
            'in_list' =>array(
                0 => 'order_id',
                1 => 'createtime',
                2 => 'final_amount',
                3 => 'totalCost',
                4 => 'profit',
                6 => 'status',
                7 => 'pay_status',
                8 => 'ship_status',
                9 => 'received_status',
                10 => 'payment',
                11 => 'shipping',
                12 => 'login_account',
                13 => 'confirm',
                14 => 'ship_area',
                15 => 'ship_addr',
                16 => 'ship_name',
                17 => 'weight',
                18 => 'tostr',
                19 => 'itemnum',
                20 => 'ip',
                21 => 'ship_zip',
                22 => 'ship_tel',
                23 => 'ship_email',
                24 => 'ship_time',
                25 => 'ship_mobile',
                26 => 'is_tax',
                27 => 'tax_type',
                28 => 'tax_content',
                29 => 'cost_tax',
                30 => 'tax_company',
                31 => 'tax_code',
                32 => 'tax_bank',
                33 => 'bank_account',
                34 => 'tax_addr',
                35 => 'tax_remark',
                36 => 'is_protect',
                37 => 'cost_protect',
                38 => 'cost_payment',
                39 => 'currency',
                40 => 'cur_rate',
                41 => 'score_u',
                42 => 'memo',
                43 => 'cost_freight',
                44 => 'soOrderNum',
                45 => 'logi_no',
           ),
            'default_in_list' =>array(
                0 => 'order_id',
                1 => 'createtime',
                2 => 'final_amount',
                3 => 'totalCost',
                4 => 'profit',
                6 => 'status',
                7 => 'pay_status',
                8 => 'ship_status',
                9 => 'received_status',
                10 => 'payment',
                11 => 'shipping',
                12 => 'login_account',
                13 => 'confirm',
                14 => 'ship_area',
                15 => 'ship_addr',
                16 => 'ship_name',
                17 => 'weight',
                18 => 'tostr',
                19 => 'itemnum',
                20 => 'ip',
                21 => 'ship_zip',
                22 => 'ship_tel',
                23 => 'ship_email',
                24 => 'ship_time',
                25 => 'ship_mobile',
                26 => 'is_tax',
                27 => 'tax_type',
                28 => 'tax_content',
                29 => 'cost_tax',
                30 => 'tax_company',
                31 => 'tax_code',
                32 => 'tax_bank',
                33 => 'bank_account',
                34 => 'tax_addr',
                35 => 'tax_remark',
                36 => 'is_protect',
                37 => 'cost_protect',
                38 => 'cost_payment',
                39 => 'currency',
                40 => 'cur_rate',
                41 => 'score_u',
                42 => 'memo',
                43 => 'cost_freight',
                44 => 'soOrderNum',
                45 => 'logi_no',
            ),
        ];
        return $schema;
   }
}
