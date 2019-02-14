<?php
class weixin_wechat{

    public function __construct($app){
        $this->weixinObject = kernel::single('weixin_object');
        $this->weixinMsg = kernel::single('weixin_message');
    }


    public function checkSignature($signature, $timestamp, $nonce, $token){
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( strtoupper($tmpStr) == strtoupper($signature) ){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  发消息
     */
    public function send_msg($postData,$paramsData){
        $data = array();
        if( !empty($paramsData) ){
            $commData['ToUserName'] = $postData['FromUserName'];
            $commData['FromUserName'] = $postData['ToUserName'];
            $commData['CreateTime'] = time();
            $data = array_merge($commData,$paramsData);
        }
        $this->weixinObject->send($data);
    }

    /**
     * 订阅事件处理
     **/
    public function subscribe($postData){
        //发送关注自动回复
        $messageData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'reply_type'=>'attention') );
        $paramsData = $this->get_message($messageData[0]['message_id'], $messageData[0]['message_type'], $postData);
        $this->send_msg($postData,$paramsData);
    }

    /**
     * 自定义菜单消息回复
     */
    public function click($postData){
        $EventKey = explode('_',$postData['EventKey']);
        $paramsData = $this->get_message($EventKey[1], $EventKey[0], $postData);
        $this->send_msg($postData,$paramsData);
    }

