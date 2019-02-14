<?php
/**
 * 说明：networksarea model
 */
class b2c_mdl_networksarea extends dbeav_model{

    var $hasAllCountry = true;//网点区域是否含有全国
    function __construct($app){
        parent::__construct($app);
        $this->_networksarea = $_COOKIE['networksarea'] ? $_COOKIE['networksarea'] : null;//网点区域
        // ee($this->_networksarea);
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


    //获取网点区域[desktop使用]
    function getNetworksarea($params=[]){
        $datas = [];
        $filter = [];
        $orderBy = '';
        $groupBy = ' group by r.region_id';
        $join = '';
        if (empty($params['networks_id'])) {
            return $datas;
        }
        $filter['networks_id|nequal'] = $params['networks_id'];
        $fields = 's.region_id,r.local_name name';
        $join .= 'LEFT JOIN sdb_ectools_regions r ON r.region_id = s.region_id';

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_networksarea` s ' 
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
        // ee($datas);
        return $datas;
    }

    //获取区域网点并且格式化
    //例如：江苏：南京市、南通市
    function getNetworksareaFormat($params=[]){
        $networksareaDatas = [];
        $datas = [];
        $tmpdatas = $this->getNetworksarea($params);
        if (empty($tmpdatas)) {
            return $tmpdatas;
        }

        //格式化成以region_id作为key
        foreach ($tmpdatas as $key => $tmpdata) {
            $networksareaDatas[$tmpdata['region_id']] = $tmpdata;
        }

        //如果是全国
        if (isset($networksareaDatas[0])) {
            return ['全国'];
        }
        // ee($networksareaDatas);

        //地区树
        $regionsModel = app::get('ectools')->model('regions');
        $regions = $regionsModel->get_regions_data();
        $regionsTree = $regionsModel->gettree($regions);
        // ee($regionsTree);

        //开始筛选
        foreach ($regionsTree as $tree) {
            $children = [];
            $province = $tree['name'];
            //如果存在省
            if (isset($networksareaDatas[$tree['id']])) {
                $datas[] = $province . "：全部";
            }else{
                //查看是否有旗下面的市级元素
                foreach ($tree['children'] as $child) {
                    if (isset($networksareaDatas[$child['id']])) {
                        $children[] = $child['name'];
                    }
                }

                if (!empty($children)) {
                    $datas[] = $province . '：' . implode('、', $children);
                }
            }
        }
        return $datas;
    }

    //保存网点网点区域
    function saveNetworksarea($datas=[]){
        $wheres = [];
        if (empty($datas['networks_id'])) {
            return false;
        }

        if ($datas['networksarea'] === '') {
            return false;
        }

        $datas['networks_id'] = (array)$datas['networks_id'];
        $wheres['networks_id|in'] = $datas['networks_id'];
        //之前添加的先删除
        if ($this->count($wheres)) {
            $this->remove($wheres);
        }
        // $this->db->mark();

        //保存网点区域
        $networksarea = explode(',', $datas['networksarea']);
        $ctime = date('Y-m-d H:i:s');
        $sqlArr = [];
        $networksIds = $datas['networks_id'];
        foreach ($networksIds as $networksId) {
            foreach ($networksarea as $area) {
                $data=[];
                $data['networks_id'] = $networks_id = $networksId;
                $data['region_id'] = $region_id = $area;
                $data['ctime'] = $ctime =  $ctime;
                $sqlArr[] = "({$networks_id},{$region_id},'{$ctime}')";
            }
        }

        //批量插入
        if (!empty($sqlArr)) {
            $sqlStr = implode(',', $sqlArr);
            $sql = "INSERT INTO `sdb_b2c_networksarea` ( `networks_id`,`region_id`,`ctime` ) VALUES " . $sqlStr;
            $res = $this->db->exec($sql);
            // ee($sql);
            if ($res) {
                return true;
            }
            // $this->db->mark($sql);
        }
        return false;
    }

    //获取网点networks_ids集合的所有区域region_ids集合(一般网点的ids集合是users表的networks值)
    function getRegionIdsByNetworksIds($networks_ids=''){
        $datas = [];
        $res = [];
        if (empty($networks_ids)) {
            return $datas;
        }
        if (is_array($networks_ids)) {
            $networks_ids = implode(',', $networks_ids);
        }

        $networks_ids = trim($networks_ids);
        $wheres['networks_id|in'] = $networks_ids;
        $rows = $this->getList('region_id', $wheres);

        if (empty($rows)) {
            return $datas;
        }

        //地区树
        $regionsModel = app::get('ectools')->model('regions');
        $regions = $regionsModel->get_regions_data(1,3);
        $regionsTree = $regionsModel->gettree($regions);
        // ee($regionsTree);

        foreach ($rows as $row) {
            //如果该网点区域是"全国"范围，则直接反回空数组
            if ($row['region_id'] == '0') {
                return [];
            }

            //开始筛选
            foreach ($regionsTree as $tree) {
                $children = [];
                //如果存在省则获取该省id和其下面所有children的id
                if ($row['region_id'] == $tree['id']) {
                    $res[] = $tree['id'];

                    //添加旗下面的所有市级元素
                    foreach ($tree['children'] as $child) {
                        $res[] = $child['id'];
                        //则补全3级
                        foreach ($child['children'] as $child3) {
                            $res[] = $child3['id'];
                        }
                    }
                }else{
                    //若该region_id不是省，则查看是否有旗下面的市级元素
                    foreach ($tree['children'] as $child) {
                        if ($row['region_id'] == $child['id']) {
                            $res[] = $child['id'];
                            //则补全3级
                            foreach ($child['children'] as $child3) {
                                $res[] = $child3['id'];
                            }
                        }
                    }
                }
            }

        }
        $res = array_unique($res);
        // ee($res);
        return $res;
    }    


    //根据networksIDS集合批量设置网点区域
    function batchUpdateArray($networks_id, $updateValue){
        $result = $this->saveShiparea(['networks_id'=>$networks_id,'networksarea'=>$updateValue]);
        return $result;
    }


    //获取网点区域
    function getGoodsRegions($iswap = false){
        $datas = [];
        $filter = [];
        $orderBy = '';
        $groupBy = ' group by r.region_id';
        $join = '';
        $filter['region_grade|in'] = [2];//默认：如果配送网点有全国范围只显示市
        if ($iswap) {
            $filter['region_grade|in'] = [1,2];//手机端是需要省市数据的,作tree使用
        }

        //首先查看有没有某些网点网点区域是全国的,如果没有则只显示有网点区域的省市，如果有则显示全国所有省市
        //新增前提：可配送全国的网点必须上架
        $asql = "SELECT s.networks_id FROM `sdb_b2c_networksarea` s INNER JOIN sdb_b2c_goods g";
        $asql .= " ON g.networks_id = s.networks_id and g.marketable='true' WHERE s.region_id='0'";
        $arows = $this->db->selectLimit($asql,-1);
        if (empty($arows)) {
            $join .= ' INNER JOIN sdb_b2c_networksarea s ON s.region_id = r.region_id';
            $join .= " INNER JOIN sdb_b2c_goods g ON g.networks_id = s.networks_id and g.marketable='true'";
            $filter['region_grade|in'] = [1,2];//如果没有一个网点网点区域是全国的则显示省市
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


    //网点区域数据，wap端使用
    function getwapareas(){
        $tree = [];
        $cityData = [];
        //配送地区
        $networksareaModel = app::get('b2c')->model('networksarea');
        $regionsModel = app::get('ectools')->model('regions');
        $cityDataTmp = $networksareaModel->getGoodsRegions();
        if (empty($cityDataTmp)) {
            return $tree;
        }
        if (!$networksareaModel->hasAllCountry) {
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
     * 说明：根据前端传递过来的region_id获取网点ids集合
     * @params int $region_id 区域id
     * @return array 网点|货品id集合,默认返回networks_id集合
     */
    function getGoodsIdsByregionId($region_id=''){
        $datas = [];
        $regionIds = [];

        if ($region_id === '') {
            return $datas;
        }
        $regionsModel = app::get('ectools')->model('regions');

        //构造regionsIds集合
        //先查看region_grade,若值为1则获取下面所有市IDS,若值为2则直接根据region_id获取网点ids集合
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

        //根据regionIds集合查询出所有网点ids && 默认把网点区域是全国的取出来
        $regionIds = implode(',', $regionIds);
        $sql = "SELECT networks_id FROM `sdb_b2c_networksarea` s WHERE s.region_id in ({$regionIds}) OR s.region_id = '0'";
        $goodsIds = $this->db->selectLimit($sql,-1);
        if (!empty($goodsIds)) {
            foreach ($goodsIds as $goodsId) {
                $datas[$goodsId['networks_id']] = $goodsId['networks_id'];
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
     * @return array 返回设置后的条件(根据cookie 传递过来的networksarea)
     */
    function makeGoodsfilterByShiparea(&$filter,$fix=true){
       if (!isset($this->_networksarea) || $this->_networksarea === '') {
           return;
       }
       $key = $fix ? 'networks_id|in' : 'networks_id';

       $goodsIds = $this->getGoodsIdsByregionId($this->_networksarea);
       // mark($goodsIds,0);
       if (isset($filter['networks_id'])) {
           $filter[$key] = array_intersect((array)$filter['networks_id'], (array)$goodsIds);
       }else{
           $filter[$key] = $goodsIds;
       }

       //为了防止筛选后结果为空，导致不生成where networks_id in(xxxx)，所以需要添加一个随机网点id过滤
       if (empty($filter[$key])) {
           $filter[$key] = [201805221412];
       }
    }




    /**
     * 根据网点区域，生成product_id集合(site 1.预售列表使用)
     * 说明：生成货品筛选条件,根据原有的filter(生成product_id集合过滤条件,原理是join goods表根据goodsids过滤product_ids)
     * @params $filter 过滤条件
     * @params $fix bool 是否需要修饰
     * @return array 返回设置后的条件(根据cookie 传递过来的networksarea)
     */
    function makeProductfilterByShiparea(&$filter,$fix=true){
       if (!isset($this->_networksarea) || $this->_networksarea === '') {
           return;
       }
       $key = $fix ? 'product_id|in' : 'product_id';
       $goodsIds = $this->getGoodsIdsByregionId($this->_networksarea);

       if (empty($goodsIds)) {
           $filter[$key] = [201805221412];
       }

       $goodsIds = implode(',', $goodsIds);
       $productIds = [];
       $sql = "SELECT product_id FROM `sdb_b2c_products` p 
               INNER JOIN `sdb_b2c_goods` g ON g.networks_id = p.networks_id 
               WHERE g.networks_id in ({$goodsIds})";
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

       //为了防止筛选后结果为空，导致不生成where product_id in(xxxx)，所以需要添加一个随机网点id过滤
       if (empty($filter[$key])) {
           $filter[$key] = [201805221412];
       }
    }


}
