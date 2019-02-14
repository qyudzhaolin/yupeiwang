<?php
//说明：合约操作日志
$db['contract_dologs']=array (
  'columns' =>
  array (
    'log_id' => [
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('合约日志ID'),
    ],
    'contract_id' =>[
        'type' => 'table:contract',
        'label' => app::get('b2c')->_('合约id'),
        'width' => 75,
        'editable' => false,
        'filtertype' => 'yes',
        'filterdefault' => true,
        'in_list' => true,
        'default_in_list' => true,
    ],
    'op_id' =>
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('b2c')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
      'comment' => app::get('b2c')->_('操作员ID'),
    ),
    'op_name' =>
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('操作人名称'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'alttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('操作时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('操作时间'),
    ),

    'behavior' =>
    array (
      'type' =>
      array (
        'creates' => app::get('b2c')->_('合约创建'),
        'edit' => app::get('b2c')->_('合约更新'),
        'ship_update' => app::get('b2c')->_('物流状态更新'),
        'do' => app::get('b2c')->_('合约处理'),
        'other' => app::get('b2c')->_('其他'),
      ),
      'default' => 'other',
      'required' => true,
      'label' => app::get('b2c')->_('操作行为'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('日志记录操作的行为'),
    ),

    'result' =>
    array (
      'type' =>
      array (
        'succ' => app::get('b2c')->_('成功'),
        'error' => app::get('b2c')->_('失败'),
      ),
      'default' => 'error',
      'required' => true,
      'label' => app::get('b2c')->_('操作结果'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('日志结果'),
    ),
    'log_text' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'comment' => app::get('b2c')->_('操作内容'),
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('序列化数据'),
    ),
  ),

  'index' =>
    array (
        'ind_contract_id' =>
        array (
            'columns' =>
            array (
                0 => 'contract_id',
            ),
        ),
        'ind_behavior' =>
        array (
            'columns' =>
            array (
                0 => 'behavior',
            ),
        ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
  'comment' => app::get('b2c')->_('订单日志表'),
);

