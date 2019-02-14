<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// function theme_widget_footer_information2(&$setting,&$smarty){
//     $setting['order'] or $setting['order'] = 'desc';
//     $setting['order_type'] or $setting['order_type'] = 'pubtime';
//     $orderby = $setting['order_type'].' '.$setting['order'];
//     $func = array('asc'=>'ksort','desc'=>'krsort');

//     $oMAI = app::get('content')->model('article_indexs');

//     $filter['ifpub'] = 'true';
//     $filter['pubtime|lthan'] = time();
//     $filter['article_id'] = $setting['article_id'];
//     $arr = $oMAI->getList('*',$filter,0,-1,$orderby);

//     $tmp['indexs'] = $arr;
//     $tmp['__stripparenturl'] = $setting['stripparenturl'];

//     $nodeItem= kernel::single('content_article_node')->get_node($setting['node_id']);
//     $tmp['node_name'] = $nodeItem['node_name'];
//     if( $tmp['homepage']=='true' )
//         $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$setting['node_id']) );
//     else
//         $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$setting['node_id']) );
//     return $tmp;
// }

function theme_widget_footer_information(&$setting,&$smarty){
    $mdl_article = app::get('content')->model('article_indexs');
    //获取合约商品
    $groupBy = ' group by a.article_id';
    $join    = '';
    $orderBy = ' order by a.pubtime DESC';
    $filter['disabled'] = 'false';
    $baseWhere=[];
    $fields  = 'a.article_id,a.title,n.node_name,a.node_id';
    $join .= ' LEFT JOIN sdb_content_article_nodes n ON n.node_id = a.node_id';
    $baseWhere[] = "n.node_id in(1,33,39,40)";
    $sql ='select ' . $fields 
                    . ' from' 
                    . ' `sdb_content_article_indexs` a ' 
                    . $join
                    . ' where ' 
                    . $mdl_article->_filter($filter, 'a', $baseWhere)
                    . $groupBy
                    . $orderBy;
    $datas = $mdl_article->db->selectLimit($sql,-1);
    // ee(sql());
    if (empty($datas)) {
        return $datas;
    }

    $res = [];
    foreach ($datas as $key => $value) {
        if(!isset($res[$value['node_id']])) {
            $res[$value['node_id']]['node_id'] = $value['node_id'];
            $res[$value['node_id']]['node_name'] = $value['node_name'];
            $res[$value['node_id']]['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$value['node_id']) );
        }
        $res[$value['node_id']]['datas'][] = [
            'article_id' =>$value['article_id'],
            'title'      =>$value['title'],

        ];
    }
    ksort($res);
    // ee($res);
    return $res;
}
