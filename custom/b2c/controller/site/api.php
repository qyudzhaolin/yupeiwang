<?php
/**
 * 说明：宇配网API
 */
class b2c_ctl_site_api extends b2c_frontpage{
    function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->pagesize = 10;
        $this->_response->set_header('Cache-Control', 'no-store');
    }

    //根据TMS物流单号,更新物流单号
    public function updateLogiNo(){
        //参数data:[{"logiNo":"18062031645","orderId":"181107101046253"},{"logiNo":"18062031646","orderId":"181107133280512"}]
        $datas = trim($_REQUEST['data']);//参数
        // mark($datas);
        $objDelivery = $this->app->model('delivery');
        $datas = json_decode($datas,true);
        if (!empty($datas)) {
            //批量处理
            foreach ($datas as $data) {
                $orderId = trim($data['orderId']);//订单号
                $logiNo = trim($data['logiNo']);//TMS物流单号
                $nums = $objDelivery->count(['order_id'=>$orderId]);
                if (!$nums) {
                    $this->apiReturn(['error'=>1,'message'=>'没有此订单的物流数据！']);
                }
                $upRes = $objDelivery->update(['logi_no'=>$logiNo], ['order_id'=>$orderId]);
                if (!$upRes) {
                    $this->apiReturn(['error'=>1,'message'=>'物流单号更新失败！']);
                }
            }
            $this->apiReturn(['error'=>0,'message'=>'物流单号更新成功！']);
        }

        $this->apiReturn(['error'=>1,'message'=>'物流单号更新失败！']);
    }



    /**
     * 说明：更新WMS库存与products、goods库存同步
     * 逻辑：1.根据条件[is_yp_store]取出字段[owner_code、storehouse_id、sku_code]的list 
     *      2.用这个list集合WMS里面查出批量商品
     *      3.然后product循环根据条件[owner_code、storehouse_id、sku_code]进行更新，顺便更新goods表store字段值
     *      5.传递参数demo：fullSkuList:   project_id^_^owner_code^_^sku_code, project_id^_^owner_code^_^sku_code
     * @params string $id 签名
     * @return string 返回说明
     */
    public function updateYPgoods($id=''){
        if ($id != 'ypwupdatestore201811'){
            ee('error:签名失败!');
        };//签名验证
        $mdl_goods = $this->app->model('goods');
        $mdl_products = $this->app->model('products');
        $wheres = ['is_yp_store'=>'1', 'marketable'=>'true'];
        $fields = 'goods_id,product_id,sku_code,owner_code,storehouse_id';
        $products = $mdl_products->getList($fields, $wheres);
        if (empty($products)) {
            ee('error:暂无货品！');
        }

        $paramsDatas = [];
        $link = '^_^';
        //组装请求WMS接口参数值
        foreach ($products as $pro) {
            $paramsDatas[] = $pro['storehouse_id'] . $link . $pro['owner_code'] . $link . $pro['sku_code'];
        }

        // ee($products);
        $paramsDatas = implode(',', $paramsDatas);
        //获取WMS接口数据
        $Tdatas = $this->apiget(getConfig('syncStore'),['fullSkuList'=>$paramsDatas,'pageSize'=>99999999]);
        if ($Tdatas['error']) {
            ee('error:WMS接口出错！');
        }
        $datas = $Tdatas['data']['rows'];

        //模拟json数据
        // $datas = '[{"project_id":"101","owner_code":"KH000000000624","sku_code":"ZJD00003","qtyUomForYupeiwang":"888"},
        //         {"project_id":"101","owner_code":"KHZ_1540186180309","sku_code":"201810221501","qtyUomForYupeiwang":"999"}]';
        // $datas = json_decode($datas,true);
        //模拟END

        //开始更新...
        // ee($datas);
        foreach ($datas as $data) {
            $storehouse_id = $data['projectId'];
            $owner_code    = $data['ownerCode'];
            $sku_code      = $data['skuCode'];
            $store         = $data['qtyUomForYupeiwang'];
            $sql = "UPDATE `sdb_b2c_products` SET `store` = {$store} WHERE storehouse_id='{$storehouse_id}' AND owner_code='{$owner_code}' AND sku_code='{$sku_code}' AND is_yp_store=1;";//更新products表
            $res = $mdl_products->db->exec($sql);
            $sql = "UPDATE `sdb_b2c_goods` SET `store` = {$store} WHERE storehouse_id='{$storehouse_id}' AND owner_code='{$owner_code}' AND sku_code='{$sku_code}' AND is_yp_store=1;";//更新goods表
            $res = $mdl_goods->db->exec($sql);
        }
        if ($res) {
            ee('success:<b style="color:green">同步库存成功！</b>');
        }
        ee('error:同步库存失败！');
    }

}