<?php

class importexport_data_b2c_analysis_common {
    function __construct($target = ''){
        //导出数据过滤,使导出与getlist get_schema一致
        $this->dataModel = kernel::single('b2c_mdl_analysis_' . $target);
        $this->dbSchema = $this->dataModel->get_schema();//获取数据结构
        foreach ($this->dbSchema['columns'] as $key => $value) {
            if ($value['default_in_list']) {
                $this->excTitle[$key] = $value['label'];
                $this->excContentKeys[] = $key;
            }
        }

    }
    public function getIdFilter($filter){
        return $filter;
    }

    public function get_title(){
        return $this->excTitle;
    }

    public function get_content_row($row){
        $content = [];
        foreach ($this->excContentKeys as $v) {
            $content[$v] = isset($row[$v]) ? $row[$v] : '';
        }
        $data[0] = $content;
        return $data;
    }

}
