<?php
/**
*说明：供应商dbschema
*注意：
*/
$db['supplier']= [
    'columns' => [
        'supplier_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('供应商id'),
            'label'    => app::get('b2c')->_('供应商id'),
            'width'    => 150,
            'hidden'   => true,
            'editable' => false,
            'in_list'  => false,
        ],

        'shortname' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('供应商简称'),
            'label'         => app::get('b2c')->_('供应商简称'),
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

        'bn' =>[
            'type' => 'varchar(200)',
            'label' => app::get('b2c')->_('供应商编号'),
            'width' => 110,
            'searchtype' => 'head',
            'editable' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ],

        'supplier_name' =>[
            'type'          => 'varchar(255)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('供应商全称'),
            'label'         => app::get('b2c')->_('供应商全称'),
            'width'         => 250,
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

        'area' =>[
            'type'     => 'region',
            'required' => true,
            'default'  => '',
            'editable' => false,
            'comment'  => app::get('b2c')->_('地区'),
            'label'    => app::get('b2c')->_('地区'),
            'in_list' => true,
            'default_in_list' => true,

        ],

        'addr' =>[
            'type'     => 'varchar(255)',
            'required' => true,
            'default'  => '',
            'editable' => false,
            'comment'  => app::get('b2c')->_('详细地址'),
            'label'    => app::get('b2c')->_('详细地址'),
            'in_list' => true,
            'default_in_list' => true,

        ],

        'tel' =>[
            'type'     => 'varchar(50)',
            'required' => true,
            'default'  => '',
            'editable' => false,
            'comment'  => app::get('b2c')->_('企业电话'),
            'label'    => app::get('b2c')->_('企业电话'),
            'width'         => 150,
            'in_list' => true,
            'default_in_list' => true,

        ],

        'linkman' =>[
            'type'     => 'varchar(30)',
            'required' => true,
            'default'  => '',
            'editable' => false,
            'comment'  => app::get('b2c')->_('联系人'),
            'label'    => app::get('b2c')->_('联系人'),
            'in_list' => true,
            'default_in_list' => true,

        ],

        'email' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('企业EMAIL'),
            'label'         => app::get('b2c')->_('企业EMAIL'),
            'width'         => 110,
            'filtertype'    => 'normal',
            'filterdefault' => 'true',
            'in_list'       => true,
        ],

        'account_period' =>[
            'type'     => 'int(5)',
            'required' => true,
            'default'  => 0,
            'editable' => false,
            'comment'  => app::get('b2c')->_('企业账期(day)'),
            'label'         => app::get('b2c')->_('企业账期(day)'),
            'width'         => 60,
            'in_list' => true,
            'default_in_list' => true,

        ],

        'logo' =>[
            'type' => 'varchar(255)',
            'comment' => app::get('b2c')->_('企业LOGO'),
            'editable' => false,
            'label' => app::get('b2c')->_('企业LOGO'),
            'in_list' => false,
            'default_in_list' => false,
        ],

        'license' =>[
            'type' => 'varchar(255)',
            'comment' => app::get('b2c')->_('企业营业执照'),
            'editable' => false,
            'label' => app::get('b2c')->_('企业营业执照'),
            'in_list' => false,
            'default_in_list' => false,
        ],

        'last_modify' =>[
          'type' => 'last_modify',
          'label' => app::get('b2c')->_('更新时间'),
          'width' => 190,
          'in_list' => true,
          'orderby' => true,
          'in_list' => true,
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

        'disabled' =>[
          'type'     => 'bool',
          'default'  => 'false',
          'comment'  => app::get('b2c')->_('排序'),
          'editable' => false,
        ],
        'salesman' =>[
            'type'     => 'varchar(50)',
            'required' => true,
            'default'  => '',
            'editable' => false,
            'comment'  => app::get('b2c')->_('业务员'),
            'label'    => app::get('b2c')->_('业务员'),
            'width'         => 150,
            'in_list' => true,
            'default_in_list' => true,

        ],
    ],
    'index' =>[
        'ind_ordernum' =>[
            'columns' => [0 => 'ordernum',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('供应商表'),
    
];
