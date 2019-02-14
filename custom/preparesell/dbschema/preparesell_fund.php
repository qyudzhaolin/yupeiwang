<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['preparesell_fund']  = array(
    'columns'=>array(
        'fund_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('preparesell_fund')->_('款项id'),
            'comment'=>app::get('preparesell_fund')->_('款项id'),
        ),

        'prepare_id'=>array(
            'type'=>'number',
            'required' => true,
            'editable' => false,
            'in_list' => false,
            'label'=>app::get('preparesell_fund')->_('预售id'),
            'comment'=>app::get('preparesell_fund')->_('预售id'),
        ),
        'goods_id'=>array(
            'type'=>'number',
            'label'=>app::get('b2c')->_('商品id'),
            'comment'=>app::get('b2c')->_('商品id'),
        ),
       


       'fund_name'=>array (
         'type'=> array(
              'n' =>app::get('b2c')->_('选择款项'),
              'y' =>app::get('b2c')->_('预付款'),
              'z' =>app::get('b2c')->_('中期进度款'),
              'w'=>app::get('b2c')->_('尾款'),
         ),
        'required' => false,
        'label' => app::get('b2c')->_('款项名'),
        'width' => 80,
        'editable' => true,
        'default' =>'n',
        'in_list' => true,
        'default_in_list' => true,
        'filtertype' => 'normal',
      ),



         'payment' =>array (
          'type' =>'money',
          'default' =>'0',
          'required' => true,
          'label' => app::get('b2c')->_('付款金额'),
          'comment' => app::get('b2c')->_('付款金额'),
           'width' => 75,
           'editable' => false,
           'filtertype' => 'number',
           'in_list' => true,
           ),
         'begin_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 7,
            'label'=>app::get('preparesell_fund')->_('开始时间'),
            'comment'=>app::get('preparesell_fund')->_('开始时间'),
        ),
        'end_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 8,
            'label'=>app::get('preparesell_fund')->_('结束时间'),
            'comment'=>app::get('preparesell_fund')->_('结束时间'),
        ),
          'remind_time_send'=>array(
            'type'=>'time',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell_fund')->_('发送开始提醒时间'),
            'comment'=>app::get('preparesell_fund')->_('发送开始提醒时间'),
            'order'=>16 ,
            'width' => 100,
        ),
        'remind_time_send_end'=>array(
            'type'=>'time',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell_fund')->_('发送结束提醒时间'),
            'comment'=>app::get('preparesell_fund')->_('发送结束提醒时间'),
            'order'=>16 ,
            'width' => 100,
        ),

         'status'=>array (
           'type'=> array(
                'true' =>app::get('b2c')->_('已开启'),
                'false' =>app::get('b2c')->_('未开启'),
                'finish' =>app::get('b2c')->_('结束'),
           ),
          'required' => true,
          'label' => app::get('b2c')->_('款项名'),
          'comment'=>app::get('preparesell_fund')->_('启用状态'),
          'width' => 50,
          'editable' => true,
          'default' =>'false',
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'yes',
        ),


        'time_out'=>array(
            'type'=>'number',
            'label'=>app::get('b2c')->_('超时时间'),
            'comment'=>app::get('b2c')->_('超时时间'),
        ),

         'time'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 7,
            'label'=>app::get('preparesell_fund')->_('添加时间'),
            'comment'=>app::get('preparesell_fund')->_('添加时间'),
        ),
       
    ),
);
