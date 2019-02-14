<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['order_fund']  = array(
    'columns'=>array(
        'order_fund_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('preparesell_fund')->_('订单款项id'),
            'comment'=>app::get('preparesell_fund')->_('订单款项id'),
        ),

        'fund_id' =>
         array (
           'type' => 'table:preparesell_fund@preparesell',
           'label' => __('款项ID'),
           'width' => 110,
        ),

       'prepare_order_id' =>
        array (
          'type' => 'table:prepare_order@preparesell',
          'label' => __('预售订单ID'),
          'width' => 110,
        ),

        'order_id' =>
         array (
           'type' => 'table:orders@b2c',
           'label' => __('订单号'),
           'width' => 110,
         ),

        'prepare_id'=>array(
            'type'=>'number',
            'required' => true,
            'editable' => false,
            'in_list' => false,
            'label'=>app::get('preparesell_fund')->_('预售规则id'),
            'comment'=>app::get('preparesell_fund')->_('预售规则id'),
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
         'is_send_start'=>array (
          'type'=>'smallint(1)',
          'required' => false,
          'comment'=>app::get('preparesell_fund')->_('开始提醒是否已发送'),
          'label' => app::get('b2c')->_('开始提醒是否已发送'),
          'default' =>0,
        ),

        'is_send_end'=>array (
           'type'=>'smallint(1)',
           'required' => false,
           'comment'=>app::get('preparesell_fund')->_('结束提醒是否已发送'),
           'label' => app::get('b2c')->_('结束提醒是否已发送'),
           'default' =>0,
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

         'ctime'=>array(
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
