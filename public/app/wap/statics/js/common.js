var joop = {};

/**
 *说明：上下滑动监视，并且执行任务
 *@params function slideUp 上滑动函数
 *@params function slideDown 下滑动函数
 *@params int minSlide  最小移动间隙
 *@params mixed dom 滑动的元素
 *@return void
 */
joop.slideUpDown = function (obj) {
    obj = obj || {};
    obj.slideUp = obj.slideUp || function(){};//上滑动函数
    obj.slideDown = obj.slideDown || function(){};//下滑动函数
    obj.minSlide = obj.minSlide || 50;//最小移动间隙
    obj.dom = obj.dom || window;//滑动的元素
    joop.slideTop = 0;//初始化
    j(obj.dom).scroll(function() {
        var tmptop = j(this).scrollTop();
        //刚开始间隙要做的事情
        // console.log(tmptop);
        if (tmptop < obj.minSlide){
            obj.slideDown();
            return;
        }
        //上滑要做的事情
        if ((tmptop-joop.slideTop) > 10) {
            obj.slideUp();
            joop.slideTop = tmptop;
        }else if((joop.slideTop-tmptop) > 10){
            obj.slideDown();
            joop.slideTop = tmptop;
        }
    });
}

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
    var curNum = parseInt(j(obj.numDom).val());
    obj.maxNum = parseInt(obj.maxNum);
    obj.nostore_sell = parseInt(obj.nostore_sell);//是否开启无库存也可以销售
	if (j(obj.cdom).hasClass('increase')) {
        if (!obj.nostore_sell) {
            if (obj.maxNum == 0) {
                joop.fixtips('库存不足！', obj.numDom);
                return;
            }else if(curNum >= obj.maxNum){
                joop.fixtips('请不要超出范围！', obj.numDom);
                return;
            }
        }
		j(obj.numDom).val(curNum + 1);
	}else if(j(obj.cdom).hasClass('decrease')){
        if (curNum <= 0) {
            joop.fixtips('数量不能小于0！', obj.numDom);
            return;
        }
		j(obj.numDom).val(curNum - 1);
	}
}


if(typeof manage == 'undefined' || !manage){
	var manage = {};
}

/**
 * 文件上传方法
 * @param obj.listBox string 缩略图容器
 */
manage.webUploader = function(obj){
	if( typeof obj != 'object') obj={};
	obj.listBox = obj.listBox || '#fileList';//缩略图容器
	obj.url = obj.url ? obj.url : '/index.php/wap/tools-upfile.html';//文件上传地址
	obj.folder = obj.folder || '';//上传到Uploads下面的文件夹
	obj.pick = obj.pick || '#filesPicker';//按钮
	obj.formData = obj.formData || {};//请求参数
	obj.thumbSize = obj.thumbSize || 100;//请求参数
	obj.fileNumLimit = obj.fileNumLimit || 9;//限制上传数量
	var $list = j(obj.listBox);

	 // 初始化Web Uploader
	var uploader = WebUploader.create({

		// 选完文件后，是否自动上传。
		auto: true,
		//限制上传数量
		fileNumLimit:obj.fileNumLimit,

		// swf文件路径
		swf: '/public/app/wap/statics/js/Uploader.swf',

		// 文件接收服务端。
		server: obj.url,

		//请求参数
		formData:obj.formData,

		// 选择文件的按钮。可选。
		// 内部根据当前运行是创建，可能是input元素，也可能是flash.
		pick: obj.pick,

		//如果上传图片过大则压缩，preserveHeaders这个要设为false否则后台压缩会出现问题
		compress: {width: 1600,height: 1600, allowMagnify: false, preserveHeaders: false,compressSize: 0},

		// 只允许选择图片文件。
		accept: {
			title: 'Images',
			extensions: 'gif,jpg,jpeg,bmp,png,apk',
			mimeTypes: 'image/*'
		}
	});

	 // 当有文件添加进来的时候
	uploader.on( 'fileQueued', function( file ) {
		//检测添加数量限制
//		var tcount = j(obj.listBox + ' .thumbnail').length;
//		if(tcount >= obj.fileNumLimit) {
//			layer.msg('最多只允许上传' + obj.fileNumLimit + '个文件哦！',{icon: 5});
//			return;
//		}
		var $li = j(
				'<div id="' + file.id + '" class="file-item thumbnail thumbnailFiles">' +
					'<img>' +
					'<div class="info cancel">×</div>' +
				'</div>'
				),
			$img = $li.find('img');


		// $list为容器jQuery实例
		$list.append( $li );

		// 创建缩略图
		// 如果为非图片文件，可以不用调用此方法。
		// thumbnailWidth x thumbnailHeight 为 100 x 100
		uploader.makeThumb( file, function( error, src ) {
			if ( error ) {
				$img.replaceWith('<span>不能预览</span>');
				return;
			}

			$img.attr( 'src', src );
		}, obj.thumbSize, obj.thumbSize );

		//删除图片
		j('.thumbnailFiles .cancel').click(function(){
			manage.delImage({dom:this,onlyFile:1});
			uploader.removeFile( file.id );
		});
	});

	// 文件上传过程中创建进度条实时显示。
	uploader.on( 'uploadProgress', function( file, percentage ) {
		var $li = j( '#'+file.id ),
			$percent = $li.find('.progress span');

		// 避免重复创建
		if ( !$percent.length ) {
			$percent = j('<p class="progress"><span></span></p>')
					.appendTo( $li )
					.find('span');
		}

		$percent.css( 'width', percentage * 100 + '%' );
	});

	// 文件上传成功，给item添加成功class, 用样式标记上传成功。
	uploader.on( 'uploadSuccess', function( file,data ) {
		if(data.error == 0){
			var datas = data.data;
			j( '#'+file.id ).addClass('upload-state-done')
			                .attr('folder',datas.folder)
							.attr('savetype',datas.savetype)
							.attr('saveFile',datas.saveFile)
							.attr('fullName',datas.fullName);
		}else{
			layer.msg(data.message,{icon: 5});
		}
		
		//单张隐藏上传按钮
		if(!obj.pick.multiple) j(obj.pick.id).addClass('hide');
		
		//点击放大
		//j('.file-item img').fsgallery();
	});

	// 文件上传失败，显示上传出错。
	uploader.on( 'uploadError', function( file ) {
		var $li = j( '#'+file.id ),
			$error = $li.find('div.error');

		// 避免重复创建
		if ( !$error.length ) {
			$error = j('<div class="error"></div>').appendTo( $li );
		}

		$error.text('上传失败');
		//单张隐藏上传按钮
		if(!obj.pick.multiple) j(obj.pick.id).addClass('hide');
	});

	// 完成上传完了，成功或者失败，先删除进度条。
	uploader.on( 'uploadComplete', function( file ) {
		j( '#'+file.id ).find('.progress').remove();
	});

}

