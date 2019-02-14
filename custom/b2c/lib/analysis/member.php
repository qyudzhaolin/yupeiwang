<?php
class b2c_analysis_member extends ectools_analysis_abstract implements ectools_analysis_interface{
    
    protected $charts_action = 'chart_view_member';//会员统计
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

    );

    //6区控制显示
    public $finder_options = array(
        'hidden' => false,
    );


    //统计项目
    public $targets = array(
        'memberCate' => array(   
            'name' => '客户类别统计',
        ),
        'memberOrder' => array(
            'name' => '客户订单top10排名统计',
        ),
    );

    public function ext_detail(&$datas){
        $filter = $this->_params;
        $statDatas = $this->app->model('statdata')->get_stat_datas($filter,['member']);

        $datas = [
            '新增客户数'=>[
                'value'=>$statDatas['memberNum'],
                'memo'=>'新增客户数',
                'icon'=>'group_add.gif',
            ],
        ];
    }

    public function finder(){
        return array(
            'model' => 'b2c_mdl_analysis_member',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href' => 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[custom]=b2c&_params[mdl]=b2c_mdl_analysis_member',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('客户统计'),
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
                'orderBy'=>'saleTimes DESC',//这里默认写死按订单量倒叙
            ),
        );
    }

}
