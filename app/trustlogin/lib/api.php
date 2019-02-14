<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * 外部信任登陆接口统一调用的api类
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment
 */
class trustlogin_api 
{
    /**
     * @var object 应用对象的实例。
     */
    private $app;

    /**
     * 构造方法
     * @param object 当前应用的app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 第三方同意后的处理
     * @params array - 页面参数
     * @return null
     */
    public function parse()
    {
        // 取到内部系统参数
        $arr_pathInfo = explode('?', $_SERVER['REQUEST_URI']);
        $pathInfo = substr($arr_pathInfo[0], strpos($arr_pathInfo[0], "parse/") + 6);
        $objShopApp = $this->getAppName($pathInfo);
        $innerArgs = explode('/', $pathInfo);
        $class_name = array_shift($innerArgs);
        $method = array_shift($innerArgs);
        //获取返回值
        $arrStr = array();
        $arrSplits = array();
        $arrQueryStrs = array();
        // QUERY_STRING
        if (isset($arr_pathInfo[1]) && $arr_pathInfo[1]){
            $querystring = $arr_pathInfo[1];
        }
        if ($querystring)
        {
            $arrStr = explode("&", $querystring);

            foreach ($arrStr as $str)
            {
                $arrSplits = explode("=", $str);
                $arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
            }
        }
        else
        {
            if ($_POST)
            {
                $arrQueryStrs = $_POST;
            }
        }

        $objtrustlogin = new $class_name($objShopApp);
        if(!$objtrustlogin instanceof trustlogin_interface_trust){
            die('Plugin error');
        }
        //返回数据
        $ret = $objtrustlogin->$method($arrQueryStrs);
        if(!$ret)
        {
            return false;
        }
        $ret['data']['trust_source'] = $class_name;
        $trustMdl = app::get('trustlogin')->model('trustinfo');
        $pamMemberMdl = app::get('pam')->model('members');
        $pamAuthMdl = app::get('pam')->model('auth');
	    if(preg_match('/wap_trustlogin_/',$ret['data']['trust_source'])){
            $path=preg_replace('/wap_trustlogin_/','',$ret['data']['trust_source']);
	        $ret['data']['trust_source']='trustlogin_plugin_'.$path;
	    }
	    $checkData = array(
            'openid'=>$ret['data']['openid'],
            'trust_source'=>$ret['data']['trust_source'],
            //'realname'=>$ret['data']['realname'],
            //'nickname'=>$ret['data']['nickname'],
        );
        $trustData = $trustMdl->getRow('trust_id,member_id',$checkData);//检查是否已经登录过了
        $_SESSION['trustlogin_openid']=$ret['data']['openid'];//以后校验备用，以免被恶意注册
        if($trustData)
        {
            $memberData = $pamMemberMdl->getRow('member_id',array('member_id'=>$trustData['member_id']));
            if($memberData['member_id'])
            {
                if(empty($ret['data']['realname']))
                {
                    $module_uid = $ret['data']['nickname'].'_'.$ret['data']['openid'];
                }else
                {
                    $module_uid = $ret['data']['realname'].'_'.$ret['data']['openid'];
                }

                $params['type'] = pam_account::get_account_type('b2c');
                $params['module'] = 'trustlogin_passport_trust';
                $params['data'] = $ret;
                if($module_uid)
                {
                    $auth = pam_auth::instance($params['type']);
                    $params['data']['account_type'] = $params['type'];
                    //$auth->account()->update($params['module'], $module_uid, $params['data']);
                    $this->bind_member($memberData['member_id']);
                    $_SESSION['account']['member'] = $memberData['member_id'];
                    if($ret['type']=='pc')
                    {
                        $back_url = app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trust','act'=>'post_login','arg0'=>$ret['type'],'full'=>1));
                    }
                    if($ret['type']=='wap')
                    {
                        $back_url = app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trustwap','act'=>'post_login','arg0'=>$ret['type'],'full'=>1));
                    }
                    echo "<script>top.window.location='".$back_url."'</script>";
                }
            }
            else
            {
                $pamAuthMdl->delete(array('account_id'=>$trustData['member_id']));
                $trustData = $trustMdl->delete($checkData);
                $params['type'] = pam_account::get_account_type('b2c');
                $params['module'] = 'trustlogin_passport_trust';
                $params['redirect'] = base64_encode($back_url);
                $params['data'] = urlencode(json_encode($ret));
                if($ret['type']=='pc')
                {
                    $params['url'] =app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trust','act'=>'bind_login','full'=>1));
                }
                if($ret['type']=='wap')
                {
                    $params['url'] =app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trustwap','act'=>'bind_login','full'=>1));
                }
                echo $this->get_html($params);exit();
            }
        }
        else
        {
            $params['type'] = pam_account::get_account_type('b2c');
            $params['module'] = 'trustlogin_passport_trust';
            $params['redirect'] = base64_encode($back_url);
            $params['data'] = urlencode(json_encode($ret));
            if($ret['type']=='pc')
            {
                $params['url'] =app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trust','act'=>'bind_login','full'=>1));
            }
            if($ret['type']=='wap')
            {
                $params['url'] =app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trustwap','act'=>'bind_login','full'=>1));
            }
            //$params['url'] =app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trust','act'=>'bind_login','full'=>1));
            echo $this->get_html($params);exit();
           
        }
    }



