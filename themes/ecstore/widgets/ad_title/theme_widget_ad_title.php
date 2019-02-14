<?php
function theme_widget_ad_title(&$setting,&$smarty) {
    //展示位置对应的样式配置
    $setting['positions'] = [
        '1' => ['css'=>''],//一列
        '2' => ['css'=>'yp-index-box-left'],//顶部两列-左
        '3' => ['css'=>'yp-index-box-right'],//顶部两列-右
        '4' => ['css'=>'floor-02-left'],//底部两列-左
        '5' => ['css'=>'floor-02-right'],//底部两列-右
        '6' => ['css'=>'floor-03-left'],//三列-左
        '7' => ['css'=>'floor-03-main'],//三列-中
        '8' => ['css'=>'floor-03-right'],//三列-右
    ];
    $setting['position_style'] = isset($setting['positions'][$setting['position']]) ? 
                                 $setting['positions'][$setting['position']]['css'] :
                                 '';

    //原产国家
    $mdl_gprovenance = kernel::single('b2c_mdl_gprovenance');
    $setting['countryRows'] = $mdl_gprovenance->get_country_format();
    $setting['countryName'] = isset($setting['countryRows'][$setting['country']]) ? 
                                 $setting['countryRows'][$setting['country']] :
                                 '';
    // ee($setting);
    return $setting;
}

?>
