<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 购物车项处理(优惠券)
 * $ 2010-05-25 14:09 $
 */
class b2c_cart_object_coupon implements b2c_interface_cart_object{

    private $app;
    private $member_ident; // 用户标识
    private $oCartObject;

    /**
     * 构造函数
     *
     * @param $object $app  // service 调用必须的
     */
    public function __construct() {
        $this->app = app::get('b2c');

        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();

        $this->oCartObjects = $this->app->model('cart_objects');
    }

    /**
	 * 购物车是否需要验证库存
	 * @param null
	 * @return boolean true or false
	 */
	public function need_validate_store() {
		return true;
	}

    public function get_type() {
        return 'coupon';
    }

    //立即购买和正常购买共用优惠劵数据
    public function data_common(){
        return true;
    }

	public function get_part_type() {
		return array('coupon');
	}

	/**
	 * 处理加入购物车商品的数据
	 * @param mixed array 传入的参数
	 * @return mixed array 处理后的数据
	 */
	public function get_data($params=array())
	{
		return $params;
	}

	/**
	 * 得到失败应该返回的url - app 数组
	 * @param array
	 * @return array
	 */
	public function get_fail_url($data=array())
	{
		return array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'checkout');
	}

	/**
	 * 校验加入购物车数据是否符合要求-各种类型的数据的特殊性校验
	 * @param array 加入购物车数据
	 * @param string message 引用值
	 * @return boolean true or false
	 */
	public function check_object($arr_data,&$msg='')
	{
		if(empty($arr_data) || empty($arr_data['coupon']))
		{
			$msg = app::get('b2c')->_('优惠券为空！');
			return false;
		}

		if (!$this->app->model("coupons")->verify_coupons($arr_data,$msg)){
			$msg = $msg ? $msg : app::get('b2c')->_('优惠券添加失败！');
			return false;
		}
		return true;
	}

	/**
	 * 检查库存
	 * @param array 加入购物车的商品结构
	 * @param array 现有购物车的数量
	 * @param string message
	 * @return boolean true or false
	 */
	public function check_store($arr_data, $arr_carts, &$msg='')
	{
		return true;
	}

	/**
	 * 添加购物车项(coupon)
	 * @param array $aData // array(
     *                          'goods_id'=>'xxxx',   // 商品编号
     *                          'product_id'=>'xxxx', // 货品编号
     *                          'adjunct'=>'xxxx',    // 配件信息
     *                          'quantity'=>'xxxx',   // 购买数量
     *                        )
	 * @param string message
	 * @return boolean
	 */
	public function add_object($aData, &$msg='', $append=true)
	{
        $objIdent = $this->_generateIdent($aData);
        $obj_coupons = $this->app->model('coupons');
        $aCouponRule = $obj_coupons->getCouponByCouponCode($aData['coupon']);
        $arr = $this->app->model('sales_rule_order')->getList( '*',array('rule_id'=>$aCouponRule[0]['rule_id']) );
        $usedCoupon = $this->getAll();

        //模拟下购物车的数据。验证促销规则的时候需要用到
        $aCart = $this->app->model('cart')->get_objects($aData);
        $discount_amount_order = $aCart['discount_amount_order'];
        $subtotal_price = $aCart['subtotal_prefilter_after'];
        $current_total = $subtotal_price - $discount_amount_order;
        $coupon_info = array(
            'obj_ident'    => $objIdent,
            'obj_type' => 'coupon',
            'quantity' => 1,
            'description' => '',
            'coupon' => $aData['coupon'],
            'rule_id'   => $aCouponRule[0]['rule_id'],
            'cpns_id'   => $aCouponRule[0]['cpns_id'],
            'cpns_type' => $aCouponRule[0]['cpns_type'],
            'name'  =>  '',
        );
        $aCart['object']['coupon'][] = $coupon_info;
        unset($aCart['_cookie']);
        //模拟结束
        //模拟下促销规则验证,老规则如果验证失败了。不做处理
        $oCond = kernel::single('b2c_sales_order_aggregator_combine');
        $conditions = $arr[0]['conditions'];
        $validate = $oCond->validate($aCart,$conditions);

        if( $validate ){
            $status = true;
            $fail = 0;
            $aggregator = $conditions['conditions']['1']['aggregator'];
            $main_conditions = $conditions['conditions']['1']['conditions'];
            $rule_count = count($main_conditions);
            //注销验证失败的规则
            foreach( $main_conditions as $key=>$rule ){
                if( $rule['attribute'] == 'order_subtotal' ){
                    $status = $this->valid_order_subtotal($rule,$current_total);
                    if( !$status ){
                        $fail++;
                        unset($conditions['conditions']['1']['conditions'][$key]);
                    }
                }
            }

            if( $fail ){
                //如果还剩余规则，则再验证剩余规则（如果没有剩余规则，验证时会返回ture，好尴尬）,主要用于组合条件的促销
                if( $conditions['conditions']['1']['conditions'] && $$aggregator == 'any' ){
                    $validate = $oCond->validate($aCart,$conditions);
                }else{
                    $validate = false;
                }
                if( !$validate ){
                    $msg = app::get('b2c')->_('不满足该优惠券的使用条件！');
                    return false;
                }
            }
        }
        if( !$arr || !is_array($arr) ) {
			$msg = app::get('b2c')->_('优惠券信息错误！');
			return false;
		}
        reset( $arr );
        $arr = current( $arr );
        if( $arr['status']!=='true' ) {
            $msg = app::get('b2c')->_('该优惠券不能使用！！活动未开启！');
            return false;
        }
        $curtime = time();
        if( $curtime<$arr['from_time'] || $curtime>$arr['to_time']  ) {
			$msg = app::get('b2c')->_('该优惠券不在可使用时间内，或者已过期！');
			return false;
		}

        if($arr['stop_rules_processing'] == 'true' && count($usedCoupon) > 0 ){
			$msg = app::get('b2c')->_('该优惠券无法与其他优惠券同时使用！');
			return false;
        }

        foreach($usedCoupon as $key => $value){
            $curRule = $this->app->model('sales_rule_order')->getList( '*',array('rule_id'=>$value['params']['rule_id']) );
            if( !$curRule || !is_array($curRule) ) {
                $msg = app::get('b2c')->_('优惠券信息错误！');
                return false;
            }
            reset( $curRule );
            $curRule = current( $curRule );
            if($curRule['stop_rules_processing'] == 'true'){
                $msg = app::get('b2c')->_('已添加的优惠券无法与其他优惠券同时使用！');
                return false;
            }
        }

        $aSave = array(
		   'obj_ident'    => $objIdent,
		   'member_ident' => $this->member_ident,
		   'obj_type'     => 'coupon',
		   'params'       => array(
								'name'  =>  $aData['coupon'],
								'rule_id'   => $aCouponRule[0]['rule_id'],
								'cpns_id'   => $aCouponRule[0]['cpns_id'],
								'cpns_type' => $aCouponRule[0]['cpns_type'],
								'extends_params' => $aData['extends_params'],
							),
		   'quantity'     => 1,  // 一张优惠券只能使用一次不能叠加
		 );

        if(kernel::single("b2c_cart_object_goods")->get_cart_status()) {
            $this->coupon_object[$aSave['obj_ident']] = $aSave;
            return $aSave['obj_ident'];
            //todo
        }; //no database

		$is_save = $this->oCartObjects->save($aSave);
		if (!$is_save){
			$msg = app::get('b2c')->_('优惠券使用失败！');
			return false;
		}
        return $aSave['obj_ident'];
	}

    // 优惠券没有更新这一说
    public function update($sIdent,$quantity) {
        return false;
    }

    /**
     * 指定的购物车优惠券
     *
     * @param string $sIdent
     * @param boolean $rich        // 是否只取cart_objects中的数据 还是完整的sdf数据
     * @return array
     */
    public function get($sIdent = null,$rich = false) {
        if(empty($sIdent)) return $this->getAll($rich);

        $aResult = $this->oCartObjects->getList('*',array(
                                           'obj_ident' => $sIdent,
                                           'member_ident'=> $this->member_ident,
                                        ));
        if(empty($aResult)) return array();
        if($rich) {
            $aResult = $this->_get($aResult);
            $aResult = $aResult[0];
        }

        return $aResult;
    }

    public function _get($aData){
        // todo要从数据库中取出对应用的优惠券的描述
		$obj_sales_rule_order = $this->app->model('sales_rule_order');
        foreach($aData as $row) {
            $params = $row['params'];
			$tmp = $obj_sales_rule_order->getList('name', array('rule_id'=>$params['rule_id']));
            $aResult[] = array(
                            'obj_ident' => $row['obj_ident'],
                            'obj_type' => 'coupon',
                            'quantity' => 1,
                            'description' => '',
                            'coupon'=>$params['name'],
                            'rule_id' => $params['rule_id'],
                            'cpns_id'=> $params['cpns_id'],
                            'cpns_type'=> $params['cpns_type'],
							'name'=>$tmp[0]['name'],
                            'used' => false // 是否使用 order conditions时处理
                        );
        }
        return $aResult;
    }

    // 购物车里的所有优惠券
    public function getAll($rich = false) {

        if(kernel::single("b2c_cart_object_goods")->get_cart_status()) {
            $aResult = $this->coupon_object;
        } else {
            $aResult= $this->oCartObjects->getList('*',array(
                                               'obj_type' => 'coupon',
                                               'member_ident'=> $this->member_ident,
                                           ));
        }
        if(empty($aResult)) return array();
        if(!$rich) return $aResult;
        return $this->_get($aResult);
    }

    // 删除购物车中指定优惠券
    public function delete($sIdent = null) {
        if(empty($sIdent)) return $this->deleteAll();
        // todo 如果dbeav中有delete方法邓 再悠修改下面
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_ident'=>$sIdent, 'obj_type'=>'coupon'));
    }

    // 清空购物车中优惠券数据
    public function deleteAll() {
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_type'=>'coupon'));
    }

    // 清空当次登录中优惠券数据
    public function deleteNow($sIdent) {
        return $this->oCartObjects->delete_custom(array('member_ident'=>$sIdent, 'obj_type'=>'coupon'));
    }

    // 统计购物车中优惠券数据
    public function count(&$aData) {}

    // todo 优惠券添加到购物车中的数据检测在这里处理
    // 优惠券的正确性 类型 是否已使用
    private function _check(&$aData) {
        if(empty($aData) || empty($aData['coupon'])) return array('status'=>'false','msg'=>'优惠券为空！');;
        return $this->app->model("coupons")->verify_coupons($aData);

        // 通过 $aData['coupon'] 验证coupon的有效性

        return true;
    }

    private function _generateIdent($aData) {
        return "coupon_".$aData['coupon'];# .'_'. ( $this->arr_member_info['member_id'] ? $this->arr_member_info['member_id'] : 0 );
    }


    public function apply_to_disabled( $data,$session,$flag ) {
        return $data;
    }

    /**
     * 满减促销规则重新验证
     * @params rule array 促销规则
     * @params current_total 当前优惠后的订单金额
     * @return bool
     */
    private function valid_order_subtotal($rule,$current_total){
        $operator = $rule['operator'];
        $value = $rule['value'];
//        logger::info('operator:'.$operator);
//        logger::info('value:'.$value);
//        logger::info('current_total:'.$current_total);

        $status = true;
        switch( $operator){
        case '>':
            $status = $current_total > $value ? true : false;
            break;
        case '>=':
            $status = $current_total >= $value ? true : false;
            break;
        case '<':
            $status = $current_total < $value ? true : false;
            break;
        case '<=':
            $status = $current_total <= $value ? true : false;
            break;
        case '=':
            $status = $current_total = $value ? true : false;
            break;
        default:
            break;
        }
        return $status;
    }

}
