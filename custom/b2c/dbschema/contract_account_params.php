<?php
/**
*说明：合约-结算参数dbschema
*注意：
*/
$db['contract_account_params']= [
    'columns' => [
        'params_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('参数id'),
            'label'    => app::get('b2c')->_('参数id'),
            'width'    => 150,
            'hidden'   => true,
        ],
        'no' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('英文编号'),
            'label'         => app::get('b2c')->_('英文编号'),
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
            'comment'       => app::get('b2c')->_('标题'),
            'label'         => app::get('b2c')->_('标题'),
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

        'disabled' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('排序'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_no' =>[
            'columns' => [0 => 'no',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('结算参数表'),
    
];
