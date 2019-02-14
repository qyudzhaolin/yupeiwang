<?php

class preparesell_ctl_site_preparesell extends preparesell_frontpage{

    function __construct($app){
        parent::__construct($app);
        $this->app = $app;
        $this->mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');//没有model就类似TP M()
        $this->mdl_product = app::get('b2c')->model('products');
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->userObject = kernel::single('b2c_user_object');
        $this->preparesell_pro = kernel::single('preparesell_preparesell_products');
    }
    function index($type_id=null,$page=1){
        $type_id = $this->_request->get_param(0);
        $GLOBALS['runtime']['path'] = $this->runtime_path($type_id);
        $this->pagedata['type_id']=$type_id;
        $page = $page ? $page : 1;
        $params = $this->filter_decode(['orderby'=>''],$type_id);
        $filter = $params['filter'];
        $orderby = $params['orderby'];
        $beabout = $this->get_product($filter,$page,$orderby);
        $this->pagedata['filter']=$filter;
        $this->pagedata['orderby_sql'] = $orderby;

		$this->pagedata['imageDefault'] = app::get('image')->getConf('image.set');
		
       
        //预售信息处理
        $nowtime= strtotime(date("Y-m-d H:i:s", time()));
        foreach ($beabout as $key => $value) {
            $products=$value['products'];
            //预售已预订量
            if($products){
                 $buy_num=$products['prepare_num']- $products['initial_num'];
                 if($buy_num>0){
                    $beabout[$key]['buy_num']= $buy_num;
                 }else{
                    $beabout[$key]['buy_num']= 0;
                 }
            }
            //状态必须是开启且当前时间小于等于预售结束时间
            if($value['status']=='true'&&$nowtime<=$value['end_time']){
               
                if( $nowtime>=$value['begin_time']&&$nowtime<=$value['end_time']){
                  //进行中
                 //预售活动倒计时
                 $beabout[$key]['time']=$value['fund_end_time']-$nowtime;
                 $under_way[]=$beabout[$key];
                 $this->pagedata['under_way'] = $under_way;
                }elseif($nowtime<$value['begin_time']){
                   //未开始
                  //预售开始时间
                  $beabout[$key]['starttime']=$value['fund_begin_time']-$nowtime;;
                  $forthcoming[]=$beabout[$key];
                  $this->pagedata['forthcoming'] = $forthcoming;
                }
            }
        }
          // ee($under_way);
       // ee($forthcoming);
		#获取会员的登录状态
		$this->pagedata['login_id'] = ($this->userObject->get_member_id()) ? ($this->userObject->get_member_id()) : "0";
        // ee($this->pagedata['under_way']);
		$this->page('site/gallery/index.html');
	}

    //顶部导航路径
    function runtime_path($type_id,$product_id=null){
        $url = "#";
        $path = array(
            array(
                'type'=>"goodscat",
                'title'=>"首页",
                'link'=>kernel::base_url(1),

            ),
            array(
                'type'=>"preparesell",
                'title'=>'预售',
                'link'=>$url,
            ),
        );
        return $path;
    }

    function get_product($filter,$page=1,$orderby=" promotion_price asc"){
        $filter['status']="true";
        //分页
        $beabout_total = $this->count($filter);
        $this->pagedata['total'] = $beabout_total;
        $pageLimit = app::get('b2c')->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $this->pagedata['page'] = $page;
        $this->pagedata['pageLimit'] = $pageLimit;
        $pagetotal= $this->pagedata['total'] ? ceil($this->pagedata['total']/$pageLimit) : 1;
        $max_pagetotal = $this->app->getConf('gallery.display.pagenum');
        $max_pagetotal = $max_pagetotal ? $max_pagetotal : 100;
        $this->pagedata['pagetotal'] = $pagetotal > $max_pagetotal ? $max_pagetotal : $pagetotal;
        
        //获取参加团购的货品
        app::get('b2c')->model('shiparea')->makeProductfilterByShiparea($filter);//配送区域过滤#TODO#
        // ee($filter);
        $filter['filter_sql']="1 GROUP BY prepare_id ";
        $preparesell_goods = $this->mdl_preparesell_goods->getList("*",$filter,$pageLimit*($page-1),$pageLimit,$orderby,$total=false);
        foreach($preparesell_goods as $value){
            $result[] = $this->preparesell_pro->getParams($value);
        }
        // ee($result);

        return $result;
    }



