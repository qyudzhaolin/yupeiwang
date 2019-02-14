<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['name']='带标题的图片广告';
$setting['catalog'] = '广告相关';
$setting['description'] = '展示模板使用的带标题的图片广告';
$setting['usual'] = '0';
$setting['stime'] ='2013-07';
$setting['userinfo'] = '';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

//展示位置配置
$setting['positions'] = [
    '1' => '一列',
    '2' => '顶部两列-左',
    '3' => '顶部两列-右',
    '4' => '底部两列-左',
    '5' => '底部两列-右',
    '6' => '三列-左',
    '7' => '三列-中',
    '8' => '三列-右',
];

//原产国
$mdl_gprovenance = kernel::single('b2c_mdl_gprovenance');
$setting['countryRows'] = $mdl_gprovenance->get_country_format();

?>
