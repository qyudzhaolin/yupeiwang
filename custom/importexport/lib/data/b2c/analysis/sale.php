<?php

class importexport_data_b2c_analysis_sale  extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('sale');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