    function count($filter){
        $total = $this->mdl_preparesell_goods->count($filter);
        return $total;
    }

    function _getProduct($filter){
        $products="";
        if($filter){
            $products = $this->mdl_product->getRow("*",array('product_id'=>$filter));
        }
        return $products;
    }


    function ajax_get_goods(){
        $post = $this->filter_decode($_POST);
        $page = $post['page'] ? $post['page'] : 1;
        $filter = $post['filter'];
        $orderby = $post['orderby'];
        $goodsData = $this->get_product($filter,$page,$orderby);
        if($goodsData){
            $this->pagedata['goodsData'] = $goodsData;
            ee($goodsData);
            echo $this->fetch('site/gallery/type/grid.html');
        }
    }

    function filter_decode($params=null,$type_id){
        if(!$params){
            $cookie_filter = $_COOKIE['S']['SPECIAL']['FILTER'];
            if($cookie_filter){
                $tmp_params = explode('&',$cookie_filter);
                foreach($tmp_params as $k=>$v){
                    $arrfilter = explode('=',$v);
                    $f_k = str_replace('[]','',$arrfilter[0]);
                    if($f_k == 'type_id' || $f_k == 'orderby'|| $f_k == 'page'){
                        $params[$f_k] = $arrfilter[1];
                    }else{
                        $params[$f_k][] = $arrfilter[1];
                    }
                }
            }
            if($params['type_id'] != $type_id){
                unset($params);
                $this->set_cookie('S[SPECIAL][FILTER]','nofilter');
            }
        }//end if

        if(!empty($params['orderby'])){
            $post['orderby'] = $params['orderby'];
            unset($params['orderby']);
        }else{
            $post['orderby'] = 'promotion_price asc';
            if (isset($params['orderby'])) {
                unset($params['orderby']);
            }

        }

        if($params['page']){
            $post['page'] = $params['page'];
        }

        $post['filter'] = $params;

        if($type_id){
            $post['filter']['type_id'] = $type_id;
        }
        return $post;
    }

    function ajax_remind_save(){
        $remind_mdl = app::get('preparesell')->model('preparesell_remind');
        $remind = $_POST;
        $way = $this->getWay($remind['goal'],$msg);
        if(!$way){
            $this->splash('error',"",$msg,true);
        }
        $product = $this->mdl_product->getRow('name',array('product_id'=>$remind['product_id']));
        $remind['remind_way'] = $way;
        $remind['savetime'] = time();
        $remind['goodsname'] = $product['name'];
        $count = $remind_mdl->count(array('product_id'=>$remind['product_id'],'goal'=>$remind['goal'],'type_id'=>$remind['type_id']));
        if($count){
            $this->splash('error',"",'您已经订阅过',true);
        }
        $result = $remind_mdl->save($remind);
        if($result){
            $url = $this->gen_url(array('app'=>'preparesell', 'ctl'=>'site_team','act'=>'index','arg0'=>$remind['product_id'],'arg1'=>$remind['type_id']));
            $this->splash('success','','订阅成功',true);
        }else{
            $this->splash('error','',"订阅失败",true);
        }
    }




	/*
	 * 获取提醒方式(邮箱，手机号码、站内信)
	 *
	 * @params $login_account 登录账号
	 * @return $account_type
	 */
	public function getWay($remind_way,&$msg){

		$way = "msgbox";
		if($remind_way && strpos($remind_way,'@')){
			if(!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i',trim($remind_way)) ){
				$msg = $this->app->_('邮件格式不正确');
				return false;
			}
			$way = 'email';
		}elseif(preg_match("/^1[34578]{1}[0-9]{9}$/",$remind_way)){
			$way = 'sms';
		}elseif($remind_way){
			$msg = $this->app->_('请输入正确邮箱或手机号码');
			return false;
		}
		return $way;
	}



    function getNowTime(){
        $timenow['timeNow'] = time();
        $timenow = json_encode($timenow);
        echo $timenow;
    }


}
