<?php
/**
*说明：付款单表-副表-合约付款单表
*注意：
*/
$db['payments_contract']=array (
  'columns' => array (
    'id' =>[
        'type'     => 'number',
        'required' => true,
        'pkey'     => true,
        'extra'    => 'auto_increment',
        'comment'  => app::get('b2c')->_('id'),
    ],
    'payment_id' => array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('ectools')->_('支付单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'contract_id' =>[
        'type'     => 'number',
        'default'       => '0',
        'required' => true,
        'comment'  => app::get('b2c')->_('合约id'),
        'label'    => app::get('b2c')->_('合约id'),
        'width'    => 150,
        'hidden'   => true,
    ],
    'contract_no' => [
        'type'   => 'varchar(50)',
        'required'      => true,
        'default'       => '',
        'comment'       => app::get('b2c')->_('合约号'),
        'label'         => app::get('b2c')->_('合约号'),
        'width'         => 100,
        'editable'      => true,
        'in_list'  => true,
        'searchtype' => 'has',
        'default_in_list' => true,
    ],
    'step_id' =>[
        'type'     => 'int(8)',
        'required' => true,
        'default'       => '0',
        'comment'  => app::get('b2c')->_('阶段id'),
        'label'    => app::get('b2c')->_('阶段id'),
        'width'    => 150,
        'hidden'   => true,
    ],
    'fee_ids' => [
        'type'     => 'varchar(255)',
        'required' => true,
        'default'  => '',
        'comment'  => app::get('b2c')->_('费种id集合'),
    ],

    'name' =>[
        'type'          => 'varchar(200)',
        'required'      => true,
        'default'       => '',
        'comment'       => app::get('b2c')->_('英文名'),
        'label'         => app::get('b2c')->_('英文名'),
        'is_title'      => true,
        'width'         => 200,
        'searchtype'    => 'has',
        'editable'      => true,
        'filtertype'    => 'custom',
        'filterdefault' => true,
        'in_list'         => true,
        'default_in_list' => true,
    ],

    'title' =>[
        'type'          => 'varchar(200)',
        'required'      => true,
        'default'       => '',
        'comment'       => app::get('b2c')->_('阶段标题'),
        'label'         => app::get('b2c')->_('阶段标题'),
        'is_title'      => true,
        'width'         => 200,
        'searchtype'    => 'has',
        'editable'      => true,
        'filtertype'    => 'custom',
        'filterdefault' => true,
        'in_list'         => true,
        'default_in_list' => true,
    ],

    'step_amount' => [
        'type' => 'money',
        'default' => '0',
        'required' => true,
        'editable' => false,
        'comment' => app::get('b2c')->_('阶段总额'),
        'label' => app::get('b2c')->_('阶段总额'),
        'width' => 75,
        'filtertype' => 'number',
        'filterdefault' => true,
        'in_list' => true,
        'default_in_list' => true,
    ],

    'ctime' =>[
        'type' => 'datetime',
        'required' => true,
        'default' => 0,
        'comment' => app::get('b2c')->_('创建时间'),
    ],


    'disabled' =>[
      'type'     => 'bool',
      'default'  => 'false',
      'comment'  => app::get('b2c')->_('是否立即开启'),
      'editable' => false,
    ],
  ),
  'index' =>[
      'ind_payment_id' =>[
          'columns' => [0 => 'payment_id'],
      ],
  ],
  'engine' => 'innodb',
  'version' => '$Rev: 43384 $',
  'comment' => app::get('ectools')->_('合约付款单表'),
);
