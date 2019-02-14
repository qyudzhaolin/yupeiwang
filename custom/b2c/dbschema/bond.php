<?php
/**
*说明：保证金dbschema
*注意：
*/
$db['bond']= [
    'columns' => [
        'bond_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('保证金id'),
            'label'    => app::get('b2c')->_('保证金id'),
            'width'    => 150,
            'hidden'   => true,
            'editable' => false,
            'in_list'  => false,
        ],

        'member_id' =>[
            'type'            => 'table:members',
            'label'           => app::get('b2c')->_('会员'),
            'editable'        => true,
            'filtertype'    => 'custom',
            'filterdefault'   => true,
            'in_list'         => true,
            'default_in_list' => true,
            'searchtype'    => 'has',
            'filtercustom'  =>[
              'has'    => app::get('b2c')->_('包含'),
              'tequal' => app::get('b2c')->_('等于'),
              'head'   => app::get('b2c')->_('开头等于'),
              'foot'   => app::get('b2c')->_('结尾等于'),
            ],
        ],
        'bond' =>[
            'type' => 'money',
            'default' => '0',
            'editable' => false,
            'label' => app::get('b2c')->_('保证金'),
            'comment' => app::get('b2c')->_('保证金'),
            'in_list' => true,
            'default_in_list' => true,
        ],

        'apply_refund_time' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('申请退回时间'),
            'label'           => app::get('b2c')->_('申请退回时间'),
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'real_refund_time' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('实际退回时间'),
            'label'           => app::get('b2c')->_('实际退回时间'),
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'ctime' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('创建时间'),
            'label'           => app::get('b2c')->_('创建时间'),
            'in_list'         => true,
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
          'comment'  => app::get('b2c')->_('是否禁用'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_member_id' =>[
            'columns' => [0 => 'member_id',],
        ],

    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('保证金表'),
];
