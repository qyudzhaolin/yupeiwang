(function($) {
	$.fn.extend({
		diyUpload: function(opt, serverCallBack) {
			if(typeof opt != "object") {
				alert('参数错误!');
				return;
			}
			var $fileInput = $(this);
			var $fileInputId = $fileInput.attr('id');
			if(opt.url) {
				opt.server = opt.url;
				delete opt.url;
			}
			if(opt.success) {
				var successCallBack = opt.success;
				delete opt.success;
			}
			if(opt.error) {
				var errorCallBack = opt.error;
				delete opt.error;
			}
			$.each(getOption('#' + $fileInputId), function(key, value) {
				opt[key] = opt[key] || value;
			});
			if(opt.buttonText) {
				opt['pick']['label'] = opt.buttonText;
				delete opt.buttonText;
			}
			var webUploader = getUploader(opt);
			if(!WebUploader.Uploader.support()) {
				alert(' 上传组件不支持您的浏览器！');
				return false;
			}
			webUploader.on('fileQueued', function(file) {
				createBox($fileInput, file, webUploader);
			});
			webUploader.on('uploadProgress', function(file, percentage) {
				var $fileBox = $('#fileBox_' + file.id);
				var $diyBar = $fileBox.find('.diyBar');
				$diyBar.show();
				percentage = percentage * 100;
				showDiyProgress(percentage.toFixed(2), $diyBar);
			});
			webUploader.on('uploadFinished', function() {
				$fileInput.next('.parentFileBox').children('.diyButton').remove();
			});
			webUploader.on('uploadAccept', function(object, data) {
				if(serverCallBack) serverCallBack(data);
			});
			webUploader.on('uploadSuccess', function(file, response) {
				var $fileBox = $('#fileBox_' + file.id);
				var $diyBar = $fileBox.find('.diyBar');
				$fileBox.removeClass('diyUploadHover');
				$diyBar.fadeOut(1000, function() {
					$fileBox.children('.diySuccess').show();
				});
				if(successCallBack) {
					successCallBack(response);
				}
			});
			webUploader.on('uploadError', function(file, reason) {
				var $fileBox = $('#fileBox_' + file.id);
				var $diyBar = $fileBox.find('.diyBar');
				showDiyProgress(0, $diyBar, '上传失败!');
				var err = '上传失败! 文件:' + file.name + ' 错误码:' + reason;
				if(errorCallBack) {
					errorCallBack(err);
				}
			});
			webUploader.on('error', function(code) {
				var text = '';
				switch(code) {
					case 'F_DUPLICATE':
						text = '该文件已经被选择了!';
						break;
					case 'Q_EXCEED_NUM_LIMIT':
						text = '上传文件数量超过限制!';
						break;
					case 'F_EXCEED_SIZE':
						text = '文件大小超过限制!';
						break;
					case 'Q_EXCEED_SIZE_LIMIT':
						text = '所有文件总大小超过限制!';
						break;
					case 'Q_TYPE_DENIED':
						text = '文件类型不正确或者是空文件!';
						break;
					default:
						text = '未知错误!';
						break;
				}
				alert(text);
			});
			return webUploader;
		}
	});

	function getOption(objId) {
		return {
			pick: {
				id: objId,
				label: ""
			},
			accept: {
				title: "Images",
				extensions: "gif,jpg,jpeg,bmp,png",
				mimeTypes: "image/*"
			},
			thumb: {
				width: 160,
				height: 120,
				quality: 90,
				allowMagnify: false,
				crop: true,
				type: "image/jpeg"
			},
			method: "POST",
			server: "",
			sendAsBinary: false,
			chunked: true,
			chunkSize: 512 * 1024,
			fileNumLimit: 50,
			fileSizeLimit: 500000 * 1024,
			fileSingleSizeLimit: 50000 * 1024,
		};
	}

	function getUploader(opt) {
		return new WebUploader.Uploader(opt);
	}

	function showDiyProgress(progress, $diyBar, text) {
		if(progress >= 100) {
			progress = progress + '%';
			text = text || '上传完成';
		} else {
			progress = progress + '%';
			text = text || progress;
		}
		var $diyProgress = $diyBar.find('.diyProgress');
		$diyProgress.width(progress).text(text);
	}

	function removeLi($li, file_id, webUploader) {
		webUploader.removeFile(file_id);
		$li.remove();
	}

	function leftLi($leftli, $li) {
		$li.insertBefore($leftli);
	}

	function rightLi($rightli, $li) {
		$li.insertAfter($rightli);
	}

	function createBox($fileInput, file, webUploader) {
		var file_id = file.id;
		var $parentFileBox = $fileInput.parents(".upload-ul");
		var file_len = $parentFileBox.children(".diyUploadHover").length;
		var li = '<li id="fileBox_' + file_id + '" class="diyUploadHover"> \
					<div class="viewThumb">\
					    <input type="hidden">\
					    <div class="diyBar"> \
							<div class="diyProgress">0%</div> \
					    </div> \
					    <p class="diyControl"> <span class="diyCancel"><i></i></span></p>\
					</div> \
				</li>';
		$parentFileBox.prepend(li);
		var $fileBox = $parentFileBox.find('#fileBox_' + file_id);
		var $diyCancel = $fileBox.find('.diyCancel').one('click', function() {
			removeLi($(this).parents('.diyUploadHover'), file_id, webUploader);
		});
		$fileBox.find('.diyLeft').on('click', function() {
			leftLi($(this).parents('.diyUploadHover').prev(), $(this).parents('.diyUploadHover'));
		});
		$fileBox.find('.diyRight').on('click', function() {
			rightLi($(this).parents('.diyUploadHover').next(), $(this).parents('.diyUploadHover'));
		});
		if(file.type.split("/")[0] != 'image') {
			var liClassName = getFileTypeClassName(file.name.split(".").pop());
			$fileBox.addClass(liClassName);
			return;
		}
		webUploader.makeThumb(file, function(error, dataSrc) {
			if(!error) {
				$fileBox.find('.viewThumb').append('<img src="' + dataSrc + '" >');
			}
		});
	}

	function getFileTypeClassName(type) {
		var fileType = {};
		var suffix = '_diy_bg';
		fileType['pdf'] = 'pdf';
		fileType['ppt'] = 'ppt';
		fileType['doc'] = 'doc';
		fileType['docx'] = 'doc';
		fileType['jpg'] = 'jpg';
		fileType['zip'] = 'zip';
		fileType['rar'] = 'rar';
		fileType['xls'] = 'xls';
		fileType['xlsx'] = 'xls';
		fileType['txt'] = 'txt';
		fileType = fileType[type] || 'ppt';
		return fileType + suffix;
	}
	
    //上传图片
        var $tgaUpload = $('#goodsUpload').diyUpload({
            url:'/uploadFilePath',
            success:function( data ) { },
            error:function( err ) { },
            buttonText : '',
            accept: {
                title: "Images",
                extensions: 'gif,jpg,jpeg,bmp,png'
            },
            thumb:{
                width:120,
                height:90,
                quality:100,
                allowMagnify:true,
                crop:true,
                type:"image/jpeg"
            }
        });	
	
})(jQuery);