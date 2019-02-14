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

$mdlB2cMemberComments = app::get("b2c")->model("member_comments");
//每次限制数
$limit = 100;
$arr_object_type = array("msg","order");
//先更新 to_id
$last_comment_id_v1 = 0;
while($rs_v1 = $mdlB2cMemberComments->getList("comment_id",array("object_type|in"=>$arr_object_type,"to_id"=>"1","to_uname"=>"管理员","comment_id|than"=>$last_comment_id_v1),"0",$limit,"comment_id ASC")){
    $comment_ids = array();
    foreach ($rs_v1 as $var_rs_v1){
        $comment_ids[] = $var_rs_v1["comment_id"];
        $last_comment_id_v1 = $var_rs_v1["comment_id"];
    }
    if (!empty($comment_ids)){
        $update_arr = array("to_id"=>"-1");
        $filter_arr = array("comment_id|in"=>$comment_ids);
        $mdlB2cMemberComments->update($update_arr,$filter_arr);
    }
}
//再更新author_id
$last_comment_id_v2 = 0;
while($rs_v2 = $mdlB2cMemberComments->getList("comment_id",array("object_type|in"=>$arr_object_type,"author_id"=>"1","author"=>"管理员","comment_id|than"=>$last_comment_id_v2),"0",$limit,"comment_id ASC")){
    $comment_ids = array();
    foreach ($rs_v2 as $var_rs_v2){
        $comment_ids[] = $var_rs_v2["comment_id"];
        $last_comment_id_v2 = $var_rs_v2["comment_id"];
    }
    if (!empty($comment_ids)){
        $update_arr = array("author_id"=>"-1");
        $filter_arr = array("comment_id|in"=>$comment_ids);
        $mdlB2cMemberComments->update($update_arr,$filter_arr);
    }
}
die("finished");