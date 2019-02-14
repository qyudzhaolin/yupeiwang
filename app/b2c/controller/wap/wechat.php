<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_wechat extends wap_frontpage{

    function __construct($app){
        parent::__construct($app);
    }

    private function __verifyWechatInfo()
    {
        $openid = $_SESSION['weixin_bind_user'];
        if(!$openid)
        {
            $this->begin(array('app'=>'b2c','ctl'=>'wap_wechat','act'=>'bindPage'));
            $msg = '获取openid失败';
            $this->end(false, $msg, $url,true,true);
            exit;
        }


        return true;
    }

    private function __verifyWechatInfoPage()
    {
        $openid = $_SESSION['weixin_bind_user'];
        if(!$openid)
        {
            $this->splash('failed',$url,'获取openid失败','','');exit;
        }
        return true;
    }

    private function __checkBind()
    {

        $openid = $_SESSION['weixin_bind_user']['openid'];
        $bind_tag = app::get('pam')->model('bind_tag')->getRow('member_id',array('open_id'=>$openid));
        if( $bind_tag ){
            $msg = app::get('b2c')->_('您已绑定，不需要重新绑定');
            $this->begin(array('app'=>'b2c','ctl'=>'wap_wechat','act'=>'bindPage'));
            $url = $_SESSION['mobile_next_page'] ? : $this->gen_url(array('app'=>'wap', 'ctl'=>'default', 'act'=>'index'));
            $this->end(false, $msg, $url,true,true);
            exit;
        }
    }

    private function __checkBindPage()
    {
        $openid = $_SESSION['weixin_bind_user']['openid'];
        $bind_tag = app::get('pam')->model('bind_tag')->getRow('member_id',array('open_id'=>$openid));
        if( $bind_tag ){
            $msg = app::get('b2c')->_('您已绑定，不需要重新绑定');
            $this->splash('failed',$url,$msg,'','');exit;
        }
    }

    public function bindPage()
    {
        $this->__verifyWechatInfoPage();
        $this->__checkBindPage();
        $this->set_tmpl('passport');

        $this->pagedata['show_varycode'] = kernel::single('b2c_service_vcode')->status();
        $this->page('wap/wechat/bind.html');
    }

    public function doBindMember()
    {
        $this->__checkBind();
        $userData = array(
            'login_account' => $_POST['uname'],
            'login_password' => $_POST['password']
        );
        $this->begin(array('app'=>'b2c','ctl'=>'wap_wechat','act'=>'bindPage'));
        $member_id = kernel::single('pam_passport_site_basic')->login($userData,$_POST['verifycode'],$msg);
        if(!$member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_wechat','act'=>'bindPage'));
            $this->end(false, $msg, $url,true,true);
            exit;
        }

        $openid = $_SESSION['weixin_bind_user']['openid'];
        $nickname = $_SESSION['weixin_bind_user']['nickname'];

        $bindWeixinData = array(
            'member_id' => $member_id,
            'open_id' => $openid,
            'tag_name' => $nickname,
            'createtime' => time()
        );
        app::get('pam')->model('bind_tag')->save($bindWeixinData);

        $_SESSION['account']['member'] = $member_id;
        $this->bind_member($member_id);
        $url = $_SESSION['mobile_next_page'] ? : $this->gen_url(array('app'=>'wap', 'ctl'=>'default', 'act'=>'index'));
        $this->end(true, '保存成功', $url,true,true);
        exit;
    }

    public function createNewNumber()
    {
        $this->__checkBind();

        $res = $_SESSION['weixin_bind_user'];
        $openid = $res['open_id'];
        $member_id = kernel::single('b2c_user_passport')->create($res,$openid);
        if($member_id ){
            $_SESSION['account']['member'] = $member_id;
            $this->bind_member($member_id);
        }
        $url = $_SESSION['mobile_next_page'] ? : $this->gen_url(array('app'=>'wap', 'ctl'=>'default', 'act'=>'index'));
        $this->splash('success',$url,'绑定成功！','','',true);exit;
    }

}
