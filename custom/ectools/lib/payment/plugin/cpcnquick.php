<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 中金支付具体实现
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */

final class ectools_payment_plugin_cpcnquick extends ectools_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '中金快捷支付';//中金支付
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '中金快捷支付';
    /**
     * @var string 支付方式key
     */
    public $app_key = 'cpcnquick';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'cpcnquick';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '中金快捷支付';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式的版本号
     */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'iscommon';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"CNY");
    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    function is_fields_valiad(){
        return true;
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function intro(){
        return '<b><h3>中金快捷支付</h3></b>';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function admin_intro(){
        return app::get('ectools')->_('<H3>中金快捷支付<H3>');
    }

    /**
     * 构造方法
     * @param object 传递应用的app
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);

        //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_cpcnquick', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
        {
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }
        else
        {
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }
        $this->submit_url = 'https://www.china-clearing.com/Gateway/InterfaceI';//正式环境
        // $this->submit_url = "https://test.cpcn.com.cn/Gateway/InterfaceI";//沙箱环境
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }







    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){
        $cardinfo=app::get('b2c')->model('member_bankcard')->getList('*',array('TxSNBinding'=>$payment['payinfo']["TxSNBinding"]));
        $institutionID = $this->getConf('InstitutionID', __CLASS__);//机构号码
        $paymentNo = $payment['payment_id'];
        $amount = intval($payment["total_amount"]*100);
        $txsnbinding = $payment['payinfo']["TxSNBinding"];
        $settlementFlag = $this->getConf('settlementFlag', __CLASS__);
        $validDate=$cardinfo[0]["ValidDate"];
        $cvn2=$cardinfo[0]["CVN2"];
        $xmltx2541=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0">
<Head>
<InstitutionID/>
<TxCode>2541</TxCode>
</Head>
<Body>
<PaymentNo/>
<TxSNBinding/>
<Amount/>
<SettlementFlag/>
<ValidDate/>
<CVN2/>
<Remark/>
</Body>
</Request>
XML;
        $simpleXML= new SimpleXMLElement($xmltx2541);

        // 4.赋值
        $simpleXML->Head->InstitutionID=$institutionID;
        $simpleXML->Body->PaymentNo=$paymentNo;
        $simpleXML->Body->Amount=$amount;
        $simpleXML->Body->TxSNBinding=$txsnbinding;
        $simpleXML->Body->SettlementFlag=$settlementFlag;
        $simpleXML->Body->CVN2=$cvn2;
        $simpleXML->Body->ValidDate=$validDate;


        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));
        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);
        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);

        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=base64_decode($response[0]);
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            return false;
        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $hobj=$simpleXML->Head;
            $harr = json_decode(json_encode($hobj),1);
            if ($harr['Code']=='2000') {
                $res['sta'] = 'succ';
                $res['msg'] = '发送成功！';
                if(strpos($payment['return_url'],'/wap/') !== false){
                    echo $this->get_waphtml($payment['return_url'],$paymentNo,$payment["total_amount"],date('Y-m-d H:i:s'));exit();
                }else{
                    echo $this->get_html($payment['return_url'],$paymentNo,$payment["total_amount"],date('Y-m-d H:i:s'));exit();
                }
            }else{

                echo "<script>alert('发送失败！:".$harr['Message']."');window.history.go(-1);</script>";

            }

        }

    }

    /*
     * 验证短信并支付
     * */
    function pay($in){


        $xmltx2542=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0">
<Head>
<InstitutionID/>
<TxCode>2542</TxCode>
</Head>
<Body>
<PaymentNo/>
<SMSValidationCode/>
<ValidDate/>
<CVN2/>
</Body>
</Request>
XML;


        $institutionID = $this->getConf('InstitutionID', __CLASS__);//机构号码
        $paymentNo = $in['pamentNo'];
        $smsvalidationcode = intval($in['code']);





        $simpleXML= new SimpleXMLElement($xmltx2542);

        // 4.赋值
        $simpleXML->Head->InstitutionID=$institutionID;
        $simpleXML->Body->PaymentNo=$paymentNo;
        $simpleXML->Body->SMSValidationCode=$smsvalidationcode;



        $xmlStr = $simpleXML->asXML();


        $message=base64_encode(trim($xmlStr));

        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);


        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=base64_decode($response[0]);
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);

        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            echo '验签失败！';
        }else{
            $h = explode('/index.php',$this->callback_url);
            $orderid=substr($paymentNo,0,15);
            $simpleXML= new SimpleXMLElement($plainText);
            $hobj=$simpleXML->Head;
            $harr = json_decode(json_encode($hobj),1);
            if ($harr['Code']=='2000') {
                $obj=$simpleXML->Body;
                $jsonobj=json_encode($obj);
                $arr = json_decode($jsonobj,1);
                $arr['money'] = $in['money'];
                kernel::single(base_httpclient)->post($this->callback_url,$arr);

                $backurl=$h[0].$in['return_url'];
                echo "<script>window.location.href='".$backurl."';</script>" ;exit();
            }else{
                echo "<script>alert('支付失败:".$harr['Message']."')</script>";

                if(strpos($in['return_url'],'/wap/') !== false){
                    $backurl=$h[0].'/index.php/wap/paycenter-'.$orderid.'.html';
                    echo "<script>window.location.href='".$backurl."';</script>" ;exit();
                }else{
                    $backurl=$h[0].'/index.php/paycenter-'.$orderid.'.html';
                    echo "<script>window.location.href='".$backurl."';</script>" ;exit();
                }


            }
        }
    }


    /*
     * 资金原路返回
     */
    function dorefund($in)
    {
        $institutionID = $this->getConf('InstitutionID', __CLASS__);;//机构号码
        $serialNumber = $in['refund_id'];
//        $serialNumber=$this->getPaymentNo();
        $paymentNo = $in['payment_id'];

        $amount = intval($in["refund_fee"]*100);
//      $remark = $_POST["Remark"];
        $xmltx1133=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0"> 
  <Head> 
    <TxCode>1133</TxCode> 
  </Head>  
  <Body> 
    <InstitutionID/>
		<SerialNumber/>
		<PaymentNo/>
		<Amount/>
		<Remark/>
		<RefundType/>
  </Body> 
</Request>
XML;
        $simpleXML= new SimpleXMLElement($xmltx1133);

        // 4.赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->SerialNumber=$serialNumber;
        $simpleXML->Body->PaymentNo=$paymentNo;
        $simpleXML->Body->Amount=$amount;
//        $simpleXML->Body->Remark=$remark;
        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));
        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);
        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);

        $response=$serobj->cfcatx_transfer($message,$signature);

        $plainText=base64_decode($response[0]);

        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            return false;

        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $data = $simpleXML->Body;

            $datajson = json_encode($data);
            $dataarr=json_decode($datajson,1);

            return true;
        }
    }


    /**
     * 支付回调的方法
     * @param array 回调参数数组
     * @return array 处理后的结果
     */
    function callback(&$in){
        $ret['payment_id'] = $in['PaymentNo'];
        $ret['account'] = 'zhanghao';
        $ret['bank'] = app::get('ectools')->_('');
        $ret['pay_account'] = app::get('ectools')->_('付款帐号');
        $ret['currency'] = 'CNY';
        $ret['money'] = $in['money'];
        $ret['paycost'] = '0.000';
        $ret['cur_money'] = $in['money'];
        $ret['trade_no'] = $in['PaymentNo'];
        $ret['t_payed'] = strtotime($in['BankNotificationTime']) ? strtotime($in['BankNotificationTime']) : time();
        $ret['pay_app_id'] = "cpcnquick";
        $ret['pay_type'] = 'online';
        $ret['memo'] ='';
        $ret['status'] = 'succ';
        return $ret;
    }

    /*
     * 对账单查询方法1810接口
     *
     */
    function reconciliation($in){
        $ret=array();
        $xmltx1810=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0"> 
  <Head> 
    <TxCode>1810</TxCode> 
  </Head>  
  <Body> 
    <InstitutionID/>  
    <Date/> 
  </Body> 
</Request>
XML;
        $institutionID = $this->getConf('InstitutionID', __CLASS__);
        $date = $in["Date"];

        $simpleXML= new SimpleXMLElement($xmltx1810);

        // 4.赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->Date=$date;

        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));
        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);


        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=trim(base64_decode($response[0]));
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            $ret['msg']="验签失败";
            $ret['status']="error";
            return $ret;

        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $obj=$simpleXML->Body;
            $info_json=json_encode($obj);
            $info_arr=json_decode($info_json,1);
            $info_arr['status']='succ';
            return $info_arr;
        }
    }
    /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    function setting(){
        return array(
            'pay_name'=>array(
                'title'=>app::get('ectools')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'InstitutionID'=>array(
                'title'=>app::get('ectools')->_('机构编号'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'settlementFlag'=>array(
                'title'=>app::get('ectools')->_('结算标识'),
                'type'=>'string',
                'validate_type' => 'required',
            ),

            'pub_key_file'=>array(
                'title'=>app::get('ectools')->_('企业公钥'),
                'type'=>'file',
                'validate_type'=>'required',
                'label'=>app::get('ectools')->_('文件后缀名.cer'),
            ),
            'pk_file'=>array(
                'title'=>app::get('ectools')->_('商户私钥'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('ectools')->_('文件后缀名为.pfx'),
            ),

            'order_by' =>array(
                'title'=>app::get('ectools')->_('排序'),
                'type'=>'string',
                'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
            ),

            'support_cur'=>array(
                'title'=>app::get('ectools')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_fee'=>array(
                'title'=>app::get('ectools')->_('交易费率'),
                'type'=>'pecentage',
                'validate_type' => 'number',
            ),
            'pay_brief'=>array(
                'title'=>app::get('ectools')->_('支付方式简介'),
                'type'=>'textarea',
            ),
            'pay_desc'=>array(
                'title'=>app::get('ectools')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                'name' => 'pay_type',
            ),
            'status'=>array(
                'title'=>app::get('ectools')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                'name' => 'status',
            ),
        );
    }
    /*
     * 中金支付2531绑卡获取短信接口
     * */
    function savecard($param){

        if ($param['CardType']==20){
            if ($param['ValidDate']==''&&$param['CVN2']){
                return array('status'=>'error','msg'=>'信用卡必须填写有效期和卡号后三位！');
            }
        }
        $xmltx2531=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0">
<Head>
<InstitutionID/>
<TxCode>2531</TxCode>
</Head>
<Body>
<TxSNBinding/>
<BankID/>
<AccountName/>
<AccountNumber/>
<IdentificationType/>
<IdentificationNumber/>
<PhoneNumber/>
<CardType/>
<ValidDate/>
<CVN2/>
</Body>
</Request>
XML;
        $res=array();
        // 1.取得参数
        $institutionid = $this->getConf('InstitutionID', __CLASS__);
        $txsnbinding = $this->getPaymentNo();//获取绑定流水号
        $bankid = $param["BankID"];
        $accountname = $param["AccountName"];
        $accountnumber = $param["AccountNumber"];
        $identificationtype = intval($param["IdentificationType"]);
        $identificationnumber = $param["IdentificationNumber"];
        $phonenumber = $param["PhoneNumber"];
        $cardtype = intval($param["CardType"]);
        $validdate = $param["ValidDate"];
        $cvn2 = $param["CVN2"];

        $simpleXML= new SimpleXMLElement($xmltx2531);
        // 4.赋值
        $simpleXML->Head->InstitutionID=$institutionid;
        $simpleXML->Body->TxSNBinding=$txsnbinding;
        $simpleXML->Body->BankID=$bankid;
        $simpleXML->Body->AccountName=$accountname;
        $simpleXML->Body->AccountNumber=$accountnumber;
        $simpleXML->Body->IdentificationType=$identificationtype;
        $simpleXML->Body->IdentificationNumber=$identificationnumber;
        $simpleXML->Body->PhoneNumber=$phonenumber;
        $simpleXML->Body->CardType=$cardtype;
        $simpleXML->Body->ValidDate=$validdate;
        $simpleXML->Body->CVN2=$cvn2;

        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));

        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);


        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=trim(base64_decode($response[0]));
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            $res['status']='error';
            $res['msg']="验签失败!";
        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $hobj=$simpleXML->Head;
            $harr = json_decode(json_encode($hobj),1);
            if ($harr['Code']=='2000'){
                $res['status']='succ';
                $res['TxSNBinding']=$txsnbinding;
                $res['BankID']=$bankid;
                $res['AccountName']=$accountname;
                $res['AccountNumber']=$accountnumber;
            }else{
                $res['ststus']='error';
                $res['msg']=$harr['Message'];
            }
        }
        return $res;
    }
    /*
     * 绑卡验证短信接口2532
     * */
    function checkcode($data){

//成功响应报文
//<?xml version="1.0" encoding="UTF-8" standalone="no"
//<!--<Response>-->
//<!--    <Head>-->
//<!--        <Code>2000</Code>-->
//<!--        <Message>OK.</Message>-->
//<!--    </Head>-->
//<!--    <Body>-->
//<!--    <InstitutionID>003480</InstitutionID>-->
//<!--    <TxSNBinding>201801231418154586506749794</TxSNBinding>-->
//<!--    <VerifyStatus>40</VerifyStatus>-->
//<!--    <Status>30</Status>-->
//<!--    <BankTxTime>20180123141923</BankTxTime>-->
//<!--    <ResponseCode>00</ResponseCode>-->
//<!--    <ResponseMessage>成功[0000000]</ResponseMessage>-->
//<!--    <IssueBankID>105</IssueBankID>-->
//<!--    <IssueCardType>10</IssueCardType>-->
//<!--    <IssInsCode/>-->
//<!--    <PayCardType/>-->
//<!--    </Body>-->
//<!--</Response>        -->






        $xmltx2532=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0">
<Head>
<InstitutionID/>
<TxCode>2532</TxCode>
</Head>
<Body>
<TxSNBinding/>
<SMSValidationCode/>
<ValidDate/>
<CVN2/>
</Body>
</Request>
XML;
        $institutionID = $this->getConf('InstitutionID', __CLASS__);
        $txsnbinding = $data["TxSNBinding"];
        $smsvalidationcode = intval($data["SMSValidationCode"]);
        $validDate=$_POST["ValidDate"];
        $cvn2=$_POST["CVN2"];



        $simpleXML= new SimpleXMLElement($xmltx2532);

        // 4.赋值
        $simpleXML->Head->InstitutionID=$institutionID;
        $simpleXML->Body->TxSNBinding=$txsnbinding;
        $simpleXML->Body->SMSValidationCode=$smsvalidationcode;
        $simpleXML->Body->CVN2=$cvn2;
        $simpleXML->Body->ValidDate=$validDate;

        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));
        $serobj=kernel::single('ectools_payment_plugin_cpcnquick_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);

        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=base64_decode($response[0]);
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            return array('sta'=>'error');

        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $hobj=$simpleXML->Head;
            $harr = json_decode(json_encode($hobj),1);
            if ($harr['Code']=='2000'){
                $obj=$simpleXML->Body;
                $info_json=json_encode($obj);
                $info_arr=json_decode($info_json,1);
                $info_arr['sta']='succ';
                return $info_arr;
            }else{
                $res['sta']='error';
                $res['msg']=$harr['Message'];
                return $res;
            }
        }
    }


    /*
     *生成银行流水号
     */
    private function getPaymentNo(){
        $arr_time=explode(' ', microtime());
        $timePrefix = date("YmdHis");
        $ms=floatval($arr_time[0])*1000;
        $ms=floor($ms);
        $randomString = $this->genRandomString(10);
        return $timePrefix.$ms.$randomString;
    }

    /**
     *  作用：array转xml
     */
    function genRandomString($lens)
    {
        $output='';
        for ($i=0; $i< $lens; $i++)
        {
            $output .= mt_rand(0, 9);
        }

        return $output;
    }
    function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
    protected function get_html($url='',$paymentNo='',$money='',$time='')
    {
        $strHtml='<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="author" content="rookie" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="format-detection" content="telephone=no" />
	<title>中金快捷短信支付</title>
	<style>
		*{
			margin: 0;
			padding: 0;
			border: none;
		}
		html,body{
		    font-family: "miscrosoft yahei", 微软雅黑, tahoma, arial, "Hiragino Sans GB", sans-serif;
		}
		ul,li{
			list-style: none;
		}
		.banner{
			height: 160px;
			background: url(\'https://test.cpcn.com.cn/Gateway/style/images/common/layout/banner.png\') no-repeat center center;
		}
		.detail{
			margin-bottom: 40px;
			height: 100px;
			background: #fafafa;
			border-bottom: 1px solid #ddd;
		}
		.detail-box{
			margin: 0 auto;
			padding-top: 10px;
			width: 1000px;
			height: 100%;
			box-sizing: border-box;
		}
		.detail-box h3{
			display: inline-block;
			vertical-align: middle;
			font-size: 12px;
			line-height: 1;
			color: #333;
		}
		.detail-box h4{
			margin: -4px 0 0;
			display: inline-block;
			vertical-align: middle;
			font-size: 20px;
			font-weight: bold;
			line-height: 1;
			color: #ee1f23;
		}
		.detail-box span{
			display: inline-block;
			vertical-align: middle;
			font-size: 12px;
			line-height: 1;
			color: #444;
		}
		.detail-row{
			padding: 10px 0;
		}
		.detail-row ul{
			font-size: 0;
		}
		.detail-row ul li{
			margin-right: 50px;
			display: inline-block;
			vertical-align: middle;
		}
		.name{
			margin: 30px 0;
			font-size: 30px;
			line-height: 1;
			color: #333;
			text-align: center;
		}
		.frame{
			margin: 0 auto;
			padding: 35px 50px;
			width: 1000px;
			height: 200px;
			border: 2px solid #72d0ff;
			box-sizing: border-box;
		}
		.frame ul li{
			margin-bottom: 20px;
			display: block;
		}
		.frame .field{
			display: inline-block;
			vertical-align: middle;
			font-size: 14px;
			line-height: 1;
			color: #333;
		}
		.frame input[type="text"]{
    		padding: 6px 12px;
			display: inline-block;
			vertical-align: middle;
			width: 180px;
			height: 34px;
		    background-color: #fff;
		    border: 1px solid #ccc;
			box-sizing: border-box;
		    font-size: 14px;
		    font-family: microsoft yahei;
		    color: #555;
		}
		.submit{
			width: 100px;
			height: 32px;
			background: #db251d;
			text-align: center;
			cursor: pointer;
			font-size: 14px;
		    font-family: microsoft yahei;
			line-height: 32px;
			color: #fff;
		}
	</style>
</head>
<body>
	
	<div class="banner"></div>
	<div class="detail">
		<div class="detail-box">
			<div class="detail-row">
				<h3>应付总金额：</h3>
				<h4>'.$money.'</h4>
				<span>元</span>
			</div>
			<div class="detail-row">
				<ul>
					<li>
						<h3>支付流水号：</h3>
						<span>'.$paymentNo.'</span>
					</li>
					<li>
						<h3>金额：</h3>
						<span>'.$money.'</span>
					</li>
					<li>
						<h3>时间：</h3>
						<span>'.$time.'</span>
					</li>	
				</ul>
			</div>
		</div>
	</div>
	<div class="name">中金快捷支付</div>
	<div class="frame">
        <form action=\'/index.php/paycenter-dopay-order.html\' method=\'POST\'>
            <ul>
                <li>
                    <div class="field">短信验证码：</div>
                    <input type="text" name=\'code\' placeholder="请输入短信验证码">
                    <input type="hidden"  name=\'PaymentNo\' value="'.$paymentNo.'">
                    <input type="hidden"  name=\'money\' value="'.$money.'">
                    <input type="hidden"  name=\'return_url\' value="'.$url.'">
                </li>
                <li>
                    <input type="submit" class="submit">
                </li>
            </ul>
        </form>
	</div>


</body>
</html>';
        return $strHtml;

    }

    public function get_waphtml($url,$paymentNo,$money,$time)
    {
        $htmlstr='<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="author" content="rookie" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="format-detection" content="telephone=no" />
	<title>中金快捷短信支付</title>
	<style>
		*{
			margin: 0;
			padding: 0;
			border: none;
		}
		html,body{
		    font-family: "miscrosoft yahei", 微软雅黑, tahoma, arial, "Hiragino Sans GB", sans-serif;
		}
		ul,li{
			list-style: none;
		}
		.banner{
			height: 160px;
			background: url(\'https://test.cpcn.com.cn/Gateway/style/images/common/layout/banner.png\') no-repeat center center;
		}
		.detail{
			margin-bottom: 30px;
			background: #fafafa;
			border-bottom: 1px solid #ddd;
		}
		.detail-box{
			margin: 0 auto;
			padding: 20px 50px;
			height: 100%;
			box-sizing: border-box;
		}
		.detail-box h3{
			display: inline-block;
			vertical-align: middle;
			font-size: 12px;
			line-height: 1;
			color: #333;
		}
		.detail-box h4{
			margin: -4px 0 0;
			display: inline-block;
			vertical-align: middle;
			font-size: 20px;
			font-weight: bold;
			line-height: 1;
			color: #ee1f23;
		}
		.detail-box span{
			display: inline-block;
			vertical-align: middle;
			font-size: 12px;
			line-height: 1;
			color: #444;
		}
		.detail-row{
			padding: 5px 0;
		}
		.detail-row ul{
			font-size: 0;
		}
		.detail-row ul li{
			margin: 10px 0;
		}
		.frame{
			margin: 0 auto;
		    padding: 30px;
		    width: 85%;
		    height: 170px;
		    border: 1px solid #db251d;
		    box-sizing: border-box;
		}
		.frame ul li{
			margin-bottom: 20px;
			display: block;
		}
		.frame .field{
			display: inline-block;
			vertical-align: middle;
			font-size: 14px;
			line-height: 1;
			color: #333;
		}
		.frame input[type="text"]{
    		padding: 6px 12px;
			display: inline-block;
			vertical-align: middle;
			width: 150px;
			height: 34px;
		    background-color: #fff;
		    border: 1px solid #ccc;
			box-sizing: border-box;
		    font-size: 14px;
		    font-family: microsoft yahei;
		    color: #555;
		}
		.submit{
			width: 100%;
			height: 32px;
			background: #db251d;
			text-align: center;
			cursor: pointer;
			font-size: 14px;
		    font-family: microsoft yahei;
			line-height: 32px;
			color: #fff;
		}
	</style>
</head>
<body>
	
	<div class="banner"></div>
	<div class="detail">
		<div class="detail-box">
			<div class="detail-row">
				<h3>应付总金额：</h3>
				<h4>'.$money.'</h4>
				<span>元</span>
			</div>
			<div class="detail-row">
				<ul>
					<li>
						<h3>支付流水号：</h3>
						<span>'.$paymentNo.'</span>
					</li>
					<li>
						<h3>金额：</h3>
						<span>'.$money.'</span>
					</li>
					<li>
						<h3>时间：</h3>
						<span>'.$time.'</span>
					</li>	
				</ul>
			</div>
		</div>
	</div>
	
	<div class="frame">
	    <form action=\'/index.php/wap/paycenter-dopay-order.html\' method=\'POST\'>
            <ul>
                <li>
                    <div class="field">短信验证码：</div>
                    <input type="text" name=\'code\' placeholder="请输入短信验证码">
                     <input type="hidden"  name=\'PaymentNo\' value="'.$paymentNo.'">
                     <input type="hidden"  name=\'money\' value="'.$money.'">
                     <input type="hidden"  name=\'return_url\' value="'.$url.'">
                </li>
                <li>
                    <input type="submit" class="submit">
                </li>
            </ul>
		</form>
	</div>


</body>
</html>';

        return $htmlstr;
    }
}

?>
