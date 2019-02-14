<?php
/**
 * 说明：bond model
 */
class b2c_mdl_bond extends dbeav_model{
    var $defaultOrder = array('bond_id',' ASC');

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();//meta扩展
    }

    //重写getList方法
   function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $mdl_prove = $this->app->model('prove');
        $cols = trim($cols, ',');
        $datas = $mdl_prove->getList($cols, $filter, $offset, $limit, $orderType);
        return $datas;
    }

     public function get_schema(){
         $schema = [
             'columns' => [
                 'mobile' => [
                     'label' => app::get('b2c')->_('手机号'),
                     'searchtype' => 'has',
                     'in_list' => true,
                     'default_in_list' => true,
                     'filtertype' => 'normal',
                     'filterdefault' => 'true',
                     'realtype' => 'varchar(50)',
                     'order' =>'1',
                 ],
               
                 'merchantName' => [
                     'label' => app::get('b2c')->_('商户名称'),
                     'in_list' => true,
                     'searchtype'    => 'has',
                     'editable'      => true,
                     'filtertype'    => 'custom',
                     'filterdefault' => true,
                     'filtercustom'  =>[
                       'has'    => app::get('b2c')->_('包含'),
                       'tequal' => app::get('b2c')->_('等于'),
                       'head'   => app::get('b2c')->_('开头等于'),
                       'foot'   => app::get('b2c')->_('结尾等于'),
                     ],
                     'order' =>'2',
                 ],

                 'merchant_type_id' => [
                    'type'            => 'table:merchant_type',
                    'sdfpath'         => 'merchant_type/merchant_type_id',
                    'label' => app::get('b2c')->_('商户类型'),
                    'in_list' => true,
                    'order' =>'2',
                 ],

                 'region_id' => [
                     'label' => app::get('b2c')->_('商户所在区域'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'addr' => [
                     'label' => app::get('b2c')->_('街道详细地址'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'linkman' => [
                     'label' => app::get('b2c')->_('联系人'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'inviter_mobile' => [
                     'label' => app::get('b2c')->_('邀请人手机号'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'bond' => [
                     'label' => app::get('b2c')->_('保证金额'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'payed_bond_time' => [
                     'label' => app::get('b2c')->_('支付时间'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'apply_refund_time' => [
                     'label' => app::get('b2c')->_('申请退回时间'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

                 'real_refund_time' => [
                     'label' => app::get('b2c')->_('实际退回时间'),
                     'in_list' => true,
                     'order' =>'2',
                 ],

             ],
            'in_list' =>[
                0 => 'mobile',
                1 => 'merchantName',
                2 => 'merchant_type_id',
                3 => 'region_id',
                4 => 'addr',
                5 => 'linkman',
                6 => 'inviter_mobile',
                7 => 'bond',
                8 => 'payed_bond_time',
                9 => 'apply_refund_time',
                10 => 'real_refund_time',
            ],
             'default_in_list' =>[
                0 => 'mobile',
                1 => 'merchantName',
                2 => 'merchant_type_id',
                3 => 'region_id',
                4 => 'addr',
                5 => 'linkman',
                6 => 'inviter_mobile',
                7 => 'bond',
                8 => 'payed_bond_time',
                9 => 'apply_refund_time',
                10 => 'real_refund_time',
            ],
         ];
         return $schema;
    }

}
