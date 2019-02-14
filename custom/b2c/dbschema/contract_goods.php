<?php
/**
*说明：合约-商品dbschema
*注意：
*/
$db['contract_goods']  = [
    'columns'=>[
        'id'=>[
            'type'=>'number',
            'required' => true,
            'pkey'=>true,
            'comment'  => app::get('b2c')->_('合约商品id'),
            'label'    => app::get('b2c')->_('合约商品id'),
            'extra'=>'auto_increment',
        ],
        'contract_id'=>[
            'type'=>'table:contract',
            'label'=>app::get('b2c')->_('合约id'),
            'comment'=>app::get('b2c')->_('合约id'),
        ],

        'goods_id'=>[
            'type'=>'table:goods@b2c',
            'label'=>app::get('b2c')->_('商品id'),
            'comment'=>app::get('b2c')->_('商品id'),
        ],

        'product_id'=>[
            'type'=>'table:products@b2c',
            'label'=>app::get('b2c')->_('货品id'),
            'comment'=>app::get('b2c')->_('货品id'),
        ],

        'bn'=>[
            'type' => 'varchar(100)',
            'required'      => true,
            'default'       => '',
            'comment'=>app::get('b2c')->_('商品货号'),
            'label' => app::get('b2c')->_('商品货号'),
            'width' => 110,
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
        ],
        'price'=>[
            'type'=>'money',
            'required' => true,
            'default' => '0',
            'in_list'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_('合约价格'),
            'comment'=>app::get('b2c')->_('合约价格'),
        ],

        'num'=>[
            'type'=>'int(8)',
            'required' => true,
            'default' => '0',
            'in_list'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_(''),
            'comment'=>app::get('b2c')->_('数量'),
        ],
        'store_left'=>[
            'type'=>'int(8)',
            'required' => true,
            'default' => '0',
            'in_list'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_(''),
            'comment'=>app::get('b2c')->_('库存结余'),
        ],
        'storehouse' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('仓库'),
            'label'         => app::get('b2c')->_('仓库'),
            'width'         => 100,
            'searchtype'    => 'has',
            'editable'      => true,
            'filtertype'    => 'custom',
            'filterdefault' => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'status'=>[
            'type'=>'bool',
            'default' => 'true',
            'in_list' => true,
            'editable' => false,
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_('状态'),
            'comment'=>app::get('b2c')->_('状态'),
            'order'=>10,
            'width' => 50,
        ],
    ],

    'index' =>[
        'ind_contract_id' =>[
            'columns' => [0 => 'contract_id'],
        ],
        'ind_product_id' =>[
            'columns' => [0 => 'product_id'],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('合约商品'),
];
