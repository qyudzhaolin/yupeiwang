<?php
class wap_ctl_default extends wap_controller{
    function index(){
        $seepage = $_GET['seepage'];
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('wap')->_('首页'),'link'=>kernel::base_url(1));
        $this->set_tmpl('index');
        $this->title=app::get('wap')->getConf('wap.shopname');
        if(in_array('index', $this->weixin_share_page)){
            $this->pagedata['from_weixin'] = $this->from_weixin;
            $this->pagedata['weixin']['appid'] = $this->weixin_a_appid;
            $this->pagedata['weixin']['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
            $this->pagedata['weixin']['linelink'] = app::get('wap')->router()->gen_url(array('app'=>'wap','ctl'=>'default', 'full'=>1));
            $this->pagedata['weixin']['shareTitle'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_name');
            $this->pagedata['weixin']['descContent'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_brief');

            //微信内置js调用
            $wechat = kernel::single('weixin_wechat');
            $signPackage = $wechat->getSignPackage();
            $this->pagedata['signPackage'] = $signPackage;
            //end
        }
        $this->pagedata['ctime'] = $_SERVER['REQUEST_TIME'];
        //$this->pagedata['ctime'] = '1';

        //四个展区
        $index_pic_dir = '/wap_themes/default/images/icons/';
        $versions = 2;
        $this->pagedata['exharea'] = [
            0=>['name'=>'生鲜冻品','img'=>$index_pic_dir.'index_pic1.png?v=' . $versions,'link'=>'/index.php/wap/gallery-cat.html'],
            1=>['name'=>'特价专区','img'=>$index_pic_dir.'index_pic2.png?v=' . $versions,'link'=>'#'],
            2=>['name'=>'海外预售','img'=>$index_pic_dir.'index_pic3.png?v=' . $versions,'link'=>'#'],
            3=>['name'=>'全球集采','img'=>$index_pic_dir.'index_pic4.png?v=' . $versions,'link'=>'#']
        ];

        //判断用户登陆实名认证
        $this->pagedata['is_prove'] = false;
        $this->pagedata['is_login'] = false;
        $member_status =$this->check_logins();
        $this->pagedata['is_alert_prove'] = false;
        if($member_status==true){
           $this->pagedata['is_login'] = true;
           $member_id=$_SESSION['account'][pam_account::get_account_type('b2c')];
           $mdl_prove = app::get('b2c')->model('prove');
           if($member_id){
                 $is_prove = $mdl_prove->getRow('*',['member_id'=>$member_id,'status'=>'pass']);
                 if($is_prove){
                    $this->pagedata['is_vistor'] = 1;
                    $this->pagedata['is_prove'] = true;
                 }else{
                    if(!$seepage) $this->pagedata['is_alert_prove'] = true;
                 }
           }
        }

        //获取热门城市
        $mdl_regions = app::get('ectools')->model('regions');
        $wheres['local_name|in'] = ['北京','上海','广州市','杭州市','苏州市'];
        $this->pagedata['hotcitys'] = $mdl_regions->getList('region_id id,local_name name', $wheres);
       
        //促销商品
        $this->pagedata['index_ad'] = '/wap_themes/default/images/icons/index_ad.png';
        $this->pagedata['pageLocation'] = 'home';

        $this->page('index.html');
    }

    //验证码组件调用
    function gen_vcode($key='vcode',$len=4){
        $vcode = kernel::single('base_vcode');
        $vcode->length($len);
        $vcode->verify_key($key);
        $vcode->display();
    }
    function check_logins(){
        kernel::single('base_session')->start();
        if($_SESSION['account'][pam_account::get_account_type('b2c')]){
            return true;
        }else{
            return false;
        }
    }

}