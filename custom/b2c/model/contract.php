<?php
/**
 * 说明：contract model
 */
class b2c_mdl_contract extends dbeav_model{
    var $defaultOrder = array('ctime',' DESC');

    function __construct($app){
        parent::__construct($app);
    }


    //根据合约id获取合约数据
    function getContractData($contract_id,$member_id=0){
        $data = [];
        if (empty($contract_id)) {
            return $data;
        }

        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $wheres = [];
        $wheres['contract_id'] = $contract_id;
        if (!empty($member_id)) {
            $wheres['member_id'] = $member_id;
        }
        $data = $this->getRow('*',$wheres);//这里其实调用的是getList，注意重复转换
        // ee(sql());
        if (empty($data)) {
            return $data;
        }
        $params  = unserialize($data['params']);//结算参数
        $data['params'] = $this->getParamRowsByParamId($params);//获取处理过的结算参数值
        //合约商品
        $tmpProducts = $this->getContractGoodsByContractId($contract_id);
        $products = [];
        $product_ids = [];
        if (!empty($tmpProducts)) {
           foreach ($tmpProducts as $v) {
               $products[$v['product_id']] = $v;
               $product_ids[] = $v['product_id'];
           }
        }
        $data['products'] = $products;
        $data['product_ids'] = $product_ids;

        //结算阶段
        $data['step'] = $this->getStepRowsByContractId($contract_id);
        return $data;
    }

