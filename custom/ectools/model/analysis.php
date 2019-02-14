<?php
/**
 *说明：概览报表model
 *
 */
class ectools_mdl_analysis extends dbeav_model
{

    //订单统计配置
    public $scaleConfig=[
        //订单统计
        'order'=>[
            '0' => ['key'=>'all','name'=>'总订单'],//name名称
            '1' => ['key'=>'useful','tag'=>'1','name'=>'有效订单'],
            '2' => ['key'=>'allReship','tag'=>'1','name'=>'整单退单订单'],
            '3' => ['key'=>'partReship','tag'=>'1','name'=>'部分退单订单'],
            '4' => ['key'=>'usefulRate','name'=>'订单有效率'],
            // '3' => ['key'=>'usefulByDay','name'=>'日均有效订单'],
        ],
        //销售额统计sales
        'sales'=>[
            '0' => ['key'=>'salesAmount','name'=>'销售额'],
            // '1' => ['key'=>'profitAmount','name'=>'利润'],
            '1' => ['key'=>'salesNum','name'=>'销售单量'],
            '2' => ['key'=>'reshipAmount','name'=>'退款额'],
            '3' => ['key'=>'reshipNum','name'=>'退款单'],
        ],
        //利润统计sales
        'profit'=>[
            '0' => ['key'=>'salesAmount','name'=>'销售额'],
            '1' => ['key'=>'profitAmount','name'=>'利润'],
            '2' => ['key'=>'profitRate','name'=>'利润比'],
        ],
        //订单整单退单统计
        // 'allReship'=>[
        //     '0' => ['key'=>'refoundAmount','name'=>'退款金额'],
        //     '1' => ['key'=>'refoundNum','name'=>'退单量'],
        // ],
        // //订单部分退单统计
        // 'partReship'=>[
        //     '0' => ['key'=>'refoundAmount','name'=>'退款金额'],
        //     '1' => ['key'=>'refoundNum','name'=>'退单量'],
        //     '2' => ['key'=>'totalNum','name'=>'总单量'],
        // ]
    ];


    //账款统计配置
    public $accConfig=[
        //订单统计
        'accounts_order'=>[
            '0' => ['key'=>'period','name'=>'账期支付'],
            '1' => ['key'=>'online','name'=>'线上支付'],
            '2' => ['key'=>'offline','name'=>'线下支付'],
            '3' => ['key'=>'mixed','name'=>'混合支付'],
        ],
        //销售额统计
        'accounts_sales'=>[
            '0' => ['key'=>'period','name'=>'账期支付'],
            '1' => ['key'=>'online','name'=>'线上支付'],
            '2' => ['key'=>'offline','name'=>'线下支付'],
            '3' => ['key'=>'mixed','name'=>'混合支付'],
        ],
        //应收应付统计(销售价、成本)
        'price_cost'=>[
            '0' => ['key'=>'tobeCharged','name'=>'应收款'],
            '1' => ['key'=>'charged','name'=>'已收款'],
            '2' => ['key'=>'cost','name'=>'应付款'],
        ],
    ];


    //商品统计配置
    public $goodsConfig=[
        //上架商品数统计
        'onlineGoodsNum'=>[
            '0' => ['key'=>'oneCateNum','name'=>'一级分类商品数量'],
            '1' => ['key'=>'twoCateNum','name'=>'二级分类商品数量'],

        ],
        //一级分类商品销售统计
        'oneCateSales'=>[
            '0' => ['key'=>'salesAmount','name'=>'销售额'],
            '1' => ['key'=>'salesNum','name'=>'销售量'],//最小单位(一个订单可以有好几件)
            '2' => ['key'=>'salesOrderNum','name'=>'销售订单量'],
        ],

        //三级分类商品销售排行
        'threeCateSalesTop'=>[
            '0' => ['key'=>'salesNum','name'=>'销售量'],
            '1' => ['key'=>'salesAmount','name'=>'销售额'],
        ],

        //三级分类商品退换货排行
        'threeCateReturnTop'=>[
            '0' => ['key'=>'salesNum','name'=>'销售量'],
            '1' => ['key'=>'returnNum','name'=>'退换货'],
            '2' => ['key'=>'returnRate','name'=>'退换货率'],
        ],
    ];


    //会员统计配置
    public $memberConfig=[
        //客户类别统计
        'memberCate'=>[
            '0' => ['key'=>'member_cate','name'=>'客户类别统计'],
        ],
        //客户订单统计
        'memberOrder'=>[
            '0' => ['key'=>'orderAmount','name'=>'订单额'],
            '1' => ['key'=>'profit','name'=>'利润'],
            '2' => ['key'=>'orderNum','name'=>'订单量'],
        ],
    ];
    
      //供应商统计配置
    public $supplierConfig=[
        //客户类别统计
        'areaSupplierNum'=>[
            '0' => ['key'=>'member_cate','name'=>'区域供应商数量'],
        ],
        //客户订单统计
        'areaSupplierGoodsNum'=>[
            '1' => ['key'=>'profit','name'=>'供应商商品top10排名'],
        ],
         'areaSupplierGoodsNumtopone'=>[
            '2' => ['key'=>'profit','name'=>'供应商商品排名top10'],
        ],
         'areaSupplierGoodsNumtoptwo'=>[
            '3' => ['key'=>'profit2','name'=>'供应商销售金额top10'],
        ],
         'areaSupplierGoodsNumtopthree'=>[
            '4' => ['key'=>'profit3','name'=>'供应商销售单量top10'],
        ],
         'areaSupplierGoodsNumcate'=>[
            '5' => ['key'=>'profit4','name'=>'供应商按一级分类所在省份集中度统计'],
        ],
    ];
    function __construct($app){
        parent::__construct($app);
        $this->timeFrom = strtotime(trim($_GET['time_from']));
        $this->timeTo = strtotime(trim($_GET['time_to']));

        $addTimeArr = ['day'=>86400,'month'=>date('t', $this->timeTo)*86400,'year'=>31536000];
        $tmpReport = $_GET['report'];
        if (in_array($tmpReport, array_keys($addTimeArr))) {
            $this->timeTo+=$addTimeArr[$tmpReport];
        }
        // ee(date('Y-m-d H:i:s',$this->timeTo));
    }



