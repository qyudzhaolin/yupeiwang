<?php

class importexport_data_b2c_analysis_accounts extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('accounts');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
