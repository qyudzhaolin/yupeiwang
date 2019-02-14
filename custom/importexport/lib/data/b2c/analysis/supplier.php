<?php

class importexport_data_b2c_analysis_supplier  extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('supplier');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