    //获取概览数据
    function get_scale_data($params=[]){
        $datas = [];
        if (empty($params)) {
            return $datas;
        }
        
        $report = $params['report'] ?  $params['report'] : 'day';//按年月周日呈现(year,month,week,day)
        $target = $params['target'];//统计项
        $filter = [];
        $orderBy = ' ORDER BY o.createtime desc';
        $groupBy = '';
        $whereStr = '';
        $having = '';
        if (empty($params['time_from']) || empty($params['time_to'])) {
            return $datas;
        }

        //统计项检测是否合法
        if (!in_array($target, array_keys($this->scaleConfig))) {
            return $datas;
        }

        $filter['promotion_type|noequal'] = 'bail';
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;

        //SQL
        $join = '';
        $fields = 'o.order_id,o.createtime,o.pay_status,o.ship_status,o.cost_item,o.final_amount,o.payment';
        $join = 'LEFT JOIN sdb_b2c_order_items od ON od.order_id = o.order_id';
        $fields .= ',od.cost,od.price,od.amount,od.nums';
        $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNum,
        sum((ifnull(od.price,0)-ifnull(od.cost,0))*od.nums) as profit';

        $groupBy .= ' group by o.order_id';

        if ($target == 'order' || $target == 'sales') {
            $fields .= ',rt.reship_id,sum(od.cost) reshipNum,
                    sum(ifnull(rt.number,0) * ifnull(od.price,0)) reshipAmount';
            $join .= ' LEFT JOIN sdb_b2c_reship_items rt ON rt.order_item_id = od.item_id';
        }

        if ($target == 'sales') {
            $fields .= ',r.refund_id,r.cur_money as refund_amount';
            $join .= " LEFT JOIN sdb_ectools_order_bills ob ON ob.rel_id = o.order_id AND ob.bill_type='refunds'";
            $join .= " LEFT JOIN sdb_ectools_refunds r ON r.refund_id = ob.bill_id AND `r`.`status` = 'succ'  AND r.disabled='false'";
        }

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_orders` o ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'o')
                        . $whereStr
                        . $groupBy
                        . $orderBy
                        . $having;
        $orders = $this->db->selectLimit($sql,-1);
        $this->tidy_data($orders, $fields);

        // ee(sql(),0);
        if (empty($orders)) {
            return $datas;
        }

        //构造list格式
        foreach ($orders as $key => $value) {
            $orders[$key]['ctime'] = date('Y-m-d H:i:s', $value['createtime']);
            $orders[$key]['day_time'] = date('Y-m-d', $value['createtime']);//按天
            $orders[$key]['year_time'] = date('Y', $value['createtime']);//按年
            $orders[$key]['month_time'] = date('Y-m', $value['createtime']);//按月
        }

        //构造时间粒度
        $timeRanges = $this->getTimeRanges($params['time_from'],$params['time_to'],$report);
        $datas['timeRanges'] = $timeRanges['keyNum'];

        //配置统计初始化数据
        $dataInt = $timeRanges['keyTime'];

        //初始化各项配置的统计数据
        foreach ($this->scaleConfig[$target] as $ckey => $cval) {
            $datas[$target][$ckey] = $dataInt;
        }

        //构造时间对应的统计数据
        foreach ($orders as $o) {
            if (!isset($dataInt[$o[$report . '_time']])) continue;

            //订单量统计
            if ($target == 'order') {
                //所有订单统计
                $keyName = '0';
                $datas[$target][$keyName][$o[$report . '_time']]++;

                //有效订单统计
                $keyName = '1';
                if (self::isUsefulOrder($o)) {
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }

                //订单整单退单统计
                if ($o['reshipNum'] == $o['totalNum']) {
                    $keyName = '2';
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }

                //订单部分退单统计
                if ((0 < $o['reshipNum']) && ($o['reshipNum'] < $o['totalNum'])) {
                    $keyName = '3';
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }

            }

            //销售额统计
            if ($target == 'sales') {
                //这里必须是有效订单的
                if (self::isUsefulOrder($o)) {
                    //销售额
                    $keyName = '0';
                    $datas[$target][$keyName][$o[$report . '_time']]+=$o['final_amount'];

                    //销售单量
                    $keyName = '1';
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }

                if ($o['refund_id']) {
                    //退款额
                    $keyName = '2';
                    $datas[$target][$keyName][$o[$report . '_time']]+=$o['refund_amount'];
                    //退款单
                    $keyName = '3';
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }

                //逻辑改为：把退货的的退款金额也加进来，退货的也加到退款单里面
                if ($o['reshipNum']) {
                    //退款额
                    $keyName = '2';
                    $datas[$target][$keyName][$o[$report . '_time']]+=$o['reshipAmount'];
                    //退款单
                    $keyName = '3';
                    $datas[$target][$keyName][$o[$report . '_time']]++;
                }
            }

            //利润统计
            if ($target == 'profit') {
                if (!self::isUsefulOrder($o)) continue;//过滤无效订单

                //销售额
                $keyName = '0';
                $amount = $datas[$target][$keyName][$o[$report . '_time']]+=$o['final_amount'];

                //利润(利润等于商品价-成本*数量,这只是毛利润)
                $keyName = '1';
                $profit=$datas[$target][$keyName][$o[$report . '_time']]+=$o['profit'];

                //利润比
                $keyName = '2';
                $datas[$target][$keyName][$o[$report . '_time']]++;
                if ($amount > 0) {
                    $datas[$target][$keyName][$o[$report . '_time']] = round(($profit/$amount)*100, 2);
                }

            }

        }

        //(随便循环一个统计维度)
        foreach ($datas[$target]['0'] as $ka => $va) {
            //订单量统计
            if ($target == 'order') {
                $usefulOrder = $datas[$target]['1'][$ka];
                $allOrder = $va;
                if ($allOrder==0) continue;
                //订单有效率统计
                $keyName = '4';
                $datas[$target][$keyName][$ka] = round(($usefulOrder/$allOrder)*100, 2);

                //日均有效订单统计
                // $keyName = '3';
                // $datas[$target][$keyName][$ka] = self::getUsefulByDay($usefulOrder, $ka, $report);
            }
        }

        // ee(sql(0),0);
        // ee($datas,0);
        return $datas;
    }



    //获取概览数据
    function get_accounts_data($params=[]){
        $datas = [];
        if (empty($params)) {
            return $datas;
        }
        $report = $params['report'] ?  $params['report'] : 'day';//按年月周日呈现(year,month,week,day)
        $target = $params['target'];//统计项
        $filter = [];
        $orderBy = 'createtime';
        $groupBy = " group by o.order_id";
        $whereStr = '';
        if (empty($params['time_from']) || empty($params['time_to'])) {
            return $datas;
        }

        //统计项检测是否合法
        if (!in_array($target, array_keys($this->accConfig))) {
            return $datas;
        }
        $filter['promotion_type|noequal'] = 'bail';
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;
        $filter['`status`|noequal'] = 'dead';

        //SQL
        $join = '';
        $fields = 'o.order_id,o.payment,o.createtime,o.status,o.pay_status,o.ship_status,o.cost_item,o.final_amount';
        $fields .= ',o.promotion_type,group_concat(DISTINCT p.pay_app_id) as payments';
        $join .= ' LEFT JOIN sdb_b2c_order_items od ON od.order_id = o.order_id';
        $join .= ' LEFT JOIN sdb_ectools_order_bills b ON  b.rel_id = o.order_id';
        $join .= " LEFT JOIN sdb_ectools_payments p ON  p.payment_id = b.bill_id and `p`.`status`='succ'";

        $fields .= ',sum(ifnull(od.cost,0)*ifnull(od.nums,0)) totalCost,od.cost,od.price,od.amount,od.nums';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_orders` o ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'o')
                        . $whereStr
                        . $groupBy;

