<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_controller extends base_render{

    var $pagedata = array();
    var $force_compile = 0;
    var $_tag_stack = array();
    public $_end_message = null;

    function begin($url_params=null){
        set_error_handler(array(&$this,'_errorHandler'), E_USER_ERROR | E_ERROR);
        if($this->transaction_start) trigger_error('The transaction has been started',E_USER_ERROR);
        $db = kernel::database();
        $this->transaction_status = $db->beginTransaction();
        $this->transaction_start = true;
        if(is_array($url_params)){
            $this->_action_url = $this->app->router()->gen_url($url_params);
        }else{
            $this->_action_url = $url_params;
        }
    }
    
    function endonly($result=true){
        if(!$this->transaction_start) trigger_error('The transaction has not started yet',E_USER_ERROR);
        $this->transaction_start = false;
        $db = kernel::database();
        restore_error_handler();
        if($result){
            $db->commit($this->transaction_status);
        }else{
            $db->rollback();
        }
    }

    function end($result=true,$message=null,$url_params=null,$params=array()){
        if(!$this->transaction_start) trigger_error('The transaction has not started yet',E_USER_ERROR);
        $this->transaction_start = false;
        $db = kernel::database();
        restore_error_handler();
        if(is_null($url_params)){
            $url = $this->_action_url;
        }elseif(is_array($url_params)){
            $url = $this->app->router()->gen_url($url_params);
        }else{
            $url = $url_params;
        }
        if($result){
            $db->commit($this->transaction_status);
            $status = 'success';
            $message = ($message=='' ? app::get('base')->_('操作成功！') : app::get('base')->_('成功：').$message);
        }else{
            $db->rollback();
            $status = 'error';
            $message = $message?$message:app::get('base')->_("操作失败: 对不起,无法执行您要求的操作");
        }
        $this->_end_message = $message;
        $this->_end_status = $status;
        $this->splash($status,$url,$message,'redirect',$params);
    }
    
    function splash($status='success',$url=null,$msg=null,$method='redirect',$params=array()){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        header('Location: '.$url);
    }

    function page($detail){
        header('Content-type: text/html; charset=utf-8');
        $object = kernel::service('theme');
        if($object){
            $object->display($detail);
        }else{
            $this->display($detail);
        }
    }

    function _errorHandler($errno, $errstr, $errfile, $errline){
        if($errno==E_ERROR){
            $errstr = basename($errfile).':'.$errline.'&nbsp;'.$errstr;
        }elseif($errno == E_USER_ERROR){
            $errstr = $errstr;
        }else{
            return;    
        }

        $this->splash('error',$this->_action_url,$errstr);
        header('Location: '.$this->_action_url);
        return true;
    }


    /**
     * [返回json格式数据]
     * @param  [array] $data 数组
     * @param  [int] $data['error'] 状态值 不传值或传0则为成功状态
     * @param  [int] $data['message'] 消息 不传值默认为‘操作成功提示’
     * @param  [int] $data['data'] 为实体数据
     * @return [json]  [返回数据]
     */
    function apiReturn($data = array()){
        $data['error']   = isset($data['error']) ? $data['error'] : 0;
        $data['message'] = isset($data['message']) ? $data['message'] : '操作成功！';

        $data['data']    = isset($data['data']) ? $data['data'] : null;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }


    /**
     * [调用外部接口数据]
     * @param  [string] $url 地址
     * @param  [array] $params 参数
     * @param  [bool] $ispost 是否是post提交
     * @return [array]  [返回数据]
     */
    function apiget($url, $params = false, $ispost = 1){
        if (!empty($params)) {
            $params = http_build_query($params);
        }else{
            $params = false;
        }

        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Data');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            $err = "cURL Error: " . curl_error($ch);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        $response = json_decode($response,true);
        $result = [];
        $result['httpCode'] = $httpCode;
        $result['info'] = $response;

        $data['error']   = 0;
        $data['message'] = '操作成功';
        $data['data']    = $response;
        if ($httpCode == 200 && is_array($response) && !empty($response) && $response['code'] == '0000') {
            return $data;
        }else{
            $data['error']   = 1;
            $data['message'] = $err;
            return $data;
        }
    }

}
