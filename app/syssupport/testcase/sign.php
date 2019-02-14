<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sign extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {

    }

    public function testEncode()
    {
        $params = [
            'aa' => 'bb',
            'cc' => 'dd',
            'ee' => 'ff',
            'gg' => 'hh',
        ];

        $str = kernel::single('syssupport_sign')->encode($params);
        echo "\n\n$str\n\n";
    }

    public function testDecode()
    {

        $str = 'd74f1aSFBiLPygZ/sxhamU9N5hGG8Z7vZh5LwfIRD3Np4n6bBN44rHvmUuQrtqQgwMegRtUbDIA6T1Ip4ucIOqcAOIYUnrOolHrPQmSM1aznKNONIei6a9+Hj+Up4zIQdoDXKTrjV2h3eYc';
        $params = kernel::single('syssupport_sign')->decode($str);
        echo $str . "\n";
        echo "decode : \n";
        var_dump($params);
    }

}
