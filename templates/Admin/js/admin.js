/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
function ItemsDelete()
{
	$(document).on("click","a.delete",function(e){
		e.preventDefault();
		var th=$(this);

		$("#delete-title").text( th.closest("div").find("a:first").text() );
		$("#delete").find("form").attr("action",th.attr("href")).end().modal("show");
	});

	var delete_skip=false,
		main_go=false;
	$("#delete").on("hidden.bs.modal",function(){
		delete_skip=false;
		main_go=false;
	}).find("form").on("submit",function(e){
		if(delete_skip)
		{
			e.preventDefault();
			$("#items-form").submit();
		}
	});
	$("#items-form").on("submit",function(e){
		if(main_go)
			return true;

		if($("#items-event").val()=="delete")
		{
			e.preventDefault();

			main_go=true;
			delete_skip=true;

			var titles=[];

			$(".selected h4 a",this).each(function(){
				titles.push($(this).text());
			});

			$("#delete-title").text( titles.join('", "') );
			$("#delete").modal("show");
		}
	});
};

function TableList()
{
	$(".table.table-list th.col_status a").addClass("istatus");
	$(".table.table-list .istatus").each(function(){
		var th=$(this),
			st=th.data("status");

		if(st)
			$(this).closest("tr").addClass(st);
	});
};

function ItemsForm()
{
	var items=$('[name="items[]"]'),
		to_disable=$("#items-event,#items-submit").prop("disabled",!items.is(":checked"));

	$("#items-form")
		.on("change",'[name="items[]"]',function(){
			var th=$(this);

			if(th.prop("checked"))
				th.closest("tr").addClass("selected");
			else
				th.closest("tr").removeClass("selected");

			to_disable.prop("disabled",!items.is(":checked"));
		})
		.on("click",".table.table-list tbody tr",function(e){
			if($(e.target).is(":not(a,img,input,.pos-lines,.col_pos)"))
			{
				$(e.target).closest("tr").find(":checkbox").each(function(i, v)
				{
					$(this).prop("checked", !$(this).prop("checked")).change();
				});

				e.stopPropagation();
			}
		}).find(".table.table-list tbody tr").has(":checked").addClass("selected");

	var submit=$("#items-submit");
	$("#items-event").change(function(){
		submit.removeClass("btn-danger btn-success btn-warning");
		switch($(this).val())
		{
			case"delete":
				submit.addClass("btn-danger");
			break;
			case"activate":
				submit.addClass("btn-success");
			break;
			case"deactivate":
				submit.addClass("btn-warning");
		}
	}).change();

	One2AllCheckboxes("#items-form","#mass-check",'[name="items[]"]',true);
};

function ParentsWithPos()
{
	var children={},
		poses={},
		pos=$("#pos"),
		block=false,
		SetPos=function(html){
			pos.html(html).select2("enable", pos.has("optgroup").size()>0 ).trigger("change");
		};

	$(document).on("change","select.parents",function(){
		if(block)
			return;

		var th=$(this),
			val=th.val(),
			isparent=$(":selected",this).data("children");

		if(!isparent)
			th.nextAll("select").select2("destroy").remove();

		//Действие при удалени "последнего" родителя
		if(val)
			th.prop("name","parent");
		else
		{
			th.removeAttr("name");
			val=th.data("parent")||"";

			if(val && (val in poses))
			{
				SetPos(poses[val]);
				return;
			}
		}

		if(isparent || !(val in poses))
		{
			if(isparent)
			{
				block=true;
				th.nextAll("select:gt(0)").select2("destroy").remove();

				var next=th.nextAll("select"),F;

				if(next.size()==0)
					next=th.clone(false).empty().html("<option></option>").removeAttr("id").removeAttr("tabindex").removeAttr("name")
						.prop("placeholder",CORE.Lang("loading")).addClass("need-tabindex").insertAfter(th)
						.data("parent",val).select2({allowClear: true}).select2("enable",false);
			}

			pos.select2("enable",false);

			F=function(){
				if(isparent)
				{
					next.html(children[val]).prop("placeholder",th.prop("placeholder")).select2("enable",true).trigger("change");
					block=false;
				}

				SetPos(poses[val]);
			};

			if((val in children)&&(val in poses))
				F();
			else
				$.get(location,{parent:val},function(r){
					if(r.children)
					{
						children[val]=r.children;
						poses[val]=r.poses;
						F();
					}
					else
					{
						if(val)
							th.find(":selected").data("children",false).end().nextAll("select").select2("destroy").remove();

						children[val]="";
						poses[val]=r.poses;
						F();
					}
				},"json");
		}
		else if(val)
			pos.val(0).find("optgroup").remove().end().trigger("change").select2("enable",false);
		else
			SetPos(poses[""]);
	});

	var par=0;
	$("select.parents").each(function(){
		if(par>0)
			children[par]=$(this).html();
		par=parseInt($(this).val());

		if(isNaN(par))
			$(this).removeAttr("name");
	}).filter("[name=parent]:last").each(function(){
		poses[ $(this).val() ]=pos.html();
	});

	pos.select2("enable",pos.has("optgroup").size()>0);
};

