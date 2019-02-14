<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class notice extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->notice = kernel::single('syssupport_notice');
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


    public function testNotice()
    {
        $res = kernel::single('syssupport_notice')->getNotice();
        $this->__dump($res);
    }

    public function testNoticeUrl()
    {
        $url = kernel::single('syssupport_notice')->getNoticeIframeUrl();
        $this->__echo($url);
    }
}



