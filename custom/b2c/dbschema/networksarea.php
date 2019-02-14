<?php
/**
*说明：网点区域表
*注意：
*/
$db['networksarea']= [
    'columns' => [
        'networksarea_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('网点区域id'),
        ],
        'networks_id' =>[
            'type'     => 'int(8)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('网点id'),
        ],

        'region_id' =>[
            'type'     => 'int(8)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('区域ID'),
            'is_title' => true,
        ],

        'ctime' =>[
            'type'     => 'datetime',
            'required' => true,
            'default'  => 0,
            'comment'  => app::get('b2c')->_('创建时间'),
            'label'    => app::get('b2c')->_('创建时间'),
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
        'ind_networks_id' =>[
            'columns' => [0 => 'networks_id',],
        ],
        'ind_region_id' =>[
            'columns' => [0 => 'region_id',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('网点区域表'),
];