function Pim()
{//Отключение pim (Post If Modified) полей при сабмите формы
	$(".pim:input").each(function(){
		$(this).data("default-value",$(this).prop("defaultValue"));
	});
	$(document).on("submit","form",function(){
		var pims=$(".pim:input",this).each(function(){
			var th=$(this);

			if(th.is("select"))
			{
				var opts=th.find("option:selected"),
					dis=true;

				if(opts.size()>0)
				{
					opts.each(function(){
						if(!this.defaultSelected)
						{
							dis=false;
							return false;
						}
					});

					if(dis)
						th.prop("disabled",true);
				}
			}
			else if(th.val()===th.data("default-value"))
				th.prop("disabled",true);
		});

		setTimeout(function(){
			pims.prop("disabled",false);
		},200);
	});

};

/** Живое превращение строки в URI
 * @param source jQuery Источник
 * @param uri jQuery Получатель URI
 * @param Translit Function Callback транслита
 * @param rep string Замена пробелов */
function Source2Uri(source,uri,Translit,rep)
{
	var olduri=uri.val(),
		qrep=rep.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"),
		Adopt=function(val){
			if(Translit)
				val=Translit(val);

			return val.split("&").join("and")
				.replace(/[=\s#,"\'\\/:*\?!&\+<>%\|`]+/ig,rep)
				.replace(new RegExp("("+qrep+")+","ig"),rep)
				.replace(new RegExp("^("+qrep+")+|("+qrep+")+$","ig"),"").toLowerCase();
		};

	source.on("input",function(){
		if(olduri!=uri.val())
			return;

		var val=Adopt(source.val());

		olduri=val;
		uri.val(val);
	});

	uri.blur(function(){
		var th=$(this),
			val=th.val();
		val=Adopt(val);
		th.val(val);

		if(val=="")
			olduri="";
	});
};

/** Инициализация вложенных подпунктов (для подкатегорий, подменю, подгрупп)
 * @param sync_n Число столбцов справа, которые нужно синхронизировать по ширине */
function InitTableSubitems(sync_n)
{
	var n=0,
		Apply=function(i,a)
		{
			a=$(a);
			var table=a.closest("table"),
				parent=table.attr("id"),
				tr=a.closest("tr"),
				target;

			if(!parent)
			{
				parent="table-"+(n++);
				table.attr("id",parent);
			}

			if(tr.next().is("tr.td_collapse_row"))
				target=tr.next().find(".td_collapse_row_box:first").attr("id");
			else
			{
				var cols=0;
				table.find("thead th,thead td").each(function(i,v){
					cols+=$(v).attr("colspan")||1;
				});

				target="div-"+n+"-"+i;
				$("<tr class='td_collapse_row'>").insertAfter(tr).append(
					$("<td>").attr("colspan",cols).append(
						$("<div class='td_collapse_row_box collapse'>").attr("id",target)
					)
				);

				a.click(function(e){
					e.preventDefault();

					if(a.data("loaded"))
						return;

					e.stopPropagation();
					CORE.Ajax(a.attr("href"),function(html){
						a.data("loaded",true);

						var whole=$(a.data("target")).html(html),
							ths=whole.find("thead.empty th"),
							ths_cnt=ths.size();

						whole.collapse('show');
						whole.find("table a.td_collapse_link").filter(function(){
								return !$(this).data("toggle") && !$(this).data("parent");
							}).each(Apply);

						ths.each(function(j,th){
							if(j>=ths_cnt-sync_n)
								$(th).width( table.find("thead tr *:eq("+j+")").outerWidth() );
						});

						//Проставим checkbox-ы
						table.find('[name="items[]"]:first').change();

						//Уведомим о создании новых подэлементов
						a.trigger("subitems",[whole]);
					});
				});
			}

			a.attr({"data-parent":"#"+parent,"data-target":"#"+target,"data-toggle":"collapse","aria-expanded":"false"}).addClass("collapsed")
				.prepend('<span class="glyphicon glyphicon-plus"></span><span class="glyphicon glyphicon-minus"></span> ');
		};

	$("table a.td_collapse_link").filter(function(){
		return !$(this).data("toggle") && !$(this).data("parent");
	}).each(Apply);

};

$(function(){
	//Автоматическое проставление tabindex
	$(".need-tabindex").prop("tabindex",function(i){
		return i;
	}).removeClass("need-tabindex");

	//Переключение языка при событии switch
	$(document).on("switch",".lang-tabs a",function(){
		$(this).tab("show");
	});

	//Реализация работоспособности <label> при включенной мультиязычности
	$("ul.lang-tabs[data-for] a").on("shown.bs.tab",function(){
		var th=$(this),
			name=th.closest("ul").data("for"),
			lang=th.data("language");

		$("#label-"+name).attr("for",name+"-"+lang);
	}).closest("li").filter(".active").find("a").trigger("shown.bs.tab");

	$(".list-pager").each(function(){
		var ajax=$(this).data("ajax"),
			input=$("input",this),
			button=$("button",this);

		if(ajax)
		{
			$("a",this).click(function(e){
				e.preventDefault();
				var page=parseInt($(this).text());
				eval(ajax+"(page)");
			});

			button.click(function(e){
				var page=parseInt(input.val());

				if(page>0)
					eval(ajax+"(page)");
			});
		}
		else
		{
			var blank=$(this).data("blank");

			button.click(function(e){
				var page=parseInt(input.val());

				if(page>0)
					location.href=blank.replace("{page}",page);
			});
		}

		input.keypress(function(e){
			if(e.which==13 || e.which==10)
			{
				e.preventDefault();
				button.click();
			}
		})
	});

	//Подключение кнопок BB редактора (генерация события bb)
	$(document).on("click",".bb-top > button.bb:has(span)",function(e){
		e.preventDefault();
		var bb=$("span",this).prop("className").match(/\-([a-z0-9]+)+$/)[1];
		$(this).trigger("bb",[bb]);
	}).on("click",".bb-top .bb-fontsize a",function(e){
		e.preventDefault();
		var bb=$(this).parent().prop("className").match(/\-([a-z0-9]+)+$/)[1];

		if(bb=="custom")
		{
			bb=prompt(CORE.Lang("input_size"),"");
			if(!bb)
				return;
		}

		$(this).trigger("bb",["size",bb]);
	})

	//Предпросмотр BB редактора
	.on("click","button.bb:has(.ico-view)",function(e){
		e.preventDefault();
		var th=$(this);

		CORE.ShowLoading();
		$.post(th.data("url"),{preview:th.closest(".bb-editor").find("textarea").val()},function(r){
			$("#bb-preview").remove();
			$("body").append(r.modal);

			var modal=$("#bb-preview"),
				iframe=modal.find("iframe"),
				d=iframe.get(0).contentWindow.document;
			modal.find("h4:first").text(th.prop("title"));

			d.open("text/html","replace");
			d.write(r.html);
			d.close();

			modal.on("shown.bs.modal",function(){
				iframe.height($("html",d).height());
			}).modal('show').on("hidden.bs.modal",function(){
				modal.remove();
			});
		}).always(CORE.HideLoading);
	})

	//Вход в полноэкранный режим и выход из него
	.on("click","button.bb:has(.ico-fullscreen)",function(e){
		e.preventDefault();

		var th=$(this).closest(".bb-editor").toggleClass("fullscreen").end();

		if($("body").toggleClass("editor-fullscreen").hasClass("editor-fullscreen"))
			$(document).keyup(function(e){
				if(e.keyCode == 27)
				{
					th.click();
					$(this).off(e);
				}
			});
		/*var fs=$(this).closest(".bb-editor").get(0),
			d=document;

		if(d.fullscreenElement || d.mozFullScreenElement || d.webkitFullscreenElement || d.msFullscreenElement )
		{
			if(d.exitFullscreen)
				d.exitFullscreen();
			else if(d.msExitFullscreen)
				d.msExitFullscreen();
			else if(d.mozCancelFullScreen)
				d.mozCancelFullScreen();
			else if(d.webkitExitFullscreen)
				d.webkitExitFullscreen();
		}
		else if(fs.requestFullscreen)
			fs.requestFullscreen();
		else if(fs.msRequestFullscreen)
			fs.msRequestFullscreen();
		else if(fs.mozRequestFullScreen)
			fs.mozRequestFullScreen();
		else if(fs.webkitRequestFullscreen)
			fs.webkitRequestFullscreen();*/
	});

	//Аплоадер: фикс показа меню файлов
	var prevdm;
	$(".el-uploader").on('shown.bs.dropdown',".min-edit",function(){
		if(prevdm)
			prevdm();

		var th=$(this),
			dm=$(".dropdown-menu",this),
			offset=dm.offset();

		dm.appendTo("body").css(offset).show();

		prevdm=function(){
			dm.appendTo(th).attr("style","");
			prevdm=null;
		};
		$(document).one("click",prevdm);
	});

	//Аплоадер: подддержка drag and drop и прямого выбора файлов
	$(".el-uploader .upl-loadframe").on("dragover",function(e){
		$(this).addClass("active");
		e.dataTransfer.dropEffect="move";
		e.stopPropagation();
		return false;
	})
	.on("dragleave",function(){
		$(this).removeClass("active");
	})
	.on("drop",function(e){
		$(this).removeClass("active");

		angular.element(this).scope().Upload(e.dataTransfer.files);

		e.stopPropagation();
		return false;
	})
	.find("button").click(function(e){
		e.preventDefault();
		$(this).parent().find(":file").click();
	}).end()
	.find(":file").change(function(){
		angular.element(this).scope().Upload(this.files);

		var th=$(this);
		setTimeout(function(){ th.val(""); },500);
	});

	//Предотвращение многократного нажатия на кнопку
	$(".once:submit",this).click(function(){
		setTimeout(function(){
			$(this).prop("disabled",true);
		}.bind(this),100);
	});
});

/*function ProgressList(m,cron)
{
	var progr={},
		ids=[];
	$("progress[data-id]").each(function(){
		progr[$(this).data("id")]=$(this);
		ids.push($(this).data("id"));
	});

	if(ids.length==0)
		return;
	$("<img>").on("load",function(){
		var img=$(this);
		CORE.Ajax(
			{
				direct:"admin",
				file:m,
				event:"progress",
				ids:ids
			},
			function(res)
			{
				if(!res)
				{
					location.reload();
					return;
				}
				var emp=true;
				for(var i in res)
				{
					if(typeof progr[i]=="undefined")
						continue;
					progr[i].val(res[i].val).attr("max",res[i].total).attr("title",res[i].percent+"%").find("span").text(res[i].percent);
					if(res[i].done)
						delete(progr[i]);
					else
						emp=false;
				}
				if(emp)
				{
					location.reload();
					return;
				}
				setTimeout(
					function(){
						img.attr("src",cron+"?rand="+Math.random())
					},
					10000
				);
			}
		);
	}).attr("src",cron+"?rand="+Math.random());
}*/