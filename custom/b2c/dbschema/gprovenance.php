<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$db['gprovenance']=array (
    'columns' =>
        array (
       'provenance_id' =>
      array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('原产地序号ID'),
                ),
        'gcountry_of_origin' =>
      array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('原产国'),
      'width' => 180,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'gsource_area' =>
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('原产地'),
      'is_title'=>true,
      'searchtype' => 'has',
      'width' => 180,
       'in_list' => true,
       'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
    'gimport_information_1' =>
     array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('进口资料配备1'),
      'width' => 180,
       'in_list' => true,
       'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
     'gimport_information_2' =>
      array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('资料配备2'),
      'width' => 180,
       'in_list' => true,
       'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
      'gimport_information_3' =>
       array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('资料配备3'),
      'width' => 180,
       'in_list' => true,
       'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
       'gimport_information_4' =>
        array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('资料配备4'),
      'width' => 180,
       'in_list' => true,
       'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
    'ordernum' =>[
        'type'     => 'number',
        'default'  => 0,
        'label'    => app::get('b2c')->_('排序'),
        'comment'  => app::get('b2c')->_('排序'),
        'width'    => 150,
        'editable' => true,
        'in_list'  => true,
    ],
    'is_hot' =>
    array (
      'type'     => 'bool',
      'default'  => 'false',
      'label' => app::get('b2c')->_('是否热门推荐'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'is_country' =>
    array (
      'type'     => 'bool',
      'default'  => 'false',
      'label' => app::get('b2c')->_('是否作为国家'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
  ),
  'index' =>
    array (
        'ind_provenance_id' =>
        array (
            'columns' =>
            array (
                0 => 'provenance_id',
            ),
        ),
     
  ),
  'comment' => app::get('b2c')->_('原产地表'),
  'version' => '$Rev: 42046 $',
);
