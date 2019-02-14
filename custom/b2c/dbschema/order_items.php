<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['order_items']=array (
  'columns' =>
  array (
    'item_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单明细ID'),
    ),
    'order_id' =>
    array (
      'type' => 'table:orders',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单ID'),
    ),
    'obj_id' =>
    array (
      'type' => 'table:order_objects',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单明细对应的商品对象ID, 对应到sdb_b2c_order_objects表'),
    ),
    'product_id' =>
    array (
      'type' => 'table:products',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'sdfpath' => 'products/product_id',
      'comment' => app::get('b2c')->_('货品ID'),
    ),
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
    ),
    'type_id' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型ID'),
    ),
    'bn' =>
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('明细商品的品牌名'),
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的名称'),
    ),
    'cost' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的成本'),
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的销售价(购入价)'),
    ),
	'g_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
	  'label' => app::get('b2c')->_('会员价原价'),
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的会员价原价'),
    ),
    'amount' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品总额'),
    ),
    'score' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('积分'),
      'width' => 30,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品积分'),
    ),
    'weight' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品重量'),
    ),
    'nums' =>
    array (
      'type' => 'float',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'sdfpath' => 'quantity',
      'comment' => app::get('b2c')->_('明细商品购买数量'),
    ),
    'sendnum' =>
    array (
      'type' => 'float',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品发货数量'),
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的规格属性'),
    ),
    'sku_code' =>
    array (
      'type' => 'varchar(32)',
      'default' => '',
      'label' => app::get('b2c')->_('WMS商品编码'),
      'filtertype' => 'normal',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    
    'owner_code' =>
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'label' => app::get('b2c')->_('货主编码'),
      'width' => 30,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    
    'storehouse_id' =>
    array (
      'type' => 'int(12)',
      'default' => 0,
      'label' => app::get('b2c')->_('仓库id'),
      'width' => 30,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'is_yp_store' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'label' => app::get('b2c')->_('是否宇培管库'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'unit' =>array (
      'type' => 'varchar(20)',
      'default' => '',
      'label' => app::get('b2c')->_('计量单位'),
      'filtertype' => 'normal',
      'editable' => false,
      'in_list' => true,
    ),
    'unitEn' =>
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'label' => app::get('b2c')->_('英文计量单位'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'minfo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品必填项信息(序列化)'),
    ),
    'item_type' =>
    array (
      'type' =>
      array (
        'product' => app::get('b2c')->_('商品'),
        'pkg' => app::get('b2c')->_('捆绑商品'),
        'gift' => app::get('b2c')->_('赠品商品'),
        'adjunct'=>app::get('b2c')->_('配件商品'),
      ),
      'default' => 'product',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品类型'),
    ),
  ),
  'index' =>
  array (
    'ind_item_bn' =>
    array (
        'columns' =>array(
            0 => 'bn',
        ),
        'type' => 'hash',
    ),
    'ind_obj_id' =>
    array (
        'columns' =>
        array (
            0 => 'obj_id',
        ),
    ),
    'ind_order_id' =>
    array (
        'columns' =>
        array (
            0 => 'order_id',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44813 $',
  'comment' => app::get('b2c')->_('订单明细表'),
);
