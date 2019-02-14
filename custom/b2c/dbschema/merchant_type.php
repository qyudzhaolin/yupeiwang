<?php
/**
*说明：商户类型dbschema
*注意：
*/
$db['merchant_type']= [
    'columns' => [
        'merchant_type_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('商户类型id'),
            'label'    => app::get('b2c')->_('商户类型id'),
            'width'    => 150,
            'hidden'   => true,
            'editable' => false,
            'in_list'  => false,
        ],

        'merchant_type' =>[
            'type'            => 'varchar(100)',
            'comment'         => app::get('b2c')->_('商户类型'),
            'label'           => app::get('b2c')->_('商户类型'),
            'is_title'=>true,
            'searchtype' => 'has',
            'width' => 180,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'default_in_list' => true,
        ],

        'ordernum' =>[
            'type'     => 'number',
            'label'    => app::get('b2c')->_('排序'),
            'comment'  => app::get('b2c')->_('排序'),
            'width'    => 150,
            'editable' => true,
            'in_list'  => true,
        ],

        'ctime' =>[
            'type' => 'datetime',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('创建时间'),
            'label'   => app::get('b2c')->_('创建时间'),

            'in_list'  => true,
            'default_in_list' => true,

        ],

        'mtime' =>[
            'type'     => 'datetime',
            'required' => true,
            'default'  => 0,
            'label'    => app::get('b2c')->_('最后修改时间'),
            'comment'  => app::get('b2c')->_('最后修改时间'),
        ],

        'disabled' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('排序'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_ordernum' =>[
            'columns' => [0 => 'ordernum',],
        ]
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('商户类型表'),
];
