<?php
/**
*说明：合约dbschema
*注意：
*/
$db['contract']= [
    'columns' => [
        'contract_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('合约id'),
            'label'    => app::get('b2c')->_('合约id'),
            'width'    => 150,
            'hidden'   => true,
        ],

        'member_id' =>[
            'type' => 'table:members',
            'label' => app::get('b2c')->_('客户简称'),
            'label'    => app::get('b2c')->_('客户简称'),
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ],
        'login_name'=>[
            'type'     =>'varchar(100)',
            'required' => true,
            'default'  => '',
            'comment'  => app::get('pam')->_('客户'),
            'label'    => app::get('b2c')->_('客户'),
            'width'    => 200,
            'editable' => true,
            'in_list'  => true,
            'default_in_list' => true,
        ],
        'contract_no' => [
            'type'   => 'varchar(50)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('合约号'),
            'label'         => app::get('b2c')->_('合约号'),
            'width'         => 100,
            'editable'      => true,
            'in_list'  => true,
            'searchtype' => 'has',
            'default_in_list' => true,
        ],

        'ship_status'=>[
            'type'=> [
                 '' =>app::get('b2c')->_(''),
                 'buy' =>app::get('b2c')->_('采购'),
                 'seastore' =>app::get('b2c')->_('海外仓储'),
                 'interlogi' =>app::get('b2c')->_('国际物流'),
                 'clearance' =>app::get('b2c')->_('通关'),
                 'storage' =>app::get('b2c')->_('仓储'),
                 'citydist' =>app::get('b2c')->_('城配'),
            ],
            'required' => true,
            'default'  => '',
            'comment'  => app::get('b2c')->_('合约物流状态'),
            'label'    => app::get('b2c')->_('合约物流状态'),
            'width'    => 100,
            'editable' => true,
            'searchtype' => 'has',
            'in_list'  => true,
            'default_in_list' => true,
        ],

        'pay_status'=>[
            'type'     =>'varchar(100)',
            'required' => true,
            'default'  => '',
            'comment'  => app::get('pam')->_('合约付款状态'),
            'label'    => app::get('b2c')->_('合约付款状态'),
            'width'    => 100,
            'editable' => true,
            'searchtype' => 'has',
            'in_list'  => true,
            'default_in_list' => true,
        ],
        'amount' => [
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
            'comment' => app::get('b2c')->_('总货值'),
            'label' => app::get('b2c')->_('总货值'),
            'width' => 75,
            'filtertype' => 'number',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ],

        'begin_time' =>[
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('生效日期'),
            'label'         => app::get('b2c')->_('生效日期'),
            'in_list'  => true,
            'default_in_list' => true,

        ],

        'end_time' =>[
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('失效日期'),
            'label'         => app::get('b2c')->_('失效日期'),
            'in_list'  => true,
            'default_in_list' => true,

        ],

        'content' =>[
            'type'          => 'varchar(255)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('服务内容'),
            'label'         => app::get('b2c')->_('服务内容'),
            'editable'      => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'accounts' =>[
            'type'          => 'varchar(255)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('结算方式'),
            'label'         => app::get('b2c')->_('结算方式'),
            'editable'      => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'params' =>[
            'type' => 'text',
            'width' => 75,
            'in_list' => true,
            'comment'       => app::get('b2c')->_('结算参数'),
            'label'         => app::get('b2c')->_('结算参数'),
        ],

        'min_amount' => [
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
            'comment' => app::get('b2c')->_('最小出库金额'),
            'label' => app::get('b2c')->_('最小出库金额'),
            'width' => 75,
            'in_list' => true,
            'default_in_list' => true,
        ],
        'min_num'=>[
            'type'=>'int(8)',
            'required' => true,
            'default' => '0',
            'in_list'=>true,
            'default_in_list' => true,
            'label'=>app::get('b2c')->_('最小出库数'),
            'comment'=>app::get('b2c')->_('最小出库数'),
            'width' => 75,

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
            'type' => 'datetime',
            'required' => true,
            'default' => 0,
            'comment' => app::get('b2c')->_('最后修改时间'),
        ],

        'state' =>[
          'type'=> [
               'on' =>app::get('b2c')->_('开启'),
               'off' =>app::get('b2c')->_('关闭'),
               'finish' =>app::get('b2c')->_('已完结'),
               'abend' =>app::get('b2c')->_('异常终止'),
          ],
          'required' => true,
          'default'  => 'off',
          'comment'  => app::get('b2c')->_('状态'),
          'label' => app::get('b2c')->_('状态'),
          'width' => 55,
          'in_list' => true,
          'default_in_list' => true,
        ],
    ],
    'index' =>[
        'ind_contract_no' =>[
            'columns' => [0 => 'contract_no'],
        ],
        'ind_begin_time' =>[
            'columns' => [0 => 'begin_time'],
        ],
        'ind_end_time' =>[
            'columns' => [0 => 'end_time'],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('合约表'),
    
];