    //重写getList方法
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null,$getPayStatus=true){
        $datas = parent::getList($cols, $filter, $offset, $limit, $orderType);
        if (!empty($datas)) {
            foreach ($datas as $key => $value) {
                $datas[$key]['begin_time'] = date('Y-m-d', $value['begin_time']);
                $datas[$key]['end_time'] = date('Y-m-d', $value['end_time']);
                if ($getPayStatus) {
                    $payStatus = $this->getContractPayStatus($value['contract_id'],$value);
                    $datas[$key]['pay_status'] = $payStatus['error'] ? '' : $payStatus['message'];
                }
            }
        }
        return $datas;
    }

    /**
     * 说明：根据结算参数id=>value获取结算参数row
     * @params array $data id=>value值集合
     * @return array 
     */
    function getParamRowsByParamId(&$data){
        $res = [];
        if (empty($data)) {
            return $res;
        }
        if (!is_array($data)) {
            return $res;
        }
        $mdl_params = app::get('b2c')->model('contract_account_params');//结算参数
        $tmpParams = $mdl_params->getList('*', ['disabled'=>'false']);
        if (empty($tmpParams)) {
            return $res;
        }
        $params = [];
        foreach ($tmpParams as $key => $value) {
            $params[$value['params_id']] = $value;
        }

        //开始根据[id=>value值集合]构造数据
        foreach ($data as $k => $v) {
            if (isset($params[$k]) && is_array($params[$k])) {
                $res[$k]['params_id'] = $k;
                $res[$k]['value'] = $v;
                $res[$k]['no'] = $params[$k]['no'];
                $res[$k]['title'] = $params[$k]['title'];
            }
        }

        return $res;
    }


    /**
     * 说明：根据合约id获取该合约商品
     * @params int $contract_id 合约id
     * @return array 
     */
    function getContractGoodsByContractId($contract_id){
        $datas = [];
        if (empty($contract_id)) {
            return $datas;
        }

        //获取合约商品
        $groupBy = ' group by cp.product_id';
        $join = '';
        $orderBy = ' order by cp.id asc';
        $filter['contract_id|nequal'] = $contract_id;
        $filter['`status`|nequal'] = 'true';
        $fields = 'cp.id,cp.product_id,g.goods_id,`p`.`name`,cp.bn,cp.store_left,g.bn gbn,p.weight,p.unit,p.spec_info as spec';
        $fields .= ',g.commodity_property attr,o.gsource_area area,s.shortname,cp.price,cp.num,cp.storehouse';
        $join .= ' LEFT JOIN sdb_b2c_products p ON p.product_id = cp.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id = p.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_gprovenance o ON o.provenance_id = g.provenance_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON s.supplier_id = g.supplier_id';

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract_goods` cp ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'cp')
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql());
        if (empty($datas)) {
            return $datas;
        }
        foreach ($datas as $key => $data) {
            $datas[$key]['price'] = round($data['price'],2);
        }
        return $datas;
    }


    /**
     * 说明：获取合约订单(阶段订单、出库订单)
     * @params int $contract_id 合约id
     * @params array $filter 过滤条件
     * @return array 
     */
    function getContractOrders($contract_id=0,$wheres=[],$orderBy='o.createtime asc'){
        $datas = [];
        if (empty($contract_id)) {
            return $datas;
        }

        //获取合约商品
        $groupBy = ' group by co.order_id';
        $join = '';
        $baseWhere=[];
        $orderBy = ' order by ' . $orderBy;
        $filter['contract_id|nequal'] = $contract_id;
        $filter['state|nequal'] = 'true';
        $fields = 'co.order_id,co.step_id,co.step_type,co.contract_id,co.contract_no,o.`status`,o.pay_status';
        $join .= ' LEFT JOIN sdb_b2c_orders o ON o.order_id = co.order_id';

        $baseWhere[] = "o.`status` != 'dead'";
        $baseWhere[] = "o.promotion_type = 'contract'";
        if (isset($wheres['pay_status'])) {
            $pay_status = $wheres['pay_status'];
            $baseWhere[] = "o.pay_status = '{$pay_status}'";
        }

        //付款状态取反
        if (isset($wheres['pay_status|noequal'])) {
            $pay_status = $wheres['pay_status|noequal'];
            $baseWhere[] = "o.pay_status != '{$pay_status}'";
        }

        if (isset($wheres['step_id'])) {
            $step_id = $wheres['step_id'];
            $baseWhere[] = "co.step_id = '{$step_id}'";
        }

        if (isset($wheres['extra'])) {
            $extra = $wheres['extra'];
            $baseWhere[] = "co.extra = '{$extra}'";
        }

        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract_order` co ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 'co', $baseWhere)
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql());
        if (empty($datas)) {
            return $datas;
        }
        return $datas;
    }


    /**
     * 说明：根据合约id获取该合约结算阶段
     * @params int $contract_id 合约id
     * @return array 
     */
    function getStepRowsByContractId($contract_id){
        $datas = [];
        if (empty($contract_id)) {
            return $datas;
        }
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $mdl_fee = app::get('b2c')->model('contract_fee');//费种
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品

        //获取费种&&格式化，生成[fee_id=>array]形式
        $tmpFees = $mdl_fee->getList('*', ['disabled'=>'false']);
        if (empty($tmpFees)) {
            return $datas;
        }
        $fees = [];
        foreach ($tmpFees as $key => $value) {
            $fees[$value['fee_id']] = $value;
        }

        //获取合约商品
        $products = [];
        $tmpProducts = $this->getContractGoodsByContractId($contract_id);
        foreach ($tmpProducts as $v) {
            $products[$v['product_id']] = $v;
        }
        // ee($products,0);

        //获取结算阶段rows
        $steps = $mdl_step->getList('*',['contract_id'=>$contract_id],0,-1,'end_time asc');
        // ee($steps,0);
        // ee($fees,0);
        // ee($products,0);

        if (empty($steps)) {
            return $datas;
        }

        //生成结算阶段数据格式
        foreach ($steps as $k => $v) {
            if (empty($v['fee_ids'])) continue;
            $steps[$k]['end_time'] = date('Y-m-d', $v['end_time']);
            $fee_ids = explode(',', $v['fee_ids']);
            $expFormat = [];
            foreach ($fee_ids as $feeId) {
                if (isset($fees[$feeId])) {
                    $expFormat[] = $fees[$feeId]['method_format'];
                }
            }
            $steps[$k]['expFormat'] = implode('+', $expFormat);

            //如果fee_ids == 8,则获取合约商品列表
            if ($v['fee_ids'] == '8') {
                $steps[$k]['products'] = $tmpProducts;//这里不用product_id作key
                $steps[$k]['proNum'] = count($tmpProducts);
            }
        }
        // ee($steps);
        return $steps;
    }


    /**
     * 说明：根据合约号计算出要支付的订单
     * @params int $contract_id 合约id
     * @return array 合约订单row
     */
    function getWaitPayOrderRowByContractId($contract_id=0){
        if (empty($contract_id)) {
            return ['error'=>1,'message'=>"合约id不能为空！"];
        }

        $payStatus = $this->getContractPayStatus($contract_id);
        if ($payStatus['error']) {
            return ['error'=>1,'message'=>$payStatus['message']];
        }

        //已经付款直接跳转到-查看合约页面
        if (!empty($payStatus['ispay'])) {
            return ['error'=>1,'message'=>"暂无可支付的结算阶段！"];
        }
        $step_id = $payStatus['data'];

        $wheres = [];
        $wheres['step_id']=$step_id;
        $wheres['pay_status|noequal']='1';
        $waitPayRow = $this->getContractOrders($contract_id,$wheres);
        // ee(sql());
        if (empty($waitPayRow)) {
            return ['error'=>1,'message'=>"暂无可支付的结算阶段！"];
        }
        return $waitPayRow[0];
    }


    /**
     * 说明：根据合约号计算合约付款状态,这里的付款状态是结合orders、contract_order去计算(site && desktop通用)
     * @params int $contract_id 合约id
     * @return array 合约订单row
     */
    function getContractPayStatus($contract_id=0,$contractRow=[]){
        $res = ['error'=>0,'message'=>''];
        if (empty($contract_id)) {
            return ['error'=>1,'message'=>"合约id不能为空！"];
        }

        //已完结情况
        if (empty($contractRow)) {
            $contractRow = $this->getRow('*',['contract_id'=>$contract_id]);
        }

        if ($contractRow['state'] == 'finish') {
            return ['error'=>0,'message'=>'已完结','data'=>0,'ispay'=>1];
        }

        if ($contractRow['state'] == 'off') {
            return ['error'=>1,'message'=>'合约未开启！','data'=>0,];
        }

        if ($contractRow['state'] == 'abend') {
            return ['error'=>1,'message'=>'合约已异常终止！','data'=>0,];
        }

        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段        

        //获取所有阶段列表
        $wheres = [];
        $prefix = '待付款';
        $title = '';
        $step_id = 0;
        $wheres['contract_id']=$contract_id;
        // $wheres['fee_ids|noequal']='9';
        $stepRowsTmp = $mdl_step->getList('*',$wheres,0,-1,'end_time asc');
        if (empty($stepRowsTmp)) {
            return ['error'=>1,'message'=>"出现错误,此合约无任何结算阶段！"];
        }
        //格式化,以step_id为key
        $stepRows = [];
        foreach ($stepRowsTmp as $st) {
            $stepRows[$st['step_id']] = $st;
        }

        //先列出所有合约阶段订单
        $orderRows = $this->getContractOrders($contract_id,[],'co.end_time ASC');
        // ee($stepRows);

        //以阶段检测付款情况,如果未找到pay_status=1的阶段,则该阶段为待付款
        $stepIsOpen = false;
        foreach ($stepRows as $key => $stepRow) {
            if ($stepRow['state'] == 'on') {
                //如果没有任何付款记录，则结果：待支付+此阶段名
                if (empty($orderRows)) {
                    $title = $stepRow['title'];//阶段标题
                    return ['error'=>0,'message'=>$prefix . $title,'data'=>$stepRow['step_id']];
                }

                //查找此阶段已支付的订单
                $stepIsOpen = true;
                $notfind = true;
                $unPayedNum = 0;
                foreach ($orderRows as $orderRow) {
                    if ($orderRow['step_id'] == $stepRow['step_id']) {
                        if ($orderRow['pay_status'] == '1') {
                            //如果找到所有阶段已付款了,则付款状态为：已付款+最后一个阶段名称,这里实现
                            $notfind = false;
                            $prefix = '已付款';
                            $title = $stepRow['title'];//阶段标题
                            $step_id = $stepRow['step_id'];
                            continue;
                        }elseif($orderRow['pay_status'] == '0'){
                            //检测若为出库阶段订单是否有某个出库批次未付款
                            $unPayedNum++;
                        }
                    }
                }

                //若未找到此阶段已付款记录
                if($notfind){
                    $prefix = '待付款';
                    $title = $stepRow['title'];//阶段标题
                    return ['error'=>0,'message'=>$prefix . $title,'data'=>$stepRow['step_id']];
                }

                //加强验证出库阶段
                //如果是出库的阶段,则已付款逻辑为：1所有商品库存为0,2所有该出库阶段订单付款状态为1
                if ($stepRow['fee_ids'] == '8') {
                    $all_store_left  = 0;
                    //TODO
                    $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
                    $leftRow = $mdl_contract_goods->count(['contract_id'=>$contract_id,'store_left|than'=>'0']);
                    if ($leftRow) {
                        $all_store_left  = 1;
                    }

                    if (!($all_store_left == 0 && $unPayedNum == 0)) {
                        $prefix = '待付款';
                        $title = $stepRow['title'];//阶段标题
                        return ['error'=>0,'message'=>$prefix . $title,'data'=>$stepRow['step_id']];
                    }
                }
            }

        }
        if (!$stepIsOpen) {
            return ['error'=>1,'message'=>"暂未开启任何结算阶段！"];
        }

        //TODO
        //严格验证(验证已经finish的阶段是否orders订单状态相对应 && 付款payment为succ)
        return ['error'=>0,'message'=>$prefix . $title,'data'=>$step_id,'ispay'=>1];
    }

    /**
     * 说明：写入合约操作日志
     * @params int $contract_id 合约id
     * @params string $res 结果[succ|error]
     * @params string $act 行为
     * @params string $content 合约备注
     * @params int $uid 操作员ID
     * @params string $name 操作人名称
     * @params string $addon 序列化数据
     * @return bool 
     */
    function saveDologs($contract_id=0,$result='error',$act='',$content='',$uid=0,$name='',$addon=''){
        $res = false;
        if (empty($contract_id)) {
            return $res;
        }
        $ctime = time();
        $fields                = [];
        $fields['contract_id'] = $contract_id;
        $fields['op_id']       = $uid ? $uid : 0;
        $fields['op_name']     = $name ? $name : '';
        $fields['behavior']    = $act ? $act : 'other';
        $fields['result']      = $result ? $result : 'error';
        $fields['log_text']    = $content ? $content : '';
        $fields['addon']       = $addon ? $addon : '';
        $fields['alttime']     = $ctime;

        $mdl_dologs = app::get('b2c')->model('contract_dologs');//合约操作日志
        $res = $mdl_dologs->insert($fields);
        // ee(sql());
        return $res;
    }

    /**
     * 说明：获取合约操作日志
     * @params int $contract_id 合约id
     * @return array 
     */
    function getDologs($contract_id){
        $res = [];
        if (empty($contract_id)) {
            return $res;
        }
        $mdl_dologs = app::get('b2c')->model('contract_dologs');//合约操作日志
        $dologsRows = $mdl_dologs->getList('*', ['contract_id'=>$contract_id]);
        if (empty($dologsRows)) {
            return $res;
        }

        $dologsSchema = $mdl_dologs->get_schema();
        $behavior_arr = $dologsSchema['columns']['behavior']['type'];//操作日志行为
        foreach ($dologsRows as $key => $value) {
            $dologsRows[$key]['alttime'] = date('Y-m-d H:i:s',$value['alttime']);
            $dologsRows[$key]['result_name'] = $value['result'] == 'succ' ? '成功' : '失败';
            $dologsRows[$key]['behavior_name'] = isset($behavior_arr[$value['behavior']]) ? $behavior_arr[$value['behavior']] : '';
        }
        return $dologsRows;
    }
    
