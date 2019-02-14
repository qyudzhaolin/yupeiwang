joop={};
//吸附提示
joop.fixtips = function(content,dom){
    layer.tips(content,dom, {
        tips: [1, '#3595CC'],
        time: 3000
    });
}
/**
 *说明：点击加减按钮
 *@params object cdom 当前点击的dom元素
 *@params string numDom 显示数字的dom元素
 *@params int maxNum 一般为库存数值
 *@return void
 */
joop.addSub = function(obj){
    obj = obj || {};
    obj.maxNum = obj.maxNum || 999;//一般为库存数值
    obj.outNote = obj.outNote || '库存不足！';//超出库存提示
    var curNum = parseInt(j(obj.numDom).val());
    var store_left_obj = j(obj.cdom).closest('tr').find('td.store_left');
    obj.maxNum = parseInt(obj.maxNum);
    obj.nostore_sell = parseInt(obj.nostore_sell);//是否开启无库存也可以销售
	if (j(obj.cdom).hasClass('increase')) {
        if (!obj.nostore_sell) {
            if (obj.maxNum == 0) {
                joop.fixtips(obj.outNote, obj.numDom);
                return;
            }else if(curNum >= obj.maxNum){
                joop.fixtips(obj.outNote, obj.numDom);
                return;
            }
        }
        var outStoreNum =  parseInt(curNum + 1);
        if (isNaN(outStoreNum)) {
            outStoreNum = 0;
        }
        j(obj.numDom).val(outStoreNum);
	}else if(j(obj.cdom).hasClass('decrease')){
        if (curNum <= 0) {
            joop.fixtips('数量不能小于0！', obj.numDom);
            return;
        }
        var outStoreNum = parseInt(curNum - 1);
        if (isNaN(outStoreNum)) {
            outStoreNum = 0;
        }
		j(obj.numDom).val(outStoreNum);
    }
    joop.storeLeft(store_left_obj,j(obj.numDom));//剩余库存动态显示

}


/**
 *说明：剩余库存动态显示
 *@params object storeLeftObj 原始的剩余库存obj，td
 *@params object outStoreObj 要出库的obj，通常是input
 *@params int maxNum 一般为库存数值
 *@return void
 */
joop.storeLeft = function(storeLeftObj,outStoreObj,maxNum){
    var originLeftNum =  parseInt(storeLeftObj.attr('store_left'));//原始剩余数量
    var outStoreNum = parseInt(outStoreObj.val());//要出库的数量
    if (isNaN(outStoreNum)) {
        outStoreNum = 0;
    }
    if (maxNum && outStoreNum > maxNum) {
        joop.fixtips('库存不足！', '#' + outStoreObj.prop('id'));
        //return;
    }
    var storeLeftNum = originLeftNum-outStoreNum;
    storeLeftObj.text(storeLeftNum);
}

/**
 *说明：动态粘贴出库或者输入而不是点击+-
 *@params object cdom 当前元素
 *@params int maxNum 一般为库存数值
 *@return void
 */
joop.writeOutStore = function(cdom,maxNum){
    var storeLeftObj =  j(cdom).closest('tr').find('td.store_left');
    var outStoreObj = j(cdom);
    var outStoreNum = outStoreObj.val().replace(/[^\d]/g,'');//转为数字
    // if (isNaN(outStoreNum)) {
    //     outStoreNum = 0;
    // }
    outStoreObj.val(outStoreNum);
    joop.storeLeft(storeLeftObj,outStoreObj,maxNum);//剩余库存动态显示
}

/**
 *说明：出库公式计算
 *@params 
 *@params 
 */
joop.getOutStoreExp = function(dom) {
    var url = './contract-getOutStoreExp.html';
    index = layer.load(1);
    var params = {contract_id:0,outStore:{}};
    params.contract_id = j(dom).attr('contract_id');
    params.step_id = j(dom).attr('step_id');
    params.extra = j('.contract_extra input').is(':checked') ? '1' : '0';//是否勾选临时附加费
    j('.pre-goods-list table input.outElem').each(function() {
        var outNum = parseInt(j(this).val());
        if (outNum < 1) {
            return true;
        }
        params.outStore[j(this).attr('product_id')] = outNum;
    });
    j.getJSON(url,params,function(result){
        if (result.error == 0 && result['data']['detail']) {
            var data = result['data']['detail'];
            var html = '';
            var prefix = '';//前缀
            var initNum = 0;
            for (var x in data) {
                if( ! data.hasOwnProperty(x) || data[x] == '') continue;
                prefix = initNum > 0 ? '+' : '';
                html += '<span>'+ prefix + data[x] +'</span>';//注意属性不能为数字，不然为undefined
                initNum++;
            }
            // console.log(data);
            j('.outStoreExp').html(html);
            //履约需要支付金额
            j('.outStoreMoney').text('￥'+ result.data.total);
            //出库是否合法
            joop.out_state = true;
        }else{
            joop.out_state = false;//出库非法
            joop.out_state_error_msg = result.message;//出库非法信息
            layer.msg(result.message);
            if (result.error == 13) {
                //joop.fixtips(result.message,'.outElem');
                //动画提示选择数量
                joop.trAnimate({row:j('.outElem')});
            }
        }
		layer.close(index);
    });
}

/**
 *说明：动画提示效果
 *用例：用在行闪烁提示用户
 *@obj.row:需指定当前哪个行需要动画
 *@obj.ok:动画完成后动作
 */
joop.trAnimate = function(obj){
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


/**
 *说明：合约支付定金
 *@return void
 */
joop.payDeposit = function(){
    var params = {btype:'goods',response_json:'true',contract_id:j('.payDeposit').attr('contract_id'),step_id:j('.payDeposit').attr('step_id')};
    var url = './cart-add_contract-goods-other.html';
    var step_type = 'other';
    params.goods = [];
    //包含合约商品出库的阶段
    if (j('#outStoreButton').length) {
        if (!joop.out_state) {
            layer.msg(joop.out_state_error_msg ? joop.out_state_error_msg : '非法的合约商品出库，请先选择商品出库！');
            return;
        }
        //组装出库商品参数
        url = './cart-add_contract-goods-out.html';
        params.extra = j('.contract_extra input').is(':checked') ? '1' : '0';//是否勾选临时附加费
        step_type = 'out';
        j('.pre-goods-list table input.outElem').each(function() {
            var outNum = parseInt(j(this).val());
            var goodsObj = {};
            if (outNum < 1) {
                return true;
            }
            goodsObj.goods_id = j(this).attr('goods_id');
            goodsObj.product_id = j(this).attr('product_id');
            goodsObj.num = outNum;
            goodsObj.store_left = j(this).closest('tr').find('td.store_left').attr('store_left') - outNum;
            params.goods.push(goodsObj);
        });
    }else{
        //构造购买虚拟商品
        var virtualGoods = j('#virtualGoods');
        var goodsObj = {};
        goodsObj.goods_id = virtualGoods.attr('goods_id');
        goodsObj.product_id = virtualGoods.attr('product_id');
        goodsObj.num = 1;
        params.goods.push(goodsObj);
    }

    j.getJSON(url,params,function(result){
        if (result.error == 0 || result.error == 2) {
            location.href=result.message;
        }else{
            layer.msg(result.message);
        }
    });
}
