<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 天工支付（支付宝）具体实现
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */

final class ectools_payment_plugin_cpcn extends ectools_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '中金支付';//中金支付
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '中金支付';
    /**
     * @var string 支付方式key
     */
    public $app_key = 'cpcn';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'cpcn';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '中金';
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
        return '<b><h3>中金支付</h3></b>';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function admin_intro(){
        return app::get('ectools')->_('<H3>中金支付<H3>');
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
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_cpcn', 'callback');
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

        $institutionID = $this->getConf('InstitutionID', __CLASS__);//机构号码
//        $institutionID='003480';
//        $paymentNo=$this->getPaymentNo();//流水号
//        $paymentNo='201801171345324560326842982';//流水号
        $paymentNo=$payment['payment_id'];//流水号
        $amount= intval($payment["total_amount"]*100);
//        $payerID = '88888888';//付款人注册ID
//        $payerName = '商派软件';//付款方名称
//        $settlementFlag = $this->getConf('settlementFlag', __CLASS__);
        $settlementFlag =$this->getConf('settlementFlag', __CLASS__);
//        $usage = '支付订单111';
//        $remark = '备注信息111';
        $note = '';
        $notificationURL = $this->callback_url;
        $xmltx1112=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2.0">
	<Head>
		<TxCode>1112</TxCode>
	</Head>
	<Body>
		<InstitutionID/>
		<PaymentNo/>
		<Amount/>
		<PayerID/>
		<PayerName/>
		<SettlementFlag/>
		<Usage/>
		<Remark/>
		<Note/>
		<NotificationURL/>
	</Body> 
</Request>
XML;

        $simpleXML= new SimpleXMLElement($xmltx1112);
        // 4.赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->PaymentNo=$paymentNo;
        $simpleXML->Body->Amount=$amount;
//        $simpleXML->Body->PayerID=$payerID;
//        $simpleXML->Body->PayerName=$payerName;
        $simpleXML->Body->SettlementFlag=$settlementFlag;
//        $simpleXML->Body->Usage=$usage;
//        $simpleXML->Body->Remark=$remark;
        $simpleXML->Body->Note=$note;
        $simpleXML->Body->NotificationURL=$notificationURL;
        $xmlStr = $simpleXML->asXML();
        $message=base64_encode(trim($xmlStr));
        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);
        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        echo $this->get_html($message,$signature);
        exit;
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
        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');
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
        $message = $_POST["message"];
        $signature = $_POST["signature"];
        $txName = "";
        $plainText=trim(base64_decode($message));
        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');


        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);

        $ok=$serobj->cfcaverify($plainText,$signature,$pub_key_file);



        if ($ok!=1){
            $message=app::get('ectools')->_("签名认证失败！");
            $ret['status'] = 'invalid';
        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $data=$simpleXML->Body;
            $datajson= json_encode($data);
            $arr=json_decode($datajson,1);
            $txCode=$simpleXML->Head->TxCode;
            $ret['payment_id'] = $arr['PaymentNo'];
            $ret['account'] = 'zhanghao';
            $ret['bank'] = app::get('ectools')->_('');
            $ret['pay_account'] = app::get('ectools')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $arr['Amount']/100;
            $ret['paycost'] = '0.000';
            $ret['cur_money'] =$arr['Amount']/100;
            $ret['trade_no'] = $arr['PaymentNo'];
            $ret['t_payed'] = strtotime($arr['BankNotificationTime']) ? strtotime($arr['BankNotificationTime']) : time();
            $ret['pay_app_id'] = "cpcn";
            $ret['pay_type'] = 'online';
            $ret['memo'] = $arr['Remark'] ?  $arr['Remark'] : "";

            switch ($txCode){
                case '1118':
                    $ret['status'] = 'succ';
                    break;
            }
        }
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
        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);


        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=trim(base64_decode($response[0]));
        // ee($plainText);

        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);

        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            $ret['msg']="验签失败";
            // $ret['msg']="维护中，暂不支持";
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

        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);


        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=trim(base64_decode($response[0]));
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=$serobj->cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            $res['status']='error';
            $res['msg']="验签失败";

        }else{
print_r($plainText);
            $res['status']='succ';
            $res['TxSNBinding']=$txsnbinding;
            $res['BankID']=$bankid;
            $res['AccountName']=$accountname;
            $res['AccountNumber']=$accountnumber;

        }
        return $res;
    }
    /*
     * 绑卡验证短信接口2532
     * */
    function checkcode($data){


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
        $serobj=kernel::single('ectools_payment_plugin_cpcn_service');
        $pk_file = $this->getConf('pk_file', __CLASS__);

        $signature=$serobj->cfcasign_pkcs12(trim($xmlStr),$pk_file);
        $response=$serobj->cfcatx_transfer($message,$signature);
        $plainText=base64_decode($response[0]);
        $pub_key_file = $this->getConf('pub_key_file', __CLASS__);
        $ok=cfcaverify($plainText,$response[1],$pub_key_file);
        if($ok!=1)
        {
            return array('sta'=>'error');

        }else{
            $simpleXML= new SimpleXMLElement($plainText);
            $obj=$simpleXML->Body;
            $info_json=json_encode($obj);
            $info_arr=json_decode($info_json,1);
            $info_arr['sta']='succ';
            return $info_arr;
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
    protected function get_html($message='',$signature='')
    {
        // 简单的form的自动提交的代码。
        header("Content-Type: text/html;charset=".$this->submit_charset);
        $strHtml ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
		<head>
		</head><body><div>Redirecting...</div>";
        $strHtml .= '<form action="' . $this->submit_url . '" method="' . $this->submit_method . '" name="pay_form" id="pay_form">';
        // Generate all the hidden field.
        $strHtml .= ' <input type="hidden" name="message" value="' . $message . '" />';
        $strHtml .= ' <input type="hidden" name="signature" value="' . $signature . '" />';
        $strHtml .= '<input type="submit" name="btn_purchase" value="'.app::get('ectools')->_('购买').'" style="display:none;" />';
        $strHtml .= '</form><script type="text/javascript">
						window.onload=function(){
							document.getElementById("pay_form").submit();
						}
					</script>';
        $strHtml .= '</body></html>';
        return $strHtml;
    }
}

?>
