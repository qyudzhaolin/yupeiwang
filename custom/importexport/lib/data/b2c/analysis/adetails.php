<?php

class importexport_data_b2c_analysis_adetails  extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('adetails');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