        $results = $this->db->selectLimit($sql,-1);
        $this->tidy_data($results, $fields);

        // ee(sql(),0);
        if (empty($results)) {
            return $datas;
        }

        //构造list格式
        foreach ($results as $key => $value) {
            $results[$key]['ctime'] = date('Y-m-d H:i:s', $value['createtime']);
            $results[$key]['day_time'] = date('Y-m-d', $value['createtime']);//按天
            $results[$key]['year_time'] = date('Y', $value['createtime']);//按年
            $results[$key]['month_time'] = date('Y-m', $value['createtime']);//按月
        }

        //构造时间粒度
        $timeRanges = $this->getTimeRanges($params['time_from'],$params['time_to'],$report);
        $datas['timeRanges'] = $timeRanges['keyNum'];

        //配置统计初始化数据
        $dataInt = $timeRanges['keyTime'];

        //初始化各项配置的统计数据
        foreach ($this->accConfig[$target] as $ckey => $cval) {
            $datas[$target][$ckey] = $dataInt;
        }
        // ee($datas);

        //支付方式配置
        $payment =[
            'period'  =>0,//账期支付
            //线上支付
            'online'  =>1,
            'cpcn'    =>1,
            'wxqrpay' =>1,
            'alipay'  =>1,
            'malipay'  =>1,

            'offline' =>2,//线下支付
            'mixed'   =>3,//混合支付
        ];
        // ee($results,0);
        foreach ($results as $o) {
            if (!isset($dataInt[$o[$report . '_time']])) continue;
            if (!self::isUsefulOrder($o)) continue;//过滤无效订单

            if (in_array($target, ['accounts_order','accounts_sales'])) {
                $paymentKey = 1;//默认是线上支付
                if ( ! isset($payment[$o['payment']])) continue;

                //满足此条件为混合支付
                $payments = explode(',', $o['payments']);
                if($o['promotion_type'] == 'prepare' && count($payments) > 1){
                    $paymentKey = 3;//混合支付
                }else{
                    $paymentKey = $payment[$o['payment']];
                }

                //订单量统计
                if ($target == 'accounts_order') {
                    $datas[$target][$paymentKey][$o[$report . '_time']]++;
                }

                //销售额统计
                if ($target == 'accounts_sales') {
                    $datas[$target][$paymentKey][$o[$report . '_time']]+=$o['final_amount'];
                } 
            }


            //应收应付统计
            if ($target == 'price_cost') {
                //应收款
                if ($o['pay_status'] != '1') {
                    $keyName = '0';
                    $datas[$target][$keyName][$o[$report . '_time']]+=$o['final_amount'];
                }

                //已收款
                if ($o['pay_status'] == '1') {
                    $keyName = '1';
                    $datas[$target][$keyName][$o[$report . '_time']]+=$o['final_amount'];
                }

                //应付款
                $keyName = '2';
                $datas[$target][$keyName][$o[$report . '_time']]+=$o['totalCost'];
            }
        }

