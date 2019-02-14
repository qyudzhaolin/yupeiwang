<?php
/**
 * 说明：supplier model
 */
class b2c_mdl_supplier extends dbeav_model{
    var $defaultOrder = array('ordernum',' DESC');

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

   function brand2json($return=false){
        @set_time_limit(600);
        $contents=$this->db->select('select supplier_id,shortname from  sdb_b2c_supplier  ORDER BY supplier_id desc');
        if($return){
            base_kvstore::instance('b2c_goods')->store('goods_supplier.data',$contents);
            return $contents;
        }else{
            return base_kvstore::instance('b2c_goods')->store('goods_supplier.data',$contents);
        }
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
     
    function get_shortname($brand_id){
        $list = $this->getRow("shortname",array('supplier_id'=>$supplier_id));
        return $list['shortname'];
    }
    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        return $datas;
    }


    function save( &$data,$mustUpdate = null, $mustInsert = false){
        $rs = parent::save($data,$mustUpdate);
        $this->supplier2json();
        return $rs;
    }

   
    function supplier2json($return=false){
        @set_time_limit(600);
        $contents=$this->db->select('SELECT * FROM sdb_b2c_supplier WHERE disabled = \'false\' order by ordernum desc');
        if($return){
            base_kvstore::instance('b2c_goods')->store('goods_supplier.data',$contents);
            return $contents;
        }else{
            return base_kvstore::instance('b2c_goods')->store('goods_supplier.data',$contents);
        }
    }

    

    function delete($filter,$subSdf = 'delete'){
        $rs =  parent::delete($filter);
        $this->supplier2json();
        return $rs;
    }

    function pre_recycle($rows){
    // 	$oGoods = $this->app->model('goods');
    // 	if(is_array($rows)){
	   //  	foreach($rows as $bk=>$bv){
				// $csupplier = $oGoods->count(array('supplier_id'=>$bv['supplier_id']));
				// if($csupplier >0){
	   //               $this->recycle_msg = app::get('desktop')->_('所有供应商');
	   //               return false;
				// }
	   //  	}
    // 	}
        return true;
    }

    //获取供应商注册项加载
    public function get_supplier_attr($supplier_id=null){
        if($supplier_id){
            $supplier_model = $this->app->model('supplier');
            $mem = $supplier_model->dump($supplier_id);
        }
        $supplier_model = $this->app->model('supplier');
        $mem_schema = $supplier_model->_columns();
        $attr =array();
        foreach($this->app->model('supplier_attr')->getList() as $item)
        {
            if(isset($supplier_id)){
                if($item['attr_show'] == "true"||$item['attr_edit'] == "true") $attr[] = $item; //筛选个人信息显示项
            }else{
                if($item['attr_show'] == "true") $attr[] = $item; //筛选注册显示项
            }
        }
        foreach((array)$attr as $key=>$item)
        {
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath)
            {
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1)
                {
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                        foreach($a_temp  as $value){
                            $name .= '['.$value.']';
                        }
                }
            }
            else
            {
                $name = $item['attr_column'];
            }
            if($mem && $item['attr_group'] == 'defalut'){
                switch($attr[$key]['attr_column']){
                case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                }
            }

            if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
                $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
                if($item['attr_sdfpath'] == ""){
                    $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
                    if($attr[$key]['attr_type'] =="checkbox"){
                        $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
                    }
                }
            }

            if($attr[$key]['attr_type'] == 'select' ||$attr[$key]['attr_type'] == 'checkbox'){
                $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
                $attr[$key]['attr_values']= implode('，',$attr[$key]['attr_value']);
            }

            $attr[$key]['attr_column'] = $name;
            if($attr[$key]['attr_column']=="birthday"){
                $attr[$key]['attr_column'] = "profile[birthday]";
            }
        }

        return $attr;
    }//end function


}
