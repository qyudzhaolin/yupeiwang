<?php
//接口地址-仿真环境
define('MSURL', 'http://10.18.32.202:8080/ypwmsserv/');//接口url地址

//物流链接地址
define('TMS_URL', 'http://ctms-uat.yupei-scm.com:8090/ctms-qt/');//物流单号
define('WMS_URL', 'http://cwms-uat.yupei-scm.com:8080/cwms-qt/');//出库单号


//////////////////////////////////////////////////////////////////////////////
return [
    //云平台链接地址
    'cloudLink' => [
        'tms_url' => WMS_URL . 'member/memberCenter.shtml',//物流单号链接
        'wms_url' => TMS_URL . 'member/memberCenter.shtml',//出库单号链接
    ],
    //TMS、WMS接口配置
    'createSoOder' => MSURL . 'ypw/so/createSoOder.do',//生成出库单
    'getEbProjectList' => MSURL . 'cwms/api/getEbProjectList.do',//仓库列表
    'ownerList' => MSURL . 'baseApi/getEbCustomerInfo.do',//货主列表
    'goodsList' => MSURL . 'baseApi/getCdWhSkuInfo.do',//商品列表
    'syncStore' => MSURL . 'api/ypwInventory.do',//同步商品库存

    'Think' => './custom/b2c/lib/Think/',//TP第三方类目录
    //通用图片缩略图尺寸,数组按照大、中、小顺序！width最大宽度，height最大高度
    'thumbImage' => [
        'a' => ['width' => 1200, 'height' => 1200],
        'b' => ['width' => 800, 'height' => 800],
        'c' => ['width' => 200, 'height' => 200],
        'd' => ['width' => 80, 'height' => 80],
    ],
    'FILE_UPLOAD_TYPE'      =>  'Local',    // 文件上传方式
    'openet'      =>  true,    // 是否开启网点
    'host'      =>  'http://www.yupeiwang.net',
];