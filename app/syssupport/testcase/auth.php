<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class auth extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->code = kernel::single('syssupport_auth_code');
        $this->auth = kernel::single('syssupport_auth');

        $params = [
            'node_id'        => $this->code->getNodeId(),
            'certificate_id' => $this->code->getCertificateId(),
            'shopex_id'      => $this->code->getShopexId(),
            'active_key'     => $this->code->getActiveKey(),
            'url'            => $this->code->getUrl(),
            'version'        => $this->code->getVersion(),
            'product_name'   => $this->code->getProductName(),
            'custome_dir'    => $this->code->getCustomDir(),
        ];

        $this->params = $params;
    }

    private function __echo($string)
    {
        echo "\n\n";
        echo $string;
        echo "\n\n";
    }

    private function __dump($params)
    {
        echo "\n\n";
        var_dump($params);
        echo "\n\n";
    }


    public function testCodeParams()
    {
        $params = $this->auth->getParams();
        $this->__dump($params);

    }

    public function testUrl()
    {
        $url = $this->auth->getUrl();
        $this->__echo($url);
    }
}



