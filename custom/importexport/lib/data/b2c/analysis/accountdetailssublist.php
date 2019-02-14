<?php

class importexport_data_b2c_analysis_accountdetailssublist  extends importexport_data_b2c_analysis_common {
    function __construct(){
        parent::__construct('accountdetailssublist');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
