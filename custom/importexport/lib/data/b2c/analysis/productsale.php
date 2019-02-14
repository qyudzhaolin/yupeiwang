<?php

class importexport_data_b2c_analysis_productsale extends importexport_data_b2c_analysis_common  {
    function __construct(){
        parent::__construct('productsale');
    }
    public function getIdFilter($filter){
        return $filter;
    }
}
