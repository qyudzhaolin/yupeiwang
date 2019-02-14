<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

class desktop_cert_certcheck
{
	function __construct($app)
        {
		$this->app = $app;
	}

    public function hasShopexId()
    {
        if( base_enterprise::ent_id()) return true;
        return false;
    }

    function check($params)
    {
	    if($this->is_demosite()) return ;
        if(!$this->hasShopexId()) return ;

        $activation_arr = $this->app->getConf('activation_code');
        logger::info('activation_code:'.var_export($activation_arr,1));
		if($activation_arr) return ;
		else
		{
            if( $params ){
                unset($_SESSION['account'][$params['type']]);
            }
            $url = $this->app->base_url(1);
            $code_url = $url.'index.php?app=desktop&ctl=code&act=error_view';
            echo "<script>location.href='".$code_url."'</script>";exit;
		}
    }

    function error_view($auth_error_msg=null)
    {
		$render = $this->app->render();
        $shopexIdUrl = app::get('base')->getConf('certificate_code_url');
        if( $shopexIdUrl && $shopexIdUrl != kernel::base_url(1) ){
		    $render->pagedata['url'] = $shopexIdUrl;
        }
        $url = $this->app->base_url(1);
        $render->pagedata['post_url'] = $url.'index.php?app=desktop&ctl=code&act=codecheck';
		$render->pagedata['res_url'] = $this->app->res_url;
		$render->pagedata['auth_error_msg'] = $auth_error_msg;
		return $render->display('active_code.html');
	}
	/**
	  *		ocs :
	  * 	$method = 'active.do_active'
	  *		$ac = 'SHOPEX_ACTIVE'
	  *
	  *		其它产品默认
	  */
	function check_code($code=null,$method='oem.do_active',$ac = 'SHOPEX_OEM')
	{
		if(!$code)return false;
		$certificate_id = base_certificate::certi_id();
		if(!$certificate_id)base_certificate::register();
		$certificate_id = base_certificate::certi_id();
		$token =  base_certificate::token();
		$data = array(
		'certi_app'=>$method,
		'certificate_id'=>$certificate_id,
		'active_key'=>$_POST['auth_code'],
		'ac'=>md5($certificate_id.$ac));
        logger::info("LICENSE_CENTER_INFO:".print_r($data,1));
		$result = kernel::single('base_httpclient')->post(LICENSE_CENTER_INFO,$data);
        logger::info("LICENSE_CENTER_INFO:".print_r($result,1));
		$result = json_decode($result,true);
		return $result;
	}

