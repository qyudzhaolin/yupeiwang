<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$constants = array(
        'DATA_DIR'=>ROOT_DIR.'/data',
        'TMP_DIR' => sys_get_temp_dir(),
        'SET_T_STR'=>0,
        'SET_T_INT'=>1,
        'SET_T_ENUM'=>2,
        'SET_T_BOOL'=>3,
        'SET_T_TXT'=>4,
        'SET_T_FILE'=>5,
        'SET_T_DIGITS'=>6,
        'LC_MESSAGES'=>6,
        'DEFAULT_TIMEZONE'=>8,
        'DEBUG_TEMPLETE'=>false, // todo
        'WITH_REWRITE'=>false,
        'PRINTER_FONTS'=>'', //打印字体
        'PHP_SELF'=>(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']),
        'LOG_TYPE'=>3,
        'DATABASE_OBJECT'=>'base_db_connections',
        'KVSTORE_STORAGE'=>'base_kvstore_filesystem',
        'CACHE_STORAGE'=>'base_cache_secache',
		'SHOP_USER_ENTERPRISE'=>'http://passport.shopex.cn/index.php',
		'SHOP_USER_ENTERPRISE_API'=>'http://passport.shopex.cn/api.php',
        'URL_APP_FETCH_INDEX'=>'http://get.ecos.shopex.cn/index.xml',
        'LICENSE_CENTER'=>'http://service.ecos.shopex.cn/openapi/api.php', //证书的正式外网地址.
        'LICENSE_CENTER_V'=>'http://service.shopex.cn',  //License授权输出图片流【tito】 请求地址
        'URL_APP_FETCH'=>'http://get.ecos.shopex.cn/%s/',
        'MATRIX_RELATION_URL' => 'http://www.matrix.ecos.shopex.cn/',
        'OPENID_URL' => 'http://openid.ecos.shopex.cn/redirect.php',
        'LICENSE_CENTER_INFO'=>'http://service.shopex.cn/',
        'IMAGE_MAX_SIZE'=> 1024*1024,
        'MAX_SPEC_VALUE_NUM' => 200,//因为php限制最大post数量为1000（可以修改php.ini的max_input_vars项），当规格值数量超过一定量时，提交会超出这个值，所以做这个限制
        'KV_PREFIX' => 'defalut',
        'MATRIX_URL'=>'http://matrix.ecos.shopex.cn/async',
		'MATRIX_REALTIME_URL'=>'http://matrix.ecos.shopex.cn/sync',
		'MATRIX_SERVICE_URL'=>'http://matrix.ecos.shopex.cn/service',
        'MATRIX_GLOBAL' => 1,
        'MATRIX_REALTIME' => 2,
        'MATRIX_SERVICE' => 3,
        'AUTH_OPEN_URL' => 'http://auth.open.shopex.cn',
        'DEVELOPER_APP_KEY' => '2vmwktu6', //wangjianjun@shopex.cn
        'DEVELOPER_APP_SECRET' => 'airfhhavauuiwo5wdut4', //wangjianjun@shopex.cn
        'GENBANAPPLY' => 'http://onex.shopex.cn/genbanapply', //银联小跟班信息提交url
        'SHOPEX_LICENSE_ACTIVE_URL' => 'https://service.ec-os.net/api/active/register',
        'SHOPEX_LICENSE_CHECK_URL' => 'https://service.ec-os.net/api/active/active_check',
        'SHOPEX_LICENSE_CHECK_HARDWARE_URL' => 'https://service.ec-os.net/api/checkhardware',
        'SHOPEX_LICENSE_HARDWARE_LIST_URL' => 'https://service.ec-os.net/api/license/list',
        'SHOPEX_LICENSE_ACTIVE_SEARCH_URL' => 'https://service.ec-os.net/active',


    );

foreach($constants as $k=>$v){
   if(!defined($k))define($k,$v);
}

