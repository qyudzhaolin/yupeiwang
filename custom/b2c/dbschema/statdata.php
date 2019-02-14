<?php
/**
*说明：统计数据
*注意：
*/
$db['statdata']= [
    'columns' => [
        // 'id' =>[
        //     'type' => 'int(10)',
        //     'required' => true,
        //     'pkey' => true,
        //     'extra' => 'auto_increment',
        //     'comment' => app::get('b2c')->_('ID'),
        // ],
        'k' =>[
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'comment' => app::get('b2c')->_('健名'),
        ],
        'v' =>[
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'comment' => app::get('b2c')->_('存储值'),
        ],
        'time_from' =>[
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('开始时间'),
        ],
        'time_to' =>[
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('结束时间'),
        ],
        'ctime' =>[
            'type' => 'datetime',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('创建时间'),
        ],
    ],
    'index' =>[
        'ind_k' =>[
            'columns' => [0 => 'k'],
        ],
        'ind_time_from' =>[
            'columns' => [0 => 'time_from'],
        ],
        'ind_time_to' =>[
            'columns' => [0 => 'time_to'],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('统计数据'),
    
];