        // ee(sql(),0);
        // ee($datas,0);
        return $datas;
    }



    //获取商品统计数据
    function get_goods_data($params=[]){
        $datas = [];
        if (empty($params)) {
            return $datas;
        }
        $report = $params['report'] ?  $params['report'] : 'day';//按年月周日呈现(year,month,week,day)
        $target = $params['target'];//统计项
        $filter = [];
        $orderBy = 'last_modify';
        $whereStr = '';
        $join = '';
        $groupBy = '';

        if (empty($params['time_from']) || empty($params['time_to'])) {
            return $datas;
        }

        //统计项检测是否合法
        if (!in_array($target, array_keys($this->goodsConfig))) {
            return $datas;
        }

        //商品分类列表
        $allGoodsCates = $this->getAllGoodsCate();
        //初始化各项配置的统计数据
        $dataInt = $this->dataIntByAllCates($allGoodsCates);

        //初始化X轴数据
        $datas['xdatas']['xdata0Cates'] = array_values($dataInt['x0']);//x轴一级分类
        $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($this->goodsConfig[$target] as $ckey => $cval) {
            //上架商品数统计
            if ($target == 'onlineGoodsNum') {
                $datas[$target][$ckey] = $dataInt[$ckey];//只统计一、二级商品分类
            }

            //一级分类商品销售统计
            if ($target == 'oneCateSales') {
                $datas[$target][$ckey] = $dataInt[0];
            }

            //三级分类商品销售排行
            if ($target == 'threeCateSalesTop') {
                $datas[$target][$ckey] = $dataInt[2];//只统计三级商品分类
            }

            //三级分类商品退换货排行
            if ($target == 'threeCateReturnTop') {
                $datas[$target][$ckey] = $dataInt[2];//只统计三级商品分类
            }
        }

        //上架商品数统计
        if ($target == 'onlineGoodsNum') {
            //SQL
            $filter['marketable|nequal'] = 'true';//上架商品
            $filter['uptime|bthan'] = $this->timeFrom;
            $filter['uptime|sthan'] = $this->timeTo;

            $groupBy = ' group by p.product_id';
            $fields = 'p.goods_id,g.cat_id,p.name';
            $join .= 'LEFT JOIN sdb_b2c_goods g ON g.goods_id = p.goods_id';
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_products` p ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'g')
                            . $whereStr
                            . $groupBy;
            $results = $this->db->selectLimit($sql,-1);
            $this->tidy_data($results, $fields);
        }

        //一级分类商品销售统计
        if (in_array($target, ['oneCateSales','threeCateSalesTop','threeCateReturnTop'])) {
            $filter['promotion_type|noequal'] = 'bail';
            $filter['createtime|bthan'] = $this->timeFrom;
            $filter['createtime|sthan'] = $this->timeTo;
            $filter['`status`|noequal'] = 'dead';
            //SQL
            $join = '';
            $groupBy = ' group by od.item_id';
            $fields = 'o.order_id,o.payment,o.createtime,o.pay_status,o.ship_status,o.cost_item,o.final_amount';

            $join .= 'LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
            $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = od.goods_id';
            $join .= ' LEFT JOIN sdb_b2c_goods_cat gc ON gc.cat_id = g.cat_id';
            $fields .= ',g.cat_id,od.goods_id,od.cost,od.price,od.amount,od.nums';
            // $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNums';

            if ($target == 'threeCateReturnTop') {
                $fields .= ',rt.reship_id';
                $join .= ' LEFT JOIN sdb_b2c_reship_items rt ON rt.order_item_id = od.item_id';
            }

            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_order_items` od ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'o')
                            . $whereStr;
            $results = $this->db->selectLimit($sql,-1);
            $this->tidy_data($results, $fields);

        }


        // ee(sql(),0);
        // ee($datas,0);
        if (empty($results)) {
            return $datas;
        }

        foreach ($results as $o) {
            if (in_array($target, ['oneCateSales','threeCateSalesTop','threeCateReturnTop'])) {
                if (!self::isUsefulOrder($o)) continue;//过滤无效订单
            }
            //上架商品数统计
            if ($target == 'onlineGoodsNum') {
                //一级分类商品数量
                $keyName = '0';
                //getCateLevelIdByCateRow 参数必须为cat表行才能准确获取分类级别ID
                $oneCateId = self::getCateLevelIdByCateRow($allGoodsCates[$o['cat_id']]);
                if ($oneCateId && isset($datas[$target][$keyName][$oneCateId])) {
                    $datas[$target][$keyName][$oneCateId]++;
                }

                //二级分类商品数量
                $keyName = '1';
                $twoCateId = self::getCateLevelIdByCateRow($allGoodsCates[$o['cat_id']],2);
                if ($twoCateId && isset($datas[$target][$keyName][$twoCateId])) {
                    $datas[$target][$keyName][$twoCateId]++;
                }
            }


            //一级分类商品销售统计
            if ($target == 'oneCateSales') {
                //销售额
                $keyName = '0';
                $oneCateId = self::getCateLevelIdByCateRow($allGoodsCates[$o['cat_id']]);
                if ($oneCateId && isset($datas[$target][$keyName][$oneCateId])) {
                    $datas[$target][$keyName][$oneCateId]+=$o['amount'];
                }

                //销售量
                $keyName = '1';
                if ($oneCateId && isset($datas[$target][$keyName][$oneCateId])) {
                    $datas[$target][$keyName][$oneCateId]+=$o['nums'];
                }

                //销售订单量
                $keyName = '2';
                if ($oneCateId && isset($datas[$target][$keyName][$oneCateId])) {
                    $datas[$target][$keyName][$oneCateId]++;
                }
            }

            //三级分类商品销售排行(先统计销售量，再排序)
            if ($target == 'threeCateSalesTop') {
                $threeCateId = self::getCateLevelIdByCateRow($allGoodsCates[$o['cat_id']],3);
                //销售量
                $keyName = '0';
                if ($threeCateId && isset($datas[$target][$keyName][$threeCateId])) {
                    $datas[$target][$keyName][$threeCateId]+=$o['nums'];
                }

                //销售额
                $keyName = '1';
                if ($threeCateId && isset($datas[$target][$keyName][$threeCateId])) {
                    $datas[$target][$keyName][$threeCateId]+=$o['amount'];
                }

            }

            //三级分类商品退换货排行(先统计销量，再排序)
            if ($target == 'threeCateReturnTop') {
                $threeCateId = self::getCateLevelIdByCateRow($allGoodsCates[$o['cat_id']],3);
                //销售量
                $keyName = '0';
                if ($threeCateId && isset($datas[$target][$keyName][$threeCateId])) {
                    $datas[$target][$keyName][$threeCateId]+=$o['nums'];
                }

                //退换货
                $keyName = '1';
                if ($threeCateId && isset($datas[$target][$keyName][$threeCateId])) {
                    if (!empty($o['reship_id'])) {
                        $datas[$target][$keyName][$threeCateId]++;
                    }
                }

            }

        }


        //三级分类商品销售排行(排序)
        if ($target == 'threeCateSalesTop') {
            arsort($datas[$target]['0']);
            arsort($datas[$target]['1']);

            //重新构造X轴数据
            $datas['xdatas']['xdata2Cates_0']=$datas['xdatas']['xdata2Cates_1']=[];
            $tempNum=1;
            foreach ($datas[$target]['0'] as $k0 => $v0) {
                if ($tempNum > 10) {
                    unset($datas[$target]['0'][$k0]);
                    continue;
                }
                if (isset($datas['xdatas']['xdata2Cates'][$k0])) {
                    $datas['xdatas']['xdata2Cates_0'][] = $datas['xdatas']['xdata2Cates'][$k0];
                }
                $tempNum++;
            }

            $tempNum=1;//默认只统计前10名
            foreach ($datas[$target]['1'] as $k1 => $v1) {
                if ($tempNum > 10) {
                    unset($datas[$target]['1'][$k1]);
                    continue;
                }
                if (isset($datas['xdatas']['xdata2Cates'][$k0])) {
                    $datas['xdatas']['xdata2Cates_1'][] = $datas['xdatas']['xdata2Cates'][$k1];
                }
                $tempNum++;
            }
            
        }



        //三级分类商品退换货排行(排序)
        if ($target == 'threeCateReturnTop') {
            arsort($datas[$target]['1']);

            //重新构造X轴数据
            $tmpXdata2Cates = $datas['xdatas']['xdata2Cates'];
            $datas['xdatas']['xdata2Cates']=[];

            $tempNum=1;//默认只保留前10名退货比较多的分类

            //重新初始化key0(销量)数据
            $tempKey0 = $datas[$target]['0'];
            $datas[$target]['0'] = $datas[$target]['2'] = [];//重新初始化


            //构造X轴数据&&重新根据(退换货key)重排销量
            foreach ($datas[$target]['1'] as $k => $v) {
                if ($tempNum > 10) {
                    unset($datas[$target]['1'][$k]);
                    continue;
                }
                //构造X轴数据
                if (isset($tmpXdata2Cates[$k])) {
                    $datas['xdatas']['xdata2Cates'][] = $tmpXdata2Cates[$k];
                }

                //重新构造销量(key0)数据
                if (isset($tempKey0[$k])) {
                    $datas[$target]['0'][$k] = $tempKey0[$k];
                }

                $tempNum++;
            }

            //退换货率统计
            foreach ($datas[$target]['1'] as $k1 => $v1) {
                if (isset($datas[$target]['0'][$k1]) && $datas[$target]['0'][$k1] > 0) {
                    $amountNums = $datas[$target]['0'][$k1];//退换货key对应的销量key值
                    $datas[$target]['2'][$k1] = round(($v1/$amountNums)*100, 2);
                }
            }
            
        }

        // ee(sql(),0);
        // ee($datas,0);
        return $datas;
    }



    //
    /**
     * 获取会员统计数据
     * @param params array $_GET
     * @param islog bool 是否只获取log数据
     */
    function get_member_data($params=[]){
        $datas = $tdatas = [];
        if (empty($params)) {
            return $datas;
        }

        $report = $params['report'] ?  $params['report'] : 'day';//按年月周日呈现(year,month,week,day)
        $target = $params['target'];//统计项
        $filter = [];
        if (empty($params['time_from']) || empty($params['time_to'])) {
            return $datas;
        }

        //统计项检测是否合法
        if (!in_array($target, array_keys($this->memberConfig))) {
            return $datas;
        }

        $datas['xdatas'] = [];

        $member_cate = app::get('b2c')->model('members')->member_cate;
        $join = '';
        $orderBy = '';
        $groupBy = '';
        $whereStr = '';
        $having = '';

        $having .= ' having member_id > 0 ';

        if ($target == 'memberCate') {
            $orderBy = ' ORDER BY cate_num DESC ';
            $filter['regtime|bthan'] = $this->timeFrom;
            $filter['regtime|sthan'] = $this->timeTo;
            $filter['disabled|nequal'] = 'false';
            //SQL
            $fields = 'p.member_id,count(p.member_cate) as cate_num,p.member_cate';
            $groupBy = ' group by p.member_cate ';

            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_members` p ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'p')
                            . $whereStr
                            . $groupBy
                            . $having
                            . $orderBy;

            $results = $this->db->selectLimit($sql,-1);
        }

        if ($target == 'memberOrder') {
            $filter['promotion_type|noequal'] = 'bail';
            $filter['createtime|bthan'] = $this->timeFrom;
            $filter['createtime|sthan'] = $this->timeTo;
            $fields = 'o.order_id,o.createtime,o.pay_status,o.ship_status,o.cost_item,o.final_amount';
            $fields .= ',price,sum(od.amount) as amount,
                        cost,sum(od.nums) as nums,
                        sum((ifnull(od.price,0)-ifnull(od.cost,0))*ifnull(od.nums,0)) as profit,m.member_cate,
                        mp.member_id,mp.login_account';

            $join = ' LEFT JOIN sdb_b2c_order_items od ON od.order_id = o.order_id';
            $join .= ' LEFT JOIN sdb_b2c_members m on o.member_id = m.member_id';
            $join .=" LEFT JOIN sdb_pam_members mp on o.member_id = mp.member_id and mp.login_type = 'local'";
            // $whereStr = " and IF((payment = 'offline' AND pay_status = '0'),0,1)";
            $groupBy = " group by o.order_id";

            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_orders` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'o')
                            . $whereStr
                            . $groupBy
                            . $having;
            $results = $this->db->selectLimit($sql,-1);
        }
        // ee(sql(),0);
        if (empty($results)) {
            return $datas;
        }
        $this->tidy_data($results, $fields);

        if ($target == 'memberCate') {
            //求总和
            $totalMembers = 0;
            foreach ($results as $trow) {
                $totalMembers+=$trow['cate_num'];
            }
            $datas['totalMembers'] = $totalMembers;

            foreach ($results as $key=>$row) {
                if (empty($member_cate[$row['member_cate']])) {
                    continue;
                }
                $pie = [];
                //客户类别统计
                $keyName = '0';
                $pie['name'] = $member_cate[$row['member_cate']];
                if($totalMembers > 0) $pie['y'] = round(($row['cate_num']/$totalMembers)*100, 2);
                $pie['num'] = $row['cate_num'];
                //第一个凸显出来
                if ($key == 0) {
                    $pie['sliced'] = true;
                    $pie['selected'] = true;
                }
                $datas[$target][$keyName][] = $pie;
            }
        }

        //客户订单统计
        // ee($results,0);
        if ($target == 'memberOrder') {
            foreach ($results as $kk=>$o) {
                if (!self::isUsefulOrder($o)) continue;//过滤无效订单
                //定单额
                $keyName = '0';
                $datas[$target][$keyName][$o['member_id']]+=$o['final_amount'];

                //利润(利润等于商品价-成本*数量)
                $keyName = '1';
                $profit = $datas[$target][$keyName][$o['member_id']]+=$o['profit'];

                //定单量
                $keyName = '2';
                $datas[$target][$keyName][$o['member_id']]++;

                //客户数组(暂时用x轴数据保存客户数组)
                $datas['xdatas'][$o['member_id']] = $o['login_account'];

            }

            //客户订单统计(排序)
            arsort($datas[$target]['0']);
            // ee($datas,0);
            //重新构造X轴数据
            $tmpXdata = $datas['xdatas'];
            $datas['xdatas']=[];


            //重新初始化key1、key2数据
            $tempKey1 = $datas[$target]['1'];
            $tempKey2 = $datas[$target]['2'];
            $datas[$target]['1'] = $datas[$target]['2'] = [];//重新初始化

            //构造X轴数据&&重新根据(退换货key)重排销量
            $tempNum=1;//默认只保留前10名
            foreach ($datas[$target]['0'] as $k => $v) {
                if ($tempNum > 10) {
                    unset($datas[$target]['0'][$k]);
                    continue;
                }
                //构造X轴数据
                if (isset($tmpXdata[$k])) {
                    $datas['xdatas'][] = $tmpXdata[$k];
                }

                //重新构造(key1)数据
                if (isset($tempKey1[$k])) {
                    $datas[$target]['1'][$k] = $tempKey1[$k];
                }

                //重新构造(key2)数据
                if (isset($tempKey2[$k])) {
                    $datas[$target]['2'][$k] = $tempKey2[$k];
                }

                $tempNum++;
            }
            
        }
        // ee(sql(),0);
        // ee($datas,0);
        return $datas;

    }