    /**
     * 普通消息公共调用
     * MsgType ： text image voice video 等
     */
    public function commonMsg($postData){

        $msgData = array();
        if( $postData['MsgType'] == 'text' && !empty($postData['Content']) ){
            $msgData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'keywords'=>$postData['Content']));
        }
        if( empty($msgData) ){
            $msgData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'reply_type'=>'message'));
        }
        $paramsData = $this->get_message($msgData[0]['message_id'], $msgData[0]['message_type'], $postData);
        $this->send_msg($postData,$paramsData);
    }

    /**
     * 获取回复消息 文字|图文
     *
     * @params $msg_id   int    消息ID
     * @params $msg_type string 消息内容
     * @params $postData array  微信POST数据
     */
    public function get_message($msg_id, $msg_type, $postData){
        $urlParams = $this->weixinObject->set_wechat_sign($postData);
        if( $msg_type == 'text' || $msg_type == 'customers'){
            $messageData = app::get('weixin')->model('message_text')->getList('is_check_bind',array('id'=>$msg_id) );
        }else{
            $messageData = app::get('weixin')->model('message_image')->getList('is_check_bind',array('id'=>$msg_id));
        }
        $shopBindWeixin = app::get('pam')->model('bind_tag')->getList('id',array('open_id'=>$postData['FromUserName']));
        if( $messageData[0]['is_check_bind'] == 'true' && empty($shopBindWeixin) ){
            $content = app::get('weixin')->getConf('weixin_sso_setting');
            $arrUrl = preg_match_all("/href[\s]*?=[\s]*?[\'|\"](.+?)[\'|\"]/",$content['noBindText'],$match);
            foreach((array)$match[1] as $url){
                if( stristr($url, '?' ) ){
                    $tmp_url = $url.'&'.$urlParams;
                }else{
                    $tmp_url = $url.'?'.$urlParams;
                }
                $content = str_replace( $url, $tmp_url, $content['noBindText']);
            }
            $paramsData['Content'] = $content;
            $paramsData['MsgType'] = 'text';
        }else{
            $paramsData = $this->weixinMsg->get_message($msg_id, $msg_type, $urlParams);
        }
		if( $msg_type == 'customers' ){
			$paramsData['MsgType'] = 'transfer_customer_service';
			$this->customers_send($postData,$paramsData['Content']);
			unset($paramsData['Content']);
		}
        return $paramsData;
    }

    // 获取微信基础信息ACCESS_TOKEN
    public function get_basic_accesstoken($bind_id){
        if( base_kvstore::instance('weixin')->fetch('basic_accesstoken_'.$bind_id, $access_token) !== false ){
            logger::info('kv获取ACCESS_TOKEN'.$access_token);
            return $access_token;
        }else{
            $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$bind_id));
            if( $bindinfo['appid'] && $bindinfo['appsecret']){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$bindinfo['appid']}&secret={$bindinfo['appsecret']}";
                $httpclient = kernel::single('base_httpclient');
                $response = $httpclient->set_timeout(6)->get($url);
                $result = json_decode($response, true);

                if( $result['errcode']==0 ){
                    if( !base_kvstore::instance('weixin')->store('basic_accesstoken_'.$bind_id, $result['access_token'], $result['expires_in']) ){ // 微信ACCESS_TOKEN的有效期,单位为秒
                        logger::info("KVSTORE写入公众账号登录邮箱为 {$bindinfo['email']} 的ACCESS_TOKEN错误");
                    }
            logger::info('远程获取ACCESS_TOKEN'.$result['access_token']);
                    return $result['access_token'];
                }else{
                    logger::info("获取公众账号登录邮箱为 {$bindinfo['email']} 的ACCESS_TOKEN错误,微信返回的错误码为 {$result['errcode']}");
                    return false;
                }
            }else{
                logger::info("没有取到公众账号ID为 {$bind_id} 的 appid 或者 appsecret 的信息");
                return false;
            }
        }
    }

    // 获取微信OAUTH2的ACCESS_TOKEN
    public function get_oauth2_accesstoken($bind_id, $code, &$result){
         #if( base_kvstore::instance('weixin')->fetch('oauth2_accesstoken_'.$bind_id.'_'.$result['openid'], $oauth2_access_token) !== false ){
         #    return $oauth2_access_token;
         #}else{
            $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$bind_id));
            if( $bindinfo['appid'] && $bindinfo['appsecret']){
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$bindinfo['appid']}&secret={$bindinfo['appsecret']}&code={$code}&grant_type=authorization_code";
                $httpclient = kernel::single('base_httpclient');
                $response = $httpclient->set_timeout(6)->get($url);
                $result = json_decode($response, true);
                if( $result['errcode']==0 ){
                    #if( !base_kvstore::instance('weixin')->store('oauth2_accesstoken_'.$bind_id.'_'.$result['openid'], $result['access_token'], $result['expires_in']) ){ // 微信ACCESS_TOKEN的有效期,单位为秒
                    #    logger::info("KVSTORE写入公众账号登录邮箱为 {$bindinfo['email']} 的OAUTH2的ACCESS_TOKEN错误");
                    #}
                    logger::info('远程获取OAUTH2_ACCESS_TOKEN'.$result['access_token']);
                    return $result['access_token'];
                }else{
                    logger::info("获取公众账号登录邮箱为 {$bindinfo['email']} 的OAUTH2认证的ACCESS_TOKEN错误,微信返回的错误码为 {$result['errcode']}");
                    return false;
                }
            }else{
                logger::info("没有取到公众账号ID为 {$bind_id} 的 appid 或者 appsecret 的信息");
                return false;
            }
        #}
    }

    // 发送微信自定义菜单
    public function createMenu($bind_id, $menu_data, &$msg){
        if(!$access_token = $this->get_basic_accesstoken($bind_id)){
            $msg = "token 错误，请稍后重试";
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $data = json_encode ( $menu_data );
        // 由于微信不直接认json_encode处理过的带中文数据的信息，这里做个转换
        $post_menu = preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->post($url, $post_menu);
        $result = json_decode($response, true);
        if( $result['errcode']==0 ){
            logger::info('更新微信菜单数据成功:'.print_r($data,1));
            return true;
        }else{
            $msg = "创建微信自定义菜单错误,微信返回的错误码为 {$result['errcode']}";
            logger::info($msg);
            return false;
        }
    }

    // 非OAUTH2网页授权方式获取用户基本信息
    public function get_basic_userinfo($bind_id, $openid){
        if(!$access_token = $this->get_basic_accesstoken($bind_id)){
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($url, $post_menu);
        $result = json_decode($response, true);
        if( $result['errcode']==0 ){
            $result['nickname'] = $this->emoji_encode($result['nickname']);
            return $result;
        }else{
            $msg = "微信基本获取用户信息错误(非OAUTH2方式),微信返回的错误码为 {$result['errcode']}";
            logger::info($msg);
            return false;
        }
    }

    // 生成微信需授权页面链接
    public function gen_auth_link($appid, $eid, $redirect_uri){
        $url = sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect',$appid,$redirect_uri,$eid);
        return $url;
    }

    // 获取微信分享自定义信息
    public function get_weixin_share_page(){
        return app::get('weixin')->getConf('weixin_basic_setting.share_page');
    }

    // 随机获取一个可以创建自定义菜单的微信的APPID
    public function get_a_appid(){
        $bindinfo = app::get('weixin')->model('bind')->getRow('appid',array('appid|noequal'=>''));
        return $bindinfo['appid'];
    }

    /**
     * 接受维权通知到微信
     */
    public function updatefeedback($bind_id, $openid, $feedbackid){
        $access_token = $this->get_basic_accesstoken($bind_id);
        $url = "https://api.weixin.qq.com/payfeedback/update?access_token={$access_token}&openid={$openid}&feedbackid={$feedbackid}";
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($url);
        $result = json_decode($response, true);
        if( $result['errcode'] == 0){
            return true;
        } else {
            return $result;
        }
    }

    //发货通知微信
    public function delivernotify($postData){
        $payData = app::get('ectools')->getConf('weixin_payment_plugin_wxpay');
        $payData = unserialize($payData);
        $postData['appid'] = trim($payData['setting']['appId']);
        $bindData = app::get('weixin')->model('bind')->getRow('id',array('appid'=>$postData['appid']));
        $access_token = $this->get_basic_accesstoken($bindData['id']);
        $url = "https://api.weixin.qq.com/pay/delivernotify?access_token={$access_token}";

        $paySignKey = trim($payData['setting']['paySignKey']); // 财付通商户权限密钥 Key
        $sign = weixin_util::sign_sha1($postData,weixin_util::trimString($paySignKey));

        $postData['app_signature'] = $sign;
        $postData['sign_method'] = 'sha1';
        $httpclient = kernel::single('base_httpclient');
        $postData = json_encode($postData);
        $response = $httpclient->set_timeout(6)->post($url,$postData);
        $result = json_decode($response, true);
        if( $result['errcode'] == 0){
            return true;
        } else {
            $msg = "发货通知到微信,微信返回的错误码为 {$result['errcode']}\n,错误信息：{$result['errmsg']}";
            logger::info($msg);
            return false;
        }
    }

    public function get_code($appid, $redirect_uri, $response_type='code', $scope='snsapi_base', $state='STATE')
    {
        $api_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $api_url = $api_url.'appid='.$appid.'&redirect_uri='.urlencode($redirect_uri).'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'#wechat_redirect';
        logger::info('location url for weixin get code :'.$api_url);
        header('Location:'.$api_url);
        exit;
    }

    public function get_openid_by_code($appid, $secret, $code, $grant_type='authorization_code')
    {
        $result = $this->get_access_token($appid, $secret, $code, $grant_type);
        return $result['openid'];
    }

    public function get_access_token($appid, $secret, $code, $grant_type='authorization_code')
    {
        $api_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $api_url = $api_url.'appid='.$appid;
        $api_url = $api_url.'&secret='.$secret;
        $api_url = $api_url.'&code='.$code;
        $api_url = $api_url.'&grant_type='.$grant_type;
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($api_url);
        logger::info('weixin get access_token by code with code('.$code.') :'.$response);
        $result = json_decode($response, true);
        return $result;
    }

    // 判断是否来自微信浏览器
    function from_weixin() {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }

    function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    function getJsApiTicket($bindID) {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket_json = app::get('weixin')->getConf('jsapi_ticket.json');
        $data = json_decode($ticket_json,1);
        if ($data['expire_time'] < time()) {
            $accessToken = $this->get_basic_accesstoken($bindID);
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
            $httpclient = kernel::single('base_httpclient');
            $response = $httpclient->set_timeout(6)->get($url);
            $result = json_decode($response, true);
            $ticket = $result['ticket'];
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                app::get('weixin')->setConf('jsapi_ticket.json',json_encode($data));
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }

        return $ticket;
    }

    function getSignPackage(){
        $bindData = app::get('weixin')->model('bind')->getRow('id,appid',array('status'=>'active','weixin_type'=>'service'));
        $jsapiTicket = $this->getJsApiTicket($bindData['id']);
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=".$jsapiTicket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;

        $signature = sha1($string);
        $signPackage = array(
          "appId"     => $bindData['appid'],
          "nonceStr"  => $nonceStr,
          "timestamp" => $timestamp,
          "signature" => $signature,
          "rawString" => $string
        );
        return $signPackage;
    }

	// 发送多客服提示消息
	public function customers_send($postData, &$msg){
        if(!$access_token = $this->get_basic_accesstoken($postData['bind_id'])){
            return false;
        }
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		$custom_data = array ('touser'=>$postData['FromUserName'],'msgtype'=>'text','text'=>array('content'=>$msg));
        $data = json_encode ( $custom_data );
        // 由于微信不直接认json_encode处理过的带中文数据的信息，这里做个转换
        $post_menu = preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->post($url, $post_menu);
        $result = json_decode($response, true);
        if( $result['errcode']==0 ){
            logger::info('发送多客服提示消息成功:'.print_r($data,1));
            return true;
        }else{
            $msg = "发送多客服提示消息错误,微信返回的错误码为 {$result['errcode']}";
            logger::info($msg);
            return false;
        }
    }

    //通过矩阵获取用户code
    public function matrix_code($params){
        $obj_apiv = kernel::service('b2c_wechat_rpc_async');

        $data = array();
        if( is_object($obj_apiv) ){
            $data = $obj_apiv->getcode($params);
            logger::info('access matrix oauth api get code:'.var_export($data,1));
        }

        return $data;
    }

    //通过矩阵获取用户openid
    public function matrix_openid($code){
        $obj_apiv = kernel::service('b2c_wechat_rpc_async');

        $data = array();
        if( is_object($obj_apiv) ){
            $data = $obj_apiv->getopenid($code);
            logger::info('access matrix token api get openid:'.var_export($data,1));
        }

        return $data;
    }
    //通过矩阵获取用户信息
    public function matrix_userinfo($openid,$access_token){
        $obj_apiv = kernel::service('b2c_wechat_rpc_async');

        $userinfo = array();
        if( is_object($obj_apiv) ){
            $userinfo = $obj_apiv->getuserinfo($openid,$access_token);
            $userinfo['nickname'] = $this->emoji_encode($userinfo['nickname']);
            logger::info('access matrix token api get openid:'.var_export($userinfo,1));
        }

        return $userinfo;
    }

    /**
     * emoji_decode 
     *
     * @param mixed $nicknamei 微信昵称
     * @access public
     * @return 转码之后的emoji表情
     */
    public function emoji_decode($nickname){
        if( !$nickname ){return '';}
        $nickname_json =  '{"nickname":"' . $nickname . '"}';
        $arr = json_decode($nickname_json,true);
        $nickname = $arr['nickname'];

        return $nickname;
    }

    /**
     * emoji_encode   
     *
     * @param mixed $nickname 微信昵称
     * @access public
     * @return 编码之后 的emoji表情
     */
    public function emoji_encode($nickname){
        if( !$nickname ){return '';}
        $nickname = json_encode($nickname);
        $nickname = preg_replace("#(\\\u(e|d)[0-9a-f]{3})#ie","addslashes('\\1')",$nickname);
        $nickname = json_decode($nickname);

        return $nickname;
    }
}
