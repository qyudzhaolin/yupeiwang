<?php
/**
*说明：合约-费种dbschema
*注意：
*/
$db['contract_fee']= [
    'columns' => [
        'fee_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('费种id'),
            'label'    => app::get('b2c')->_('费种id'),
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

        'method' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('计算公式'),
            'label'         => app::get('b2c')->_('计算公式'),
            'is_title'      => true,
            'width'         => 200,
            'editable'      => true,
            'in_list'         => true,
        ],

        'method_format' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('计算公式-页面显示'),
            'label'         => app::get('b2c')->_('计算公式'),
            'is_title'      => true,
            'width'         => 200,
            'editable'      => true,
            'in_list'         => true,
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

        'isone' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('是否单选'),
          'label'    => app::get('b2c')->_('是否单选'),
          'editable' => false,
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
    'comment' => app::get('b2c')->_('费种表'),
    
];
