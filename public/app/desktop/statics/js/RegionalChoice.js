region = {};
/*模拟数据*/
region.GetRegionPlug = function (datas) {
    j(".regionContent").append(
        j("<div/>",{
            "class":"place-div"
        }).append(
            j("<div/>",{}).append(
                j("<div/>",{
                    "class":"checkbtn hide"
                }).append(
                    j("<a/>",{
                        "class":"allcheck",
                        "text":"全选",
                        "click":function () {
                            j(".place").find("input").prop("checked",true);
                            region.ShowTipNum();
                        }
                    }).append(
                        j("<img/>",{
                            "src":"img/allcheck.png"
                        })
                    )
                ).append(
                    j("<a/>",{
                        "class":"inStead",
                        "text":"反选",
                        "click":function () {
                            j(".place").find("input").each(function(index,a){
                                if(j(this).prop("checked")){
                                    j(this).prop("checked",false);
                                }else{
                                    j(this).prop("checked",true);
                                }
                                j(".ratio").html("");
                            })
                            region.ShowTipNum();
                        }
                    }).append(
                        j("<img/>",{
                            "src":"img/fanxuan.png"
                        })
                    )
                ).append(
                    j("<a/>",{
                        "class":"ri clearCheck",
                        "text":"清空",
                        "click":function () {
                            j(".place").find("input").prop("checked",false);
                            j(".ratio").html("");
                        }
                    }).append(
                        j("<img/>",{
                            "src":"img/clearcheck.png"
                        })
                    )
                )
            ).append(
                j("<div/>",{
                    "class":"placegroup"
                }).append(
                    region.GetPlace(datas)
                )
            )
        )
    )
}

region.GetPlace = function(datas){
	//console.log(datas);
	return datas.map(function(item){
        //console.log(item);
        return j("<div/>",{
            "class":"place clearfloat"
        }).append(
        	j("<div/>",{
        		"class":"bigplace"
			}).append(
				j("<div/>",{}).append(
					j("<label/>",{
						"text":item.name
					}).append(
                        j("<input/>",{
                            "id":item.id,
                            "name":item.name,
                            "type":"checkbox",
                            "class":"bigarea",
							"click":function () {
                                var bool = j(this).prop("checked");
                                var single = j(this).parents(".bigplace").next().find("input");
                                var ee = j(this).parents(".bigplace").next().find(".place-tooltips");
                                single.prop("checked",bool);
                                if(single.prop("checked")){
                                    ee.each(function(index,a){
                                        var num = j(this).find(".citys").find("input").length;
                                        j(this).find(".ratio").html("("+num+"/"+num+")");
                                    })
                                }else{
                                    ee.each(function(index,a){
                                        var num = j(this).find(".citys").find("input").length;
                                        j(this).find(".ratio").html("");
                                    })
                                }
                            }
                        })
					)
				)
			)
		).append(
            function(){
                if(item.children){
                    return region.GetSmallPlace(item.children)
                }
            }()
        )
    })
}
region.GetSmallPlace = function(datas) {
	return j("<div/>",{
		"class":"smallplace clearfloat"
	}).append(
        datas.map(function (item) {
            return	j("<div/>",{
					"class":"place-tooltips"
				}).append(
					j("<label/>",{
						"text":item.name
					}).append(
						j("<input/>",{
                            "id":item.id,
                            "name":item.name,
							"type":"checkbox",
							"class":"bigcity",
							"click":function () {
                                var small = j(this).parent().next(".citys").find("input");
                                var smalllength = small.length;
                                var ee = j(this).parents(".smallplace").find(".ratio");
                                if(j(this).prop("checked")){
                                    small.prop("checked",true);
                                    j(this).parents(".place-tooltips").find(".ratio").html("("+smalllength+"/"+smalllength+")");
                                    //j(this).parents(".smallplace").prev().find(".bigarea").prop("checked",true);
                                }else{
                                    small.prop("checked",false);
                                    j(this).parents(".place-tooltips").find(".ratio").html("");
                                    region.ClearArea(j(this).parents(".smallplace"),j(this).parents(".smallplace").prev().find(".bigarea"));
                                };
                            }
						})
					).append(
                        function () {
                            if(item.children){
                                return j("<span/>",{
                                    "class":"ratio"
                                })
                            }
                        }
					)
				).append(
					function () {
                        if(item.children){
                            return j("<div/>",{
                                "class":"citys"
                            }).append(
                                j("<i/>",{
                                    "class":"jt"
                                }).append(j("<i/>",{}))
                            ).append(
                                  region.GetSmallCitys(item.children)
                            )
                        }
                    }

				)
        })
	)
}

