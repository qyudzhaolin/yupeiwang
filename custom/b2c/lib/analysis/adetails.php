<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_analysis_adetails extends ectools_analysis_abstract implements ectools_analysis_interface
{
    
    public function finder()
    {
        return array(
            'model' =>'b2c_mdl_analysis_adetails',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href' => 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]=b2c&_params[mdl]=b2c_mdl_analysis_adetails',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('账款明细统计'),//1区标题
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }

}
