<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class desktop_ctl_code extends base_controller
{
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    //激活码校验
    public function codecheck()
    {
        //if ($_POST['auth_code'] && preg_match("/^\d{19}$/", substr($_POST['auth_code'], 1)))
        if ($_POST['auth_code'])
        {
            $result = kernel::single('base_license_active')->active($_POST['auth_code']);

            if ($result['res'] == 'succ' && $result)
            {
                header('Location:' .kernel::router()->app->base_url(1));
                exit;
            }
            else
            {
                die($this->error_view($result));
            }

            header("Location: index.php");
            exit();
        }
    }

    function error_info_view(){
        $render =  app::get('desktop')->render();
        $result = $_GET['result'];
        app::get('desktop')->setConf('activation_code_check', false);
        $render->pagedata['error_code'] = $result['msg'];
        $render->pagedata['shopexUrl'] = app::get('base')->getConf('certificate_code_url');
        $render->pagedata['shopexId'] = base_enterprise::ent_id();

        switch($result['msg']){
            case "invalid_version":
                $msg = "版本号有误，查看mysql是否运行正常"; break;
            case "RegUrlError":
                $msg = "您当前使用的域名与证书所绑定的域名不一致。";break;
            case "SessionError":
                $msg = "中心请求网店API失败!，请联系您的服务商，或找贵公司相关人员检测网络，以确保网络正常"; break;
            case "license_error":
                $msg = "证书号错误!";
                $Certi = base_certificate::get('certificate_id');
                if( !$Certi ){
                    $msg .= "查询不到证书，请确认config/certi.php文件是否存在";
                }
                break;
            case "method_not_exist":
                $msg = "接口方法不存在!"; break;
            case "method_file_not_exist":
                $msg = "接口文件不存在!"; break;
            case "NecessaryArgsError":
                $msg = "缺少必填参数!"; break;
            case "ProductTypeError":
                $msg = "产品类型错误!"; break;
            case "UrlFormatUrl":
                $msg = "URL格式错误!"; break;
            case "invalid_sign":
                $msg = "验签错误!"; break;
            default:
                $msg = null;break;
        }
        if($result == null){
            $msg = "请检测您的服务器域名解析是否正常！";
            $fp = fsockopen("service.shopex.cn", 80, $errno, $errstr, 30);
            if (!$fp) {
                $render->pagedata['fsockopen'] = 'fsockopen解析service.shopex.cn错误，请确认是否将fsockopen函数屏蔽</br>错误信息：'.$errstr;
            }
        }

        $url = $this->app->base_url(1);
        $code_url = $url.'index.php?app=desktop&ctl=code&act=error_view';
        $order_url = $url.'index.php/shopadmin/#app=b2c&ctl=admin_order&act=index';
        $cleanexpired_url = $url.'index.php/shopadmin/#ctl=adminpanel';
        $render->pagedata['msg'] = ($msg)?$msg:"";
        $render->pagedata['url'] = $url;
        $render->pagedata['order_url'] = $order_url;
        $render->pagedata['code_url'] = $code_url;
        $render->pagedata['cleanexpired_url'] = $cleanexpired_url;
        echo  $render->fetch('codetip.html');
        exit;
    }

    function error_view($auth_error_msg)
    {
        $render = app::get('desktop')->render();
        $url = $this->app->base_url(1);
        $render->pagedata['post_url'] = $url.'index.php?app=desktop&ctl=code&act=codecheck';
        $render->pagedata['res_url'] = app::get('desktop')->res_url;
        $render->pagedata['auth_error_msg'] = $auth_error_msg;
        echo $render->display('active_code.html');
        exit;
    }

}
