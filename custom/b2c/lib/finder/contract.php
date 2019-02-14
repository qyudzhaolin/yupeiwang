<?php
/**
 *@说明：contract finder
 * 
 */
class b2c_finder_contract{
    var $column_view = '查看';
    var $column_editbutton;
    public function __construct($app) {
        $this->app = $app;
        $this->column_editbutton = app::get('b2c')->_('操作');
    }

    function detail_view($contract_id){
        $render = $this->app->render();
        $mdl_contract = app::get('b2c')->model('contract');//合约主表
        $data = $mdl_contract->getContractData($contract_id);
        if (empty($data)) {
            exit('无此合约！');
        }
        $member_id = $data['member_id'];
        //合约操作日志
        $dologs=$mdl_contract->getDologs($contract_id);

        //支付流水
        $contractpayment=$mdl_contract->getPayment($contract_id,$member_id);

        //出库流水
         $goodsout=$mdl_contract->getGoodsout($contract_id,$member_id);
         if($goodsout){
            foreach ($goodsout as $key => $value) {
                $goodsout[$key]['price']=round($value['price'],2);
            }
         }

        $render->pagedata['dologs'] = $dologs;    
        $render->pagedata['goodsout'] = $goodsout;    
        $render->pagedata['contractpayment'] = $contractpayment;
        $render->pagedata['data'] = $data;
        $render->pagedata['params'] = $data['params'];//结算参数
        $render->pagedata['step'] = $data['step'];//结算阶段
        $render->pagedata['products'] = $data['products'];
        $render->pagedata['products_ids']= $data['products_ids'];//商品列表数据
        return $render->fetch('admin/contract/finder/detail.html');
    }

    var $column_edit = '编辑';
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $contract_id = $row['contract_id'];
        return '<a class="editContract" contract_id="' . $contract_id . '" href="index.php?app=b2c&ctl=admin_contract&act=edit&_finder[finder_id]='.$finder_id.'&p[0]='.$contract_id.'&finder_id='.$finder_id.'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }

    public function column_editbutton($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $contract_id = $row['contract_id'];
        $render = $this->app->render();
        $target = "dialog::{title:'操作', width:800, height:420}";
        $arr = array(
            'app'         =>$_GET['app'],
            'ctl'         =>$_GET['ctl'],
            'act'         =>$_GET['act'],
            'finder_id'   =>$_GET['_finder']['finder_id'],
            'action'      =>'detail',
            'finder_name' =>$_GET['_finder']['finder_id'],
        );

        $arr_link = array(
            'finder'=>array(
                'detail_handle'=>array(
                    'href'   =>'javascript:void(0);',
                    'submit' =>'index.php?app=b2c&ctl=admin_contract&act=setpage&_finder[finder_id]='.$finder_id.'&p[0]='.$contract_id.'&finder_id='.$finder_id,
                    'label'  =>app::get('b2c')->_('操作'),
                    'target' =>$target,
                ),
                'detail_pay'=>array(
                    'href'   =>'javascript:void(0);',
                    'submit' =>'index.php?app=b2c&ctl=admin_order&act=gopay_contract&_finder[finder_id]='.$finder_id.'&p[0]='.$contract_id.'&finder_id='.$finder_id,
                    'label'  =>app::get('b2c')->_('支付'),
                    'target' =>$target,
                ),
            ),
        );

        $render->pagedata['arr_link'] = $arr_link;
        $render->pagedata['handle_title'] = app::get('b2c')->_('操作');
        $render->pagedata['is_active'] = 'true';
        return $render->fetch('admin/actions.html');
    }

    // var $column_toset = '操作';
    // function column_toset($row){
    //     $finder_id = $_GET['_finder']['finder_id'];
    //     $contract_id = $row['contract_id'];
    //     $target = "dialog::{title:'操作', width:800, height:420}";
    //     return '<a href="index.php?app=b2c&ctl=admin_contract&act=setpage&_finder[finder_id]='.$finder_id.'&p[0]='.$contract_id.'&finder_id='.$finder_id.'" target="'.$target.'">操作</a>';
    // }
}
