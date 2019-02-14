<?php
/**
*说明：合约-结算阶段dbschema
*注意：
*/
$db['contract_account_step']= [
    'columns' => [
        'step_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
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

        'extra' =>[
            'type'     => 'tinyint(1)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('是否包含临时附加费'),
            'label'    => app::get('b2c')->_('是否包含临时附加费'),
        ],
        'contract_id' =>[
            'type' => 'table:contract',
            'label' => app::get('b2c')->_('合约id'),
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
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

        'sorting' =>[
            'type'     => 'smallint(3)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('排序'),
            'label'    => app::get('b2c')->_('排序'),
            'in_list' => true,
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

        'end_time' =>[
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('截止日期'),
        ],

        'state' =>[
          'type'=> [
               'on' =>app::get('b2c')->_('开启'),
               'off' =>app::get('b2c')->_('关闭'),
               'finish' =>app::get('b2c')->_('已完成'),
          ],
          'required' => true,
          'default'  => 'off',
          'comment'  => app::get('b2c')->_('状态'),
          'label' => app::get('b2c')->_('状态'),
          'width' => 55,
          'in_list' => true,
          'default_in_list' => true,
        ],

        'disabled' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('是否立即开启'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_contract_id' =>[
            'columns' => [0 => 'contract_id'],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('费种表'),
    
];
