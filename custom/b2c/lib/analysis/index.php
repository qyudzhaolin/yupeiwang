<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_analysis_index extends ectools_analysis_abstract implements ectools_analysis_interface  
{
    protected $charts_action = 'chart_view_scale';
    public $detail_options = array(
        'hidden' => false,
        'force_ext' => false,
    );
    public $logs_options = array(
        'order' => array(   
            'name' => '订单量统计',
            'flag' => array(),
        ),
        'sales' => array(
            'name' => '销售额统计',
            'flag' => array(),
        ),
        'profit' => array(
            'name' => '利润统计',
            'flag' => array(),
        ),
    );

    //统计项目
    public $targets = array(
        'order' => array(   
            'name' => '订单量统计',
        ),
        'sales' => array(
            'name' => '销售额统计',
        ),
        'profit' => array(
            'name' => '利润统计',
        )
    );

    public $graph_options = array(
        'hidden' => false,//TODO
        'iframe_height' => 300,
        'iframe_width' => '100%',

    );

    function makeTimeRange(&$filter){
        $this->timeFrom = strtotime(trim($filter['time_from']));
        $this->timeTo = strtotime(trim($filter['time_to']));
        $addTimeArr = ['day'=>86400,'month'=>date('t', $this->timeTo)*86400,'year'=>31536000];
        $tmpReport = $filter['report'];
        if (in_array($tmpReport, array_keys($addTimeArr))) {
            $this->timeTo+=$addTimeArr[$tmpReport];
        }
        $filter['time_from'] = $this->timeFrom;
        $filter['time_to'] = $this->timeTo;
    }

    public function ext_detail(&$datas){
        $filter = $this->_params;
        $statDatas = $this->app->model('statdata')->get_stat_datas($filter,['scale']);

        $datas = [
            '总销售额'=>[
                'value'=>$statDatas['salesAmount'],
                'memo'=>'总销售额',
                'icon'=>'coins_add.gif',
            ],
            // '退款额'=>[
            //     'value'=>$statDatas['reshipAmount'],
            //     'memo'=>'退款额',
            //     'icon'=>'coins_go.gif',
            // ],
            '总利润'=>[
                'value'=>$statDatas['profitAmount'],
                'memo'=> '“总销售额”减去“总退款额”',
                'icon'=>'coins.gif',
            ],
            '总订单量'=>[
                'value'=>$statDatas['allOrder'],
                'memo'=> '总订单量',
                'icon'=>'application_add.gif',
            ],
            '总有效订单量'=>[
                'value'=>$statDatas['usefulOrderNum'],
                'memo'=> '总有效订单量',
                'icon'=>'application_key.gif',
            ],
        ];
    }


    public function ext_detail_Old(&$datas){
        $datas = [];
        $filter = $this->_params;
        $saleObj = $this->app->model('analysis_sale');
        $this->makeTimeRange($filter);
        $payMoney = $saleObj->get_pay_money($filter);
        $refundMoney = $saleObj->get_refund_money($filter);
        $earn = $payMoney-$refundMoney;

        $shopsaleObj = $this->app->model('analysis_shopsale');
        $order = $shopsaleObj->get_order($filter); //全部
        $orderAll = $order['saleTimes'];

        $datas = [
            '收款额'=>[
                'value'=>$payMoney,
                'memo'=>'收款额',
                'icon'=>'coins_add.gif',
            ],
            '退款额'=>[
                'value'=>$refundMoney,
                'memo'=>'退款额',
                'icon'=>'coins_go.gif',
            ],
            '收入'=>[
                'value'=>$earn,
                'memo'=> '“收款额”减去“退款额”',
                'icon'=>'coins.gif',
            ],
            '新增订单'=>[
                'value'=>$orderAll,
                'memo'=> '新增订单',
                'icon'=>'application_add.gif',
            ],
        ];
    }

    public function ext_detail8(&$detail){
        $detail = array();
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?strtotime($filter['time_from']):'';
        $filter['time_to'] = isset($filter['time_to'])?(strtotime($filter['time_to'])+86400):'';

        $saleObj = $this->app->model('analysis_sale');
        $payMoney = $saleObj->get_pay_money($filter);
        $refundMoney = $saleObj->get_refund_money($filter);
        $earn = $payMoney-$refundMoney;

        $detail[app::get('b2c')->_('收入')]['value']= $earn;
        $detail[app::get('b2c')->_('收入')]['memo']= app::get('b2c')->_('“收款额”减去“退款额”');
        $detail[app::get('b2c')->_('收入')]['icon'] = 'coins.gif';

        $shopsaleObj = $this->app->model('analysis_shopsale');
        $filterOrder = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
        );
        $filterShip = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
            'ship_status' => 1,
        );
        $filterPay = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
            'pay_status' => 1,
        );

        $order = $shopsaleObj->get_order($filterOrder); //全部
        $orderAll = $order['saleTimes'];
        
        $orderShip = $shopsaleObj->get_order($filterShip); //已发货
        $orderShip = $orderShip['saleTimes'];
        
        $orderPay = $shopsaleObj->get_order($filterPay); //已支付
        $orderPay = $orderPay['saleTimes'];


        $detail[app::get('b2c')->_('新增订单')]['value']= $orderAll;
        $detail[app::get('b2c')->_('新增订单')]['memo']= app::get('b2c')->_('新增加的订单数量');
        $detail[app::get('b2c')->_('新增订单')]['icon'] = 'application_add.gif';
        $detail[app::get('b2c')->_('付款订单')]['value']= $orderPay;
        $detail[app::get('b2c')->_('付款订单')]['memo']= app::get('b2c')->_('付款的订单数量');
        $detail[app::get('b2c')->_('付款订单')]['icon'] = 'application_key.gif';
        $detail[app::get('b2c')->_('发货订单')]['value']= $orderShip;
        $detail[app::get('b2c')->_('发货订单')]['memo']= app::get('b2c')->_('发货的订单数量');
        $detail[app::get('b2c')->_('发货订单')]['icon'] = 'application_go.gif';

        $memObj = $this->app->model('members');
        $filterMem = array(
            'regtime' => 'true',
            'regtime_from' => date('Y-m-d',$filter['time_from']),
            'regtime_to' => date('Y-m-d',$filter['time_to']),
            '_regtime_search' => 'between',
            '_DTIME_' => array(
                'H'=>array('regtime_from'=>'00','regtime_to'=>'00'),
                'M'=>array('regtime_from'=>'00','regtime_to'=>'00')
            ),
        );

        $memberNewadd = $memObj->count($filterMem);
        $memberNum = $memObj->count();

        $detail[app::get('b2c')->_('新增会员')]['value']= $memberNewadd;
        $detail[app::get('b2c')->_('新增会员')]['memo']= app::get('b2c')->_('新增加的会员数量');
        $detail[app::get('b2c')->_('新增会员')]['icon'] = 'folder_user.gif';
        $detail[app::get('b2c')->_('会员总数')]['value']= $memberNum;
        $detail[app::get('b2c')->_('会员总数')]['memo']= app::get('b2c')->_('网店会员总数');
        $detail[app::get('b2c')->_('会员总数')]['icon'] = 'group_add.gif';
    }


    public function finder()
    {
        return array(
            'model' => 'b2c_mdl_analysis_sale',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href' => 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]=b2c&_params[mdl]=b2c_mdl_analysis_sale',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('经营概况'),
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }
}//End Class