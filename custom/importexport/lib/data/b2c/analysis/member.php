<?php

class importexport_data_b2c_analysis_member extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('member');
    }
    public function getIdFilter($filter){
        return $filter;
    }

}
