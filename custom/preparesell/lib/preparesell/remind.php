<?php

class preparesell_preparesell_remind{
    /*
     *保存开团提醒方式
     *
     *
     */
    function __construct($app){
        $this->app = $app;
        $this->remind_mdl = kernel::single('preparesell_mdl_preparesell_remind');
    }
    function save_remind($params){
        return $this->remind_mdl->save($params);
    }
}