region.GetSmallCitys = function(datas) {
	return j("<div/>",{
		"class":"row-div clearfloat"
	}).append(
		datas.map(function (item) {
            return j("<p/>",{}).append(
                j("<label/>",{}).append(
                    j("<input/>",{
                        "id":item.id,
                        "name":item.name,
                        "type":"checkbox",
                        "class":"city",
						"click":function () {
                            var tf = j(this).parents(".citys").find("input:checked").length;
                            var alltf = j(this).parents(".citys").find("input").length;
                            if(tf > 0){
                                j(this).parents(".place-tooltips").find(".bigcity").prop("checked",true);
                                j(this).parents(".place-tooltips").find(".ratio").html("("+tf+"/"+alltf+")");
                                //j(this).parents(".smallplace").prev().find(".bigarea").prop("checked",true);
                            }else if(tf == 0){
                                j(this).parents(".place-tooltips").find(".bigcity").prop("checked",false);
                                j(this).parents(".place-tooltips").find(".ratio").html("");
                                region.ClearArea(j(this).parents(".smallplace"),j(this).parents(".smallplace").prev().find(".bigarea"));
                            }
                        }
                    })
                ).append(
                    j("<span/>",{
                        "text":item.name
                    })
                )
            )
        })
	)
}


//控制提示个数的显示
region.ShowTipNum = function(){
	var n = j(".place-div").find(".place");
	n.each(function(index,a){
		var m = j(this).find(".place-tooltips");
			m.each(function(index,a){
				var u = j(this).find(".citys").find(".city").length;
				var uu = j(this).find(".citys").find(".city:checked").length;
				if(uu != 0){
					j(this).find(".ratio").html("("+uu+"/"+u+")");
					j(this).find(".bigcity").prop("checked",true);
				}else{
					j(this).find(".ratio").html("");
				}
				
			})

	})
}
//省市区全部取消选择时华北东北等取消选择
region.ClearArea = function(place,area){//参数area为包含省级input的父级div
	var checked = place.find("input:checked").length;
	if(checked == 0){
		area.prop("checked",false);
	}
}

//获取已选中的省市县级id
region.GetChecked = function(){
    // var Checked = [];//先清空数组
    var Checked = {ids:[],names:[]};//先清空
	var n = j(".place-div").find(".place");
	n.each(function(index,a){
        var m = j(this).find(".smallplace");

        //如果全国下所有市都被被选中则直接返回全国ID#TODO#
        var bigarea = j(this).find(".bigarea");
        var citys = j(this).find(".city");
        var citysChecked = j(this).find(".city:checked");
        if(citys.length == citysChecked.length){
            Checked.ids.push(bigarea.attr("id"));
            Checked.names.push(bigarea.attr("name"));
            return Checked;
        }

		m.each(function(index,a){
			var p = j(this).find(".bigcity");
			p.each(function(index,a){
                var s = j(this).parents(".place-tooltips").find(".city");
                var sChecked = j(this).parents(".place-tooltips").find(".city:checked");

                //如果此省下面所有市都被选中了,则保存值为此省ID,下级元素ID不保存#TODO#
				if(j(this).prop("checked")){
				    if(s.length == sChecked.length){
                        Checked.ids.push(j(this).attr("id"));
                        Checked.names.push(j(this).attr("name"));
                        return true;
                    }
                }
                
                //遍历所有市
				s.each(function(index,a){
					if(j(this).prop("checked")){
                        Checked.ids.push(j(this).attr("id"));
                        Checked.names.push(j(this).attr("name"));
                        //console.log(j(this).attr("id"));//此时能获取到已选中的县区级id
					}
				})
			})
		})
	})
    return Checked;
}

//根据从后台获取的已选中的id来显示
region.SetChecked = function(param) {
	j.each(param,function (index,value) {
        j("#"+value).trigger("click");
    })
}

//说明：默认打开页面的数据是放在shipareaButton属性shiparea里面的，而input.shiparea是空，这样就保证无动作提交不更新,
//当编辑配送区域的时候数据会保存到input.shiparea的值中，且会保存在shipareaButton属性shiparea
region.setShipArea = function(dom) {
    var url = 'index.php?app=b2c&ctl=admin_goods_editor&act=get_regions';
    var that = dom;
    j.get(url,{shiparea:j(that).attr('shiparea')},function(result){
        layer.open({
            title: '设置配送范围',
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['950px', '550px'],
            btn: '保存',
            content: result,
            yes: function(index, layero){
                var selectedDatas = region.GetChecked().ids.toString();//ids
                var selectedNames = region.GetChecked().names.toString();//names
                // console.log(selectedDatas);
                j('input.shiparea').val(selectedDatas);
                j(that).attr('shiparea',selectedDatas);
                j('.shipareaBox').html(selectedNames.replace(/,/g,'，'));
                layer.close(index);
            }
        });
    },'html');
}


