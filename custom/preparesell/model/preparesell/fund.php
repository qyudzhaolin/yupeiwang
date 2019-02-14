<?php

class preparesell_mdl_preparesell_fund extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

    //重写getList方法
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType='begin_time asc'){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        if (!empty($datas)) {
            $fundModel = app::get('preparesell')->model('preparesell_fund');
            $fundColumns = $fundModel->schema['columns'];
            foreach ($datas as $key => $o) {
                //支付结束时间(先处理)
                $datas[$key]['lefttime'] = $this->lefttime($o);

                $datas[$key]['begin_time_format']=date('Y-m-d H:i',$o['begin_time']);
                $datas[$key]['end_time_format']=date('Y-m-d H:i',$o['end_time']);
                $datas[$key]['end_time_format_left']=date('Y-m-d H:i:s',$o['end_time']);

                //款项名称
                if (isset($fundColumns['fund_name']['type'][$o['fund_name']])) {
                    $datas[$key]['fund_name_format'] = $fundColumns['fund_name']['type'][$o['fund_name']];
                }

                //启用状态
                $datas[$key]['status_format']=$o['status']=='true' ? '<span class="color-green">已开启</span>' : '<span class="color-gray">未开启</span>';

            }
        }
        return $datas;
    }


    //获取款项价格
    function getFundPayment($cols='*', $filter=array(), $orderType='begin_time asc'){
        $payment = '';
        if (empty($filter['prepare_id']) || empty($filter['fund_name'])) {
            return $payment;
        }
        $where = ['prepare_id'=>$filter['prepare_id'],'fund_name'=>$filter['fund_name']];
        $row = $this->getRow($cols,$where,$orderType);
        if (empty($row)) {
            return $payment;
        }
        $payment = $row['payment'];
        $this->translateToPrice($payment,$filter['prepare_id'],$filter['product_id']);
        return $payment;
    }


    //转换款项比率为实际价格
    function translateToPrice(&$funds=[], $prepare_id=0, $product_id=0){
        if (empty($funds) || empty($prepare_id) || empty($product_id)) {
            return false;
        }
        //预售价格修改#TODO#
        // $mdl_preparesell_goods = $this->app->model('preparesell_goods');
        //$wheres = ['prepare_id' => $prepare_id, 'product_id' => $product_id];
        $mdl_products = app::get('b2c')->model('products');
        $wheres = ['product_id' => $product_id];
        $goodsRow = $mdl_products->getRow('price', $wheres);
        if (empty($goodsRow)) {
            return false;
        }
        $price = $goodsRow['price'];
        $this->objMath = kernel::single('ectools_math');
        if (is_string($funds) || is_numeric($funds)) {
            $funds = $this->objMath->formatNumber(($funds * $price)/100,2);
        }elseif (is_array($funds)) {
            foreach ($funds as $key => $value) {
                $payment = ($value['payment'] * $price)/100;
                $funds[$key]['payment'] = $this->objMath->formatNumber($payment,2);
            }
        }

        return true;
    }

    /**
     * 说明：获取款项中"指定款项"开始结束时间
     * @params array $fundRows 某个预售规则款项列表
     * @params string $fund_name 某个款项，默认是预付款
     * @return array 开始结束时间
     */
    function getFundTimes($fundRows, $fund_name='y'){
        $datas = ['begin_time'=>'','end_time'=>''];
        if (empty($fundRows)) {
           return $datas;
        }

        foreach ($fundRows as $row) {
            if ($row['fund_name'] == $fund_name) {
                $datas['begin_time'] = $row['begin_time'];
                $datas['end_time'] = $row['end_time'];
                break;
            }
        }
        return $datas;
    }


    //支付剩余时间
    function lefttime(&$row){
        $data = '';
        if ($row['status']=='true') {
            $data = $row['end_time'] - time();
        }
        return $data;
    }

}