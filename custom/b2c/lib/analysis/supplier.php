<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_analysis_supplier extends ectools_analysis_abstract implements ectools_analysis_interface
{
    protected $charts_action = 'chart_view_supplier';//商品统计
    //3区
    public $detail_options = array(
        'hidden'=>false,
    );
    public $graph_options = array(
        'hidden' => false,//TODO
        'iframe_height' => 300,
        'iframe_width' => '100%',

    );

    public $logs_options = array(
        '1' => array(
            'name' => '供应商',
            'flag' => array(),
            'memo' => '',
            'icon' => 'money.gif',
        ),
    );


    //6区控制显示
    public $finder_options = array(
        'hidden' => false,
    );


    //统计项目
    public $targets = array(
        'areaSupplierNum' => array(   
            'name' => '区域供应商数量',
        ),
        'areaSupplierGoodsNum' => array(
            'name' => '供应商商品top10排名',
        ),
          'areaSupplierGoodsNumtopone' => array(
            'name' => '供应商供货top10排名',
        ),
         'areaSupplierGoodsNumtoptwo' => array(
            'name' => '供应商销售金额top10',
        ),
         'areaSupplierGoodsNumtopthree' => array(
            'name' => '供应商销售单量top10',
        ),
          'areaSupplierGoodsNumcate' => array(
            'name' => '供应商按一级分类所在省份集中度统计',
        ),    
    );
  
    public function ext_detail(&$datas){
        $filter = $this->_params;
         $statDatas = $this->app->model('statdata')->get_stat_datas($filter,['supplier']);
        $datas = [
            '区域搜索供应商总数'=>[
                'value'=>$statDatas['supplierNum'],
                'memo'=>'区域搜索供应商总数',
                'icon'=>'group_add.gif',
            ],
        ];
       
     
    }






    public function finder()
    {
        return array(
            'model' => 'b2c_mdl_analysis_supplier',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href' => 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]=b2c&_params[mdl]=b2c_mdl_analysis_supplier',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('供应商统计 (注：以实际供货商品数据为纬度进行统计)'),//1区标题
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }

}
