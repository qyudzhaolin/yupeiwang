<?php
/**
 * 说明：supplier model
 */
class b2c_mdl_shiparea extends dbeav_model{

    var $hasAllCountry = true;//配送范围是否含有全国
    function __construct($app){
        parent::__construct($app);
        $this->_shiparea = $_COOKIE['shiparea'] ? $_COOKIE['shiparea'] : null;//配送区域
        // $this->_shiparea = '0';//配送区域
        // ee($this->_shiparea);
    }


     function getAll(){
        if(base_kvstore::instance('b2c_goods')->fetch('goods_supplier.data', $contents) !== false){

            if(!is_array($contents)){
                if(($result=json_decode($contents,true))){
                    return json_decode($contents,true);
                }else{
                    return $this->brand2json(true);
                }
            }else{
                    return $contents;
            }
        }else{
            return $this->brand2json(true);
        }
    }
     

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        return $datas;
   }


    function save( &$data,$mustUpdate = null, $mustInsert = false){
        $rs = parent::save($data,$mustUpdate);
        return $rs;
    }

    //用户收货城市是否与首页确认的配送范围匹配
    function receiveAddressIsMatchShiparea($region_id=0){
        $errmsg = '友情提示：<br> 此处收货城市与您首页确认的收货城市不匹配';
        $res = ['error'=>1,'msg'=>$errmsg];
        if (!isset($this->_shiparea) || $this->_shiparea === '') {
            return $res;
        }

        if (empty($region_id)) {
            return $res;
        }

        $oregion = app::get('ectools')->model('regions');
        $children = $oregion->getAllChild($this->_shiparea);
        // ee($children);
        if (!in_array($region_id, $children)) {
            return $res;
        }

        $res = ['error'=>0,'msg'=>''];
        return $res;
    }


    //获取配送区域[desktop使用]
    function getShiparea($params=[]){
        $datas = [];
        $filter = [];
        $orderBy = '';
        $groupBy = ' group by r.region_id';
        $join = '';
        if (empty($params['goods_id'])) {
            return $datas;
        }
        $filter['goods_id|nequal'] = $params['goods_id'];
        $fields = 's.region_id,r.local_name name';
        $join .= 'LEFT JOIN sdb_ectools_regions r ON r.region_id = s.region_id';

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_shiparea` s ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 's')
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql(),0);
        if (empty($datas)) {
            return $datas;
        }
        foreach ($datas as $key=>$data) {
            if ($data['region_id'] == 0) {
                $datas[$key]['name'] = '全国';
            }
        }
        return $datas;
    }

    //保存商品配送范围
    function saveShiparea($datas=[]){
        $wheres = [];
        if (empty($datas['goods_id'])) {
            return false;
        }
        if ($datas['shiparea'] === '') {
            return false;
        }
        $datas['goods_id'] = (array)$datas['goods_id'];
        $wheres['goods_id|in'] = $datas['goods_id'];
        //之前添加的先删除
        if ($this->count($wheres)) {
            $this->remove($wheres);
        }
        // $this->db->mark();

        //保存配送范围
        $shiparea = explode(',', $datas['shiparea']);
        $ctime = time();
        $sqlArr = [];
        $goodsIds = $datas['goods_id'];
        foreach ($goodsIds as $goodsId) {
            foreach ($shiparea as $area) {
                $data=[];
                $data['product_id'] = $product_id = $datas['product_id'] ? $datas['product_id'] : '0';
                $data['goods_id'] = $goods_id = $goodsId;
                $data['region_id'] = $region_id = $area;
                $data['last_modify'] = $last_modify =  $ctime;

                $sqlArr[] = "({$product_id},{$goods_id},{$region_id},{$last_modify})";
            }
        }

        //批量插入
        if (!empty($sqlArr)) {
            $sqlStr = implode(',', $sqlArr);
            $sql = "INSERT INTO `sdb_b2c_shiparea` ( `product_id`,`goods_id`,`region_id`,`last_modify` ) VALUES " . $sqlStr;
            $this->db->exec($sql);
            // $this->db->mark($sql);
        }
        return true;
    }

    //根据goodsIDS集合批量设置配送范围
    function batchUpdateArray($goods_id, $updateValue){
        $result = $this->saveShiparea(['goods_id'=>$goods_id,'shiparea'=>$updateValue]);
        return $result;
    }



    //获取商品配送区域[site使用，有商品的区域名称才显示]
    function getGoodsRegions($iswap = false){
        $datas = [];
        $filter = [];
        $orderBy = '';
        $groupBy = ' group by r.region_id';
        $join = '';
        $filter['region_grade|in'] = [2];//默认：如果配送商品有全国范围只显示市
        if ($iswap) {
            $filter['region_grade|in'] = [1,2];//手机端是需要省市数据的,作tree使用
        }

        //首先查看有没有某些商品配送区域是全国的,如果没有则只显示有配送区域的省市，如果有则显示全国所有省市
        //新增前提：可配送全国的商品必须上架
        $asql = "SELECT s.goods_id FROM `sdb_b2c_shiparea` s INNER JOIN sdb_b2c_goods g";
        $asql .= " ON g.goods_id = s.goods_id and g.marketable='true' WHERE s.region_id='0'";
        $arows = $this->db->selectLimit($asql,-1);
        if (empty($arows)) {
            $join .= ' INNER JOIN sdb_b2c_shiparea s ON s.region_id = r.region_id';
            $join .= " INNER JOIN sdb_b2c_goods g ON g.goods_id = s.goods_id and g.marketable='true'";
            $filter['region_grade|in'] = [1,2];//如果没有一个商品配送范围是全国的则显示省市
            $this->hasAllCountry = false;
        }

        $fields = 'r.region_id id,p_region_id pid,local_name name,pinyin,letter,region_grade as level';

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_ectools_regions` r ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'r')
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql());
        if (empty($datas)) {
            return $datas;
        }
        return $datas;
    }

    //配送范围数据，wap端使用,弃用
    function getwapareas_old(){
        // $tree = [];
        // //配送地区
        // $shipareaModel = app::get('b2c')->model('shiparea');
        // $regionsModel = app::get('ectools')->model('regions');
        // $cityDataTmp = $shipareaModel->getGoodsRegions(true);
        // $provinceDataTmp = $regionsModel->get_regions_data(1,1);

        // $provinceData = [];
        // foreach ($provinceDataTmp as $pro) {
        //     $provinceData[$pro['id']] = $pro;
        // }

        // //生成树格式
        // foreach ($cityDataTmp as $city) {
        //     $pid = $city['pid'];
        //     if (empty($pid)) {
        //         $tree[$city['id']] = $city;
        //     }else{
        //         if (!empty($provinceData[$pid])) {
        //             if(!isset($tree[$pid])) $tree[$pid] = $provinceData[$pid];
        //             $tree[$pid]['child'][] = $city;
        //         }
        //     }
        // }
        // // ee($tree);
        // $tree = array_values($tree);
        // return $tree;
    }



    //配送范围数据，wap端使用
    function getwapareas(){
        $tree = [];
        $cityData = [];
        //配送地区
        $shipareaModel = app::get('b2c')->model('shiparea');
        $regionsModel = app::get('ectools')->model('regions');
        $cityDataTmp = $shipareaModel->getGoodsRegions();
        if (empty($cityDataTmp)) {
            return $tree;
        }
        if (!$shipareaModel->hasAllCountry) {
            //获取所需省下面所有市(获取level为1、2级别)
            $regionsTree = [];
            $provinceData = [];//省id数组

            foreach ($cityDataTmp as $c) {
                if ($c['level'] == '1') {
                    $provinceData[] = $c['id'];
                }

                //处理成key为region_id
                if ($c['level'] == '2') {
                    $cityData[$c['id']] = $c;
                }
            }

            if (!empty($provinceData)) {
                $regions = [];
                $provinceDataStr = implode(',', $provinceData);
                $regionsTmp = $regionsModel->get_regions_data(2,2,"and p_region_id in({$provinceDataStr})");
                if (!empty($regionsTmp)) {
                    //与$cityData进行合并
                    foreach ($regionsTmp as $reg) {
                        $cityData[$reg['id']] = $reg;
                    }
                    $cityDataTmp = $cityData;
                    ksort($cityDataTmp);
                    // ee($cityDataTmp);
                }

            }

        }

        //生成树格式
        foreach ($cityDataTmp as $city) {
            $tree[$city['letter']][] = [
                'id'=>$city['id'],
                'name'=>$city['name'],
                'letter'=>$city['letter']
            ];
        }
        ksort($tree);
        $tree = array_values($tree);
        return $tree;
    }

    /**
     * 说明：根据前端传递过来的region_id获取商品ids集合
     * @params int $region_id 区域id
     * @return array 商品|货品id集合,默认返回goods_id集合
     */
    function getGoodsIdsByregionId($region_id=''){
        $datas = [];
        $regionIds = [];

        if ($region_id === '') {
            return $datas;
        }
        $regionsModel = app::get('ectools')->model('regions');

        //构造regionsIds集合
        //先查看region_grade,若值为1则获取下面所有市IDS,若值为2则直接根据region_id获取商品ids集合
        $regionIds = [$region_id];//默认设置一个id[传递过来的自身id](省或市id)
        $row = $regionsModel->dump(['region_id'=>$region_id]);
        // ee($row);
        if (empty($row)) {
            return $datas;
        }

        $rows = [];
        //获取此省下面所有市region_id集合
        if ($row['region_grade'] == '1') {
            $rows = $regionsModel->getList('region_id',['p_region_id'=>$region_id]);
            if (!empty($rows)) {
                foreach ($rows as $v) {
                    $regionIds[] = $v['region_id'];
                }
            }
        }elseif ($row['region_grade'] == '2') {
            //增加上一级(即省级ID)
            $regionArr = explode(',', trim($row['region_path'],','));
            if (isset($regionArr[0])) {
                $regionIds[] = $regionArr[0];
            }
        }

        // ee($regionIds);

        //根据regionIds集合查询出所有商品ids && 默认把配送范围是全国的取出来
        $regionIds = implode(',', $regionIds);
        $sql = "SELECT goods_id FROM `sdb_b2c_shiparea` s WHERE s.region_id in ({$regionIds}) OR s.region_id = '0'";
        $goodsIds = $this->db->selectLimit($sql,-1);
        if (!empty($goodsIds)) {
            foreach ($goodsIds as $goodsId) {
                $datas[$goodsId['goods_id']] = $goodsId['goods_id'];
            }
        }
        // ee(sql());
        // ee($datas);
        return $datas;
    }


    /**
     * 说明：生成筛选条件,根据原有的filter
     * @params $filter 过滤条件
     * @params $fix bool 是否需要修饰
     * @return array 返回设置后的条件(根据cookie 传递过来的shiparea)
     */
    function makeGoodsfilterByShiparea(&$filter,$fix=true){
       return;
       if (!isset($this->_shiparea) || $this->_shiparea === '') {
           return;
       }
       $key = $fix ? 'goods_id|in' : 'goods_id';

       $goodsIds = $this->getGoodsIdsByregionId($this->_shiparea);
       // mark($goodsIds,0);
       if (isset($filter['goods_id'])) {
           $filter[$key] = array_intersect((array)$filter['goods_id'], (array)$goodsIds);
       }else{
           $filter[$key] = $goodsIds;
       }

       //为了防止筛选后结果为空，导致不生成where goods_id in(xxxx)，所以需要添加一个随机商品id过滤
       if (empty($filter[$key])) {
           $filter[$key] = [201805221412];
       }
    }




    /**
     * 根据配送区域，生成product_id集合(site 1.预售列表使用)
     * 说明：生成货品筛选条件,根据原有的filter(生成product_id集合过滤条件,原理是join goods表根据goodsids过滤product_ids)
     * @params $filter 过滤条件
     * @params $fix bool 是否需要修饰
     * @return array 返回设置后的条件(根据cookie 传递过来的shiparea)
     */
    function makeProductfilterByShiparea(&$filter,$fix=true){
       if (!isset($this->_shiparea) || $this->_shiparea === '') {
           return;
       }
       $key = $fix ? 'product_id|in' : 'product_id';
       $goodsIds = $this->getGoodsIdsByregionId($this->_shiparea);

       if (empty($goodsIds)) {
           $filter[$key] = [201805221412];
       }

       $goodsIds = implode(',', $goodsIds);
       $productIds = [];
       $sql = "SELECT product_id FROM `sdb_b2c_products` p 
               INNER JOIN `sdb_b2c_goods` g ON g.goods_id = p.goods_id 
               WHERE g.goods_id in ({$goodsIds})";
        // ee($sql);
       $productRows = $this->db->selectLimit($sql,-1);
       if (!empty($productRows)) {
           foreach ($productRows as $productRow) {
               $productIds[$productRow['product_id']] = $productRow['product_id'];
           }
       }

       // mark($goodsIds,0);
       if (isset($filter['product_id'])) {
           $filter[$key] = array_intersect((array)$filter['product_id'], (array)$productIds);
       }else{
           $filter[$key] = $productIds;
       }

       //为了防止筛选后结果为空，导致不生成where product_id in(xxxx)，所以需要添加一个随机商品id过滤
       if (empty($filter[$key])) {
           $filter[$key] = [201805221412];
       }
    }


}
