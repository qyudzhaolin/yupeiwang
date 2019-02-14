<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_analysis_goods extends ectools_analysis_abstract implements ectools_analysis_interface
{
    protected $charts_action = 'chart_view_goods';//商品统计
    //3区
    public $detail_options = array(
        'hidden' => false,
    );
    public $graph_options = array(
        'hidden' => false,//TODO
        'iframe_height' => 300,
        'iframe_width' => '100%',

    );

    public $logs_options = array(
        '1' => array(
            'name' => '商品总数',
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
        'onlineGoodsNum' => array(   
            'name' => '上架商品数统计',
        ),
        'oneCateSales' => array(
            'name' => '一级分类商品销售统计',
        ),
        'threeCateSalesTop' => array(
            'name' => '三级分类商品销售排行(top10)',
        ),
        'threeCateReturnTop' => array(
            'name' => '三级分类商品退换货排行(top10)',
        ),
    );

    public function ext_detail(&$datas){
        $filter = $this->_params;
        $statDatas = $this->app->model('statdata')->get_stat_datas($filter,['goods']);
        $datas = [
            '商品总数'=>[
                'value'=>$statDatas['onlineGoodsNum'],
                'memo'=>'商品总数(统计所有时间段)',
                'icon'=>'add.gif',
            ],
        ];
    }

    public function get_logs($time){
        $filter = array(
            'time_from' => $time,
            'time_to' => $time+86400,
        );
        $saleObj = $this->app->model('analysis_sale');
        $payMoney = $saleObj->get_pay_money($filter);
        $refundMoney = $saleObj->get_refund_money($filter);
        $earn = $payMoney-$refundMoney;

        $result[] = array('type'=>0, 'target'=>1, 'flag'=>0, 'value'=>$payMoney);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>0, 'value'=>$refundMoney);
        $result[] = array('type'=>0, 'target'=>3, 'flag'=>0, 'value'=>$earn);

        return $result;
    }

    public function finder()
    {
        return array(
            'model' => 'b2c_mdl_analysis_productsale',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href' => 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]=b2c&_params[mdl]=b2c_mdl_analysis_productsale',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('商品统计'),//1区标题
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }
}
