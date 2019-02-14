<?php
class syssupport_auth_code
{

    private $deploy = [];

    public function __construct()
    {
        $this->deploy = $this->getDeploy();
    }

    public function getCode()
    {
        return $this->encode($this->getParams());
    }

    public function getParams()
    {
        $params = kernel::single('base_license_active')->getBasicParams();;
        return [
            'node_id' => $params['node_id'],
            'certificate_id' => $params['certificate'],
            'shopex_id' => $params['shopex_id'],
            'active_key' => $params['active_code'],
            'url' => $params['shop_url'],
            'version' => $params['version'],
            'product_name' => $params['product_name'],
            'custom_dir' => $params['custom_dir'],
        ];
    }

    public function encode($params, $expire=0)
    {
        if($expire === 0)
            kernel::single('base_license_sign')->setExpire(300);
        elseif($expire > 0)
            kernel::single('base_license_sign')->setExpire($expire);
        return kernel::single('base_license_sign')->encode($params);

    }

    public function getNodeId()
    {
        return kernel::single('base_license')->getParams();
    }

    public function getCertificateId()
    {
        return base_certificate::certi_id();
    }

    public function getShopexId()
    {
        return base_enterprise::ent_id();
    }

    public function getActiveKey()
    {
        return app::get('desktop')->getConf('activation_code');
    }

    public function getUrl()
    {
        return url::action('topc_ctl_default@index');
    }

    public function getVersion()
    {
        return $this->deploy['product_version'];
    }

    public function getProductName()
    {
        return "ShopEx-ONex-ECStore-3.0.x-Source";
        $zl = ecos_get_code_license_info();
        return $zl['Product-Name'];
    }

    public function getCustomDir()
    {
        if(defined('CUSTOM_CORE_DIR')) $dir = CUSTOM_CORE_DIR;
        else $dir = '';
        return $dir;
    }

    public function getDeploy()
    {
        return kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR.'/config/deploy.xml'),'base_deploy');
    }
}