	function check_certid()
	{
		$params['certi_app'] = 'open.login';
        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        $params['certificate_id']  = $this->Certi;
        $params['format'] = 'json';
        /** 增加反查参数result和反查基础地址url **/
        $code = md5(microtime());
        base_kvstore::instance('ecos')->store('net.login_handshake',$code);
        $params['result'] = $code;
		$obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $params['version'] = $app_xml['local_ver'];
        $params['url'] = kernel::base_url(1);
        /** end **/
        $token = $this->Token;
        $str   = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.=$value;
        }
        $params['certi_ac'] = md5($str.$token);
        $http = kernel::single('base_httpclient');
        $http->set_timeout(20);
        $result = $http->post(LICENSE_CENTER_INFO,$params);
        $api_result = stripslashes($result);
        $api_arr = json_decode($api_result,true);
		return $api_arr;
	}

    function listener_login($params)
    {
        //验证域名是否正确
        if(!kernel::single('base_license_active')->checkHomeUrl())
        {
            app::get('desktop')->setConf('chk_certid_errtimes', 999);
            app::get('desktop')->setConf('chk_certid_lasttime', 0);
        }

        if($this->is_demosite()) return ;
        //距离上次成功验证时长不足一周的，不用验证
        $chk_certid_lasttime = app::get('desktop')->getConf('chk_certid_lasttime');
        if($chk_certid_lasttime && (time()-$chk_certid_lasttime)<86400*7){
            return ;
        }

        //预先计算验证失败的次数
        $chk_certid_errtimes = app::get('desktop')->getConf('chk_certid_errtimes');
        $chk_certid_errtimes = intval($chk_certid_errtimes) + 1;

        $res = kernel::single('base_license_active')->checkActiveKey();
        if($res['code'] === 0){
            app::get('desktop')->setConf('chk_certid_errtimes', 0);
            app::get('desktop')->setConf('chk_certid_lasttime', time());
            kernel::single('base_license_active')->writeHomeUrl();
            return ;
        }
        elseif($chk_certid_lasttime && $chk_certid_errtimes < 5 )
        {
            app::get('desktop')->setConf('chk_certid_errtimes', $chk_certid_errtimes);
            return ;
        }else{

  //        $pagedata['msg'] = ($res['message_zh'])?$res['message_zh']:"";
  //        $pagedata['url'] = $url = url::route('shopadmin');
  //        $pagedata['res_url'] = app::get('desktop')->res_url;
  //        $pagedata['code_url'] = url::route('shopadmin', array('app' => 'desktop', 'ctl' => 'code', 'act' => 'error_view'));
  //        echo  view::make('desktop/codetip.html', $pagedata);exit;
            unset($_SESSION['account'][$params['type']]);
            $this->error_info_view($res);
            $url = $this->app->base_url(1);
            $code_url = $url.'index.php?app=desktop&ctl=code&act=error_info_view&result[msg]='.$result['msg'];
            echo "<script>location.href='".$code_url."'</script>";exit;


        }
    }

    public function error_info_view($errorInfo){
        $render =  app::get('desktop')->render();
        $msg = 'Error：'. $errorInfo['code'] . ',' . ($errorInfo['message_zh'] ? : $errorInfo['message_zh']);
        $render->pagedata['msg'] = ($msg)?$msg:"";
        $render->pagedata['url'] = $errorInfo['url'];
        echo  $render->fetch('codetip.html');
        exit;

    }


	function listener_login2($params)
	{
        $this->check($params);
		$opencheck = false;
		$objCertchecks = kernel::servicelist("desktop.cert.check");
        foreach ($objCertchecks as $objCertcheck)
        {
            if(method_exists($objCertcheck , 'certcheck') && $objCertcheck->certcheck()){
				$opencheck = true;
				break;
			}
        }
	    if($this->is_demosite()) return ;
        $kvstore = base_kvstore::instance('ecos');

        $kvstore->fetch('chk_certid_lasttime', $chk_certid_lasttime);
        if($chk_certid_lasttime && (time()-$chk_certid_lasttime)<86400*7){
            return ;
        }

		if($params['type'] === pam_account::get_account_type('desktop'))
		{
			$result = $this->check_certid();
            $kvstore->fetch('chk_certid_errtimes',$chk_certid_errtimes);
            $chk_certid_errtimes = intval($chk_certid_errtimes) + 1;

			if($result['res'] == 'succ')
			{
                if( $result['info']['valid'] ){
                    app::get('desktop')->setConf('activation_code_check', true);
                    if( !app::get('base')->getConf('certificate_code_url') )
                        app::get('base')->setConf('certificate_code_url',kernel::base_url(1));
                     $kvstore->store('chk_certid_errtimes', 0);
                    return ;
                }else{
                    if($chk_certid_errtimes < 5){
                        $kvstore->store('chk_certid_errtimes',$chk_certid_errtimes);
                        $kvstore->store('chk_certid_lasttime', time());
                        return ;
                     }
                    $this->app->setConf('activation_code','');
                }
			}
            else
            {
                if($chk_certid_errtimes < 5){
                    $kvstore->store('chk_certid_errtimes',$chk_certid_errtimes);
                    $kvstore->store('chk_certid_lasttime', time());
                    return ;
                }
                $url = $this->app->base_url(1);
                $code_url = $url.'index.php?app=desktop&ctl=code&act=error_info_view&result[msg]='.$result['msg'];
                echo "<script>location.href='".$code_url."'</script>";exit;
            }
        }
	}

    function check_error_info($error_code){
        if( $error_code == 'RegUrlError' ){
            $regurl = app::get('desktop')->getConf('activation_code_regurl');
        }
    }

    /*
     * 检测当环境是外网demo站点时的跳过激活检测
     */
    function is_demosite(){
        if(defined('DEV_CHECKDEMO') && DEV_CHECKDEMO){
            return true;
        }
    }

	function is_internal_ip()
	{
        $ip = $this->remote_addr();
        if($ip=='127.0.0.1' || $ip=='::1'){
            return true;
        }

		$ip = ip2long($ip);
		$net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
		$net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
		$net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
		return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c;
    }


	function remote_addr()
	{
		if(!isset($GLOBALS['_REMOTE_ADDR_'])){
			$addrs = array();

			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				foreach( array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
				{
					$x_f = trim($x_f);

					if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
					{
						$addrs[] = $x_f;
					}
				}
			}

			$GLOBALS['_REMOTE_ADDR_'] = isset($addrs[0])?$addrs[0]:$_SERVER['REMOTE_ADDR'];
		}
		return $GLOBALS['_REMOTE_ADDR_'];
	}
}
