<?php
/**
*说明：实名认证dbschema
*注意：
*/
$db['prove']= [
    'columns' => [
        'prove_id' =>[
            'type'     => 'number',
            'required' => true,
            'pkey'     => true,
            'extra'    => 'auto_increment',
            'comment'  => app::get('b2c')->_('实名认证id'),
            'label'    => app::get('b2c')->_('实名认证id'),
            'width'    => 150,
            'hidden'   => true,
            'editable' => false,
            'in_list'  => false,
        ],

        'member_id' =>[
            'type'            => 'table:members',
            'label'           => app::get('b2c')->_('姓名'),
            'editable'        => true,
            'filtertype'      => 'yes',
            'filterdefault'   => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'merchantName' =>[
            'type'          => 'varchar(200)',
            'required'      => true,
            'default'       => '',
            'comment'       => app::get('b2c')->_('商户名称'),
            'label'         => app::get('b2c')->_('商户名称'),
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

        'merchant_type_id' =>[
            'type'            => 'table:merchant_type',
            'sdfpath'         => 'merchant_type/merchant_type_id',
            // 'comment'         => app::get('b2c')->_('商户类型'),
            'label'           => app::get('b2c')->_('商户类型'),
            'editable'        => true,
            'filtertype'      => 'yes',
            'filterdefault'   => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'region_id' =>[
            'type'=> 'table:regions@ectools',
            'sdfpath'=> 'ectools_regions/region_id',
            'required' => true,
            'default'  => 0,
            'comment'  => app::get('b2c')->_('区域'),
            'label'           => app::get('b2c')->_('商户所在区域'),
            'editable'        => true,
            'filtertype'      => 'yes',
            'filterdefault'   => true,
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'area' =>[
          'type'            => 'varchar(255)',
          'comment'         => app::get('b2c')->_('区域格式'),
          'label'           => app::get('b2c')->_('区域格式'),
          'width'           => 110,
          'editable'        => false,
        ],

        'addr' =>[
          'type'            => 'varchar(255)',
          'comment'         => app::get('b2c')->_('街道详细地址'),
          'label'           => app::get('b2c')->_('街道详细地址'),
          'width'           => 110,
          'editable'        => false,
          'filtertype'      => 'yes',
          'filterdefault'   => 'true',
          'in_list'         => true,
          'default_in_list' => false,
        ],

        'linkman' =>[
            'type'            => 'varchar(30)',
            'required'        => true,
            'default'         => '',
            'editable'        => false,
            'comment'         => app::get('b2c')->_('联系人'),
            'label'           => app::get('b2c')->_('联系人'),
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'mobile' => [
          'type'            => 'varchar(15)',
          'label'           => app::get('b2c')->_('手机号'),
          'comment'         => app::get('b2c')->_('手机号'),
          'width'           => 75,
          'editable'        => false,
          'filtertype'      => 'yes',
          'filterdefault'   => 'true',
          'in_list'         => true,
          'default_in_list' => false,
        ],

        'inviter_mobile' => [
          'type'            => 'varchar(15)',
          'label'           => app::get('b2c')->_('邀请人手机号'),
          'comment'         => app::get('b2c')->_('邀请人手机号'),
          'width'           => 75,
          'editable'        => false,
          'filtertype'      => 'yes',
          'filterdefault'   => 'true',
          'in_list'         => true,
          'default_in_list' => false,
        ],

        'store_img' =>[
            'type'            => 'varchar(255)',
            'comment'         => app::get('b2c')->_('门店图片'),
            'editable'        => false,
            'label'           => app::get('b2c')->_('门店图片'),
            'in_list'         => false,
            'default_in_list' => false,
        ],

        'license_img' =>[
            'type'            => 'varchar(255)',
            'comment'         => app::get('b2c')->_('企业营业执照'),
            'editable'        => false,
            'label'           => app::get('b2c')->_('企业营业执照'),
            'in_list'         => false,
            'default_in_list' => false,
        ],

        'id_front_img' =>[
            'type'            => 'varchar(255)',
            'comment'         => app::get('b2c')->_('身份证正面'),
            'editable'        => false,
            'label'           => app::get('b2c')->_('身份证正面'),
            'in_list'         => false,
            'default_in_list' => false,
        ],

        'id_back_img' =>[
            'type'            => 'varchar(255)',
            'comment'         => app::get('b2c')->_('身份证背面'),
            'editable'        => false,
            'label'           => app::get('b2c')->_('身份证背面'),
            'in_list'         => false,
            'default_in_list' => false,
        ],

        'ordernum' =>[
            'type'     => 'number',
            'label'    => app::get('b2c')->_('排序'),
            'comment'  => app::get('b2c')->_('排序'),
            'width'    => 150,
            'editable' => true,
            'in_list'  => true,
        ],

        'status' =>[
            'type' => [
                'first' =>app::get('b2c')->_('待初审'),
                'review' =>app::get('b2c')->_('待复审'),
                'pass' =>app::get('b2c')->_('审核通过'),
                'nopass' =>app::get('b2c')->_('审核不通过'),
            ],
            'required' => false,
            'label' => app::get('b2c')->_('审核状态'),
            'editable' => true,
            'default' =>'first',
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
        ],
        'op_name' => [
            'type' => 'varchar(50)',
            'label' => app::get('b2c')->_('操作员'),
            'comment' => app::get('b2c')->_('操作员'),
            'editable' => false,
            'default' =>'',
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
        ],

        'op_name_first' => [
            'type' => 'varchar(50)',
            'label' => app::get('b2c')->_('初审操作员'),
            'comment' => app::get('b2c')->_('初审操作员'),
            'editable' => false,
            'default' =>'',
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
        ],

        'op_name_review' => [
            'type' => 'varchar(50)',
            'label' => app::get('b2c')->_('复审操作员'),
            'comment' => app::get('b2c')->_('复审操作员'),
            'editable' => false,
            'default' =>'',
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
        ],

        'content_first' =>[
          'type'            => 'varchar(255)',
          'comment'         => app::get('b2c')->_('初审审核意见'),
          'label'           => app::get('b2c')->_('初审审核意见'),
          'width'           => 110,
          'editable'        => false,
          'filtertype'      => 'yes',
          'filterdefault'   => 'true',
          'in_list'         => true,
          'default_in_list' => false,
        ],

        'content_review' =>[
          'type'            => 'varchar(255)',
          'comment'         => app::get('b2c')->_('复审审核意见'),
          'label'           => app::get('b2c')->_('复审审核意见'),
          'width'           => 110,
          'editable'        => false,
          'filtertype'      => 'yes',
          'filterdefault'   => 'true',
          'in_list'         => true,
          'default_in_list' => false,
        ],

        'first_time' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('初审时间'),
            'label'           => app::get('b2c')->_('初审时间'),
            'in_list'         => true,
            'default_in_list' => true,

        ],

        'review_time' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('复审时间'),
            'label'           => app::get('b2c')->_('复审时间'),
            'in_list'         => true,
            'default_in_list' => true,

        ],

        'memo' =>[
          'type' => 'longtext',
          'label' => app::get('b2c')->_('备注'),
          'comment' => app::get('b2c')->_('备注'),
          'editable' => false,
          'filtertype' => 'normal',
          'in_list' => true,
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

        'payed_bond_time' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('支付时间'),
            'label'           => app::get('b2c')->_('支付时间'),
            'in_list'         => true,
            'default_in_list' => true,
        ],

        'ctime' =>[
            'type'            => 'datetime',
            'required'        => true,
            'default'         => 0,
            'comment'         => app::get('b2c')->_('认证时间'),
            'label'           => app::get('b2c')->_('认证时间'),
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
          'comment'  => app::get('b2c')->_('排序'),
          'editable' => false,
        ],
    ],
    'index' =>[
        'ind_ordernum' =>[
            'columns' => [0 => 'ordernum',],
        ],
        'ind_member_id' =>[
            'columns' => [0 => 'member_id',],
        ],
        'ind_region_id' =>[
            'columns' => [0 => 'region_id',],
        ],
    ],
    'version' => '$Rev: 40654 $',
    'comment' => app::get('b2c')->_('实名认证表'),
];
