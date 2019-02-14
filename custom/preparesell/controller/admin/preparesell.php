<?php
class preparesell_ctl_admin_preparesell extends desktop_controller
{
    function index()
    {
        $custom_actions[] = array('label'=>app::get('preparesell')->_('添加预售规则'),'href'=>'index.php?app=preparesell&ctl=admin_preparesell&act=add_rule','target'=>'_blank');
        $custom_actions[] = array('label'=>app::get('preparesell')->_('删除规则'),'submit'=>'index.php?app=preparesell&ctl=admin_delprepare&act=del_rules','target'=>'dialog');
        $custom_actions[] = array('label'=>app::get('preparesell')->_('预售订单统计'),'submit'=>'index.php?app=preparesell&ctl=admin_prepare_order&act=prepare_order_number','target'=>'dialog');
        $actions_base['title'] = app::get('preparesell')->_('预售规则');
        $actions_base['actions'] = $custom_actions;
        $actions_base['use_buildin_recycle'] = false;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_view_tab'] = true;
        $this->finder('preparesell_mdl_preparesell',$actions_base);
    }


    public function _views()
    {
          $mdl_preparesell=$this->app->model('preparesell');
        $sub_menu = array(
            0=>array('label'=>app::get('preparesell')->_('未开启'),'optional'=>false,'filter'=>array('status'=>'false')),
            1=>array('label'=>app::get('preparesell')->_('未开始'),'optional'=>false,'filter'=>array('begin_time|than'=>time(),'status'=>'true')),
            2=>array('label'=>app::get('preparesell')->_('进行中'),'optional'=>false,'filter'=>array('end_time|bthan'=>time(),'begin_time|sthan'=>time(),'status'=>'true')),
            3=>array('label'=>app::get('preparesell')->_('过期'),'optional'=>false,'filter'=>array('end_time|sthan'=>time())),

        );

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;

                if($k==$_GET['view']){
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $mdl_preparesell->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=preparesell&ctl=admin_preparesell&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    function add_rule()
    {
        //$this->pagedata['return_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_info'));
        $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
          $this->_public_data();
          $this->pagedata['fund_name'] =
           array(
            array(
                'id' =>n,
                'name' => '选择款项'
            ),
            array(
                'id' =>y,
                'name' => '预付款'
            ),
             array(
                'id' =>z,
                'name' => '中期进度款'
            ),
              array(
                'id' =>w,
                'name' => '尾款'
            )
        );
           //预售活动号
           $code = '';
         for($i=0;$i<5;$i++){
             $code .= mt_rand(0,9);
           }
           $time='YP';
           $time.=strtotime(date("Y-m-d", time()));
           $time.=$code;
 
         $this->pagedata['active_time'] =$time;
         $this->pagedata['active_timeadd'] =1;
         $this->pagedata['status'] =2;
         $this->pagedata['statuss'] =1;
        $this->singlepage('admin/preparesell.html');
    }
    //页面异步请求的方法
    public function get_goods_spec () {
        $id = $_POST['id'];
        $arr = app::get('b2c')->model('products')->getList( '*',array('goods_id'=>$id) );
        $this->pagedata['specs'] = $arr;
        $this->display( 'admin/preparesell/spec/spec.html' );
    }
    //页面异步请求的方法
    public function get_goods_info () {
        $data = $_POST['data'];
        $arr = app::get('b2c')->model('goods')->dump_b2c( array('goods_id'=>$data[0]) );
        echo json_encode( array('name'=>$arr['name'],'bn'=>$arr['bn'],'store'=>$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief']) );
    }
    //短信设置
    function _public_data(){

        $this->pagedata['remind_way'] = array(
            'email'=>app::get('preparesell')->_('邮件提醒'),
            'sms'=>app::get('preparesell')->_('手机短信'),
        );
         $this->pagedata['remind_wayend'] = array(
            'email'=>app::get('preparesell')->_('邮件提醒'),
            'sms'=>app::get('preparesell')->_('手机短信'),
        );
    }
    //添加预售商品
    function save_rule()
    {
      
        $mdl_preparesell_goods = $this->app->model('preparesell_goods');
        $mdl_preparesell = $this->app->model('preparesell');
        $mdl_goods = app::get('b2c')->model('goods');
        $this->objMath = kernel::single('ectools_math');
        $this->begin();
        $fund=$_POST['fund'];
        $postdata = $this->_prepareRuleData($_POST);
      
        if(!empty($postdata['products'])){
                $sale_price=$postdata['products'];
                 $price=$sale_price[0]['promotion_price'];
                if($price){
                          foreach ($fund as $key => $value) {
                            $sum+=$value['payment'];
                            
                          }
                        $a=$this->objMath->formatNumber($sum,2);
                        $b=$this->objMath->formatNumber($price,2);
                 if( $a != 100.00){
                    $this->end(false,'支付金额比例之和应该等于100%' );
                 }

                }
        }
      
        //判断是否是无库存可销售的商品
        $is_prepare=$mdl_goods->getRow('nostore_sell',array('goods_id'=>$postdata['goods_id']));
        if($is_prepare['nostore_sell']!=1)
        {
             $this->end(false,'请选择无库存也可销售的商品作为预售商品！' );
        }
        $fund_data['goods_id']=$_POST['goods_id'];
        $fund_data['time_out']=$_POST['timeout'];
        $fund_data['time']=strtotime(date("Y-m-d H:i:s", time()));
        // $insert_id= $this->db->lastInsertId();
        $time=time();
        $preparesell_fund =$this->app->model('preparesell_fund');
        $db = kernel::database();
        $db->beginTransaction();
       // 编辑的货品进行更新，新增的货品进行新增
         if(!empty($_POST['update_id'])){
           $prepare_id=$_POST['update_id'];
            $is_have=$mdl_preparesell->getRow('prepare_id',array('prepare_id'=>$prepare_id));
            if($is_have){
                $postdata['prepare_id']=$prepare_id;
                // $del_rule=$mdl_preparesell->delete(array('prepare_id'=>$prepare_id),'delete');
            }
          }
         $remind_time=$postdata['remind_time'];

         if(empty($postdata['remind_time'])){
          $postdata['remind_time']=0;
         }
         $postdata['begin_time']=$postdata['active_begin_time'];
         $postdata['end_time']=$postdata['active_end_time'];

         // ee($postdata);
         $result = $mdl_preparesell->save($postdata);
          $lastid = $mdl_preparesell->db->lastinsertid();
          if($lastid){
              $addprepare_id=$mdl_preparesell_goods->getRow('prepare_id',array('id'=>$lastid));
               $fund_data['prepare_id']=$addprepare_id['prepare_id'];
          }
        if(empty($remind_time)){
        $remind_time=0;
        }
        if ($lastid) {
            foreach ($fund as $key => $value) {
           $fund_data['fund_name']=$value['fund_name'];
           $fund_data['payment']=$value['payment'];
           $fund_data['begin_time']=strtotime($value['begin_time']);
           $fund_data['end_time']=strtotime($value['end_time']);
            // $rule['remind_time_send'] = $value['end_time'] - (strtotime('+'.$value['remind_time'])-time());
           $time_begin_time=strtotime($value['begin_time']);
           $time_end_time=strtotime($value['end_time']);
           $fund_data['remind_time_send']=$time_begin_time- (strtotime('+'.$remind_time.' '.'hours')-time());
           $fund_data['remind_time_send_end']=$time_end_time- (strtotime('+'.$remind_time.' '.'hours')-time());
           $fund_data['status']=$value['status'];
            if (!empty($value['fund_id'])) {
                $fund_id=$value['fund_id'];
                if($fund_id){
                   $del_rule=$preparesell_fund->delete(array('fund_id'=>$fund_id),'delete');
                   
                }
            }  
            if (!empty($fund_data['fund_id'])) {
               unset($fund_data['fund_id']);
            }  
           $result= $preparesell_fund->save($fund_data);


            }

           if($result==false){
            $db->rollback();
           }
        }
        $this->end($result);
    }
    
    //数据处理
    function _prepareRuleData($params)
    {
        //预售时间处理
        //开始小时的处理
        $hour = $params['_DTIME_']['H'];
        $fund=$params['fund'];
        $ruledata = $params['ruledata'];
        if($fund){
              foreach ($fund as $key =>$value) {
                    // $data= $value['ruledata']?:0;
                    // //提醒规则
                    // $ruledata=$fund['ruledata']?:0;

                    $rule['begin_time'] = strtotime($value['begin_time']);
                    $rule['end_time'] = strtotime($value['end_time']);
                    if($rule['begin_time'] >= $rule['end_time']){
                        $this->end(false,'支付订金开始时间不能大于或等于结束时间！' );
                    }
                    if ($rule['end_time'] <= $rule['begin_time']) {
                        $this->end(false,'支付订金结束时间不能小于或等于开始时间！' );
                    }
                    if ($rule['begin_time'] <= strtotime(date("Y-m-d H:i:s", time()))) {
                        //$this->end(false,'支付订金开始时间不能小于或等于当前时间！' );
                    }
                    if ($rule['end_time'] <= strtotime(date("Y-m-d H:i:s", time()))) {
                        $this->end(false,'支付订金结束时间不能小于或等于当前时间！' );
                    }
                }  
        }

        
        $rule['remind_time'] = ($params['remind_time'] && $ruledata['remind'] =="true") ? $params['remind_time'] : 0;
        if(!is_numeric($rule['remind_time']))
        {
            $this->end(false,'提醒时间必须是数字！' );
        }
        if(empty($rule['remind_time'])&&$ruledata['remind'] =="true")
        {
            $this->end(false,'提醒时间不能为空！' );
        }
        $rule['remind_way'] = $params['remind_way'];
        $rule['active_begin_time'] = strtotime($params['begin_time']);
        $rule['active_end_time'] = strtotime($params['end_time']);
        //支付尾款开始和结束时间

        $rule['begin_time_final']=strtotime($params['begin_time']);
        $rule['end_time_final']=strtotime($params['end_time']);
        if($rule['active_begin_time'] >= $rule['active_end_time']){
            $this->end(false,'预售活动开始时间不能大于或等于结束时间！' );
        }
        if ($rule['active_end_time'] <= $rule['active_begin_time']) {
            $this->end(false,'预售活动结束时间不能小于或等于开始时间！' );
        }
        if ($rule['active_begin_time'] <= strtotime(date("Y-m-d H:i:s", time()))) {
            //$this->end(false,'预售活动开始时间不能小于或等于当前时间！' );
        }
        if ($rule['active_end_time'] <= strtotime(date("Y-m-d H:i:s", time()))) {
            $this->end(false,'预售活动结束时间不能小于或等于当前时间！' );
        }

        $rule['timeout'] = $params['timeout'] ? $params['timeout'] : 0;
        if(!is_numeric($rule['timeout']))
        {
            $this->end(false,'提醒时间必须是数字！' );
        }
        $rule['goods_id'] = $params['goods_id'];
        $rule['preparename'] = $params['preparename'];
        $rule['description'] = $params['description'];
        $rule['status'] = $params['status'];
        $rule['product_id'] = $params['to_prepare'];
        $rule['promotion_type'] = 'prepare';
        $rule['product'] = $params['product'];
        $rule['remind_time'] = $params['remind_time'];
        $rule['remind_way'][count($ruledata['remind_way'])] = $ruledata['remind_way'];
        $rule['remind_wayend'][count($ruledata['remind_wayend'])] = $ruledata['remind_wayend'];
        //二开添加
        $rule['stock_state'] = $params['stock_state'];
        $rule['stock_num'] = $params['stock_num'] ? $params['stock_num'] : 0;
        $rule['custmer_state'] = $params['custmer_state'];
        $rule['custmer_upperlimit'] = $params['custmer_upperlimit']? $params['custmer_upperlimit'] : 0;
        $rule['custmer_lowerlimit'] = $params['custmer_lowerlimit']? $params['custmer_lowerlimit'] : 0;
        $rule['active_num'] = $params['active_num'];
        $rule['products'] = $this->getProduct($rule);
        return $rule;
    }
    /*
    *获取货品的信息
    */
    function getProduct($rule)
    {
        $product = app::get('b2c')->model('products');
        $product_detail = $product->getList('product_id,price',array('product_id|in'=>$rule['product_id']));
        foreach ($product_detail as $key => $value)
        {
            if($rule['product'][$value['product_id']]['prepare_price'] > $value['price'])
            {
                $this->end(false,'预售价格不能大于销售价！' );
            }
            if($rule['product'][$value['product_id']]['prepare_price']==null)
            {
                $this->end(false,'预售价格不能为空！' );
            }
            $product_detail[$key]['preparesell_price'] = $rule['product'][$value['product_id']]['prepare_price'] > $value['price'] ? $value['price'] : $rule['product'][$value['product_id']]['prepare_price'];
            $product_detail[$key]['promotion_price'] = $value['price'];
            $product_detail[$key]['sales_price'] = $rule['product'][$value['product_id']]['sales_price'];//销售标准价
            $product_detail[$key]['preparename'] = $rule['preparename'];
            $product_detail[$key]['description'] = $rule['description'];
            $product_detail[$key]['status'] = $rule['status'];
            $product_detail[$key]['begin_time'] = $rule['active_begin_time'];
            $product_detail[$key]['end_time'] = $rule['active_end_time'];
            $product_detail[$key]['begin_time_final'] = $rule['begin_time_final'];
            $product_detail[$key]['end_time_final'] = $rule['end_time_final'];
            $product_detail[$key]['remind_way'] =  $rule['remind_way'];
            $product_detail[$key]['remind_time'] = $rule['remind_time'];
            // $product_detail[$key]['remind_time_send'] = $rule['remind_time_send'];
            $product_detail[$key]['timeout'] = $rule['timeout'];
            $product_detail[$key]['promotion_type'] = $rule['promotion_type'];

            $product_detail[$key]['initial_num'] = $initial_num = $rule['product'][$value['product_id']]['initial_num'];

            //二开添加
            $product_detail[$key]['prepare_num'] = $initial_num;
            $product_detail[$key]['stock_num'] =  $rule['stock_num'];
            $product_detail[$key]['stock_state'] =  $rule['stock_state'];
            $product_detail[$key]['custmer_state'] =  $rule['custmer_state'];
            $product_detail[$key]['custmer_upperlimit'] =  $rule['custmer_upperlimit'];
            $product_detail[$key]['custmer_lowerlimit'] =  $rule['custmer_lowerlimit'];
            // $product_detail[$key]['fund_name'] =  $rule['fund_name'];
            // $product_detail[$key]['payment'] =  $rule['payment'];
            $product_detail[$key]['active_num'] =  $rule['active_num'];
            $product_detail[$key]['remind_wayend'] =  $rule['remind_wayend'];
            if(empty($product_detail[$key]['initial_num']))
            {
                $this->end(false,'库存不能为空！' );
            }
        }
        return $product_detail;
    }

    /*
    *修改预售规则
    */
    function edit_rule($id,$view){
        
        
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $mdl_product = app::get('b2c')->model('products');
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $preparesell=$mdl_preparesell->getRow('*',array('prepare_id'=>$id));
        if(!$preparesell['remind_time']){
            $preparesell['remind'] = "false";
            $preparesell['remind_time'] = null;
        }else{
            $preparesell['remind'] = "true";
        }
       if($preparesell){
        
                 $preparesell['begin_time']=date('Y-m-d  H:i:s',$preparesell['begin_time']);
                 $preparesell['end_time']=date('Y-m-d  H:i:s',$preparesell['end_time']);
                
                  $preparesell['start_way']=$preparesell['remind_way']['1'][0];
                  $preparesell['end_way']= $preparesell['remind_wayend']['1'][0];

        }
       
        $this->pagedata['ruleInfo'] = $preparesell;
        $this->pagedata['id'] = $id;
        $this->pagedata['is_edit'] =2;
        $this->pagedata['view'] =$view;
        $this->pagedata['fund_name'] =
           array(
            array(
                'id' =>n,
                'name' => '选择款项'
            ),
            array(
                'id' =>y,
                'name' => '预付款'
            ),
             array(
                'id' =>z,
                'name' => '中期进度款'
            ),
              array(
                'id' =>w,
                'name' => '尾款'
            )
        );

        $this->pagedata['remind_way'] = array(
            'email'=>app::get('preparesell')->_('邮件提醒'),
            'sms'=>app::get('preparesell')->_('手机短信'),
        );
         $this->pagedata['remind_wayend'] = array(
            'email'=>app::get('preparesell')->_('邮件提醒'),
            'sms'=>app::get('preparesell')->_('手机短信'),
        );
          $mdl_preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $fund_accounts=$mdl_preparesell_fund->getList('*',array('prepare_id'=>$id));
          if($fund_accounts){
            foreach ($fund_accounts as $key => $value) {
                 $fund_accounts[$key]['begin_time']=date('Y-m-d  H:i:s',$value['begin_time']);
                 $fund_accounts[$key]['end_time']=date('Y-m-d  H:i:s',$value['end_time']);
                # code...
            }
        }

        $this->pagedata['fundInfo'] = $fund_accounts;
        $product=$mdl_preparesell_goods->getList('product_id,preparesell_price,sales_price,initial_num,status',array('prepare_id'=>$id));
        $product_id=$mdl_product->getList('product_id',array('goods_id'=>$preparesell['goods_id']));
        //获取货品id
        foreach ($product_id as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }

        //为了下面赋价格的直给页面
        foreach ($product as $key => $value) {
            $price[$value['product_id']]['product_id']=$value['product_id'];
            $price[$value['product_id']]['preparesell_price']=$value['preparesell_price'];
            $price[$value['product_id']]['initial_num']=$value['initial_num'];
             $price[$value['product_id']]['prepare_num']=$value['prepare_num'];
            $price[$value['product_id']]['status']=$value['status'];
            $price[$value['product_id']]['sales_price']=$value['sales_price'];
        }
        $arr = $mdl_product->getList('*',array('product_id|in'=>$product_id));
        foreach ($arr as $key => $value) {
            $arr[$key]['prepare_price']=$price[$value['product_id']]['preparesell_price'];
            $arr[$key]['initial_num']=$price[$value['product_id']]['initial_num'];
            $arr[$key]['status']=$price[$value['product_id']]['status'];
            $arr[$key]['sales_price']=$price[$value['product_id']]['sales_price'];

        }
        // ee($arr);
        $this->pagedata['specs'] = $arr;

        $this->pagedata['nowtime'] = time();
        $this->_public_data();
        $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['prepareNums'] = app::get('b2c')->model('goods')->count($this->pagedata['filter']);

        $this->pagedata['goodsName'] = $arr[0]['name'];
        $this->pagedata['return_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_info'));
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
        if($preparesell['begin_time'] <= time() && time() <= $preparesell['end_time_final']  && $preparesell['status'] == 'true' )
        {
            $this->singlepage('admin/preparesell.html');
        }else{
            $this->singlepage('admin/preparesell.html');
            
        }
    }

    //删除规则
    public function del_rule($id)
    {
       
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
      
        $prepare=$mdl_preparesell->getRow('begin_time,end_time_final,goods_id,status',array('prepare_id'=>$id));
        $product=$mdl_preparesell_goods->getList('product_id',array('prepare_id'=>$id));
        foreach ($product as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }


        $this->begin();

        //删除条件判断
        if($prepare['begin_time'] >= time() || $prepare['status']=='false' || $prepare['end_time_final'] <= time())
        {
            $del_pre=$mdl_preparesell->delete(array('prepare_id'=>$id),'delete');
            $del_rule=$mdl_preparesell_goods->delete(array('prepare_id'=>$id),'delete');
            $del=array($del_pre,$del_rule,$del_fund);
            $this->end($del,true,'删除成功');
        }else{
            $this->end(false,'活动未结束，不可删除');
        }

    }


    //用于挂件ajax请求获取商品地址
    public function ajax_get_goods_url()
    {
        $obj_preparesell = app::get('preparesell')->model('preparesell');
        $obj_products = app::get('b2c')->model('products');
        $goods_ids = $obj_preparesell->getList('goods_id', $filter);
        foreach($goods_ids as $k=>$v)
        {
            $fmt_goods_ids[$v['goods_id']] = $v['goods_id'];
        }
        $products_filter = array(
                'goods_id|in' => $fmt_goods_ids,
                'is_default' => 'true',
                'disabled' => 'false',
            );
        $products = $obj_products->getList('product_id,name', $products_filter);
        $url_array = array(
            'app'=>'b2c',
            'ctl'=>'site_product',
            'full'=>1,
            'act'=>'index',
        );
        foreach($products as $key=>$product)
        {
            $url_array['arg']=$product['product_id'];
            $url = app::get('site')->router()->gen_url($url_array);
            $products[$key]['url'] = $url;
        }
        $json_products = json_encode($products);
        echo $json_products;
        return;
    }


    /*
    *添加款项
    */
    function add_accounts(){
        
          $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
          $this->_public_data();
         $this->pagedata['fund_name'] =
           array(
            array(
                'id' =>n,
                'name' => '选择款项'
            ),
            array(
                'id' =>y,
                'name' => '预付款'
            ),
             array(
                'id' =>z,
                'name' => '中期进度款'
            ),
              array(
                'id' =>w,
                'name' => '尾款'
            )
        );

            $this->pagedata['num'] = $_GET['num'];
           if($_GET['num']==null){
            $this->pagedata['num'] =0;
           }
        //款项记录数
           $id=$_GET['id'];
           if($id){
      
        $this->pagedata['is_foreach'] =1;
           }
     
        $this->display('admin/accounts.html');
    }


     /*
    *添加款项
    */
    function edit_accounts(){
        
          $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
          $this->_public_data();
         $this->pagedata['fund_name'] =
           array(
            array(
                'id' =>n,
                'name' => '选择款项'
            ),
            array(
                'id' =>y,
                'name' => '预付款'
            ),
             array(
                'id' =>z,
                'name' => '中期进度款'
            ),
              array(
                'id' =>w,
                'name' => '尾款'
            )
        );

            $this->pagedata['num'] = $_GET['num'];
           if($_GET['num']==null){
            $this->pagedata['num'] =0;
           }
       // 款项记录数
        $mdl_preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $fund_accounts=$mdl_preparesell_fund->getList('*',array('prepare_id'=>$_GET['id']));
        if($fund_accounts){
            foreach ($fund_accounts as $key => $value) {
                 $fund_accounts[$key]['begin_time']=date('Y-m-d H',$value['begin_time']);
                 $fund_accounts[$key]['end_time']=date('Y-m-d H',$value['end_time']);
                # code...
            }
        }
        $this->pagedata['fundInfo'] = $fund_accounts;
        $this->display('admin/accountsedit.html');
    }

   
      //短信提醒
    public function preparesell_fund_send(){

        echo "------款项短信发送------";
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $mdl_preparesell_fund = app::get('preparesell')->model('preparesell_fund');
        $mdl_preparesell_order_fund = app::get('preparesell')->model('order_fund');
        $mdl_preparesell_order= app::get('b2c')->model('orders');
        $mdl_member_addrs= app::get('b2c')->model('member_addrs');
        $nowtime= strtotime(date("Y-m-d H", time()).':00:00');
        $_POST['status']=1;
        //会员手机号查询
        $sqlphone='select  GROUP_CONCAT(DISTINCT mobile) as mobiles from sdb_b2c_member_addrs ';
        $phone= $mdl_member_addrs->db->select($sqlphone);
           if($phone){
                $datas = $phone[0]['mobiles'];
            }
         $sql = "select * from sdb_b2c_orders as o,
                 sdb_preparesell_prepare_order  as po,
                 sdb_preparesell_order_fund  as pf
                 where po.order_id=o.order_id 
                 and po.order_id=pf.order_id 
                 and pf.status=true 
                 and pf.is_send_start=0 
                 and pf.remind_time_send=".$nowtime ;
        $result = $mdl_preparesell_order->db->select($sql);
        if($result){
           foreach ($result as $key => $value) {
               if($value['fund_name']=='y'){
                   //执行发送短信
                      $_POST['content']='预售预付款开始支付' . $key;
                      $_POST['mobile_number']=json_encode($datas);
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                    if($attr==1){
                        //填写发送记录
                      $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='y' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                    }
               }elseif ($value['fund_name']=='z') {
                    $sqlzhifu="select * from  sdb_ectools_payments as ep,
                              sdb_ectools_order_bills as eo
                              where  eo.bill_type='payments' 
                              and eo.bill_id=ep.payment_id
                              and status='succ' 
                              and eo.rel_id=
                              ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售中期进度款待支付';
                  if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                  if($attr==1){
                          $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='z' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                      }
                     
                   }
               }elseif ($value['fund_name']=='w') {
                      $sqlzhifu="select * from  sdb_ectools_payments as ep,
                                sdb_ectools_order_bills as eo
                                where  eo.bill_type='payments' 
                                and eo.bill_id=ep.payment_id
                                and status='succ' 
                                and eo.rel_id=
                                ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售尾款待支付';
                      if(!empty($value['ship_mobile'])){
                           $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                            
                      }
                      if($attr==1){
                         $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='w' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                         
                      }
                     
                   }
                      
               }
           }
           
        }
        
            //结束提醒
           $sqlend = "select * from sdb_b2c_orders as o,
                 sdb_preparesell_prepare_order  as po,
                 sdb_preparesell_order_fund  as pf
                 where po.order_id=o.order_id 
                 and po.order_id=pf.order_id 
                 and pf.status=true 
                 and pf.is_send_start=0 
                 and pf.remind_time_send_end=".$nowtime ;
        $result = $mdl_preparesell_order->db->select($sqlend);
        if($result){
           foreach ($result as $key => $value) {
               if($value['fund_name']=='y'){
                   //执行发送短信
                      $_POST['content']='预售预付款开始支付';
                      $_POST['mobile_number']=$value['ship_mobile'];
                    
                      if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                    if($attr==1){
                        //填写发送记录
                      $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='y' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                    }
               }elseif ($value['fund_name']=='z') {
                    $sqlzhifu="select * from  sdb_ectools_payments as ep,
                              sdb_ectools_order_bills as eo
                              where  eo.bill_type='payments' 
                              and eo.bill_id=ep.payment_id
                              and status='succ' 
                              and eo.rel_id=
                              ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信

                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售中期进度款待支付';
                    
                      if(!empty($value['ship_mobile'])){
                      $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                      if($attr==1){
                          $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='z' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                      }
                     
                   }
               }elseif ($value['fund_name']=='w') {
                      $sqlzhifu="select * from  sdb_ectools_payments as ep,
                                sdb_ectools_order_bills as eo
                                where  eo.bill_type='payments' 
                                and eo.bill_id=ep.payment_id
                                and status='succ' 
                                and eo.rel_id=
                                ".$value['order_id'];
                 $result = $mdl_preparesell_order->db->select($sqlzhifu);
                   if ($result) {
                        //执行发送短信
                      $_POST['mobile_number']=$value['ship_mobile'];
                      $_POST['content']='预售尾款待支付';
                      if(!empty($value['ship_mobile'])){
                           $attr = kernel::single('b2c_ctl_admin_member')->prepare_sms_queue($_POST);
                      }
                      if($attr==1){
                         $order_fund = $mdl_preparesell_order_fund->db->exec("UPDATE sdb_preparesell_order_fund set is_send_start='1' where prepare_id =".$value['prepare_id']." and fund_name='w' and fund_id=".$value['fund_id']." and order_id=".$value['order_id']);
                         
                      }
                     
                   }
                      
               }
           }
            
        }
        
    }



}
