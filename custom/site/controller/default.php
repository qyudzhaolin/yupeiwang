<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_ctl_default extends site_controller{
    function index(){
        //跳转到四合一页面
        //header("Location:http://www.baidu.com");
        if(defined('APP_SITE_INDEX_MAXAGE') && APP_SITE_INDEX_MAXAGE > 1){
            $this->set_max_age(APP_SITE_INDEX_MAXAGE);
        }//todo: 首页max-age设定

        if(kernel::single('site_theme_base')->theme_exists()){

            $obj = kernel::service('site_index_seo');

            if(is_object($obj) && method_exists($obj, 'title')){
                $title = $obj->title();
            }else{
                $title = (app::get('site')->getConf('site.name')) ? app::get('site')->getConf('site.name') : app::get('site')->getConf('page.default_title');
            }

            if(is_object($obj) && method_exists($obj, 'keywords')){
                $keywords = $obj->keywords();
            }else{
                $keywords = (app::get('site')->getConf('page.default_keywords')) ? app::get('site')->getConf('page.default_keywords') : $title;
            }

            if(is_object($obj) && method_exists($obj, 'description')){
                $description = $obj->description();
            }else{
                $description = (app::get('site')->getConf('page.default_description')) ? app::get('site')->getConf('page.default_description') : $title;
            }

            //新闻
            $mdl_article_index = app::get('content')->model('article_indexs');
            $wheres = ['node_id'=>42,'disabled'=>'false','ifpub'=>'true','platform'=>'pc'];
            $articlRow = $mdl_article_index->getRow('article_id,title', $wheres,'article_id desc');
            $this->pagedata['articlRow'] = $articlRow;
            // ee(sql());

            $this->pagedata['headers'][] = '<title>' . htmlspecialchars($title) . '</title>';
            $this->pagedata['headers'][] = '<meta name="keywords" content="' . htmlspecialchars($keywords). '" />';
            $this->pagedata['headers'][] = '<meta name="description" content="' . htmlspecialchars($description) . '" />';
            $this->pagedata['headers'][] = "<link rel='icon' href='{$this->app->res_url}/favicon.ico' type='image/x-icon' />";
            $this->pagedata['headers'][] = "<link rel='shortcut icon' href='{$this->app->res_url}/favicon.ico' type='image/x-icon' />";
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('b2c')->_('首页'),'link'=>kernel::base_url(1));
            $this->set_tmpl('index');
            $this->page('index.html');
        }else{
            $this->display('splash/install_template.html');
        }
    }

    //验证码组件调用
    function gen_vcode($key='vcode',$len=4){
        $vcode = kernel::single('base_vcode');
        $vcode->length($len);
        $vcode->verify_key($key);
        $vcode->display();
    }

    public function activeCallback()
    {
        echo kernel::single('base_license_active')->callBack();
        exit;
    }
}
