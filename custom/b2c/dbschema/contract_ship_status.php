<?php
/**
*说明：合约-物流状态dbschema
*注意：
*/
$db['contract_ship_status']  = [
    'columns'=>[
        'id'=>[
            'type'=>'number',
            'required' => true,
            'pkey'=>true,
            'comment'  => app::get('b2c')->_('合约物流状态id'),
            'label'    => app::get('b2c')->_('合约物流状态id'),
            'extra'=>'auto_increment',
        ],
      
        'contract_id'=>[
            'type'=>'table:contract',
            'label'=>app::get('b2c')->_('合约id'),
            'comment'=>app::get('b2c')->_('合约id'),
        ],

        'ship_status'=>[
            'type' => 'varchar(50)',
            'required'      => true,
            'default'       => '',
            'comment'=>app::get('b2c')->_('物流状态'),
            'label' => app::get('b2c')->_('物流状态'),
            'width' => 110,
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
        ],

        'state'=>[
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
         'ctime' =>[
            'type' => 'datetime',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('出库时间'),
        ],
    ],
     
    'index' =>[
        'ind_contract_id' =>[
            'columns' => [0 => 'contract_id'],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('合约物流状态'),
];
