<?php
/**
*说明：合约-费种dbschema
*注意：
*/
$db['contract_order'] = [
    'columns'=>[
    	'id' =>[
	      'type' => 'mediumint(8)',
	      'required' => true,
	      'pkey' => true,
	      'extra'=>'auto_increment',
	    ],
       'order_id' =>[
	      'type' => 'table:orders@b2c',
	      'label' => __('订单号'),
	      'width' => 110,
	    ],

        'contract_no'=>[
        'type' => 'varchar(255)',
        'required' => true,
        'default' =>'',
        'editable' => true,
        'in_list' => true,
        'is_title' => true,
        'default_in_list' => false,
        'label'=>app::get('b2c')->_('合约号'),
        'comment'=>app::get('b2c')->_('合约号'),
        'order' => 12,
        'width' => 100,
        ],

        'member_id' =>[
          'type' => 'table:members@b2c',
          'label' => app::get('b2c')->_('会员用户名'),
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ],
        'contract_id' =>
	    [
	      'type' => 'mediumint(8)',
	      'default' => '0',
	      'label' => __('合约ID'),
	      'width' => 75,
	    ],

        'step_id' =>[
          'type' => 'mediumint(8)',
          'default' => '0',
          'label' => __('阶段ID'),
          'comment' => __('阶段ID'),
          'width' => 75,
        ],        

        'step_type' =>[
            'type' =>[
                'out' => app::get('b2c')->_('出库'),
                'other' => app::get('b2c')->_('非出库'),
            ],
            'default' => 'other',
            'required' => true,
            'label' => app::get('b2c')->_('阶段类型'),
            'comment'=>app::get('b2c')->_('阶段类型'),
            'editable' => false,
            'in_list' => false,
        ],

        'extra' =>[
            'type'     => 'tinyint(1)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('是否支付临时附加费'),
            'label'    => app::get('b2c')->_('是否支付临时附加费'),
        ],

        'state'=>[
            'type'=>'bool',
            'default' => 'true',
            'in_list' => true,
            'editable' => false,
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_('状态'),
            'comment'=>app::get('b2c')->_('状态'),
            'order'=>10,
            'width' => 50,
        ],

        'begin_time'=>[
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 7,
            'label'=>app::get('b2c')->_('开始时间'),
            'comment'=>app::get('b2c')->_('开始时间'),
        ],
        'end_time'=>[
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 8,
            'label'=>app::get('b2c')->_('结束时间'),
            'comment'=>app::get('b2c')->_('结束时间'),
        ],
    ],
    'index' =>[
        'contract_no' =>[
            'columns' => [0 => 'contract_no',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('合约订单表'),
];