///////////////////////////////////////////////SITE USE//////////////////////////////////////////////////////
    /**
     * 说明：合约出库合法性检测(这里只检测最小出库量&&最小出库金额)
     * @params array $data 合约基础数据(getContractData()返回值)
     * @params array $outResult 出库结果(stepMoneyCalculate()返回值)
     * @params array $step_id 阶段id(用来判断最后一批若有临时附加费则必须勾选上用)
     * @params array $extra 临时附加费状态(0|1)
     * @return array 返回[error、message]
     */
    function outStoreCheck($data=[],$outResult=[],$step_id=0, $extra='0'){
        $res = ['error'=>0,'message'=>''];

        $outTotalNum     = $outResult['outTotalNum'];//出库总数量
        $storeLeftNum    = $outResult['storeLeftNum'];//剩余库存量
        $storeLeftAmount = $outResult['storeLeftAmount'];//剩余库存总值
        $total           = $outResult['total'];//出库总值
        $min_num         = $data['min_num'];
        $min_amount      = round($data['min_amount'],2);

        //最小出库数检测
        //如果剩余库存比最小出库限制大
        if ($min_num < $storeLeftNum) {
           if ($outTotalNum < $min_num) {
               return ['error'=>1,'message'=>"每次出库商品总数量不能少于{$min_num}！"];
           }
        }else{
            //如果剩余总库存不足以达到出库数量限制,还应该限制一个条件就是本次应该全部出库
            if ($outTotalNum < $storeLeftNum) {
                return ['error'=>1,'message'=>"因每次出库数量限制,本次商品必须全部出库！"];
            }
        }
        
        //最小出库金额检测
        if ($min_amount < $storeLeftAmount) {
            if ($total < $min_amount) {
                return ['error'=>1,'message'=>"每次出库商品总金额不能少于 ￥{$min_amount}元！"];
            }
        }else{
            //如果剩余总货值不足以达到出库总金额限制,还应该限制:1.一个条件就是本次应该全部出库
            if ($total < $storeLeftAmount) {
                return ['error'=>1,'message'=>"因每次出库总金额限制,本次商品必须全部出库！"];
            }
        }

        //非法检测一：最后一批出库(1.剩余不足以最小限制,2.全部出库)
        //非法检测二：如果[1.有临时附加费2.且临时附加费没有支付过3.临时附加费没有勾选]则提示
        if ($min_num >= $storeLeftNum || $min_amount >= $storeLeftAmount || $outTotalNum >= $storeLeftNum) {
           $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
           $stepRow = $mdl_step->getRow('*',['step_id'=>$step_id,'state'=>'on']);

           if (empty($stepRow)) {
               return ['error'=>1,'message'=>"无此合约的结算阶段数据！"];
           }

            if ($stepRow['extra'] == '1') {
                //获取已经支付过临时附加费的此合约的出库阶段
                $contract_id = $stepRow['contract_id'];
                $whereArr = ['extra'=>'1','pay_status'=>'1'];
                $extraPayedOrderRow = $this->getContractOrders($contract_id, $whereArr);
                if (empty($extraPayedOrderRow) && $extra <= 0) {
                    return ['error'=>1,'message'=>"最后一批出库临时附加费必须一起支付！"];
                }
            }
           
        }
        return $res;
    }
    /**
     * 说明：阶段总值计算
     * @params string $fee_ids 费种
     * @params array $params 结算参数设置的值数组
     * @params array $products 合约商品
     * @return array 返回[total=>出库总值、detail=>出库公式详细,outTotalNum=>出库总数量,storeLeftNum=>剩余库存量]
     */
    function stepMoneyCalculate($fee_ids='',$params=[],$products=[],$extra='0'){
        //storeLeftAmount：剩余库存总值
        $res = ['total'=>0,'detail'=>'','outTotalNum'=>0,'storeLeftNum'=>0,'storeLeftAmount'=>0];
        if (empty($fee_ids) || empty($params)) {
            return $res;
        }
        if (!empty($products)) {
            $ctime = date('Y-m-d');
            $res['detail'] = [];
            foreach ($products as $p) {
                $p['outNum'] = (isset($p['outNum']) && $p['outNum'] > 0) ? $p['outNum'] : 0;
                $feeMoneyData = $this->feeMoneyOutStoreCalculate($params,$p['price'],$p['outNum'],$ctime,$p['store_left']);
                $res['total'] += $feeMoneyData['total'];
                $res['storeLeftAmount'] += $feeMoneyData['storeLeftAmount'];
                $res['outTotalNum'] += $p['outNum'];//出库总数量
                $res['storeLeftNum'] += $p['store_left'];//当前总数量
                $res['detail'][] = $feeMoneyData['detail'];

                if ($p['outNum'] > 0) {
                    $outDetial = $p;//单种商品出库详情(amount、price、goods_id、product_id、bn、store_left、)
                    $outDetial['amount']     = round($feeMoneyData['total'],2);//每个商品总价
                    $outDetial['store_left'] = $p['store_left'];
                    $res['outDetial'][$p['product_id']] = $outDetial;
                }

            }

            $res['storeLeftAmount'] = round($res['storeLeftAmount'],2);

            //出库的临时附加费
            if ($extra > 0) {
                $extraAmount = !empty($params['9']['value']) ? $params['9']['value'] : 0;//附加费价格
                if ($extraAmount) {
                    $res['detail'][] = $extraAmount;
                    $res['total'] += $extraAmount;
                }

            }
        }else{
            $fee_ids = explode(',', $fee_ids);
            foreach ($fee_ids as $fee_id) {
                $feeMoneyData = $this->feeMoneyCalculate($fee_id,$params);
                $res['total'] += $feeMoneyData['total'];
                $res['detail'][] = $feeMoneyData['detail'];
            }
            $res['detail'] = implode('+', $res['detail']);
        }
        $res['total'] = round($res['total'],2);
        return $res;
    }


    /**
     * 说明：单个费种计算值
     * @params int $fee_id 费种id
     * @params array $params 结算参数设置的值数组
     * @return array 返回[总值、详细]
     */
    function feeMoneyCalculate($fee_id=0,$params=[]){
        $res = ['total'=>0,'detail'=>''];
        if (empty($fee_id) || empty($params)) {
            return $res;
        }
        //费种id计算值对应的结算参数id
        $feeidToParamid = [
            '2'=>'7',
            '3'=>'10',
            '4'=>'2',
            '5'=>'5',
            '6'=>'8',
            '7'=>'11',
            '9'=>'9',
        ];
        switch ($fee_id) {
            case '1':
                if (empty($params['1']) || empty($params['4'])) {
                    return $res;
                }
                $res['total'] = $params['1']['value'] * ($params['4']['value']/100);
                $res['detail'] = $params['1']['value'] . '*' . $params['4']['value'] . '%';
                break;
            default:
                if (empty($feeidToParamid[$fee_id]) || empty($params[$feeidToParamid[$fee_id]])) {
                    return $res;
                }
                $res['total'] = $params[$feeidToParamid[$fee_id]]['value'];
                $res['detail'] = $res['total'];
                break;
        }
        return $res;
    }


    /**
     * 说明：单个费种(单独出库的计算)计算值
     * @params array $params 结算参数设置的值数组
     * @params float $price 商品价格
     * @params int $num 商品数量
     * @params string $ctime 当次出库日期
     * @params int $storeLeft 当前库存
     * @return array 返回[total=>出库总值、storeLeftAmount=>剩余库存总值,detail=>详细]
     */
    function feeMoneyOutStoreCalculate($params=[],$price=0,$num=0,$ctime='',$storeLeft=0){
        $res = ['total'=>0,'storeLeftAmount'=>0,'detail'=>''];
        if (empty($params)) {
            return $res;
        }
        if (empty($price) || empty($ctime)) {
            return $res;
        }
        //检测金融起息日
        if (empty($params['6'])) {
            return $res;
        }
        //检测点数
        if (empty($params['3'])) {
            return $res;
        }
        $price = round($price,2);
        $point = round($params['3']['value']/100,2);
        $vdate = $params['6']['value'];

        //天数之差
        $vdateFormatInt = strtotime($params['6']['value']);
        $ctimeFormatInt = strtotime($ctime);
        $Ldate = ($ctimeFormatInt - $vdateFormatInt)/86400 + 1;

        //即使未出库,剩余货值也需要计算出来
        $res['storeLeftAmount'] = $price*$storeLeft+$price*$storeLeft*$point/365*$Ldate;
        if (empty($num)) {
            return $res;
        }

        $res['detail'] = '(' . $price . '*' . $num . '+' . $price . '*' . $num . '*' . $point . '/365*([' . $ctime . ']-[' . $vdate . ']))';
        $res['total'] = $price*$num+$price*$num*$point/365*$Ldate;
        return $res;
    }


    /**
     * 说明：出库合法性检测(1.检测根据出库数量剩余库存是否不足)
     * @params array $products 合约商品
     * @return array
     */
    function storeLeftCheck($products=[]){
        $res = ['error'=>0,'message'=>''];
        if (empty($products)) {
            return ['error'=>1,'message'=>'合约商品不能为空！'];
        }
        foreach ($products as $p) {
            if ($p['outNum'] > $p['store_left']) {
                return ['error'=>1,'message'=>"商品【{$p['name']}】库存不足！"];
            }
        }
        return $res;
    }


    /**
     * 说明：阶段开启验证(不能同时开启多个阶段)-desktop
     * @params array $steps 合约阶段
     * @return array
     */
    function stepOpenCheck($steps=[]){
        $res = ['error'=>0,'message'=>''];
        if (empty($steps)) {
            return $res;
        }

        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段

        $openNum = 0;//同时开启数量
        $fee_ids = [];//同时开启数量
        foreach ($steps as $step) {
            if (empty($step['state'])) continue;
            if ($step['state'] == 'on') {
                $openNum++;
                //如果有step_id却没有fee_ids则需要到阶段表里面查询处理fee_ids
                if (empty($step['fee_ids'])) {
                    if (!empty($step['step_id'])) {
                       $stepRow = $mdl_step->getRow('*',['step_id'=>$step['step_id']]);
                       if (empty($stepRow)) {
                           return ['error'=>1,'message'=>'没有此结算阶段！'];
                       }
                       $fee_ids[] = $stepRow['fee_ids'];
                    }
                }else{
                    $fee_ids[] = $step['fee_ids'];
                }
            }
        }    
        //注意8,9两个费种可以同时开启
        if ($openNum > 1) {
            if (!($openNum == 2 && count(array_intersect([8,9],$fee_ids)) == 2)) {
                return ['error'=>1,'message'=>'不能同时开启两个阶段！'];
            }
        }
        return $res;
    }

    /**
     * 说明：合约的阶段付款列表(履约头部使用)
     * @params int $contract_id 合约id
     * @return array 
     */
    function stepsPayment($contract_id=0,$step_id=0){
        $datas = [];
        if (empty($contract_id)) {
            return $datas;
        }
        //获取合约商品
        $groupBy = ' group by s.step_id';
        $join    = '';
        $orderBy = ' order by s.end_time asc';
        $filter['contract_id|nequal'] = $contract_id;
        $filter['`state`|noequal'] = 'off';
        if (!empty($step_id)) {
            $filter['step_id|nequal'] = $step_id;
        }
        $fields  = 's.step_id,s.fee_ids,s.extra,s.title,s.state,sum(p.money) payed';
        $join .= ' LEFT JOIN sdb_ectools_payments_contract pc ON pc.step_id = s.step_id';
        $join .= " LEFT JOIN sdb_ectools_payments p ON (p.payment_id = pc.payment_id and p.`status`='succ')";
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract_account_step` s ' 
                        . $join
                        . ' where ' 
                        . $this->_filter($filter, 's')
                        . $groupBy
                        . $orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        // ee(sql());
        if (empty($datas)) {
            return $datas;
        }

        foreach ($datas as $key => $value) {
            $datas[$key]['payed'] = round(floatval($value['payed']),2);
        }
        // ee($datas);
        return $datas;
    }


    /**
     * 说明：合约的各个阶段付款状态
     * @params int $contract_id 合约id
     * @params array $steps 合约所有阶段
     * @return array 
     */
    function stepsPayStatus(&$steps, $contract_id=0){
        if (empty($contract_id) || empty($steps)) {
            return false;
        }
        $stepPayRows = $this->getContractOrders($contract_id);
        foreach ($steps as $key => $step) {
            $steps[$key]['payed'] = 0;//默认此阶段是未支付的
            //查找并确定已付款的阶段
            $unPayedNum = 0;//用于计算出库未付款单数
            foreach ($stepPayRows as $stepPayRow) {
                if ($step['step_id'] == $stepPayRow['step_id']) {
                    if ($stepPayRow['pay_status'] == '1') {
                        $steps[$key]['payed'] = 1;
                    }elseif($stepPayRow['pay_status'] == '0'){
                        $unPayedNum++;
                    }
                }
            }


            //如果是出库的阶段,则已付款逻辑为：1所有商品库存为0,2所有该出库阶段订单付款状态为1
            if ($step['fee_ids'] == '8') {
                $steps[$key]['payed'] = 0;
                //到这里会重新计算当前出库的付款状态0未付款或者部分付款,1为已付款干净
                $all_store_left  = 0;
                $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
                $leftRow = $mdl_contract_goods->count(['contract_id'=>$contract_id,'store_left|than'=>'0']);
                if ($leftRow) {
                    $all_store_left  = 1;
                }

                if ($all_store_left == 0 && $unPayedNum == 0) {
                    $steps[$key]['payed'] = 1;
                }
            }
        }

    }


    /**
     * 说明：修改合约购物车-site(把普通购物车的数据改为适应合约的，例如总价、合约商品单价等)
     * @params array $aCart 合约购物车
     * @params int $member_id 会员id
     * @return array 
     */
     function editContractCart(&$aCart,$member_id=0){
        //合约商品每种货品价格列表计算价格 && 单价 && 总价
        $cgoods = $aCart['object']['goods'];
        $batchGoods = [];//本批次商品
        $batchGoods['contract_id'] = $_SESSION['contract_id'];
        $batchGoods['outStore'] = [];
        foreach ($cgoods as $cg) {
            $product_id = $cg['params']['product_id'];
            $batchGoods['outStore'][$product_id] = $cg['quantity'];
        }
        $extra = $_SESSION['extra'];
        $step_id = $_SESSION['step_id'];
        $outStoreRes = $this->getOutStoreCalcu($batchGoods,$member_id,$step_id,$extra);
        $aCart['contract_goods'] = $outStoreRes;
        $aCart['contract_goods']['step_id'] = $step_id;
        $aCart['contract_goods']['step_type'] = $_SESSION['step_type'];
        $aCart['contract_goods']['extra'] = $extra;//是否包含临时附加费
        // ee($outStoreRes);

        //重新修改购物车每件商品总价格,修改aCart.object.goods.key.subtotal_prefilter_after
        //重新修改购物车每件商品销售价格,修改aCart.object.goods.key.obj_items.products.0.price.price
        foreach ($aCart['object']['goods'] as $gkey => $gval) {
            $product_id = $gval['params']['product_id'];
            $cmoney = $outStoreRes['outDetial'][$product_id]['amount'];//单商品购买一定数量后计算价格
            $cprice = $outStoreRes['outDetial'][$product_id]['price'];//但商品合约价
            $aCart['object']['goods'][$gkey]['subtotal_prefilter_after'] = $cmoney;
            $aCart['object']['goods'][$gkey]['subtotal'] = $cmoney;
            $aCart['object']['goods'][$gkey]['subtotal_price'] = $cmoney;
            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['subtotal'] = $cmoney;

            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['price']['price'] = $cprice;
            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['price']['buy_price'] = $cprice;
            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['json_price']['price'] = $cprice;
            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['json_price']['buy_price'] = $cprice;
        }
    }


    /**
     * 说明：修改合约虚拟购物车-site(非出库阶段使用,例如修改总价为当前阶段总价)
     * @params array $aCart 合约购物车
     * @params int $member_id 会员id
     * @return array 
     */
     function editContractVirtualCart(&$aCart,$member_id=0){
        $params['contract_id'] = $_SESSION['contract_id'];
        $params['step_id'] = $step_id = $_SESSION['step_id'];
        $outStoreRes = $this->getNoOutStoreCalcu($params,$member_id);
        $aCart['contract_goods'] = $outStoreRes;
        $aCart['contract_goods']['step_id'] = $step_id;
        $aCart['contract_goods']['step_type'] = $_SESSION['step_type'];
        // ee($outStoreRes);

        foreach ($aCart['object']['goods'] as $gkey => $gval) {
            $product_id = $gval['params']['product_id'];
            $amount = $outStoreRes['total'];//总价
            $aCart['object']['goods'][$gkey]['subtotal_prefilter_after'] = $amount;
            $aCart['object']['goods'][$gkey]['subtotal'] = $amount;
            $aCart['object']['goods'][$gkey]['subtotal_price'] = $amount;
            $aCart['object']['goods'][$gkey]['obj_items']['products'][0]['subtotal'] = $amount;
        }
    }



    /**
     * 说明：出库计算-site(用于合约购物车)
     * @params array $params 合约出库数据
     * @params int $member_id 会员id
     * @params string $extra 临时附加费
     * @params int $step_id 阶段id
     * @return array 
     */
     function getOutStoreCalcu($params=[],$member_id,$step_id=0,$extra='0'){
        if (empty($params['contract_id'])) {
            return ['error'=>1,'message'=>'非法的合约ID号！'];
        }

        $contract_id = trim($params['contract_id']);

        //获取合约数据
        $data = $this->getContractData($contract_id,$member_id);
        if (empty($data)) {
            return ['error'=>1,'message'=>'无此合约数据！'];
        }

        if (empty($data['products'])) {
            return ['error'=>1,'message'=>'合约商品不能为空'];
        }
        $products = $data['products'];

        if (empty($params['outStore'])) {
            return ['error'=>13,'message'=>'请至少选择一个商品出库！'];
        }

        //合并出库到合约商品
        $outStore = $params['outStore'];
        $outTotalNum = 0;//出库商品总量
        foreach ($outStore as $key => $num) {
            if (empty($products[$key])) {
                return ['error'=>1,'message'=>"没有此合约商品[{$key}]！"];
            }
            $products[$key]['outNum'] = $num;
            $outTotalNum += $num;
        }

        // ee($products);
        $stepMoney = $this->stepMoneyCalculate('8',$data['params'],$products,$extra);

        //最小出库数检测 && 最小出库金额检测
        $outCheckRes = $this->outStoreCheck($data,$stepMoney,$step_id,$extra);
        if ($outCheckRes['error'] != 0) {
            return ['error'=>1,'message'=>$outCheckRes['message']];
        }

        $stepMoney['outNum'] = $outTotalNum;
        $stepMoney['contract_id'] = $contract_id;
        $stepMoney['contract_no'] = $data['contract_no'];
        return $stepMoney;
    }


    /**
     * 说明：非出库计算-site(用于合约购物车)
     * @params array $params 参数数组，包括contract_id、step_id
     * @params int $member_id 会员id
     * @return array 
     */
     function getNoOutStoreCalcu($params=[],$member_id=0){
        if (empty($params['contract_id'])) {
            return ['error'=>1,'message'=>'非法的合约ID号！'];
        }

        $contract_id = trim($params['contract_id']);

        //获取合约数据
        $data = $this->getContractData($contract_id,$member_id);
        if (empty($data)) {
            return ['error'=>1,'message'=>'无此合约数据！'];
        }
        $mdl_step = app::get('b2c')->model('contract_account_step');//结算阶段
        $stepRow = $mdl_step->getRow('*',['step_id'=>$params['step_id']]);

        if (empty($stepRow)) {
            return ['error'=>1,'message'=>'没有此结算阶段！'];
        }

        // ee($stepRow);
        $stepMoney = $this->stepMoneyCalculate($stepRow['fee_ids'],$data['params']);
        // ee($stepMoney);

        $stepMoney['contract_id'] = $contract_id;
        $stepMoney['contract_no'] = $data['contract_no'];
        return $stepMoney;
    }


    /**
     * 说明：保存合约出库数据(table:contract_goods_out)
     * @params array $outRes $this->stepMoneyCalculate()的返回值,合约出库数据结果
     * @return array 
     */
     function saveOutStore($outRes=[],$order_id=0){
        $res = ['error'=>0,'message'=>'操作成功！'];
        if (empty($outRes)) {
            return ['error'=>1,'message'=>'没有合约出库数据！'];
        }
        if (empty($outRes['outDetial'])) {
            return ['error'=>1,'message'=>'没有合约商品出库！'];
        }
        if (empty($order_id)) {
            return ['error'=>1,'message'=>'订单号不能为空！'];
        }
        $mdl_contract_goods_out = app::get('b2c')->model('contract_goods_out');//合约出库商品
        $mdl_contract_goods = app::get('b2c')->model('contract_goods');//合约商品
        $data = [];
        $ctime = date('Y-m-d H:i:s');
        $outGoods = $outRes['outDetial'];
        $updateSql = [];
        $cgtable = $mdl_contract_goods->table_name(true);
        foreach ($outGoods as $ogoods) {
            $out = [];
            $store_left = $ogoods['store_left'] - $ogoods['outNum'];
            if ($store_left < 0 ) {
                $cgoodsName = $ogoods['name'];
                return ['error'=>1,'message'=>"商品[{$cgoodsName}]库存不足！"];
            }
            $out['order_id'] = $order_id;
            $out['contract_id'] = $outRes['contract_id'];
            $out['goods_id'] = $ogoods['goods_id'];
            $out['product_id'] = $ogoods['product_id'];
            $out['bn'] = $ogoods['bn'];
            $out['price'] = $ogoods['price'];
            $out['num'] = $ogoods['outNum'];
            $out['store_left'] = $store_left;
            $out['storehouse'] = $ogoods['storehouse'];
            $out['ctime'] = $ctime;
            $data[] = $out;


            //拼装更新所有合约商品当前库存(store_left)SQL
            $cgid = $ogoods['id'];
            $updateSql[] = "update {$cgtable} set store_left={$store_left} where id=$cgid;";
        }

        if (empty($data)) {
            return ['error'=>1,'message'=>'没有出库商品数据！'];
        }

        $result = $mdl_contract_goods_out->insertAll($data);
        if ($result) {
            //更新所有合约商品当前库存(store_left)
            foreach ($updateSql as $csql) {
                $updateRes = $mdl_contract_goods->db->exec($csql);
                if (!$updateRes) {
                    return ['error'=>1,'message'=>'更新合约商品当前库存时出错！'];
                }
            }
            return $res;
        }
        return ['error'=>1,'message'=>'保存合约商品出库流水出错！'];
    }


    /**
     * 说明：生成合约订单(table:contract_order)
     * @params array $contract_goods aCart 的数据
     * @return array 
     */
     function createContractOrder($contract_goods=0,$order_id=0,$step_id=0,$step_type='out'){
        $res = ['error'=>0,'message'=>'操作成功！'];
        $contract_id = $contract_goods['contract_id'];
        $step_id = $contract_goods['step_id'];
        $step_type = $contract_goods['step_type'];
        $extra = $contract_goods['extra'];
        // ee($contract_goods);

        if (empty($contract_id)) {
            return ['error'=>1,'message'=>'合约id不能为空'];
        }
        if (empty($order_id)) {
            return ['error'=>1,'message'=>'订单id不能为空'];
        }

        $wheres['contract_id'] = $contract_id;
        $contractRows = parent::getList('*',$wheres);

        if (empty($contractRows)) {
            return ['error'=>1,'message'=>'没有此合约！'];
        }
        $contractRow = $contractRows[0];
        $mdl_contract_order = app::get('b2c')->model('contract_order');//合约订单
        $data = [];
        $data['order_id']    = $order_id;
        $data['contract_no'] = $contractRow['contract_no'];
        $data['member_id']   = $contractRow['member_id'];
        $data['contract_id'] = $contractRow['contract_id'];
        $data['state']       = 'true';
        $data['begin_time']  = $contractRow['begin_time'];
        $data['end_time']    = $contractRow['end_time'];
        $data['step_id']     = $step_id;//从哪保存?
        $data['step_type']   = $step_type;
        $data['extra']       = $extra;
        $result              = $mdl_contract_order->insert($data);
        if ($result) {
            return $res;
        }
        return ['error'=>1,'message'=>'保存合约订单时出错！'];
    }

    //根据合约id会员id获取合约基本信息
    function getContract($contract_id,$member_id){
        $datas = [];
        if (empty($contract_id)||empty($member_id)) {
            return $datas;
        }
        $orderBy =' GROUP BY contract_id';
        $join = '';
        $fields = 'c.contract_no,c.contract_id,c.ship_status,c.mtime,c.amount,c.begin_time,c.end_time,c.content,c.accounts,c.pay_status,c.min_amount,c.min_num';
        $join .= ' LEFT JOIN sdb_b2c_contract_account_step ca ON c.contract_id=ca.contract_id'; 
        $whereStr='1 and c.member_id='.$member_id.'   and c.contract_id='.$contract_id;
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract` c ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        if($datas){
            foreach ($datas as $key => $value) {
                //物流状态
                if($value['ship_status']=='seastore'){
                   $datas[$key]['ship_status']="海外仓储";
                }elseif ($value['ship_status']=='interlogi') {
                   $datas[$key]['ship_status']="国际物流";
                }elseif ($value['ship_status']=='clearance'){
                   $datas[$key]['ship_status']="通关";
                }elseif ($value['ship_status']=='storage'){
                   $datas[$key]['ship_status']="仓储";
                }elseif ($value['ship_status']=='citydist'){
                   $datas[$key]['ship_status']="城配";
                }elseif ($value['ship_status']=='buy'){
                   $datas[$key]['ship_status']="采购";
                }
                //显示时间处理
                 $datas[$key]['begin_time']=date('Y.m.d',$value['begin_time']);
                 $datas[$key]['end_time']=date('Y.m.d',$value['end_time']);
            }
        }
        return $datas;
}
     //根据合约id会员id获取商品基本信息
    function getGoods($contract_id,$member_id){
        $datas = [];
        if (empty($contract_id)||empty($member_id)) {
            return $datas;
        }
        $orderBy =' GROUP BY cg.product_id';
        $join = '';
        $whereStr='1 and c.member_id='.$member_id.'  and cas.state="on" and c.contract_id='.$contract_id;
        $fields = 'g.name,cg.bn as bn ,g.weight,g.unit,g.commodity_specification,g.commodity_property,gp.gsource_area,s.supplier_name,cg.num,cg.price as mktprice,cg.storehouse';
        $join .= ' LEFT JOIN sdb_b2c_contract_account_step cas ON c.contract_id=cas.contract_id';
        $join .= ' LEFT JOIN sdb_b2c_contract_goods cg ON cg.contract_id=c.contract_id';
        $join .= ' LEFT JOIN sdb_b2c_products p ON cg.product_id=p.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON p.goods_id=g.goods_id';
        $join .= ' LEFT JOIN sdb_b2c_gprovenance gp ON g.provenance_id=gp.provenance_id';
        $join .= ' LEFT JOIN sdb_b2c_supplier s ON g.supplier_id=s.supplier_id';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract` c ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        return $datas;

}



     //根据合约id会员id商品出库流水
    function getGoodsout($contract_id,$member_id){
        $datas = [];
        if (empty($contract_id)||empty($member_id)) {
            return $datas;
        }
        $orderBy ='';
        $join = '';
        $whereStr='1 and o.member_id='.$member_id.'  and o.contract_id='.$contract_id;
        $fields = 'cgo.order_id,cgo.ctime,g.name,cgo.bn as bn ,p.spec_info,cgo.price,cgo.store_left,cgo.num ';
        $join .= ' LEFT JOIN sdb_b2c_contract_goods_out cgo ON o.contract_id=cgo.contract_id';
        $join .= ' LEFT JOIN sdb_b2c_products p ON p.product_id=cgo.product_id';
        $join .= ' LEFT JOIN sdb_b2c_goods g ON g.goods_id=p.goods_id';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract` o ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        return $datas;


}

      //根据合约id会员id商品支付流水
    function getpayment($contract_id,$member_id){
        $datas = [];
        if (empty($contract_id)||empty($member_id)) {
            return $datas;
        }
        $orderBy ='';
        $join = '';
        $whereStr='1 and co.member_id='.$member_id.' and  eo.bill_type='.'"payments"'.' and  co.contract_id='.$contract_id;
        $fields = ' ep.pay_type,ep.money,ep.pay_app_id,ep.t_payed,ep.status  ';
        $join .= ' LEFT JOIN sdb_ectools_order_bills eo ON co.order_id=eo.rel_id';
        $join .= ' LEFT JOIN sdb_ectools_payments ep ON eo.bill_id=ep.payment_id';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract_order` co ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        if($datas){
            foreach ($datas as $key => $value) {
                   //支付方式
                $opayment = app::get('ectools')->model('payments');
                $payment = $opayment->dump(array('pay_app_id'=>$value['pay_app_id']), 'pay_name', $subsdf=null);  
                $datas[$key]['pay_app_id']= $payment['pay_name'];
                $datas[$key]['t_payed']=date('Y-m-d H:i:s',$value['t_payed']);
                //支付类型
                 if($value['pay_type']=='online'){
                   $datas[$key]['pay_type']="在线支付";
                }elseif ($value['pay_type']=='offline') {
                   $datas[$key]['pay_type']="线下支付";
                }elseif ($value['pay_type']=='deposit'){
                   $datas[$key]['pay_type']="预存款支付";
                }
                //支付状态
                switch ($value['status']) {
                      case "succ":
                      $datas[$key]['status']='支付成功';
                break;
                      case "failed":
                      $datas[$key]['status']='支付失败';
                break;
                      case "cancel":
                      $datas[$key]['status']='未支付';
                break;
                      case "error":
                      $datas[$key]['status']='处理异常';
                break;
                      case "invalid":
                      $datas[$key]['status']='非法参数';
                break;
                      case "progress":
                      $datas[$key]['status']='已付款至担保方';
                break;
                      case "timeout":
                      $datas[$key]['status']='超时';
                break;
                     case "ready":
                     $datas[$key]['status']='准备中';       
            default:
                break;
        }
            }

        }
        return $datas;

   
}
//根据合约号查询物流状态
function contract_ships_status($contract_id){
  $datas = [];
        if (empty($contract_id)) {
            return $datas;
        }
        $orderBy =' ORDER BY ctime asc';
        $join = '';
        $whereStr='1 and o.contract_id='.$contract_id;
        $fields = 'o.contract_id,o.ship_status,ctime';
        $sql ='select ' . $fields 
                        . ' from' 
                        . ' `sdb_b2c_contract_ship_status` o ' 
                        . $join
                        . ' where ' 
                        . $whereStr
                        .$orderBy;
        $datas = $this->db->selectLimit($sql,-1);
        foreach ($datas as $key => $value) {
            //物流状态
                if($value['ship_status']=='seastore'){
                   $datas[$key]['ship_status']="海外仓储";
                }elseif ($value['ship_status']=='interlogi') {
                   $datas[$key]['ship_status']="国际物流";
                }elseif ($value['ship_status']=='clearance'){
                   $datas[$key]['ship_status']="通关";
                }elseif ($value['ship_status']=='storage'){
                   $datas[$key]['ship_status']="仓储";
                }elseif ($value['ship_status']=='citydist'){
                   $datas[$key]['ship_status']="城配";
                }elseif ($value['ship_status']=='buy'){
                   $datas[$key]['ship_status']="采购";
                }
        }
        return $datas;
}
}