<?php
/**
 * Created by PhpStorm.
 * User: songjiang
 * Date: 2018/1/18
 * Time: 13:37
 */
class reconciliation_ctl_reconciliation extends desktop_controller{
    function index(){
		$time=array('time'=>date("Y-m-d",strtotime("-1 day")));
        $this->pagedata=$time;
        return $this->page('submit.html');
    }

    function getinfo(){

        $this->begin();
        if (!$_POST['Date'])
        {
           $this->end(false,'请输入日期！','index.php?app=reconciliation&ctl=reconciliation&act=index');
        }
        $param['Date']=$_POST['Date'];

        $objcpcn=kernel::single('ectools_payment_plugin_cpcn');

        $data=$objcpcn->reconciliation($param);



        //整合数据

        if ($data['status']=='succ'){

            foreach ($data['Tx'] as $key=>$v){
                if ($v['BankNotificationTime']){
                    $v['BankNotificationTime']=$this->changetime($v['BankNotificationTime']);
                }else{
                    $v['BankNotificationTime']='暂无时间';
                }
                $v['InstitutionAmount'] = $v['InstitutionAmount']/100;//原单位为：分；转为：元；
                $v['TxAmount'] = $v['TxAmount']/100;
                $v['PaymentAmount'] = $v['PaymentAmount']/100;
                $data['Tx'][$key]=$v;
            }
            $this->pagedata=$data;
            return $this->page('list.html');
        }else{
            $this->end(false,$data['msg']);
        }


    }

    function changetime($time){
        $Y=substr($time,0,4);
        $m=substr($time,4,2);
        $d=substr($time,6,2);
        $H=substr($time,8,2);
        $i=substr($time,10,2);
        $s=substr($time,12,2);

        return $Y.'-'.$m.'-'.$d.' '.$H.':'.$i.':'.$s;

    }






}