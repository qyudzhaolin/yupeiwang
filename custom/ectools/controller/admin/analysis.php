<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_admin_analysis extends desktop_controller
{

    public function chart_view() 
    {
         $show = $_GET['show'];

         //todo 这里需要根据不同的需求读取数据
         if($_GET['callback']){
             $data = kernel::single($_GET['callback'])->fetch_graph_data($_GET);
         }else{
             $data = kernel::single('ectools_analysis_base')->fetch_graph_data($_GET);
         }
         
         $this->pagedata['categories']='["' . @join('","', $data['categories']) . '"]';
        
         foreach($data['data'] AS $key=>$val){
             $tmp[] = '{name:"'.addslashes($key).'",data:['.@join(',', $val).']}';
         }
         $this->pagedata['data'] = '['.@join(',', $tmp).']';

         switch($show){
            case 'line':
                $this->display("analysis/chart_type_line.html");                
                break;
            case 'column':
                $this->display("analysis/chart_type_column.html");                
                break;
            default :
                $this->display("analysis/chart_type_default.html");                
                break;
        }   
    }//End Function


//////////////////////////////////二次开发///////////////////////////////////////////////
    //概览
    public function chart_view_scale() 
    {
        $show = $_GET['show'];
        $target = $_GET['target'];//统计项
        $analysisModel = $this->app->model('analysis');
        $data = $analysisModel->get_scale_data($_GET);
        //统计项检测是否合法
        if (!in_array($target, array_keys($analysisModel->scaleConfig))) {
            exit("统计项参数[target={$_GET['target']}]不合法");
        }

        // print_r($_GET,0);//TEST

        $this->pagedata['categories']=$data['timeRanges'];
        //订单统计
        $this->pagedata['report'] = $_GET['report'] ? $_GET['report'] : 'day';
        $this->pagedata['target'] = $target;

        $this->pagedata['datas'] = $data[$target];//页面数据根据target参数取值
        $this->display('analysis/chart_scale.html');                

    }//End Function



    //账款统计
    public function chart_view_accounts() 
    {
        $target = $_GET['target'];//统计项
        $analysisModel = $this->app->model('analysis');
        $data = $analysisModel->get_accounts_data($_GET);
        // print_r($_GET,0);//TEST

        //统计项检测是否合法
        if (!in_array($target, array_keys($analysisModel->accConfig))) {
            exit("统计项参数[target={$_GET['target']}]不合法");
        }

        $this->pagedata['categories']=$data['timeRanges'];
        //订单统计
        $this->pagedata['report'] = $_GET['report'] ? $_GET['report'] : 'day';
        $this->pagedata['target'] = $target;

        $this->pagedata['datas'] = $data[$target];//页面数据根据target参数取值
        $this->display('analysis/chart_accounts.html');                

    }//End Function


    //商品统计
    public function chart_view_goods() 
    {
        $target = $_GET['target'];//统计项
        $analysisModel = $this->app->model('analysis');
        $data = $analysisModel->get_goods_data($_GET);
        // print_r($_GET,0);//TEST

        //统计项检测是否合法
        if (!in_array($target, array_keys($analysisModel->goodsConfig))) {
            exit("统计项参数[target={$_GET['target']}]不合法");
        }

        $this->pagedata['xdatas']=$data['xdatas'];
        //订单统计
        $this->pagedata['report'] = $_GET['report'] ? $_GET['report'] : 'day';
        $this->pagedata['target'] = $target;
        $this->pagedata['chartNum'] = 1;

        if (in_array($target, ['onlineGoodsNum','threeCateSalesTop'])) {
            $this->pagedata['chartNum'] = 2;
        }

        $this->pagedata['datas'] = $data[$target];//页面数据根据target参数取值
        $this->display('analysis/chart_goods.html');                

    }//End Function

    //会员统计
    public function chart_view_member() 
    {
        $show = $_GET['show'];
        $target = $_GET['target'];//统计项
        $analysisModel = $this->app->model('analysis');
        $data = $analysisModel->get_member_data($_GET);
        //统计项检测是否合法
        if (!in_array($target, array_keys($analysisModel->memberConfig))) {
            exit("统计项参数[target={$_GET['target']}]不合法");
        }

        // print_r($_GET,0);//TEST

        $this->pagedata['xdatas']=$data['xdatas'];
        //订单统计
        $this->pagedata['report'] = $_GET['report'] ? $_GET['report'] : 'day';
        $this->pagedata['target'] = $target;

        $this->pagedata['datas'] = $data[$target];//页面数据根据target参数取值
        $this->display('analysis/chart_member.html');                

    }//End Function
    
   //供应商统计
    public function chart_view_supplier() 
    {
        // return ['p'=> $_GET['P']];
        $show = $_GET['show'];
        $target = $_GET['target'];//统计项
        $time_to= $_GET['time_to'];
        $time_from=$_GET['time_from'];
        $analysisModel = $this->app->model('analysis');
        $data = $analysisModel->get_supplier_data($_GET);
        //统计项检测是否合法
        if (!in_array($target, array_keys($analysisModel->supplierConfig))) {
            exit("统计项参数[target={$_GET['target']}]不合法");
        }
           if($target=='areaSupplierGoodsNumcate'){
            $sql="select cat_id,cat_name from sdb_b2c_goods_cat where parent_id=0 GROUP BY cat_name";
               $obj_goodscat = app::get('b2c')->model('goods_cat');
             $resultsname = $obj_goodscat->db->select($sql);
             $this->pagedata['resultsname']=$resultsname;
             $this->pagedata['type']=1;
           }
          
        // print_r($_GET,0);//TEST
        $this->pagedata['xdatas']=$data['xdatas'];
        //订单统计
        $this->pagedata['report'] = $_GET['report'] ? $_GET['report'] : 'day';
        $this->pagedata['target'] = $target;
        $this->pagedata['time_from'] =$time_from;
        $this->pagedata['time_to'] = $time_to;
        $this->pagedata['datas'] = $data[$target];//页面数据根据target参数取值
        if($cat_name = $_GET['cat_name']){
            exit(json_encode($data));
        }
        $this->display('supplier/chart_supplier.html');

    }//End Function
    //供应商统计
    


}//End Class