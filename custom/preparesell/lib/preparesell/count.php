<?php
class preparesell_preparesell_count {
    //获取用户购买数量
    public function get_member_count($member_id, $product_id, $preparesell_id)
    {
        $mdl_count_member_buy = app::get('preparesell')->model('count_member_buy');
        $filter = array(
            'member_id'=>$member_id,
            'product_id'=>$product_id,
            'preparesell_id'=>$preparesell_id,
        );
        $ret = $mdl_count_member_buy->getRow('cid,count,member_id,product_id', $filter);
        return $ret;
    }

    //用于释放订单
    public function cut_count($preparesell_id, $member_id, $product_id, $cut_count)
    {
        $mdl_count_member_buy = app::get('preparesell')->model('count_member_buy');
        $filter = array(
            'member_id'=>$member_id,
            'preparesell_id'=>$preparesell_id,
            'product_id'=>$product_id,
        );
        $sdf = $mdl_count_member_buy->getRow('*', $filter);
        if($sdf == null)
        {
            return true;
        }else{
            $sdf['count'] = $sdf['count'] - $cut_count;
        }
        if($sdf['count'] < 0)
        {
            $sdf['count'] = 0;
        }
        $ret = $mdl_count_member_buy->save($sdf);

        $preparesell_info = $this->get_preparesell_info($product_id, 'preparesell_id,`limit`');
        if( $sdf['count'] < $preparesell_info['limit'] )
        {
            $keyStarBuy = 'starBuy_' . $preparesell_id . '_' . $member_id . '_' . $product_id;
            base_kvstore::instance('cache/expires')->store($keyStarBuy, '');
        }
        return $ret;
    }

    //添加用户购买数量
    public function add_count($member_id, $product_id, $add_count)
    {
        $isStarLimit  = 'none';
        $preparesell_info = $this->get_preparesell_info($product_id, 'preparesell_id,`limit`');
        $preparesell_id = $preparesell_info['preparesell_id'];
        $keyStarBuy = 'starBuy_' . $preparesell_id . '_' . $member_id . '_' . $product_id;

        base_kvstore::instance('cache/expires')->fetch($keyStarBuy, $isStarLimit);

        if('hasbuy' == $isStarLimit){
            return false;
        }

        $mdl_count_member_buy = app::get('preparesell')->model('count_member_buy');
        $sql = "update sdb_preparesell_count_member_buy set `count`=`count`+{$add_count} where `product_id`={$product_id} and `member_id`={$member_id} and `preparesell_id`={$preparesell_id}";
        $mdl_count_member_buy->db->exec($sql);
        if($mdl_count_member_buy->db->affect_row()==0)
        {
            $sql = "insert into sdb_preparesell_count_member_buy(preparesell_id, product_id, member_id,count) values({$preparesell_id}, {$product_id}, {$member_id}, {$add_count})";
            $mdl_count_member_buy->db->exec($sql);
        }

        //$filter = array('product_id'=>$product_id, 'preparesell_id'=>$preparesell_id, 'member_id'=>$member_id);
        //$count_info = $mdl_count_member_buy->getRow('count', $filter);
        $sql = "SELECT `count` FROM `sdb_preparesell_count_member_buy` WHERE product_id={$product_id} AND preparesell_id={$preparesell_id} AND member_id={$member_id}";
        $count_info = $mdl_count_member_buy->db->selectrow($sql);
        if( $count_info['count'] > $preparesell_info['limit'] )
        {
            return false;
        }else{
            if( $count_info['count'] == $preparesell_info['limit'] )
            {
                base_kvstore::instance('cache/expires')->store($keyStarBuy, 'hasbuy');
            }
            return true;
        }

    }

    //获取活动信息
    public function get_preparesell_info($product_id, $columns='preparesell_id', $time=null)
    {
        if($time == null)
        {
            $time = time();
        }
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $preparesell_goods_filter = array(
            'product_id' => $product_id,
            'end_time|bthan'=>$time,
            'begin_time|sthan'=>$time,
        );
        $preparesell_goods = $mdl_preparesell_goods->getRow($columns, $preparesell_goods_filter);
        return $preparesell_goods;
    }

    //检查用户是否可以购买
    public function check_count($member_id, $product_id, $number)
    {
        $preparesell_info = $this->get_preparesell_info($product_id, 'preparesell_id,`limit`');
        $preparesell_id = $preparesell_info['preparesell_id'];
        $limit = $preparesell_info['limit'];
        $member_buy_info = $this->get_member_count($member_id, $product_id,$preparesell_id);
        $count = $member_buy_info['count'];
        if($count + $number <= $limit)
        {
            return true;
        }else{
            return false;
        }
    }

    //批量获取活动货品
    public function get_preparesell_products($fmt_check_products, $sdf_time=null)
    {
        if($sdf_time == null)
        {
            $time = time();
        }else{
            $time = $sdf_time;
        }
        foreach($fmt_check_products as $product_id=>$num)
        {
            $product_ids[$product_id] = $product_id;
        }
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $preparesell_goods_filter = array(
            'product_id|in' => $product_ids,
            'end_time|bthan'=>$time,
            'begin_time|sthan'=>$time,
        );
        $columns = 'preparesell_id,`limit`,product_id';
        $preparesell_goods = $mdl_preparesell_goods->getList($columns, $preparesell_goods_filter);
        foreach($preparesell_goods as $v)
        {
            $fmt_preparesell_id[$v['preparesell_id']] = $v['preparesell_id'];
            $fmt_preparesell_goods[$v['preparesell_id']][$v['product_id']] = $v;
        }
        if($sdf_time == null)
        {
            $mdl_preparesell = app::get('preparesell')->model('preparesell');
            $preparesells = $mdl_preparesell->getList('preparesell_id,status', array('preparesell_id|in'=>$fmt_preparesell_id));
            foreach($preparesells as $s)
            {
                if($s['status']=='false')
                {
                    unset($fmt_preparesell_goods[$s['preparesell_id']]);
                }
            }
        }
         if(empty($fmt_preparesell_goods)){
           return null;
         }
        $preparesell_goods=array();
        foreach ($fmt_preparesell_goods  as $key=>$preparesell){
            foreach($preparesell as $val){
                $preparesell_goods[]=$val;
            }
        }
        return $preparesell_goods;
    }
}

