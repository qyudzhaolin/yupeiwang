<?php
class b2c_mdl_analysis_accounts extends dbeav_model{
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
        $fields = 'o.order_id,o.payment,o.createtime,o.ship_status,o.cost_item,o.final_amount,o.pay_status';
        $fields .= ',g.unit,mp.login_account,mp.member_id,o.payment,`o`.`status`,s.shortname,s.bn supplier_bn';
        $fields .= ',od.bn,od.name,od.nums,od.cost,od.price,od.amount';
        $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNums,
                    sum((ifnull(od.price,0)-ifnull(od.cost,0))*ifnull(od.nums,0)) as profit';

        $join = ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
        $join .= ' LEFT JOIN sdb_b2c_products pd ON pd.product_id = od.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = od.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';
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
        $datas = $this->db->selectLimit($sql,$limit,$offset);
        
        if ($cols == 'getCount') {
            return count($datas);
        }

        if (empty($datas)) {
            return $datas;
        }
        $ordersModel = app::get('b2c')->model('orders');
        $ordersColumns = $ordersModel->schema['columns'];
        foreach ($datas as $k=>$o) {
            $datas[$k]['profit']= round($o['profit'], 2);
            $datas[$k]['totalAmount']= round($o['totalAmount'], 2);
            //订单状态
            if (isset($ordersColumns['status']['type'][$o['status']])) {
                $datas[$k]['status'] = $ordersColumns['status']['type'][$o['status']];
            }
            //支付方式
            if (isset($ordersColumns['payment']['type'][$o['payment']])) {
                $datas[$k]['payment'] = $ordersColumns['payment']['type'][$o['payment']];
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
                'createtime' => [
                    'label' => app::get('b2c')->_('下单时间'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'type' => 'time',
                    'order' =>'2',

                ],
                'name' => [
                    'label' => app::get('b2c')->_('商品名称'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'3',
                ],

                'bn' => [
                    'label' => app::get('b2c')->_('商品货号'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'4',
                ],


                'totalNums' => [
                    'label' => app::get('b2c')->_('销售量'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'5',

                ],

                'cost' => [
                    'label' => app::get('b2c')->_('成本价'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'6',

                ],
                'price' => [
                    'label' => app::get('b2c')->_('销售价'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'7',

                ],
                'totalAmount' => [
                    'label' => app::get('b2c')->_('销售额'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'8',

                ],
                'profit' => [
                    'label' => app::get('b2c')->_('利润'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'9',

                ],
                'unit' => [
                    'label' => app::get('b2c')->_('单位'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'10',

                ],
                'login_account' => [
                    'label' => app::get('b2c')->_('购买客户'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'11',

                ],
                'member_id' => [
                    'label' => app::get('b2c')->_('客户编号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'12',

                ],
                'payment' => [
                    'label' => app::get('b2c')->_('支付方式'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'13',

                ],
                'status' => [
                    'label' => app::get('b2c')->_('订单支付状态'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'14',

                ],
                'shortname' => [
                    'label' => app::get('b2c')->_('供应商'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'15',

                ],
                'supplier_bn' => [
                    'label' => app::get('b2c')->_('供应商编号'),
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
                1 => 'createtime',
                2 => 'name',
                3 => 'bn',
                4 => 'totalNums',
                5 => 'cost',
                6 => 'price',
                7 => 'totalAmount',
                8 => 'profit',
                9 => 'unit',
                10 => 'login_account',
                11 => 'member_id',
                12 => 'payment',
                13 => 'status',
                14 => 'shortname',
                15 => 'supplier_bn',
                16 => 'pay_status',
           ),
            'default_in_list' =>array(
                0 => 'order_id',
                1 => 'createtime',
                2 => 'name',
                3 => 'bn',
                4 => 'totalNums',
                5 => 'cost',
                6 => 'price',
                7 => 'totalAmount',
                8 => 'profit',
                9 => 'unit',
                10 => 'login_account',
                11 => 'member_id',
                12 => 'payment',
                13 => 'status',
                14 => 'shortname',
                15 => 'supplier_bn',
                16 => 'pay_status',
            ),
        ];
        return $schema;
   }
}
