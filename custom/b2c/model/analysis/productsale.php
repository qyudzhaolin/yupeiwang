<?php
class b2c_mdl_analysis_productsale extends dbeav_model{
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
        $orderBy  = ' order by nums desc';
        $groupBy = " group by pd.product_id";//这里按商品统计
        $whereStr = '';
        $baseWhere = [];
        $filter['promotion_type|noequal'] = 'bail';
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;

        //商品货号
        if (!empty($filter['bn'])) {
            $baseWhere[] = "g.bn LIKE '%". $filter['bn'] ."%'";
            unset($filter['bn']);
        }

        //商品分类
        if (!empty($filter['cat_name'])) {
            $baseWhere[] = "gc.cat_name LIKE '%". $filter['cat_name'] ."%'";
            unset($filter['cat_name']);
        }

        //品牌
        if (!empty($filter['brand_name'])) {
            $baseWhere[] = "b.brand_name LIKE '%". $filter['brand_name'] ."%'";
            unset($filter['brand_name']);
        }

        //原产地
        if (!empty($filter['gsource_area'])) {
            $baseWhere[] = "pv.gsource_area LIKE '%". $filter['gsource_area'] ."%'";
            unset($filter['gsource_area']);
        }

        //供应商简称
        if (!empty($filter['shortname'])) {
            $baseWhere[] = "s.shortname LIKE '%". $filter['shortname'] ."%'";
            unset($filter['shortname']);
        }

        //供应商编号
        if (!empty($filter['supplier_bn'])) {
            $baseWhere[] = "s.bn LIKE '%". $filter['supplier_bn'] ."%'";
            unset($filter['supplier_bn']);
        }

        $whereStr .= $this->_filter($filter, 'o', $baseWhere);

        //SQL
        $join = '';
        $fields = 'o.order_id,o.payment,o.createtime,o.ship_status,o.cost_item,o.final_amount';
        $fields .= ',g.cat_id,g.bn,`g`.`name`,od.goods_id,od.amount,od.cost,od.price,od.nums';
        $fields .= ',pd.unit,pd.spec_info,b.brand_name,pv.gsource_area,s.shortname,s.bn supplier_bn';
        $fields .= ',rt.reship_id,rt.number,gc.cat_name';
        $fields .= ',sum(od.amount) totalAmount,sum(od.nums) totalNums,
                    sum((ifnull(od.price,0)-ifnull(od.cost,0))*od.nums) as profit';
        // $fields .= ',sum(od.cost*od.nums) as totalCost';

        $join .= ' LEFT JOIN sdb_b2c_orders o ON o.order_id = od.order_id';
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
                        . $whereStr
                        . $groupBy;

        $datas = $this->db->selectLimit($sql,$limit,$offset);

        if ($cols == 'getCount') {
            return count($datas);
        }

        foreach ($datas as $k=>$o) {
            $datas[$k]['profit']= round($o['profit'], 2);
            $datas[$k]['totalAmount']= round($o['totalAmount'], 2);
            $datas[$k]['returnRate'] = '0%';
            if($o['nums'] > 0) $datas[$k]['returnRate'] = round(($o['number']/$o['nums'])*100, 2) . '%';//退换货率

        }
        // ee($whereStr,0);
        // ee(sql(),0);
        // ee($datas,0);
        // $this->totalNum = count($datas);

        return $datas;
    }

    public function count($filter=null){
        $totalnum = $this->getlist('getCount', $filter);
        return $totalnum;
    }

    public function get_schema(){
        $schema = [
            'columns' => [
                'bn' => [
                    'label' => app::get('b2c')->_('商品货号'),
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
                    'default_in_list' => true,
                    'order' =>'3',
                ],

                'number' => [
                    'label' => app::get('b2c')->_('退换货量'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'5',

                ],
                'returnRate' => [
                    'label' => app::get('b2c')->_('退换货率'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'6',

                ],

                'cost' => [
                    'label' => app::get('b2c')->_('成本价'),
                    'default_in_list' => true,
                    'order' =>'7',

                ],
                'price' => [
                    'label' => app::get('b2c')->_('销售价'),
                    'default_in_list' => true,
                    'order' =>'8',

                ],
                'totalNums' => [
                    'label' => app::get('b2c')->_('销售量'),
                    'default_in_list' => true,
                    'order' =>'9',

                ],
                'totalAmount' => [
                    'label' => app::get('b2c')->_('销售额'),
                    'default_in_list' => true,
                    'order' =>'10',

                ],

                'profit' => [
                    'label' => app::get('b2c')->_('利润'),
                    'default_in_list' => true,
                    'order' =>'11',

                ],
                'unit' => [
                    'label' => app::get('b2c')->_('单位'),
                    'default_in_list' => true,
                    'order' =>'12',

                ],
                'spec_info' => [
                    'label' => app::get('b2c')->_('商品规格'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'13',

                ],
                'brand_name' => [
                    'label' => app::get('b2c')->_('品牌'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'14',

                ],
                'gsource_area' => [
                    'label' => app::get('b2c')->_('原产地'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'15',

                ],
                'shortname' => [
                    'label' => app::get('b2c')->_('供应商简称'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'16',

                ],
                'supplier_bn' => [
                    'label' => app::get('b2c')->_('供应商编号'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'17',

                ],

            ],
            'in_list' =>array(
                0 => 'bn',
                1 => 'name',
                2 => 'cat_name',
                4 => 'number',
                5 => 'returnRate',
                6 => 'cost',
                7 => 'price',
                8 => 'totalNums',
                9 => 'totalAmount',
                10 => 'profit',
                11 => 'unit',
                12 => 'spec_info',
                13 => 'brand_name',
                14 => 'gsource_area',
                15 => 'shortname',
                16 => 'supplier_bn',
           ),
            'default_in_list' =>array(
                0 => 'bn',
                1 => 'name',
                2 => 'cat_name',
                4 => 'number',
                5 => 'returnRate',
                6 => 'cost',
                7 => 'price',
                8 => 'totalNums',
                9 => 'totalAmount',
                10 => 'profit',
                11 => 'unit',
                12 => 'spec_info',
                13 => 'brand_name',
                14 => 'gsource_area',
                15 => 'shortname',
                16 => 'supplier_bn',
            ),
        ];
        return $schema;
   }
}
