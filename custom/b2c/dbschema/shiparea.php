<?php
/**
*说明：商品配送范围
*注意：
*/
$db['shiparea']= [
    'columns' => [
        'shiparea_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('配送范围id'),
        ],
        'product_id' =>[
            'type' => 'int(8)',
            'required' => true,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('b2c')->_('货品ID'),
        ],
        'goods_id' =>[
            'type' => 'int(8)',
            'required' => true,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('b2c')->_('商品ID'),
        ],

        'region_id' =>[
            'type' => 'int(8)',
            'required' => true,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('b2c')->_('区域ID'),
            'is_title' => true,
        ],

        'last_modify' =>[
          'type' => 'last_modify',
          'required' => true,
          'comment' => app::get('b2c')->_('最后编辑时间'),
        ],

        'disabled' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('是否失效'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_region_id' =>[
            'columns' => [0 => 'region_id',],
        ],
        'ind_product_id' =>[
            'columns' => [0 => 'product_id',],
        ],
        'ind_goods_id' =>[
            'columns' => [0 => 'goods_id',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('商品配送区域表'),
    
];
