//合约
fee={};

//随机数
fee.randomStr = function(len){
    len = len || 1;
    var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

/**
 *说明：table>tr动画效果
 *用例：用在table列表增改时候tr行闪烁提示用户
 *@obj.row:需指定当前哪个行需要动画
 *@obj.ok:动画完成后动作
 */
fee.trAnimate = function(obj){
	if(!obj) obj = {};
	if(!obj.ok) obj.ok = function(){};
	if(!obj.row) return false;
	//动画效果
	obj.row.queue(function(next){
		obj.row.addClass('background_yellow');
		next();
	}).fadeOut().fadeIn().queue(function(next){
		obj.row.removeClass('background_yellow');
		next();
	}).queue(function(next){
		if(obj.ok) obj.ok();
		next();
	});

}

//--辅助--检测只能输入数字或者小数
fee.checkNum = function(e) {
    var re = /^\d+(?=\.{0,1}\d+$|$)/;
    var tipsObj = j(e).parent("td").find(".tips");
    if (e.value != "") {
        if (!re.test(e.value)) {
            tipsObj.html("请输入正确的数字");
            tipsObj.addClass('error');
            e.focus();
        }else{
            tipsObj.html("");
            tipsObj.removeClass('error');
        }
    }else{
        tipsObj.html("");
        tipsObj.removeClass('error');
    }
}


//--辅助--获取设置好的结算参数值
fee.getSetedParams = function(dataType) {
    var data = {};
    if(!dataType) dataType=1;//参数类型
    var accountParams = j(".setBox.accountParamsBox b");
    if (accountParams.length) {
        accountParams.each(function(){
            var no = j(this).attr('no');
            var value = j(this).attr('value');
            if (dataType == 1) {
                data['param['+ no +']'] = value;
            }else{
                data[no] = value;
            }
        });
    }
    return data;
}

/**
 *说明：获取结算参数,获取contract_account_params表所有数据,并且按sorting asc排序.
 *@params 
 *@params 
 */
fee.getAccountParamsData = function() {
    var url = 'index.php?app=b2c&ctl=admin_contract&act=get_account_params_html';
    loadParams = layer.load(1);
    var params = fee.getSetedParams();////获取设置好的结算参数值
    j.get(url,params,function(result){
        layer.open({
            title: '添加结算参数',
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['500px', '530px'],
            btn: '保存',
            content: result,
            yes: function(index, layero){
                if(fee.saveAccountParamsData()){
                    layer.close(index);//设定了yes需手工关闭
                }
            }
        });
        j('.vdate').layDate({format: 'YYYY-MM-DD'});
		layer.close(loadParams);
    },'html');
}

/**
 *说明：保存结算参数
 *@params 
 *@params 
 */
fee.saveAccountParamsData = function() {
    //先验证合法性
    if(j(".accountParamsform .tips.error").length){
        return false;
    }
    //总货值是必须填写的
    var amountInputObj = j(".accountParamsform input[name=amount]");
    if (amountInputObj.val() == '') {
        layer.msg('总货值必须填写！');
        fee.trAnimate({row:amountInputObj.closest('tr')});
        return false;
    }
    var html = '';
    var customData = j(".accountParamsform input");
    customData.each(function(){
        var value = j(this).val();
        var no = j(this).attr('name');
        var params_id = j(this).attr('params_id');
        var title = j(this).attr('title');
        var fix = j(this).attr('fix');
        fix = fix ? fix : '';
        if (value == '') {
            return true;
        }
        html+='<b no="'+no+'" value="'+value+'">'+title+'：'+value + fix +'</b>';
        html+='<input type="hidden" name="data[params]['+ params_id +']" value="'+ value +'"/>';
    });

    if (html == '') {
        layer.msg('请设置结算参数！');
        return false;
    }
    j(".setBox.accountParamsBox").html(html);
    return true;
}

/**
 *说明：获取费种,获取contract_fee表所有数据,并且按sorting asc排序.
 *注意：编辑的时候也用这个
 *@params 
 *@params 
 */
fee.getFeeData = function(obj) {
    var url = 'index.php?app=b2c&ctl=admin_contract&act=get_fee_html';
    params = {data:''};
    //如果是编辑
    if (obj) {
        var stepRow = j(obj).find('.stepRow').text();//阶段行数据
        params.data = stepRow;
    }
    
    //检测结算参数是否未设置
    var paramsData = fee.getSetedParams();
    if (j.isEmptyObject(paramsData)) {
        layer.msg('请先设置结算参数！');
        return false;
    }
	loadIndex = layer.load(1);
    j.post(url,params,function(result){
        layer.open({
            title: '添加结算阶段',
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['60%', '600px'],
            btn: '保存',
            content: result,
            yes: function(index, layero){
                if(fee.saveAccountStepData()){
                    layer.close(index);//设定了yes需手工关闭
                }
            }
		});
		j('.layDateFee').layDate({format: 'YYYY-MM-DD'});
		layer.close(loadIndex);
    },'html');
}


/**
 *说明：保存结算阶段
 *@params 
 *@params 
 */
fee.saveAccountStepData = function() {
    var contract_id = j('input#contract_id').val();//合约id
    var step_id = j('.feebox input[name=step_id]').val();//阶段id,如果是编辑
    var step_title = j('.feebox input[name=step_title]').val();//阶段名
    var end_time = j('.feebox input[name=end_time]').val();//阶段付款截至日期
    var fee_ids = '';//费种
    var usedfor = '合约';
    var randNum = fee.randomStr(15);
    var html = '';
    var expression = '';
    var expFormat = '';
    var editButton = '';

    //检测费种填写与选择是否合法
    if (step_title == '') {
        layer.msg('阶段名必须填写');
        return false;
    }

    var checkbox = j(".feebox table.feeList input.feeItem:checked");
    if (!checkbox.length) {
        layer.msg('至少选择其中一个费种！');
        return false;
    }

    //验证单选[如果单选按钮用户多选了]
    // if (checkbox.length > 1 && j(".feebox table.feeList .feeItem[isone=true]:checked").length) {
    //     layer.msg('单选的费种不能和其他费种组合使用!');
    //     fee.trAnimate({row:j(".feebox table.feeList .feeItem[isone=true]:checked").closest('tr')});
    //     return false;
    // }

    //如果用户选中了单选按钮(规则：出库的阶段只能和临时附加费组合并且勾选临时附加费是可选的)
    if (j(".feebox table.feeList .feeItem[isone=true]:checked").length) {
        //非法1：选择了出库,却又选择了非临时附加费阶段
        if (checkbox.length > 1 && j(".feebox table.feeList .feeItem:checked:not(.temp_extra_charge):not(.partial_service_charge)").length) {
            layer.msg('出库的结算阶段只能和临时附加费组合使用!');
            fee.trAnimate({row:j(".feebox table.feeList .feeItem[isone=true]:checked").closest('tr')});
            return false;
        }else if(!step_id && checkbox.length == 1 && j('.setBox.accountStepBox input.fee_ids[value='+  checkbox.attr('feeId') +']').length){
            //非法2：已经保存了同样的费种(前提是添加,编辑不算)
            layer.msg('相同的<b>单选</b>费种不能重复添加！');
            return false;
        }
    }

    if (step_id > 0 ) {
        //注意这里的stepRow中的json数据需要保存后更新!
        editButton = '<span class="opt" onclick="fee.getFeeData(this)">' + 
                        '编辑' + 
                        '<a class="hide stepRow"></a>' +
                    '</span>';
    }


    //[分批出库货值及服务费]单独生成多行tr
    if (j(".feebox table.feeList .feeItem[isone=true]:checked").length) {
        var contractGoods = j('.contractGoods');
        var goodsNum = contractGoods.length;//选择的合约商品总数
        var params = fee.getSetedParams(2);//获取设置好的结算参数值
        var point = params['point'];//点数
        var vdate = params['vdate'];//金融起息日
        var temp_extra_charge = params['temp_extra_charge'];//临时附加费
        expFormat = checkbox.attr('expFormat');//显示的值,而expression是计算的值
        fee_ids = checkbox.attr('feeId');//费种id
        var extra = 0;//是否勾选了临时附加费
        //验证合法性
        if (!goodsNum && !contract_id) {
            layer.msg('合约商品必须选择!');
            return false;
        }
        if (!point) {
            layer.msg('结算参数[点数]必须填写！');
            return false;
        }

        if (!vdate) {
            layer.msg('结算参数[金融起息日]必须填写！');
            return false;
        }

        //如果勾选了临时附加费,验证结算参数
        if (j(".feebox table.feeList .feeItem.temp_extra_charge:checked").length) {
            extra = 1;
            if(!temp_extra_charge){
                layer.msg('结算参数[临时附加费]必须填写！');
                return false;
            }
        }

        var step=1;
        contractGoods.each(function(){
            var goodsName = j(this).attr('name');//合约商品名称
            var price = j(this).find('input[tag=price]').val();//单价
            //注意这里每个商品的表达式都不一样(暂未用到)
            // expression = price + '*[出库数量]+'+ price +'*[出库数量]*'+ point +'/365*([当次出库日期]-'+ vdate +')';
            //这里row跨行处理
            if (step == 1) {
                html+='<tr class="partial_service_charge" step_id='+ step_id +'>'+
                    '<td rowspan="'+ goodsNum +'">'+
                        '<span class="opt" onclick="fee.deleteStep(this'+ (step_id > 0 ? '' : ',1') +')">删除</span>' + editButton +
                    '</td>'+
                    '<td rowspan="'+ goodsNum +'">'+ step_title +
                    '<input name="data[step]['+ randNum +'][title]" type="hidden" value="'+ step_title +'"/>' +
                    '<input name="data[step]['+ randNum +'][step_id]" type="hidden" value="'+ step_id +'"/>' +
                    '<input name="data[step]['+ randNum +'][extra]" type="hidden" value="'+ extra +'"/>' +
                    '</td>'+
                    '<td>'+ goodsName +'</td>'+
                    '<td>'+ expFormat +'<input class="fee_ids" name="data[step]['+ randNum +'][fee_ids]" type="hidden" value="'+ fee_ids +'"/></td>'+
                    '<td rowspan="'+ goodsNum +'">'+ end_time +'<input name="data[step]['+ randNum +'][end_time]" type="hidden" value="'+ end_time +'"/></td>'+
                    '<td rowspan="'+ goodsNum +'">'+
                        '<input name="data[step]['+ randNum +'][state]" value="on" type="radio">是'+
                        '<input name="data[step]['+ randNum +'][state]" checked="checked" value="off" type="radio">否'+
                    '</td>'+
                '</tr>';
            }else{
                html+='<tr class="partial_service_charge" step_id='+ step_id +'>'+
                    '<td>'+ goodsName +'</td>'+
                    '<td>'+ expFormat +'</td>'+
               ' </tr>';
            }
            step++;
        });
    }else{
        var feeData = fee.makeExpression();//计算公式
        fee_ids = feeData.fee_ids;
        //结算参数填写是否合法(够勾选的费种使用)
        if (feeData === false) {
            return false;
        }
    
        html+='<tr step_id='+ step_id +'>'+
                    '<td>'+
                        '<span class="opt" onclick="fee.deleteStep(this'+ (step_id > 0 ? '' : ',1') +')">删除</span>' + editButton +
                    '</td>'+
                    '<td>'+ step_title + 
                    '<input name="data[step]['+ randNum +'][title]" type="hidden" value="'+ step_title +'"/>' +
                    '<input name="data[step]['+ randNum +'][step_id]" type="hidden" value="'+ step_id +'"/>' +
                    '</td>'+
                    '<td>'+ usedfor +'</td>'+
                    '<td>'+ feeData.expFormat +'<input class="fee_ids" name="data[step]['+ randNum +'][fee_ids]" type="hidden" value="'+ fee_ids +'"/></td>'+
                    '<td>'+  end_time +'<input name="data[step]['+ randNum +'][end_time]" type="hidden" value="'+ end_time +'"/></td>'+
                    '<td>'+
                        '<input name="data[step]['+ randNum +'][state]" value="on" type="radio">是'+
                        '<input name="data[step]['+ randNum +'][state]" checked="checked" value="off" type="radio">否'+
                    '</td>'+
                '</tr>';
    }

    //如果是编辑则删除原来的tr,并且在原来位置替换,新增则直接追加
    if (step_id > 0 ) {
        var editTr = j('tr[step_id='+ step_id +']');
        if(editTr.length){
            editTr.last().after(html);//防止出库有多个重复的step_id,导致重复添加,这里为了兼容出库阶段需要取最后一个
            editTr.remove();

            //更新结算阶段json数据
            var stepRow = j('.feebox a.stepRow').text();//阶段行json数据
            stepRow = JSON.parse(stepRow);
            stepRow.title = step_title;
            stepRow.end_time = end_time;
            stepRow.fee_ids = fee_ids;
            stepRow.extra = extra;
            var stepRow_json = JSON.stringify(stepRow);
            if (j('tr[step_id='+ step_id +'] a.stepRow').length) {
                j('tr[step_id='+ step_id +'] a.stepRow').text(stepRow_json);
            }
        }
    }else{
        j(".setBox.setStepBox.accountStepBox table tbody").append(html);
    }
    return true;
}


//--辅助--生成计算公式[注意：此方法不包含“分批出库货值及服务费”的公式计算]
fee.makeExpression = function() {
    var data = {expFormat:[],fee_ids:[]};
    var result = true;
    var feeItem = j(".feebox table.feeList input.feeItem:checked");
    var params = fee.getSetedParams(2);////获取设置好的结算参数值
    // console.log(params);
    feeItem.each(function(){
        var exp = '';
        var no = j(this).attr('no');
        var title = j(this).attr('title');
        var fee_id = j(this).attr('feeId');
        if (no == 'deposit') {
            //检测结算参数合法性
            if (!params['amount']) {
                layer.msg('结算参数[总货值]没有填写！');
                result = false;
                return false;
            }else if(!params['deposit_rate']){
                layer.msg('结算参数[定金支付比例]没有填写！');
                result = false;
                return false;
            }
            // exp = params['amount'] + '*' + params['deposit_rate'];

        }else{
            if (!params[no]) {
                layer.msg('结算参数['+ title +']没有填写！');
                result = false;
                return false;
            }
            // exp = params[no];
        }
        exp = j(this).attr('expFormat');
        data.expFormat.push(exp);
        data.fee_ids.push(fee_id);
    });
    if (result) {
        data.expFormat = data.expFormat.join('+');
        data.fee_ids = data.fee_ids.join(',');
        return data;
    }else{
        return result;
    }
}

//处理添加商品后事宜
fee.doAddedGoods = function() {
    fee.addGoodsHead();//添加合约商品表头
}

//添加合约商品表头
fee.addGoodsHead = function() {
    var html = '';
    var goodsbox = j('.contract_goods_box .gridlist.rows-body');
    if (goodsbox.find('.row.row-head').length) {
        return false;
    }
    html += '<div class="row row-head">'+
                '<div class="row-line">'+
                '<div class="span-auto row-h">'+
                    '<span class="opt row-del">操作</span>'+
                '</div>'+
                '<div class="span-auto row-h row-title">商品名称</div>'+
                '<div class="objectBox">'+
                    '<span class="bn t-n1">商品货号</span>'+
                    '<span class="t-n2">重量(kg)</span>'+
                    '<span class="t-n2">计量单位</span>'+
                    '<span class="t-n3">商品规格</span>'+
                    '<span class="t-n2">商品属性</span>'+
                    '<span class="t-n3">原产地</span>'+
                    '<span class="t-n3">供应商</span>'+
                    '<span class="t-n4">数量</span>'+
                    '<span class="t-n4">单价</span>'+
                    '<span class="t-n4">仓库</span>'+
                '</div>'+
                '</div>'+
            '</div>';

    goodsbox.prepend(html);
}

//提交表单前验证提交的内容是否合法
fee.checkSubmitContent = function() {
    var goodsRows = j('.objectBox.contractGoods');
    var isPass = true;//是否通过
    var msg = '';
    var notPassRow = null;
    goodsRows.each(function () {
        var num = j(this).find('input[tag=num]').val();//数量
        var price = j(this).find('input[tag=price]').val();//单价

        if (!j.isNumeric(num)) {
            msg = '商品数量必须为数值';
            isPass = false;
            notPassRow = this;
            return false;
        }
        
        if (!j.isNumeric(price)) {
            msg = '商品价格必须为数值';
            isPass = false;
            notPassRow = this;
            return false;
        }
        
    });
    if (!isPass) {
        layer.msg(msg);
        fee.trAnimate({row:j(notPassRow)});
        return false;
    }
    return true;

}

fee.delItem = function(obj){
	obj = obj ? obj : {};//Object
	if(!obj.ok) obj.ok = function(){};
    obj.url = obj.url || '';//请求地址
    obj.params = obj.params || {};

    if (!obj.url) {
        layer.msg('地址不能为空！');
        return;
    }
	layer.confirm('确定要删除吗？',function(){
		var index = layer.load(1);
		j.getJSON(obj.url,obj.params,function(data){
			if(data.error == 0){
                if(obj.ok) obj.ok();
                layer.msg(data.message);
			}else{
				layer.msg(data.message,{icon: 5});
			}
			layer.close(index);
		});
	});
}

//删除结算阶段
fee.deleteStep = function(dom,isNew) {
    //如果是新增的阶段,直接删除dom
    if (isNew) {
        var tr = j(dom).closest('tr');
        if (tr.hasClass('partial_service_charge')) {
            j('.accountStepBox table tr.partial_service_charge[step_id=""]').remove();
        }else{
            tr.remove();
        }
        return;
    }

    var step_id = j(dom).closest('tr').attr('step_id');
    fee.delItem({
        url:'index.php?app=b2c&ctl=admin_contract&act=deleteStep',
        params:{step_id:step_id},
        ok:function(){
            var tr = j(dom).closest('tr');
            if (tr.hasClass('partial_service_charge')) {
                j('.accountStepBox table tr.partial_service_charge[step_id="'+ step_id +'"]').remove();
            }else{
                tr.remove();
            }
        }
    });

}

//出库的总货值计算
fee.totalAmountCalc = function(obj){
	obj = obj ? obj : {};//Object
    obj.params = obj.params || {};

    var url = 'index.php?app=b2c&ctl=admin_contract&act=totalAmountCalc';
    obj.params = j("#contract_form").serialize();
    var index = layer.load(1);
    j.post(url,obj.params,function(data){
        if(data.error == 0){
            //显示总货值            
            j(".accountParamsform input[name=amount]").val(data.data);
        }else{
            layer.msg(data.message,{icon: 5});
        }
        layer.close(index);
    },'json');
}

