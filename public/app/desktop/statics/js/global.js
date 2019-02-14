manage={};
/**
 *说明：处理点击按钮显示数据点
 *@params clickDom 当前点击的dom元素
 *@params obj 配置 {'#container':obj1,'#container2':obj2}
 */
manage.showChartsData = function(clickDom,obj){
	var plotOptions={
			series: {
				dataLabels: {
					enabled: true,
					//shape: 'callout',
					borderRadius: 2,
					backgroundColor: '#a4edba',
					borderWidth: 1,
					borderColor: '#CCC',
					style: {
						textOutline: 'none',
						color:'red',
						fontSize:'10px',
						opacity: 0.85

					},
					padding: 0,
					verticalAlign: 'bottom',
					y: -11
				},
				
			}
		};
	$(clickDom).is(':checked') ? (plotOptions.series.dataLabels.enabled = true) : (plotOptions.series.dataLabels.enabled = false);
	
	//处理多曲线情况
	for(var x in obj){
		if($.isEmptyObject(obj[x])){
			continue;
		}
		obj[x].plotOptions = plotOptions;
		$(x).highcharts(obj[x]);
	}
}

/**
 *说明：选择云仓库商品，默认打开页面时候input.value是空，这样就保证无动作提交不更新,
 *@params bool isKeyword 是否搜索
 */
manage.selectCloudGoods = function(isKeyword) {
    var url = 'index.php?app=b2c&ctl=admin_goods&act=getCloudGoods';
	var ownerCode = j('.ownerSelect').val();//货主编码
	var projectId = j('.storehouseSelect').val();//仓库ID参数
	var keyword = j('.fullTablebox input.search-text').val();//搜索关键词
	var sku_code = j('input.sku_code').val();//已经填写的商品编码

	if (projectId <= 0) {
		layer.msg('仓库必须选择');
		return;
	}
	if (ownerCode == '') {
		layer.msg('货主必须选择');
		return;
	}
	if (isKeyword && !keyword) {
		layer.msg('请输入商品名称');
		return;
	}
	var params= {ownerCode:ownerCode, projectId:projectId, sku_code:sku_code, skuName:keyword};
	var indexbox = layer.load(1);
    j.get(url,params,function(result){
		//如果是搜索，则不关闭弹框，直接修改商品列表内容即可
		if (isKeyword && keyword) {
			j(goodsSelectBox).find('.layui-layer-content').html(result);
		}else{
			layer.open({
				title: '设置WMS商品编号',
				type: 1,
				skin: 'layui-layer-rim', //加上边框
				area: ['950px', '550px'],
				btn: '确定',
				content: result,
				yes: function(index, layero){
					var selectedRadio = j('input[name=cloudGoods]:checked');
					var skuCode = selectedRadio.val();
					var unit = selectedRadio.attr('unit');
					var unitEn = selectedRadio.attr('unitEn');
					j('input.yp-unit').val(unit);//计量单位
					j('input.yp-unit-en').val(unitEn);//计量单位英文
					j('.yp-disabled-cover.unit').text(unit);//计量单位
					j('input.sku_code').val(skuCode);//商品编码
					j('.skuCodeSelectedBox').text(skuCode);//商品编码

					//异步获取接口库存
					var cloudGoodStoreUrl = 'index.php?app=b2c&ctl=admin_goods&act=getCloudGoodStore';
					var paramObj= {owner_code:ownerCode, storehouse_id:projectId, sku_code:skuCode};
					var loadIndex = layer.load(1);
					j.getJSON(cloudGoodStoreUrl,paramObj,function(res) {
						if (res.error == 0) {
							var store = res.data.qtyUomForYupeiwang;
							//设置以及显示商品库存
							j('input.yp-store').val(store);//库存
							j('.yp-disabled-cover.store').text(store);//库存
							layer.close(index);
						}else{
							layer.msg(res.message);
						}
						layer.close(loadIndex);
					});

				},
				success: function(layero, index){
					goodsSelectBox = layero;
				}
			});
		}
		
		//增加radio点击颜色控制
		j('input[name=cloudGoods]').on('click',function(){
			var ctr = j(this).closest('tr');
			ctr.addClass('bkmark').siblings().removeClass('bkmark');
		});

		//关闭加载层
		layer.close(indexbox);
    },'html');
}


/**
 *说明：选择云仓库货主，默认打开页面时候input.value是空，这样就保证无动作提交不更新,
 *@params bool isKeyword 是否搜索
 */
manage.selectCloudOwners = function(isKeyword) {
    var url = 'index.php?app=b2c&ctl=admin_goods&act=getCloudOwners';
	var ownerCode = j('input.ownerSelect').val();//货主编码
	var keyword = j('.fullTablebox input.search-text').val();//搜索关键词

	if (isKeyword && !keyword) {
		layer.msg('请输入货主名称');
		return;
	}
	var params= {ownerCode:ownerCode, keyword:keyword};
	var indexbox = layer.load(1);
    j.get(url,params,function(result){
		//如果是搜索，则不关闭弹框，直接修改商品列表内容即可
		if (isKeyword && keyword) {
			j(ownerSelectBox).find('.layui-layer-content').html(result);
		}else{
			layer.open({
				title: '请选择货主',
				type: 1,
				skin: 'layui-layer-rim', //加上边框
				area: ['950px', '550px'],
				btn: '确定',
				content: result,
				yes: function(index, layero){
					var selectedRadio = j('input[name=cloudData]:checked');
					var owner_code = selectedRadio.val();
					var owner_name = selectedRadio.attr('dataName');
					j('input.ownerSelect').val(owner_code);//货主编码
					j('.ownerCodeSelectedBox').text(owner_name);//货主名称

					layer.close(index);
				},
				success: function(layero, index){
					ownerSelectBox = layero;
				}
			});
		}
		
		//增加radio点击颜色控制
		j('input[name=cloudData]').on('click',function(){
			var ctr = j(this).closest('tr');
			ctr.addClass('bkmark').siblings().removeClass('bkmark');
		});

		//关闭加载层
		layer.close(indexbox);
    },'html');
}