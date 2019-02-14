<?php
//调试TODO
if( ! function_exists('ee')){
    function ee($str='',$isCut=true)
    {
        header("Content-type: text/html; charset=utf-8");
        echo '<pre>';
        if(is_array($str) || is_object($str)){
            print_r($str);
        }else{
            echo $str;
        }
        echo '</pre>';
        if ($isCut) {
            exit;
        }
    }
}

//输出最近一条sql
function sql($getLatest = true){
    $allSql = [];
    $allSql = kernel::database()->lastSql;
    if (!empty($allSql)) {
        if ($getLatest && isset($allSql[0])) {
            return array_pop($allSql);
        }else{
            krsort($allSql);
            return $allSql;
        }
    }
    return '';
}

function mark($data = [],$isconvert=1){
    kernel::database()->mark($data,$isconvert);
}

//获取配置
function getConfig($key=null){
    $configs = include(ROOT_DIR.'/config/setting.php');
    if ($key) {
        if (isset($configs[$key])) {
            return $configs[$key];
        }else{
            return '';
        }
    }
    return $configs;
}

#include("app/serveradm/xhprof.php");
define('ROOT_DIR',realpath(dirname(__FILE__)));
require(ROOT_DIR.'/app/base/kernel.php');
kernel::boot();

if(defined("STRESS_TESTING")){
    b2c_forStressTest::logSqlAmount();
}