///////////////////////////////////////以下为辅助方法////////////////////////////////////////
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

    //根据商品分类ROW获取商品分类对应级别的id,默认获取一级分类ID
    public static function getCateLevelIdByCateRow($catRow=[],$level=1){
        $cat_id = 0;
        if (empty($catRow)) {
            return $cat_id;
        }

        $cates = trim($catRow['cat_path'] . $catRow['cat_id'], ',');
        $catesArr = explode(',',$cates);

        if (isset($catesArr[$level-1])) {
            $cat_id = $catesArr[$level-1];
        }
        return $cat_id;
    }


    //根据商品分类初始化数据
    function dataIntByAllCates($allCates=[]){
        //key0,1分别表示一级、二级数据,x1一级分类x轴数据,x2表示二级分类x轴数据
        $res = ['0'=>[],'1'=>[],'2'=>[],'x0'=>[],'x1'=>[],'x2'=>[]];
        foreach ($allCates as $key => $catRow) {
            $cates = trim($catRow['cat_path'] . $catRow['cat_id'], ',');
            $catesArr = explode(',',$cates);
            $level = count($catesArr) -1;//level是几就说明此(level+1)值是几级分类

            if(isset($res[$level])) $res[$level][$catRow['cat_id']] = 0;
            if(isset($res['x' . $level])) $res['x' . $level][$catRow['cat_id']] = $catRow['name'];
        }
        // ee($res);
        return $res;
    }


    //获取商品所有分类列表
    function getAllGoodsCate(){
        $datas=[];
        $sql="SELECT o.cat_name name,o.cat_id,o.parent_id,o.cat_path 
                FROM sdb_b2c_goods_cat o 
                where o.disabled = 'false'
                ORDER BY o.p_order,o.cat_id";
        $res = $this->db->select($sql);
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                $datas[$value['cat_id']] = $value;
            }
        }
        return $datas;
    }

    /** 
    * 获取获取日均有效订单
    * @param string $num 订单数量
    * @param string $dates 日期
    * @param string $report 标签(year|month|day)
    */ 
    public static function getUsefulByDay($num, $dates, $report='day') 
    {
        $res = 0;
        $baseNum = 1;//被除数,默认是day取值的

        if (!in_array($report, ['year','month','day'])) {
            return $res;
        }

        if ($report == 'month') {
            $baseNum = date('t', strtotime($dates));
        }elseif ($report == 'year') {
            $baseNum = 365;
        }

        $res = round($num/$baseNum, 1);
        return $res;
    } 

    /** 
    * 获取时间范围(年月日) 
    * @param string $start 
    * @param string $end 
    */ 
    function getTimeRanges($start, $end, $report='day') 
    {
        $res = ['keyNum'=>[], 'keyTime'=>[], 'valueRate'=>[]];//keyNum数字key,keyTime时间key,valueRate值为0%
        $conf=[
            'year'  =>['dateformat'=>'Y'],
            'month' =>['dateformat'=>'Y-m'],
            'day'   =>['dateformat'=>'Y-m-d'],
        ];
        //转为时间戳 
        $st = strtotime($start);
        $et = strtotime($end);

        $t = $st;
        $i = 0;
        while($t <= $et) {
            //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
            $tempTime = date($conf[$report]['dateformat'],$t);
            $res['keyNum'][] = $tempTime;
            $res['keyTime'][$tempTime] = 0;
            // $res['valueRate'][$tempTime] = '0%';
            $t += strtotime('+1 ' . $report, $t)-$t;
            $i++;
        }
        return $res;
    } 
               /**
     * 获取供应商统计数据
     * @param params array $_GET
     * @param istable bool 是否只获取table数据
     */
    function get_supplier_data($params=[],$istable=false){
          $cat_name = $params['cat_name'];
        $datas = [];
        if (empty($params)) {
            return $datas;
        }
        $report = $params['report'] ?  $params['report'] : 'day';//按年月周日呈现(year,month,week,day)
        $target = $params['target'];//统计项

        $filter['last_modify|bthan'] = $this->timeFrom;
        $filter['last_modify|sthan'] = $this->timeTo;
        $whereStr = '';
        if (empty($params['time_from']) || empty($params['time_to'])) {
            return $datas;
        }

        //统计项检测是否合法
        if (!in_array($target, array_keys($this->supplierConfig))) {
            return $datas;
        }
        $datas['xdatas'] = [];
        $join = '';
        $orderBy = ' order by counts desc';
        $groupBy = '';
        $whereStr = '';
        if ($target == 'areaSupplierNum') {

            $fields = 'count(*) AS counts,o.goods_id,o.bn,o.name,od.supplier_id,SUBSTRING_INDEX(substring(od.area, 10), '.'"/"'.', 1)as p,SUBSTRING_INDEX(SUBSTRING_INDEX(od.area,'.'"/"'.', -2), '.'"/"'.', 1)as city, od.shortname,od.area,od.bn';
            if ($target == 'areaSupplierNum') {
                $join = 'LEFT JOIN sdb_b2c_supplier od ON od.supplier_id = o.supplier_id';
                $fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                $whereStr = " and o.supplier_id =od.supplier_id GROUP BY p";
            }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_goods` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'o')
                            . $whereStr
                            . $orderBy;
            $results = $this->db->selectLimit($sql,-1);
    
            $this->tidy_data($results, $fields);

        }
        //供应商商品top10排名
       if ($target == 'areaSupplierGoodsNum') {

            $fields = 'count(o.goods_id) AS counts,o.goods_id,o.bn,o.name,od.supplier_id,SUBSTRING_INDEX(substring(od.area, 10), '.'"/"'.', 1)as p,SUBSTRING_INDEX(SUBSTRING_INDEX(od.area,'.'"/"'.', -2), '.'"/"'.', 1)as city, od.shortname,od.area,od.bn';
            if ($target == 'areaSupplierGoodsNum') {
                $join = 'LEFT JOIN sdb_b2c_supplier od ON od.supplier_id = o.supplier_id';
                $fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                $whereStr = " and o.supplier_id =od.supplier_id GROUP BY supplier_id ";
            }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_goods` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'o')
                            . $whereStr
                            . $orderBy;
            $results = $this->db->selectLimit($sql,10);
            $this->tidy_data($results, $fields);
               // var_dump($results);
        }
       
       

        //供应商供货top10排名1
       if ($target == 'areaSupplierGoodsNumtopone') {
             $orderBy = ' order by m desc';
              $filters['createtime|bthan'] = $this->timeFrom;
              $filters['createtime|sthan'] = $this->timeTo;
            $fields = 'sum(nums) as m ,s.shortname,o.createtime';
            if ($target == 'areaSupplierGoodsNumtopone') {
                $join = ',sdb_b2c_order_items as oi, sdb_b2c_goods as g,
                sdb_b2c_supplier as s ';
                //$fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                $whereStr = " 
                              AND (o.payment = 'online'
                              OR o.payment = 'offline'
                              OR o.payment = 'period')
                              AND o.pay_status = 1
                              AND o.ship_status = 1
                              AND o.order_id = oi.order_id
                              AND oi.goods_id = g.goods_id
                              AND g.supplier_id = s.supplier_id
                                GROUP BY
                                        s.supplier_id
                                ";
                                                               }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_orders` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filters, 'o')
                            . $whereStr
                            . $orderBy;
                 
              $results = $this->db->select($sql,10);
         
            $this->tidy_data($results, $fields);
        }
         //供应商供货top10排名2
       if ($target == 'areaSupplierGoodsNumtoptwo') {
             $orderBy = ' order by a desc';
              $filter['promotion_type|noequal'] = 'bail';
              $filters['createtime|bthan'] = $this->timeFrom;
              $filters['createtime|sthan'] = $this->timeTo;
            $fields = 'sum(amount) as a ,s.shortname,o.createtime';
            if ($target == 'areaSupplierGoodsNumtoptwo') {
                $join = ',sdb_b2c_order_items as oi, sdb_b2c_goods as g,
                sdb_b2c_supplier as s ';
                //$fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                $whereStr = " 
                              AND (o.payment = 'online'
                              OR o.payment = 'offline'
                              OR o.payment = 'period')
                              AND o.pay_status = 1
                              AND o.ship_status = 1
                              AND o.order_id = oi.order_id
                              AND oi.goods_id = g.goods_id
                              AND g.supplier_id = s.supplier_id
                                GROUP BY
                                        s.supplier_id
                                ";
                                                               }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_orders` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filters, 'o')
                            . $whereStr
                            . $orderBy;

              $results = $this->db->select($sql,10);
            $this->tidy_data($results, $fields);
        }
           //供应商供货top10排名3
       if ($target == 'areaSupplierGoodsNumtopthree') {
             $orderBy = ' order by o desc';
              $filters['createtime|bthan'] = $this->timeFrom;
              $filters['createtime|sthan'] = $this->timeTo;
            $fields = 'count(oi.order_id) as o ,s.shortname,o.createtime';
            if ($target == 'areaSupplierGoodsNumtopthree') {
                $join = ',sdb_b2c_order_items as oi, sdb_b2c_goods as g,
                sdb_b2c_supplier as s ';
                //$fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                $whereStr = " 
                              AND (o.payment = 'online'
                              OR o.payment = 'offline'
                              OR o.payment = 'period')
                              AND o.pay_status = 1
                              AND o.ship_status = 1
                              AND o.order_id = oi.order_id
                              AND oi.goods_id = g.goods_id
                              AND g.supplier_id = s.supplier_id
                                GROUP BY
                                        s.supplier_id
                                ";
                                                               }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_orders` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filters, 'o')
                            . $whereStr
                            . $orderBy;

              $results = $this->db->select($sql,10);
            $this->tidy_data($results, $fields);
        }


          //供应商按一级分类所在省份集中度统计
       if ($target == 'areaSupplierGoodsNumcate') {
        
            $fields = 'count(od.supplier_id) AS
                       counts,o.goods_id,o.bn,
                       o.name,od.supplier_id,c.cat_id,c.cat_name,
                       SUBSTRING_INDEX(substring(od.area, 10), "/", 1)as p,
                       SUBSTRING_INDEX(SUBSTRING_INDEX(od.area,"/", -2), "/", 1)as city,
                       od.shortname,od.area,od.bn,od.supplier_id,
                       od.shortname,od.area,od.bn';
            if ($target == 'areaSupplierGoodsNumcate') {
                $join = 'LEFT JOIN sdb_b2c_supplier od ON od.supplier_id = o.supplier_id  LEFT JOIN sdb_b2c_goods_cat c ON c.cat_id = o.cat_id';
                $fields .= ',od.supplier_id,od.shortname,od.area,od.bn';
                 $whereStr = " and o.supplier_id =od.supplier_id 
                              and c.parent_id=0  and c.cat_name="."'水产海鲜'"."
                GROUP BY p";   
                if($cat_name){
                  $whereStr = " and o.supplier_id =od.supplier_id 
                              and c.parent_id=0 and  c.cat_name='".$cat_name."'
                GROUP BY p";   
                }
               
            }
            $sql ='select ' . $fields 
                            . ' from' 
                            . ' `sdb_b2c_goods` o ' 
                            . $join
                            . ' where ' 
                            . $this->_filter($filter, 'o')
                            . $whereStr
                            . $orderBy;
                           
            $results = $this->db->selectLimit($sql,10);
            $this->tidy_data($results, $fields);
           

        }
        if (empty($results)) {
            return $datas;
        }

        //只获取报表数据
        if ($istable) {
            return $datas;
        }
        //饼图
        if ($target == 'areaSupplierNum') {
            //求总和
            $totalNum = 0;
            foreach ($results as $trow) {
                $totalNum+=$trow['counts'];
            }
            $datas['totalNum'] = $totalNum;

            foreach ($results as $key=>$row) {
                $pie = [];
                //客户类别统计
                $keyName = '0';
                $pie['name'] = $row['p'];
                if($totalNum > 0) $pie['y'] = round(($row['counts']/$totalNum)*100, 2);
                $pie['num'] = $row['counts'];
                //第一个凸显出来
                if ($key == 0) {
                    $pie['sliced'] = true;
                    $pie['selected'] = true;
                }
                $datas[$target][$keyName][] = $pie;
            }
        }




        //条形图
         if ($target == 'areaSupplierGoodsNum') {
             //初始化X轴数据
        $datas['xdatas']=[];//x轴一级分类
        // $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        // $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($results as $ckey => $cval) {

            //x轴数据
            $datas['xdatas'][] = $cval['shortname'];
            //上架商品数统计

            //一级分类商品销售统计
            if ($target == 'areaSupplierGoodsNum') {
                $datas[$target][0][] = (int)$cval['counts'];
            }

        }
        
        }

         //供应商供货1
         if ($target == 'areaSupplierGoodsNumtopone') {
             //初始化X轴数据
        $datas['xdatas']=[];//x轴一级分类
        // $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        // $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($results as $ckey => $cval) {

            //x轴数据
            $datas['xdatas'][] = $cval['shortname'];
            //上架商品数统计

            //一级分类商品销售统计
            if ($target == 'areaSupplierGoodsNumtopone') {
                $datas[$target][0][] = (int)$cval['m'];
            }

        }
        
        }
        //供应商供货2
         if ($target == 'areaSupplierGoodsNumtoptwo') {
             //初始化X轴数据
        $datas['xdatas']=[];//x轴一级分类
        // $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        // $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($results as $ckey => $cval) {

            //x轴数据
            $datas['xdatas'][] = $cval['shortname'];
            //上架商品数统计

            //一级分类商品销售统计
            if ($target == 'areaSupplierGoodsNumtoptwo') {
                $datas[$target][0][] = (int)$cval['a'];
            }

        }
        
        }
         //供应商供货3
         if ($target == 'areaSupplierGoodsNumtopthree') {
             //初始化X轴数据
        $datas['xdatas']=[];//x轴一级分类
        // $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        // $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($results as $ckey => $cval) {

            //x轴数据
            $datas['xdatas'][] = $cval['shortname'];
            //上架商品数统计

            //一级分类商品销售统计
            if ($target == 'areaSupplierGoodsNumtopthree') {
                $datas[$target][0][] = (int)$cval['o'];
            }

        }
        
        }
       
        //供应商按一级分类所在省份集中度统计
         if ($target == 'areaSupplierGoodsNumcate') {
             //初始化X轴数据
        $datas['xdatas']=[];//x轴一级分类
        // $datas['xdatas']['xdata1Cates'] = array_values($dataInt['x1']);//x轴二级分类
        // $datas['xdatas']['xdata2Cates'] = $dataInt['x2'];//x轴三级分类(因为需要排序所以key为catid)
        // ee(sql());

        //初始化Y轴数据
        foreach ($results as $ckey => $cval) {
                           $count+=$cval['counts'];
            //x轴数据      
            $datas['xdatas'][] = $cval['p'];
            //上架商品数统计

            //一级分类商品销售统计
            if ($target == 'areaSupplierGoodsNumcate') {
                $datas[$target][0][] = (int)$cval['counts'];
            }

        }
         $datas['xdatas'][] = "全国";

         $datas[$target][0][] = (int)$count;
           $datas['cat_name'][] = $cat_name;
        }
       
       



         // //ee(sql(),0);
          // var_dump($datas);
        return $datas;
    }


}