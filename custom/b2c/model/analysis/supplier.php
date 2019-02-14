<?php
class b2c_mdl_analysis_supplier extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->analysis = app::get('ectools')->model('analysis');
    }
  function makeTimeRange($filter){
        $this->timeFrom = strtotime(trim($filter['time_from']));
        $this->timeTo = strtotime(trim($filter['time_to']));
        $addTimeArr = ['day'=>86400,'month'=>date('t', $this->timeTo)*86400,'year'=>31536000];
        $tmpReport = $_GET['report'];
        if (in_array($tmpReport, array_keys($addTimeArr))) {
            $this->timeTo+=$addTimeArr[$tmpReport];
        }
    }

    function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $ordersModel = app::get('b2c')->model('orders');
        $ordersColumns = $ordersModel->schema['columns'];
        $this->makeTimeRange($filter);
        $orderBy  = ' order by  s.supplier_id desc';
         $groupBy = " group by pd.product_id";
        
        $baseWhere = [];
        $filter['createtime|bthan'] = $this->timeFrom;
        $filter['createtime|sthan'] = $this->timeTo;

        //供应商编号
        if (!empty($filter['supplierbn'])) {
            $supplierbn = $filter['supplierbn'];
            $baseWhere[] = "s.bn LIKE '%{$supplierbn}%'";
            unset($filter['supplierbn']);
        }  
        //供应商简称
        if (!empty($filter['shortname'])) {
            $shortname = $filter['shortname'];
            $baseWhere[] = "shortname LIKE '%{$shortname}%'";
            unset($filter['shortname']);
        }  

        //商编号
        if (!empty($filter['bn'])) {
            $bn = $filter['bn'];
            $baseWhere[] = "g.bn LIKE '%{$bn}%'";
            unset($filter['bn']);
        }  

        //商编名称
        if (!empty($filter['name'])) {
            $name = $filter['name'];
            $baseWhere[] = "g.name LIKE '%{$name}%'";
            unset($filter['name']);
        }  

      //商品分类
        if (!empty($filter['cat_name'])) {
            $baseWhere[] = "gc.cat_name LIKE '%". $filter['cat_name'] ."%'";
            unset($filter['cat_name']);
        }

        //原产地
        if (!empty($filter['gsource_area'])) {
            $baseWhere[] = "pv.gsource_area LIKE '%". $filter['gsource_area'] ."%'";
            unset($filter['gsource_area']);
        }
       
        $whereStr .= $this->_filter($filter, 'o', $baseWhere);
        //SQL
        $join = '';
        $fields = 'g.goods_id as goods_id,s.bn as supplierbn,s.shortname as shortname,o.order_id,o.payment,o.createtime,o.ship_status,o.cost_item,o.final_amount,SUBSTRING_INDEX(substring(s.area, 10), '.'"/"'.', 1)as p,SUBSTRING_INDEX(SUBSTRING_INDEX(s.area,'.'"/"'.', -2), '.'"/"'.', 1)as city,
            SUBSTRING_INDEX(SUBSTRING_INDEX(s.area,'.'"/"'.', -1),":", 1)as area,
            od.nums as nums,
            od.cost  as cost,
            od.price  as price,
            count(od.order_id)    as om,
            rt.number as number';
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
        // $whereStr.= "   and (o.payment = 'online'
        //                 OR o.payment = 'offline'
        //                 OR o.payment = 'period')
        //                 AND o.pay_status = 1
        //                 AND o.ship_status = 1";
       
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
              //付款状态
            if (isset($ordersColumns['pay_status']['type'][$o['pay_status']])) {
                $datas[$k]['pay_status'] = $ordersColumns['pay_status']['type'][$o['pay_status']];
            }

            //发货状态
            if (isset($ordersColumns['ship_status']['type'][$o['ship_status']])) {
                $datas[$k]['ship_status'] = $ordersColumns['ship_status']['type'][$o['ship_status']];
            }

         

            //支付方式
            if (isset($ordersColumns['payment']['type'][$o['payment']])) {
                $datas[$k]['payment'] = $ordersColumns['payment']['type'][$o['payment']];
            }
            $datas[$k]['profit'] = ($o['price']-$o['cost'])*$o['nums'];//利润
            $datas[$k]['amount'] = ($o['price']*$o['nums']);//销售额
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
                'supplierbn' => [
                    'label' => app::get('b2c')->_('供应商编号'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                     'filtertype' => 'normal',
                    'filterdefault' => 'true',
                    'realtype' => 'varchar(50)',
                    'order' =>'1',
                ],
              
                'shortname' => [
                    'label' => app::get('b2c')->_('供应商简称'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'2',
                ],

                
                'bn' => [

                    'label' => app::get('b2c')->_('货号'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'3',
                ],
                'name' => [
                    'label' => app::get('b2c')->_('商品名称'),
                    'searchtype' => 'has',
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'4',
                ],
                'cat_name' => [
                    'label' => app::get('b2c')->_('商品分类'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'5',
                ],
                 'unit' => [
                    'label' => app::get('b2c')->_('单位'),
                    'default_in_list' => true,
                    'order' =>'6',

                ],
                'spec_info' => [
                    'label' => app::get('b2c')->_('商品规格'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'7',

                ],
                'nums' => [
                    'label' => app::get('b2c')->_('销售量'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'8',

                ],

                'number' => [
                    'label' => app::get('b2c')->_('退换货量'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'9',

                ],
                'returnRate' => [
                    'label' => app::get('b2c')->_('退换货率'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'10',

                ],
                 'om' => [
                    'label' => app::get('b2c')->_('销售量对应订单量'),
                    'in_list' => true,
                    'default_in_list' => true,
                    'order' =>'11',

                ],
                'cost' => [
                    'label' => app::get('b2c')->_('成本价'),
                    'default_in_list' => true,
                    'order' =>'12',

                ],
                'price' => [
                    'label' => app::get('b2c')->_('销售价'),
                    'default_in_list' => true,
                    'order' =>'13',

                ],
                'amount' => [
                    'label' => app::get('b2c')->_('销售额'),
                    'default_in_list' => true,
                    'order' =>'14',

                ],
                'profit' => [
                    'label' => app::get('b2c')->_('利润'),
                    'default_in_list' => true,
                    'order' =>'15',

                ],
               
                'brand_name' => [
                    'label' => app::get('b2c')->_('品牌'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'16',

                ],
                'gsource_area' => [
                    'label' => app::get('b2c')->_('原产地'),
                    'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'17',

                ],
                'p' => [
                    'label' => app::get('b2c')->_('所在省'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'18',

                ],
                'city' => [
                    'label' => app::get('b2c')->_('所在市'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'19',

                ],
                  'area' => [
                    'label' => app::get('b2c')->_('所在区'),
                    // 'searchtype' => 'has',
                    'default_in_list' => true,
                    'order' =>'20',

                ],

            ],
            'in_list' =>array(
                0 => 'supplierbn',
                1 => 'shortname',
                2 => 'bn',
                3 => 'name',
                4 => 'cat_name',
                5 => 'unit',
                6 => 'spec_info',
                7 => 'nums',
                8 => 'number',
                9 => 'returnRate',
                10 => 'om',
                11 => 'cost',
                12 => 'price',
                13 => 'amount',
                14 => 'profit',
                15 => 'brand_name',
                16 => 'gsource_area',
                17 => 'p',
                18 => 'city',
                19 => 'area',
           ),
            'default_in_list' =>array(
                0 => 'supplierbn',
                1 => 'shortname',
                2 => 'bn',
                3 => 'name',
                4 => 'cat_name',
                5 => 'unit',
                6 => 'spec_info',
                7 => 'nums',
                8 => 'number',
                9 => 'returnRate',
                10 => 'om',
                11 => 'cost',
                12 => 'price',
                13 => 'amount',
                14 => 'profit',
                15 => 'brand_name',
                16 => 'gsource_area',
                17 => 'p',
                18 => 'city',
                19 => 'area',
            ),
        ];
        return $schema;
   }
}


