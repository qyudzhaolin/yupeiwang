<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class pointprofessional_point_change
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
        $this->app_b2c = app::get('b2c');
    }
    
    /**
     * 接管（delegate）处理积分修改相关事宜
     * @param int member id
     * @param int point
     * @param string message 引用值
     * @param string reason type
     * @param string type
     * @param string 关联的对象id
     * @param string 操作员id
     * @param string remark
     */
    public function point_change_delegate($nMemberId,$point,&$msg,$reason_type,$type=0,$rel_id,$operator,$remark='pay')
    {
        //事务
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        
        $objMember = $this->app->model('members');
        $objMember_point = $this->app->model('member_point');
        $row = $objMember->getList('*', array('member_id'=>$nMemberId));
        if(!$row) return null;

        $change_remark = true;
        $memo = ($msg) ? $msg : '';
        $falg = 1;
        // 是否延时标记
        $is_delay = false;
        // 取到有效的积分值
        $obj_member_point = $this->app->model('member_point');
        
        //目前 后台管理员改变积分，前台兑换优惠券两个口子必须请求接口返回成功
        $type = 1;
        $must_api_suc_list = array("exchange_coupon","operator_adjust");
        if(in_array($reason_type,$must_api_suc_list)){
            $type = 2;
        }
        $real_total_point = $objMember->get_real_point($nMemberId,$type);
        //在传的第二个参数type是2的情况下 如果接口获取积分失败 直接 返回msg
        if($type == 2 && $real_total_point === "api_get_failed"){
            $db->rollback();
            $msg = app::get('b2c')->_("接口获取真实积分失败");
            return false;
        }

        if ($point<0)
        {
            if ($remark == 'refund')
                if ($real_total_point < abs($point))
                {
                    $point = 0 - $real_total_point;
                    $real_total_point = 0;
                    $msg = app::get('b2c')->_("积分扣除超过会员已有积分");
                    $change_remark = false;
                }
                else
                {
                    $real_total_point = $real_total_point + $point;
                }
            else
            {
                if ($real_total_point < abs($point))
                {
                    $msg = app::get('b2c')->_("积分扣除超过会员已有积分");return false;
                }
                else
                {
                    $real_total_point = $real_total_point + $point;
                }
            }
        }
        else
        {
            $site_get_point_interval_time = $this->app_b2c->getConf('site.get_point_interval_time');
            if ($site_get_point_interval_time > 0)
            {
                // 存入任务临时表
                $obj_member_point_task = $this->app->model('member_point_task');
                $obj_pointprofessional_point_task_datas = kernel::servicelist('pointprofessional_point_task_data');

                if ($obj_pointprofessional_point_task_datas)
                {
                    $arr_point_task = array();
                    foreach ($obj_pointprofessional_point_task_datas as $obj_service)
                    {
                        if ($obj_service->get_point_task_type() == $remark)
                        {
                            $time = time();
                            // todo...
                            $arr_data = array(
                                'member_id' => $nMemberId,
								'task_name' => app::get('b2c')->_('订单获得积分'),
                                'point' => $point,
                                'addtime' => $time,
                                'enddate' => $time + $site_get_point_interval_time * 24 * 3600,
                                'related_id' => $rel_id,
                                'point' => $point,
                                'operator' => $operator,
                            );
                            $obj_service->generate_data($arr_data, $arr_point_task);
                            $obj_member_point_task->insert($arr_point_task);
                            $is_delay = true;
                        }
                    }
                }
            }
            $real_total_point = $real_total_point + $point;
        }

        if (!$is_delay)
        {
            if($point) $change_point = $point;
            $newValue = $real_total_point;
            $sdf_member = $objMember->dump($nMemberId,'*');
            $sdf_member['score']['total'] = $newValue;
            // 取到此会员等级对应的
            $obj_member_lv = $this->app_b2c->model('member_lv');
            $rows = $obj_member_lv->getList('*', array('member_lv_id'=>$sdf_member['member_lv']['member_group_id']));
			$default_expired = $this->app_b2c->getConf('site.point_expired_value');
            $time = time();
            if ($rows)
            {
                $site_point_expired = $this->app_b2c->getConf('site.point_expired');
                $site_point_expried_method = $this->app_b2c->getConf('site.point_expried_method');
                if ($site_point_expired == 'true')
                {
                    //如果积分过期方式是按照日期，且过期的时间小于等于1990-01-01的时间戳,大于0。则积分过期时间为0
                    if( $site_point_expried_method == '1' && $rows[0]['expiretime'] <= 631123200 && $rows[0]['expiretime'] > 0 ){
                        $rows[0]['expiretime'] = 0;
                        $obj_member_lv->update(array('expiretime'=>strtotime($default_expired)),array('member_lv_id'=>$sdf_member['member_lv']['member_group_id']));
                    }

                    //如果积分过期方式是按照长度，且过期的时间大于1990-01-01的时间戳。则积分过期时间为0
                    if( $site_point_expried_method == '2' && $rows[0]['expiretime'] > 631123200 ){
                        $rows[0]['expiretime'] = 0;
                        $obj_member_lv->update(array('expiretime'=>$default_expired),array('member_lv_id'=>$sdf_member['member_lv']['member_group_id']));
                    }

                    switch ($site_point_expried_method)
                    {
                        case '1':
                            $expired_time = $rows[0]['expiretime'] ? $rows[0]['expiretime'] : strtotime($default_expired);
                            break;
                        case '2':
                            $expired_time = $time + ($rows[0]['expiretime'] ? $rows[0]['expiretime'] : $default_expired) * 24 * 3600;
                            break;
                        default:
                            $expired_time = $rows[0]['expiretime'] ? $rows[0]['expiretime'] : strtotime($default_expired);
                            break;
                    }
                }
            }

            $reasons = $obj_member_point->getHistoryReason();
            $reason = $reasons[$reason_type];
            $remark = $pointInfo['modify_remark'];
            $sdf_point = array(
                          'member_id'=>$nMemberId,
                          'point'=>$newValue,
                          'change_point'=>$change_point,
                          'addtime'=>$time,
                          'expiretime'=>(isset($expired_time) && $expired_time) ? $expired_time : '0',
                          'reason'=>$reason['describe'],
                          'type'=>$type,
                          'related_id'=>($rel_id) ? $rel_id : 0,
                          'operator' => $operator,
                          'remark'=>$memo ? $memo : '',
                        );
            $point_id = $obj_member_point->insert($sdf_point);

            if($point_id)
            {
                $nodes_obj = $this->app_b2c->model('shop');
                $nodes = $nodes_obj->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));
                
                if ($nodes>0){
                    // 绑定了crm 打更新积分流水接口
                    $rpc_result = kernel::single('b2c_member_point_contact_crm')->pointChange($point_id);
                    if($type == 2 && !$rpc_result){
                        $db->rollback();
                        $msg = app::get('b2c')->_("接口更新积分失败");
                        return false;
                    }
                }

                if ($point<0){
                    //绑定crm后积分的使用由crm来处理
                    if($nodes <= 0){
                        // 得到所有有效的可用积分记录
                        $arr_point_historys = $obj_member_point->get_usable_point($nMemberId);
                        if ($arr_point_historys)
                        {
                            $discount_point = abs($point);
                            foreach ($arr_point_historys as $arr_points)
                            {
                                // 已经消耗完的积分不在处理.
                                if ($arr_points['change_point'] == $arr_points['consume_point'])
                                    continue;

                                if ($arr_points['change_point'] >= ($arr_points['consume_point'] + $discount_point))
                                {
                                    $arr_points['consume_point'] = $arr_points['consume_point'] + $discount_point;
                                    $objMember_point->update($arr_points, array('id'=>$arr_points['id']));
                                    break;
                                }
                                else
                                {
                                    $real_change_point = $arr_points['change_point'] - $arr_points['consume_point'];
                                    $arr_points['consume_point'] = $arr_points['change_point'];
                                    $discount_point = $discount_point - $real_change_point;
                                    $objMember_point->update($arr_points, array('id'=>$arr_points['id']));
                                }
                            }
                        }
                    }
                }

                if(($this->app->getConf('site.level_switch')== 0) && $falg == 1)
                {
                    $sdf_member['member_lv']['member_group_id'] = $obj_member_point->member_lv_chk($nMemberId,$sdf_member['member_lv']['member_group_id'],$newValue);
                }
                $objMember->save($sdf_member);
                
                $db->commit($transaction_status);
                
                $msg = app::get('b2c')->_("修改成功");
                return true;
            }
            else
            {
                $db->rollback();
                $msg = app::get('b2c')->_("修改失败");
                return false;
            }
        }
        else
        {
            $db->commit($transaction_status);
            $msg = app::get('b2c')->_("修改成功");
            return true;
        }
        
    }
}
