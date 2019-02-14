<?php

class preparesell_prepare_prepare{

    function __construct($app){
        $this->app = $app;
        //$this->mdl_preparesell = app::get('preparesell')->model('preparesell');
        $this->mdl_preparesell_product = app::get('preparesell')->model('preparesell_goods');
    }


    function getSpecialProduct($filter,$num=0){
        $prepare = $this->mdl_preparesell_product->getRow('*',array('product_id'=>$filter));
        if ($num) {
            //预售预付款价格显示#TODO#
            $pwhere = ['prepare_id'=>$prepare['prepare_id'],'product_id'=>$filter,'fund_name'=>'y'];
            $pre_payment = app::get('preparesell')->model('preparesell_fund')->getFundPayment('*',$pwhere);
            $prepare['preparesell_unit_price']= $pre_payment;//预售单价

            $prepare['preparesell_price']= round($pre_payment*$num, 2);
        }
        return $prepare;
    }

   
}
