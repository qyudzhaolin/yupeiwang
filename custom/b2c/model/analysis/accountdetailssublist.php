<?php
class b2c_mdl_analysis_accountdetailssublist extends dbeav_model{
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
        $fields = 'o.order_id,o.payment,o.createtime,o.ship_status,o.cost_item,o.final_amount';
        $fields .= ',gc.cat_name,ROUND(g.merchandisecost,2) as merchandisecost,ROUND( 
                 g.packingcost,2) as packingcost,s.supplier_name,
                 s.salesman,g.unit,mp.login_account,mp.member_id,o.payment,`o`.`status`,s.shortname,s.bn supplier_bn';
        $fields .= ',od.bn,od.name,od.nums,od.cost,ROUND(od.price,2)as price,od.amount';
        $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNums,
                    sum((ifnull(od.price,0)-ifnull(od.cost,0))*ifnull(od.nums,0)) as profit';

        $join = ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
        $join .= ' LEFT JOIN sdb_b2c_products pd ON pd.product_id = od.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = od.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';
        $join .= ' LEFT JOIN sdb_b2c_goods_cat gc ON gc.cat_id = g.cat_id';
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
                'name' => [
                    'label' => app::get('b2c')->_('商品名称'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'2',
                ],

                'cat_name' => [
                    'label' => app::get('b2c')->_('商品分类'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'3',
                ],

                'price' => [
                    'label' => app::get('b2c')->_('销售价'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'4',

                ],
                'unit' => [
                    'label' => app::get('b2c')->_('单位'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'5',

                ],
                'nums' => [
                    'label' => app::get('b2c')->_('数量'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'6',

                ],
                'merchandisecost' => [
                    'label' => app::get('b2c')->_('成本单价'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'7',

                ],
                'packingcost' => [
                    'label' => app::get('b2c')->_('包装成本单价'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'8',

                ],
                'supplier_name' => [
                    'label' => app::get('b2c')->_('供应商'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'9',

                ],
                'salesman' => [
                    'label' => app::get('b2c')->_('业务员'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'10',

                ],
                
            ],
            'in_list' =>array(
                0 => 'order_id',
                1 => 'name',
                2 => 'cat_name',
                3 => 'price',
                4 => 'unit',
                5 => 'nums',
                6 => 'merchandisecost',
                7 => 'packingcost',
                8 => 'supplier_name',
                9 => 'salesman',
           ),
            'default_in_list' =>array(
                0 => 'order_id',
                1 => 'name',
                2 => 'cat_name',
                3 => 'price',
                4 => 'unit',
                5 => 'nums',
                6 => 'merchandisecost',
                7 => 'packingcost',
                8 => 'supplier_name',
                9 => 'salesman',
            ),
        ];
        return $schema;
   }
}