    /**
     * 生成支付方式提交的表单的请求
     * @params null
     * @return string
     */
    protected function get_html($params)
    {
        // 简单的form的自动提交的代码。
        header("Content-Type: text/html;charset=utf-8");
        $strHtml ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
        <head>
        </head><body><div>Redirecting...</div>";
        $strHtml .= '<form action="' . $params['url'] . '" method="post" name="pay_form" id="pay_form">';
        foreach ($params as $key=>$value)
        {
            $strHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }

        $strHtml .= '<input type="submit" name="btn_purchase" value="'.app::get('trustlogin')->_('绑定').'" style="display:none;" />';
        $strHtml .= '</form><script type="text/javascript">
                        window.onload=function(){
                            document.getElementById("pay_form").submit();
                        }
                    </script>';
        $strHtml .= '</body></html>';
        return $strHtml;
    }
    //
    public function todecirct($data)
    {
        $back_url = app::get('site')->router()->gen_url(array('app'=>'trustlogin','ctl'=>'trustlogin_trust','act'=>'post_login','arg0'=>$data['data']['type'],'full'=>1));
        $this->login($data);
        return "<script>top.window.location='".$back_url."'</script>";
    }

    /**
     * 得到实例应用名
     * @params string - 请求的url
     * @return object - 应用实例
     */
    public function getAppName($strUrl='')
    {
        if (strpos($strUrl, '/') !== false)
        {
            $arrUrl = explode('/', $strUrl);
        }
        return app::get($arrUrl[0]);
    }

    function bind_member($member_id){
        $columns = array(
            'account'=> 'member_id,login_account,login_password',
            'members'=> 'member_id,member_lv_id,cur,lang',
        );
        $userObject = kernel::single('b2c_user_object');
        $data = $userObject->get_members_data($columns);
        $secstr = kernel::single('b2c_user_passport')->gen_secret_str($member_id, $data['account']['login_name'], $data['account']['login_password']);
        $login_name = $userObject->get_member_name($data['account']['login_name']);
        $this->cookie_path = kernel::base_url().'/';
        #$this->set_cookie('MEMBER',$secstr,0);
        $this->set_cookie('loginName',$login_name,time()+31536000);
        $this->set_cookie('UNAME',$login_name,0);
        $this->set_cookie('MLV',$data['members']['member_lv_id'],0);
        $this->set_cookie('CUR',$data['members']['cur'],0);
        $this->set_cookie('LANG',$data['members']['lang'],0);
        $this->set_cookie('S[MEMBER]',$member_id,0);
    }
    function set_cookie($name,$value,$expire=false,$path=null){
        if(!$this->cookie_path){
            $this->cookie_path = kernel::base_url().'/';
            #$this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
            $this->cookie_life =  app::get('b2c')->getConf('system.cookie.life');
        }
        $this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
        $expire = $expire === false ? time()+$this->cookie_life : $expire;
        setcookie($name,$value,$expire,$this->cookie_path);
        $_COOKIE[$name] = $value;
    }
    /**
    * 登录调用的方法
    * @param array $params 认证传递的参数,包含认证类型，跳转地址,第三方返回数据等
    */
    function login($params)
    {
        $params['module'] = utils::_filter_input($params['module']);//过滤xss攻击
        $auth = pam_auth::instance($params['type']);
        
        try{
            class_exists($params['module']);
        }catch (Exception $e){
            kernel::single('site_router')->http_status('p404');
        }
        if($params['module'])
        {
            if(class_exists($params['module']) && ($passport_module = kernel::single($params['module'])))
            {
                $module_uid = $passport_module->login($params);
                if($module_uid)
                {
                    $params['data']['account_type'] = $params['type'];
                    $auth->account()->update($params['module'], $module_uid, $params['data']);
                }
            }
            else
            {

            }
        }
    }
}
