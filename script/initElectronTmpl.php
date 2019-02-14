#!/usr/bin/env php
<?php

ob_implicit_flush(1);
$root_dir = realpath(dirname(__FILE__).'/../');

require($root_dir."/config/config.php");

require(APP_DIR.'/base/kernel.php');
@require(APP_DIR.'/base/defined.php');

if(!kernel::register_autoload()){
    require(APP_DIR.'/base/autoload.php');
}
cachemgr::init(false);

$dlytpl_dir = ROOT_DIR."/app/express/initial/tpl/";
if($handle = opendir($dlytpl_dir)){
    while(false !== ($dtp = readdir($handle))){
        $path_parts = pathinfo($dtp);
        if($path_parts['extension'] == 'dtp'){
            $file['tmp_name'] = $dlytpl_dir.$dtp;
            $file['name'] = $dtp;
            $result = kernel::single('express_print_tmpl')->upload_tmpl($file);
        }
    }
    closedir($handle);
}