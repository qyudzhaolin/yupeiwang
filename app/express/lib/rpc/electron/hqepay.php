<?php
class express_rpc_electron_hqepay extends express_rpc_electron_abstract
{

    public function directRequest($sdf){
        $this->primaryBn = $sdf['primary_bn'];
        $order = $sdf['order'];
        $shopInfo = $sdf['shop'];
        $orderItems = $sdf['order_item'];
        $safemail = $sdf['safemail'];
        //获取电子面单账号密码
        $info = explode('|||',$this->channel['shop_id']);
        //解析省市区县
        $area = explode(':',$order['ship_area']);
        $area = explode('/',$area[1]);
        $to_address = $order['ship_addr'] ? $area['0'] . $area['1'] . $area['2'] . $order['ship_addr'] : '_SYSTEM';

        $receiver = array(
            'Company'       =>  $this->charFilter($order['ship_name']),
            'Name'          =>  $this->charFilter($order['ship_name']),
            'Tel'           =>  $order['ship_tel'],
            'Mobile'        =>  $order['ship_mobile'],
            'PostCode'      =>  $order['ship_zip'],
            'ProvinceName'  =>  $area['0'],
            'CityName'      =>  $area['1'],
            'ExpAreaName'   =>  $area['2'],
            'Address'       =>  $this->charFilter($to_address),
        );
        unset($area);

        $area = explode(':',$shopInfo['region']);
        $area = explode('/',$area[1]);
        $sender = array(
            'Company'       =>  $shopInfo['name'] ? $shopInfo['name'] : '_SYSTEM',
            'Name'          =>  $shopInfo['name'] ? $shopInfo['name'] : '_SYSTEM',
            'Tel'           =>  $shopInfo['cellphone'],
            'Mobile'        =>  $shopInfo['phone'],
            'PostCode'      =>  $shopInfo['zip'],
            'ProvinceName'  =>  $area[0],
            'CityName'      =>  $area[1],
            'ExpAreaName'   =>  $area[2],
            'Address'       =>  $this->charFilter($shopInfo['address']),
        );
        //增值服务字段
        $addservice =  array(
            'Name'      =>  'SafeMail', #隐私快递 1：隐藏收件人信息 2：隐藏发件人信息 3 同时隐藏收件人，发件人信息
            'Value'     =>  $safemail,  # 1：隐藏收件人信息 2：隐藏发件人信息 3 同时隐藏收件人，发件人信息
            'CustomerID' => '', #客户id（选填）
        );
        $requestdata = array(
            'CallBack'      =>  '',  #用户自定义回调信息
            'MemberID'      =>  '',  #会员标识平台方与快递鸟统一用户标识的商家ID
            'CustomerName'  =>  '',  #电子面单客户账号
            'CustomerPwd'   =>  '',  #电子面单密码
            'SendSite'      =>  '',  #收件网点标识
            'ShipperCode'   =>  $this->channel['logistics_code'],    #快递公司编码
            'LogisticCode'  =>  '',  #快递单号
            'ThrOrderCode'  =>  '',  #第三方订单号
            'OrderCode'     =>  $order['order_id'],
            'MonthCode'     =>  $info['3'],  #月结编码
            'PayType'       =>  $info['2'],
            'ExpType'       =>  $info['4'],  #快递类型
            'IsNotice'      =>  '',  #是否通知快递员
            'Cost'          =>  $order['cost_freight'],  #快递费
            'Receiver'      =>  $receiver,
            'Sender'        =>  $sender,
            'AddService'    =>  array($addservice),
            'StartDate'     =>  '', #上门取货时间
            'EndDate'       =>  '',
            'Weight'        =>  '',
            'Quantity'      =>  '',
            'Volume'        =>  '',
            'Remark'        =>  $order['mark_text'],
            'Commodity'     =>  $this->format_order_item($sdf['order_item']),
            'IsReturnPrintTemplate'     =>  '1',
            'IsSendMessage'             =>  '0',
        );
        if ($info['5'] && !empty($info['5'])) {
            $appkey = $info['5'];
        }else{
            $appkey = '863a5ee3-c1d5-4499-a85c-f5d991a85ca0';
        }
        $DataSign=urlencode(base64_encode(md5(json_encode($requestdata).$appkey)));
        $params  = array(
            'EBusinessID'   =>  '1296848',
            'RequestData'   =>  urlencode(json_encode($requestdata)),
            'RequestData'   =>  json_encode($requestdata),
            'RequestType'   =>  '1007',
            'RequestType'   =>  '1007',
            'DataType'      =>  '2',
            'DataSign'      =>  $DataSign,
         );
        //$back =   $this->requestCall('store.trade.pub', $params);
        //测试地址
        //$url = 'http://testapi.kdniao.cc:8081/api/eorderservice';
        //正式地址
        $url = 'http://api.kdniao.cc/api/EOrderService';
        $back = json_decode(kernel::single('base_httpclient')->post($url,$params),true);
        $msg = '';
        $result = array();
        $result[] = array(
            'succ' => $back['Order'] ['LogisticCode']? true : false,
            'msg' => $msg,
            'order_id' => $back['Order']['OrderCode'],
            'logi_no' => $back['Order']['LogisticCode'],
            'package_wdjc' => $data['Order']['DestinatioName'],
            'package_wd' => $back['Order']['DestinatioCode'],
            'print_config' => 'hqepay',
            'json_packet' => $back['PrintTemplate']
        );
        $this->directDataProcess($result);
        return $result;
    }
    #获取货物名称
    public function format_order_item($orderItems = null) {
        $items = array();
        foreach($orderItems as $key=>$item){
            $items[$key]['GoodsCode']    = $item['bn'];
            $items[$key]['GoodsName']    = $item['name'];
            $items[$key]['Goodsquantity']     = $item['nums'];
            $items[$key]['GoodsPrice']   = $item['price'];
            $items[$key]['GoodsWeight']  = $item['weight'];
            $items[$key]['GoodsDesc']    = $item['desc'];
            $items[$key]['GoodsVol']     = $item['vol'];
        }
        return $items;
    }

    public function recycleWaybill($sdf) {
        $this->title = $this->channel['name'] . '取消电子面单';
        $this->primaryBn = $sdf['logi_no'];
        $params = array(
            'order_id' => $sdf['logi_no'],
            'shipper_code'=>  $this->channel['logistics_code'],
             'is_auto_bill'  =>'1',
            'device_type' =>'pc',
            'exp_type'=>'1',
        );
        $back = $this->requestCall('store.trade.cancel', $params);

        //本地waybill表中运单号变更成作废，不管接口取消有无成功
        $this->recycleDataProcess($sdf);
        return $result;
    }

}
