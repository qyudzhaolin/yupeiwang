<?php
/**
*说明：网点
*注意：
*/
$db['networks']= [
    'columns' => [
        'networks_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('网点id'),
        ],
        'networks_name' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('网点名称'),
            'label'         => app::get('b2c')->_('网点名称'),
            'is_title'      => true,
            'width'         => 200,
            'searchtype'    => 'has',
            'editable'      => true,
            'filtertype'    => 'custom',
            'filterdefault' => true,
            'filtercustom'  =>[
              'has'    => app::get('b2c')->_('包含'),
              'tequal' => app::get('b2c')->_('等于'),
              'head'   => app::get('b2c')->_('开头等于'),
              'foot'   => app::get('b2c')->_('结尾等于'),
            ],
            'in_list'         => true,
            'default_in_list' => true,
            'order'           =>'1',
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
          'comment'  => app::get('b2c')->_('是否失效'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_networks_name' =>[
            'columns' => [0 => 'networks_name',],
        ]
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('网点表'),
];
