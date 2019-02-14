<?php
/**
 * User: jintao
 * Date: 2016/6/24
 */
class express_waybill_hqepay
{
    /**
     * 默认订单来源类型
     * @var String 默认来源
     */
    public static $defaultChannelsType = 'OTHER';

    /**
     * 获取物流公司编码
     * @param Sring $logistics_code 物流代码
     */
    public function logistics($logistics_code) {
        $logistics = array(
            'EMS'   => array('code'=>'EMS','name'=>'普通EMS'),
            'SF'    => array('code'=>'SF','name'=>'顺丰'),
            'ZJS'   => array('code' => 'ZJS', 'name'=>'宅急送'),
            'ZTO'   => array('code' => 'ZTO', 'name' => '中通'),
            'HTKY'  => array('code' => 'HTKY', 'name'=>'百世汇通'),
            'YTO'   => array('code' => 'YTO', 'name' => '圆通'),
            'STO'   => array('code' => 'STO', 'name' => '申通'),
            'YD' => array('code' => 'YD', 'name' => '韵达快递'),
            'DBKD'  => array('code' => 'DBKD', 'name' => '德邦快递'),
            'UC'    => array('code' => 'UC', 'name'=>'优速快递'),
            'KYSY'  => array('code' => 'KYSY', 'name'=>'跨越速运'),
            'QFKD'  => array('code' => 'QFKD', 'name'=>'全峰快递'),
            // 'JD'    => array('code' => 'JD', 'name'=>'京东快递'),
            'XFEX'  => array('code' => 'XFEX', 'name'=>'信丰快递'),
            'ANE'   => array('code' => 'ANE', 'name'=>'安能'),
            'FAST'  => array('code' => 'FAST', 'name'=>'快捷'),
        );

        if (!empty($logistics_code)) {
            return $logistics[$logistics_code];
        }
        return $logistics;
    }
    public function pay_method($method = '') {
        $payMethod = array(
                '1' => array('code' => '1', 'name' => '现付'),
                '2' => array('code' => '2', 'name' => '到付'),
                '3' => array('code' => '3', 'name' => '月结'),
                '4' => array('code' => '4', 'name' => '第三方支付'),
        );
        if (!empty($method)) {
            return $payMethod[$method];
        }
        return $payMethod;
    }
   public function  get_ExpType($type){
       $logistics = array( 
           'SF'=>array(
               1=>'顺丰次日',
               2=>'顺丰隔日',
               5=>'顺丰次晨',
               6=>'顺丰即日',
               9=>'顺丰宝平邮',
               10=>'顺丰宝挂号',
               11=>'医药常温',
               12=>'医药温控',
               13=>'物流普运',
               14=>'冷运宅配',
               15=>'生鲜速配',
               16=>'大闸蟹专递',
               17=>'汽配专线',
               18=>'汽配吉运',
               19=>'全球顺',
               37=>'云仓专配次日',
               38=>'云仓专配隔日'
           ),
          'HTKY' => array(
               1=>'标准快递'
           ),
          'DBKD'=>array(
              '1'=>'标准快递',
              '2'=>'360特惠件',
              '3'=>'电商尊享'
           ),
          'STO' => array(
              1=>'标准快递'
          ),
          'YTO' => array(
              0=>'自己联系',
              1=>'上门揽收',
              2=>'次日达',
              4=>'次晨达',
              8=>'当日达'
         ),
          'YD'=>array(
              1=>'标准快递'
         ),
          'EMS' => array(
              1=>'标准快递',
              4=>'经济快递',
              8=>'代收到付',
              9=>'快递包裹',
          ),
          'ZTO' =>array(
             1=>'普通订单',
             2=>'线下订单',
             3=>'COD订单',
             4=>'限时物流',
             5=>'快递保障订单'
          ),
         'QRT'=>array(
            1=>'标准快递'
          ),
         'UC'=>array(
           1=>'标准快递'
         ),
         'ZJS'=>array(
           1=>'标准快递'
         ),
         'KYSY'=>array(
           1=>'当天达',
           2=>'次日达',
           3=>'隔日达',
           4=>'同城件',
           5=>'同城即日',
           6=>'同城次日',
           7=>'陆运件',
         ),
         'QFKD'=>array(
           1=>'标准快递'
         ),
         'JD'=>array(
           1=>'标准快递'
         ),
         'XFEX'=>array(
           1=>'标准快递'
         ),
         'ANE'=>array(
           1=>'标准快递'
         ),
         'FAST'=>array(
           1=>'标准快递'
         ),
       );
       if(!empty($logistics)){
          return $logistics[$type];
       }else{
          return '';
       }
    }
}