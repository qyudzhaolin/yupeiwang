manage={};
manage.cityData=[];
/**
 *说明：处理点击按钮显示数据点
 *@params clickDom 当前点击的dom元素
 *@params obj 配置 {'#container':obj1,'#container2':obj2}
 */
manage.xxxx = function(){
}

//jq让div居中
j.fn.extend({
	center: function () {
		return this.each(function() {
			var top = (j(window).height() - j(this).outerHeight()) / 2;
			var left = (j(window).width() - j(this).outerWidth()) / 2;
			j(this).css({position:'fixed', margin:0, top: (top > 0 ? top : 0)+'px', left: (left > 0 ? left : 0)+'px'});
		});
	}
}); 


//弹出提示设置配送区域
manage.alertSetRegion = function(){
	j('.regionButton').click();

	//强制弹窗设置配送范围
	j('.city-select').addClass('hidden').prependTo('body');
	setTimeout("manage.doSetAlert()",1000);//延迟,上下居中
}

//设置弹窗以及让弹窗居中
manage.doSetAlert = function(){
	j('<div id="alertBox-shade"></div>').prependTo('body');
	j('.city-select').removeClass('hidden').center();
	var top = (j(window).height() - 390) / 2;
	j('.city-select').css({top: (top > 0 ? top : 0)+'px'});
}

//显示按钮，以及显示配送区域
manage.setRegionButton = function(){
	var logoObj = j('.logo');
	var htmlButton = '<span shiparea="-1" class="regionButton" onclick="manage.showRegions()" ><img src="/public/images/pcimg/dingwei.png" /><em>配送至</em><p><b>未设置</b><i></i></p></span><div class="city-select down"></div>';
	logoObj.addClass('areaBox');
	logoObj.append(htmlButton);

	//设置样式(搜索框右移动)
	j('.page-header .header-left').css({'width':'415px'});

	//设置默认配送区域cookie[如果用户还没选择配送区域],若用户设置过则用用户设置的区域显示
	// j.cookie('shiparea', '');j.cookie('shipareaName', '');//清空cookie
	if (!j.cookie('shiparea')) {
		setTimeout("manage.alertSetRegion();",500);//展开
	}else{
		//显示区域名称
		var shipareaName = j.cookie('shipareaName');
		var shiparea = j.cookie('shiparea');
		j('.regionButton b').text(shipareaName);
		j('.regionButton').attr('shiparea',shiparea);
	}


}


//城市列表
manage.doShowRegions = function(){
	j('.city-select').removeClass('hide').addClass('down');

	var singleSelect1 = j('.city-select').citySelect({
		dataJson: manage.cityData,
		multiSelect: false,
		whole: true,
		hotCity: ['北京市', '上海', '上海市', '广州市', '深圳市', '南京市', '杭州市'],
		shorthand: false,
		search: true,
		onInit: function () {

		},
		onTabsAfter: function (target) {
			//console.log(target)
		},
		onCallerAfter: function (target, values) {
			//console.log(JSON.stringify(values));
			j('.regionButton').attr('shiparea',values.id).find('b').text(values.name);
			j('.city-select').addClass('hide').removeClass('down');

			//设置cookie
			j.cookie('shiparea', values.id, { expires: 100 });
			j.cookie('shipareaName', values.name, { expires: 100 });

			//重载页面
			location.reload();
		}
	});

	setTimeout("j('.city-select').find('.city-pavilion').removeClass('hide')",10);//展开

	//设置标题
	//j('.city-select .city-info').prepend('<div class="citySelectTitle">设置配送范围</div>');

}

/**
 *说明：设置配送地区
 *@params 无
 *@return
 */
manage.showRegions = function(){
	var url = '/index.php/tools-getareas.html';
	if(!manage.cityData.length){
		//layerIndex = layer.load();
		j.getJSON(url,{},function(data){
			if (data.error == 0) {
				manage.cityData = data.data;
				manage.doShowRegions();
			}
			//layer.close(layerIndex);
		});
	}else{
		manage.doShowRegions();
	}

}


j(function(){
	if(j('.logo').length){
		//manage.setRegionButton();//显示按钮		
	}






});