/**
 * 点击放大
 * @param string pic  大图地址
 */
manage.enlargePic = function(pic,dom){
	if(typeof pic== 'object' && j(pic).parents('.thumbnail').length && j(pic).parents('.thumbnail').attr('fullname')){
		pic = j(pic).parents('.thumbnail').attr('fullname').replace(/^\./,'');
	}
		
	var html = '<div class="enlargePic"><img src="' + pic + '" /></div>';
	//如果无图
	if(dom && j(dom).attr('src').indexOf('default.jpg') !== -1){
		layer.msg('没有图像！');
		return;
	}
	layer.open({
	  type: 1,
	  title: false,
	  closeBtn: 0,
	  area: '800px',
	  skin: 'layui-layer-nobg', //没有背景色
	  shadeClose: true,
	  content: html
	});
}

/**
 * 删除上传的图片,EXP:1.删除刚选择的图片manage.delImage({dom:this,onlyFile:1});
 * EXP:2.删除已经上传并且保存到数据库的图片：manage.delImage({dom:this,type:'goods'})
 */
manage.delImage = function(obj){
	obj = obj ? obj : {};//Object
	obj.dom = obj.dom || '';//DOM,必须在调用时候指定为this
	var thumbnail = j(obj.dom).parents('.thumbnail');
	// obj.url = obj.url || '/Qwadmin/setting/imagedel';//请求地址
	obj.url = obj.url || '/index.php/wap/member-imagedel.html';//请求地址
	obj.id = obj.id || thumbnail.attr('id');//图片数据表行id
	obj.fullName = obj.fullName || thumbnail.attr('fullName');//图片或文件全称
	obj.type = obj.type || '';//删除类型,扩展目录名称
	obj.onlyFile = obj.onlyFile || '';//是否只删除图片或者文件
	
	var wapPicker = j(obj.dom).closest('#uploader-box').find('.wapPicker');//按钮
	
	//如果上传失败,则只删除前台图片即可
	if(thumbnail.find('.error').length){
		thumbnail.remove();
		//如果单图片上传按钮被隐藏,则显示出来
		if(wapPicker.hasClass('hide')){
			wapPicker.removeClass('hide');
		}		
	}else{
		layer.confirm('确定要删除吗？',function(){
			var index = layer.load();
			j.getJSON(obj.url,{id:obj.id,fullName:obj.fullName,type:obj.type,onlyFile:obj.onlyFile},function(data){
				if(data.error == 0){
					thumbnail.remove();					
					//如果单图片上传按钮被隐藏,则显示出来
					if(wapPicker.hasClass('hide')){
						wapPicker.removeClass('hide');
					}					
				}else{
					layer.msg(data.message,{icon: 5});
				}
				layer.closeAll();
			});
		});		
	}
	
}

//获取所有已经上传的图片，数据在fileList DOM 的data属性里面
manage.getAllUpfile=function(obj){
	obj = obj ? obj : {};//Object
	obj.listBox = obj.listBox || '.uploader-list';//容器
	var datas = {};
	j(obj.listBox).each(function(){
		var files = j(this).find('.thumbnailFiles');//容器里面的图片集
		var type = j(this).attr('type');//提交的文件域名称
		var tempDatas = [];
		files.each(function(){
			tempFile = {};
			tempFile.folder = j(this).attr("folder");
			tempFile.savetype = j(this).attr("savetype");
			tempFile.saveFile = j(this).attr("saveFile");
			tempFile.fullName = j(this).attr("fullName");
			tempDatas.push(tempFile);
		})
		datas[type] = tempDatas;
	})
	return datas;
}

//上传图片
manage.imgerror = function(obj){
	obj.src= '/public/files/static/images/default.png';
}