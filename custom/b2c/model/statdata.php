<?php
/**
 * 说明：统计数据model
 */
class b2c_mdl_statdata extends dbeav_model{
    //统计汇总配置(统计好后把统计数据写入数据库)
    public $statConfig=[
        //经营概况
        'scale'=>[
            'allOrder'=>'0',//总订单(新增订单)
            'salesAmount'=>'0',//总销售额
            'profitAmount'=>'0',//总利润
            // 'reshipNum'=>'0',//退款单
            // 'reshipAmount'=>'0',//退款金额
            'usefulOrderNum'=>'0',//总有效订单
        ],

        //商品统计
        'goods'=>[
            'onlineGoodsNum'=>'0',//上架商品数
        ],

        //账款统计
        'accounts'=>[
            'tobeChargedAmount'=>'0',//应收总额
            'chargedAmount'=>'0',//已收总额
            'costAmount'=>'0',//应付总额
        ],

        //客户统计
        'member'=>[
            'memberNum'=>'0',//总客户数
        ],
         //供应商统计
        'supplier'=>[
            'supplierNum'=>'0',//供应商总数
        ],
    ];

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

    /**
     * 获取统计数据
     * @params array $filter 条件,一般是time_from,time_to条件
     * @params array $statKeys 统计哪项的[值应为$statConfig的keys数组子集]
     * @return array
     */
    function get_stat_datas($filter=[], $statKeys=[]){
        $datas = [];
        $wheres = [];

        $this->timeFrom = $filter['time_from'] = strtotime(trim($filter['time_from']));
        $this->timeTo = $filter['time_to'] = strtotime(trim($filter['time_to']));
        $addTimeArr = ['day'=>86400,'month'=>date('t', $filter['time_to'])*86400,'year'=>31536000];
        $tmpReport = $filter['report'];
        if (in_array($tmpReport, array_keys($addTimeArr))) {
            $this->timeTo += $addTimeArr[$tmpReport];//统计时间偏移量
        }

        $wheres['createtime|bthan'] = $this->timeFrom;
        $wheres['createtime|sthan'] = $this->timeTo;
        $wheres['`status`|noequal'] = 'dead';

        //该时间段统计过了就直接返回
        // $datas = $this->get_datas($filter,$statKeys);//TODO即时统计
        // ee(sql(),0);
        if(!empty($datas)) {
            return $datas;
        }

        $statConfig=[];
        $join = '';
        $groupBy = '';
        $whereStr = '';
        $having = '';

        //[经营概况、账款统计]
        if (in_array('scale', $statKeys)) {
            $fields = 'o.order_id,o.createtime,o.pay_status,o.ship_status,o.cost_item,o.final_amount,o.payment';
            $fields .= ',od.cost,od.price,od.amount,od.nums';
            $fields .= ',sum(od.amount) as totalAmount,sum(ifnull(od.cost,0)*ifnull(od.nums,0)) totalCost';
            $fields .= ',sum((ifnull(od.price,0)-ifnull(od.cost,0))*od.nums) as profit';
            $join .= 'LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
            $groupBy .= ' group by o.order_id';
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_order_items` od ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($wheres, 'o')
                            . $whereStr
                            . $groupBy;
            $results = $this->db->selectLimit($sql,-1);
            // ee(sql(),0);
            foreach ($results as $o) {
                $statConfig['allOrder']++;//总订单(新增订单)

                //经营概况(以下统计建立在有效订单基础上)
                if (self::isUsefulOrder($o)) {
                    $statConfig['usefulOrderNum']++;//总有效订单
                    $statConfig['salesAmount']+=$o['totalAmount'];//销售额
                    $statConfig['profitAmount']+=$o['profit'];//利润
                    //账款统计
                    if ($o['pay_status'] != '1') {
                        $statConfig['tobeChargedAmount']+=$o['final_amount'];//应收总额
                    }
                    if ($o['pay_status'] == '1') {
                        $statConfig['chargedAmount']+=$o['final_amount'];//已收总额
                    }
                    $statConfig['costAmount']+=$o['totalCost'];//应付总额
                }

            }
        }

        //[商品统计]
        if (in_array('goods', $statKeys)) {
            $wheres = [];
            $wheres['uptime|bthan'] = $filter['time_from'];
            $wheres['uptime|sthan'] = $filter['time_to'];
            $wheres['marketable|nequal'] = 'true';//上架商品
            $groupBy = '';
            $fields = 'count(p.product_id) _count';
            $join .= 'LEFT JOIN sdb_b2c_goods g ON g.goods_id = p.goods_id';
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_products` p ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($wheres, 'g')
                            . $groupBy;
            $results = $this->db->selectLimit($sql,-1);
            $statConfig['onlineGoodsNum']=intval($results[0]['_count']);
        }

        //[会员统计]
        if (in_array('member', $statKeys)) {
            $wheres = [];
             
            $wheres['regtime|bthan'] = $filter['time_from'];
            $wheres['regtime|sthan'] = $filter['time_to'];
            $wheres['disabled|nequal'] = 'false';

            //SQL
            $fields = 'count(p.member_id) _count';

            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_members` p ' 
                            . ' where ' 
                            . $this->_filter($wheres, 'p');
            
            $results = $this->db->selectLimit($sql,-1);
            $statConfig['memberNum']=intval($results[0]['_count']);

        }
         //[供应商统计]
        if (in_array('supplier', $statKeys)) {
            $whereStr = [];
          

            $whereStr['createtime|bthan'] = $filter['time_from'];
            $whereStr['createtime|sthan'] = $filter['time_to'];
               
            //SQL
            $orderBy  = ' order by  s.supplier_id desc';
            $groupBy = " group by s.supplier_id";
            $join = '';
             $fields = 's.supplier_id as supplier_id,s.shortname as shortname,o.order_id,o.payment,o.createtime,o.ship_status,o.cost_item,o.final_amount,SUBSTRING_INDEX(substring(s.area, 10), '.'"/"'.', 1)as p,SUBSTRING_INDEX(SUBSTRING_INDEX(s.area,'.'"/"'.', -2), '.'"/"'.', 1)as city,
            SUBSTRING_INDEX(s.area,'.'"/"'.', -1)as area,
            sum(od.nums) as nums,
            sum(od.cost)  as cost,
            sum(od.price)  as price,
            count(od.order_id)as om,
            sum(rt.number) as number';
           $fields .= ',g.cat_id,g.bn,`g`.`name`,od.goods_id,od.amount,od.cost,od.price';
           $fields .= ',pd.unit,pd.spec_info,b.brand_name,pv.gsource_area,s.shortname,s.bn supplier_bn';
           $fields .= ',rt.reship_id,rt.number,gc.cat_name';
           $join .= ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id  ';
           $join .= ' LEFT JOIN sdb_b2c_products pd ON pd.product_id = od.product_id';
           $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = od.goods_id';
           $join .= ' LEFT JOIN sdb_b2c_goods_cat gc ON gc.cat_id = g.cat_id';
           $join .= ' LEFT JOIN sdb_b2c_brand b ON b.brand_id = g.brand_id';
           $join .= ' LEFT JOIN sdb_b2c_gprovenance pv ON pv.provenance_id = g.provenance_id';
           $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';
           $join .= ' LEFT JOIN sdb_b2c_reship_items rt ON rt.order_item_id = od.item_id';
            if ($cols == 'getCount') {
            $fields = 'od.item_id _id';
            }
           
       
            $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_order_items` od ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($wheres, 'o')
                        . $groupBy;
              
            $results = $this->db->selectLimit($sql,-1);

           if($results){
            foreach ($results as $key => $value) {
              $nums+=count($value['supplier_id']);
            }
           }
            $statConfig['supplierNum']=$nums;
        }

        // ee($statConfig,0);
        // ee(sql(),0);
        // $this->saveStatDatas($statConfig,$filter);//TODO即时统计
        $datas = $statConfig;
        return $datas;
    }


///////////////////////////////////////以下为辅助方法////////////////////////////////////////
    //保存汇总数据
    public function saveStatDatas($datas=[],$filter) 
    {
        if (empty($datas)) {
            return;
        }

        $tmpArr = [];
        $tmpArr['time_from'] = $filter['time_from'];
        $tmpArr['time_to'] = $filter['time_to'];
        $ctime = date('Y-m-d H:i:s');
        foreach ($datas as $k => $v) {
            $fields = [];
            $tmpArr['k'] = $k;
            if($this->count($tmpArr)){
                continue;
            }
            $tmpArr['ctime'] = $ctime;
            $tmpArr['v'] = round($v, 2);//四舍五入
            $lastId = $this->insert($tmpArr);
        }

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

    /**
     * 获取统计数据(辅助方法)
     * 注意：必须统计的statKeys都有才算统计过，否则不算
     * @params array $filter 条件,一般是time_from,time_to条件
     * @params array $statKeys 统计哪项的[值应为$statConfig的keys数组子集]
     * @return array
     */
    function get_datas($filter,&$statKeys){
        $datas = [];
        $isStat = true;
        if (!is_array($statKeys)) {
            $statKeys = (array)$statKeys;
        }

        $configKeys = array_keys($this->statConfig);
        $realStatkeys = [];//统计输出的keys
        foreach ($statKeys as $val) {
            if (!in_array($val, $configKeys)) {
                exit('statKeys is illegal!');
            }
            $realStatkeys = array_merge($realStatkeys, array_keys($this->statConfig[$val]));
        }

        $wheres['time_from'] = $filter['time_from'];
        $wheres['time_to'] = $filter['time_to'];
        $wheres['k|in'] = $realStatkeys;
        $limit = count($realStatkeys);
        $datas = $this->getList("`k`,`v`,`time_from`,`time_to`",$wheres,0,$limit,'ctime desc');
        // ee(sql(),0);
        if (empty($datas)) {
            return $datas;
        }

        //将数据库数据格式化[k=>v模式]
        $tmpData = [];
        foreach ($datas as $key => $value) {
            $tmpData[$value['k']] = $value['v'];
        }
        $datas = $tmpData;

        //为获取的key设置默认值
        foreach ($realStatkeys as $v) {
            if (!isset($datas[$v])) {
                $isStat = false;
                break;
            }
        }

        //发现有未统计后告知
        if (!$isStat) {
            return $isStat;
        }
        return $datas;
    }

}