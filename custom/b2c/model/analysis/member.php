<?php
class b2c_mdl_analysis_member extends dbeav_model{
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
        $member_cate = app::get('b2c')->model('members')->member_cate;
        $orderBy = '';
        $groupBy = " group by o.order_id";
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

        $fields = 'o.order_id,o.createtime,o.ship_status,o.cost_item,o.final_amount';
        $fields .= ',od.amount,m.member_cate,pm.member_id,pm.login_account';
        $fields .= ',sum(od.amount) totalAmount,sum(od.cost*od.nums) totalCost,
                    sum((ifnull(od.price,0)-ifnull(od.cost,0))*od.nums) profit';


        $join = ' LEFT JOIN sdb_b2c_order_items od ON od.order_id = o.order_id';
        $join .= ' LEFT JOIN sdb_b2c_members m on o.member_id = m.member_id';
        $join .=" LEFT JOIN sdb_pam_members pm on o.member_id = pm.member_id and pm.login_type = 'local'";

        if ($cols == 'getCount') {
            $fields = 'o.order_id _id';
        }

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_orders` o ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        . $groupBy;
        $datas = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($rows, $cols);
        if ($cols == 'getCount') {
            return count($datas);
        }
        
        foreach ($datas as $k=>$o) {
            $datas[$k]['profit']= round($o['profit'], 2);
            $datas[$k]['totalCost']= round($o['totalCost'], 2);
            if (isset($member_cate[$o['member_cate']])) {
                $datas[$k]['member_cate_name'] = $member_cate[$o['member_cate']];//会员名
            }
        }
        // ee($whereStr,0);
        // ee(sql(),0);
        // ee($datas,0);
        return $datas;
    }

    public function count($filter=null){
        $totalnum = $this->getlist('getCount', $filter);
        return $totalnum;
    }


    /** 
    * 是否为有效订单
    * 说明：使用前提,参数数组必须包含payment、ship_status、pay_status
    * @param string $row 订单行
    * @return bool 
    */ 
    public static function isUsefulOrder($row) 
    {
        if (empty($row)) return false;
        //账期支付看发货状态
        if ($row['payment'] == 'period') {
            if ($row['ship_status'] != '0') {
                return true;
            }
        }else{
            //其他支付情况看付款状态
            if ($row['pay_status'] != '0') {
                return true;
            }
        }
        return false;
    } 
    

    public function get_schema(){
        $schema = [
            'columns' => [
                'login_account' => [
                    'label' => app::get('b2c')->_('客户名'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'1',
                ],
                'member_cate_name' => [
                    'label' => app::get('b2c')->_('客户类别'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'2',
                ],
                'order_id' => [
                    'label' => app::get('b2c')->_('订单号'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'3',

                ],

                'createtime' => [
                    'label' => app::get('b2c')->_('订单日期'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'type' => 'time',
                    'order' =>'4',

                ],
                'final_amount' => [
                    'label' => app::get('b2c')->_('订单额'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'5',

                ],

                'totalCost' => [
                    'label' => app::get('b2c')->_('订单成本'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'6',

                ],
                'profit' => [
                    'label' => app::get('b2c')->_('利润'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'7',
                ],
            ],
            'in_list' =>array(
                0 => 'login_account',
                1 => 'member_cate_name',
                2 => 'order_id',
                3 => 'createtime',
                4 => 'final_amount',
                5 => 'totalCost',
                6 => 'profit',
           ),
            'default_in_list' =>array(
                0 => 'login_account',
                1 => 'member_cate_name',
                2 => 'order_id',
                3 => 'createtime',
                4 => 'final_amount',
                5 => 'totalCost',
                6 => 'profit',
            ),
        ];
        return $schema;
   }
}
