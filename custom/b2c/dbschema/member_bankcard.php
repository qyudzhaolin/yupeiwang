<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$db['member_bankcard']=array (
    'columns' =>
        array (
            'bankcard_id' =>
                array (
                    'type' => 'int(10)',
                    'required' => true,
                    'pkey' => true,
                    'extra' => 'auto_increment',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('会员银行卡序号ID'),
                ),
            'TxSNBinding' =>
                array (
                    'type' => 'varchar(50)',
                    'default' => 0,
                    'required' => true,
                    'editable' => false,
                    'comment' => app::get('b2c')->_('中金绑定银行卡流水号'),
                ),
            'member_id' =>
                array (
                    'type' => 'table:members',
                    'default' => 0,
                    'required' => true,
                    'editable' => false,
                    'comment' => app::get('b2c')->_('会员ID'),
                ),
            'bank' =>
                array (
                    'is_title' => true,
                    'type' => 'varchar(50)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('银行名称'),
                ),
            'AccountName' =>
                array (
                    'type' => 'varchar(50)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('持卡人姓名'),
                ),
            'AccountNumber' =>
                array (
                    'type' => 'varchar(255)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('银行卡号'),
                ),
            'last_modify' =>
                array (
                    'type' => 'varchar(255)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('绑定成功时间'),
                ),
            'ValidDate' =>
                array (
                    'type' => 'varchar(255)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('信用卡有效期'),
                ),
            'CVN2' =>
                array (
                    'type' => 'varchar(10)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('卡号后三位'),
                ),
            'effective' =>
                array (
                    'type' => 'varchar(10)',
                    'editable' => false,
                    'comment' => app::get('b2c')->_('是否有效 无效0；有效1'),
                ),

        ),
    'comment' => app::get('b2c')->_('用户绑定银行卡表'),
    'version' => '$Rev: 48882 $',
);
