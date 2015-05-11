/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
function CreateEdit(next_add)
{
	$(".form-group input:not(:checkbox,[class*=select2]),textarea,select").addClass("form-control pim");

	//Заголовок каждого из блоков
	$("ul.lang-tabs[data-for] a").on("shown.bs.tab",function(){
		$(this).closest(".bcont").find("input[name*='[title]']:visible").trigger("input");
	});

	//Изменение заголовка
	$(document).on("input","input[name*='[title]']:visible",function(e){
		$(this).closest(".block-t").find(".site-title").text( $(this).val() );
	}).find("input[name*='[title]']:visible").trigger("change");

	//Удаление возможных ошибок
	$(document).on("input","input[name*='[prefix]'],input[name*='[host]'],input[name*='[db]'],input[name*='[user]'],input[name*='[pass]']",function(){
		$(this).closest(".form-group").removeClass("has-error has-success");
	});

	//Добавление сайта
	$("#add-site").click(function(e){
		e.preventDefault();

		var source=$("#mainbar .block-t:last");
		source.clone()
			.find("[name]").attr("name",function(i,n){
				return n.replace(/\[\d+\]/,"["+next_add+"]");
			}).end()
			.find("[id]").attr("id",function(i,id){
				return id.replace(/\-\d+/,"-"+next_add);
			}).end()
			.find("[for]").attr("for",function(i,id){
				return id.replace(/\-\d+/,"-"+next_add);
			}).end()
			.find("[data-for]").attr("data-for",function(i,id){
				return id.replace(/\-\d+/,"-"+next_add);
			}).end()
			.find(":input:not(select,:button)").val("").end()
			.insertAfter(source)
			.find("input[name*='[title]']:visible").trigger("input").end();

		next_add++;
	});

	$("#mainbar")
		//Удаление сайта
		.on("click",".delete-site",function(e){
			e.preventDefault();
			e.stopPropagation();

			var num=$("#mainbar .delete-site").size(),
				base=$(this).closest(".block-t");

			if(num>1)
				base.remove();
			else
				base.find(":input:not(select,:button)").val(function(){
					return $(this).data("default")||"";
				});
		})

		//Кнопка проверки
		.on("click",".button-check",function(e){
			e.preventDefault();

			var block=$(this).closest(".bcont"),
				prefix=block.find("input[name*='[prefix]']"),
				fields={
					host:block.find("input[name*='[host]']"),
					db:block.find("input[name*='[db]']"),
					user:block.find("input[name*='[user]']"),
					pass:block.find("input[name*='[pass]']")
				},
				data={},status;

			$.each(fields,function(i,v){
				data[i]=$.trim(v.val());
			});

			status=data.host && data.db && data.user
				? prefix.add(fidels.host).add(fidels.db).add(fidels.user).add(fidels.pass)
				: prefix;

			CORE.Ajax({
					data:data.host && data.db && data.user ? data : {},
					prefix:prefix.val()
				},function(r){
					status.closest(".form-group").removeClass("has-error").addClass("has-success");
				},function(error){
					alert(error);
					status.closest(".form-group").removeClass("has-success").addClass("has-error");
				}
			);
		});

	//Сабмит формы
	$("#create-edit :submit").click(function(e){
		if($("#mainbar .delete-site").size()==1)
		{
			var inputs=$("#mainbar .block-t :input:enabled"),
				pass=true;

			inputs.each(function(){
				if($(this).is(":checkbox"))
					return;

				var val=$(this).val();

				if(val && val!==$(this).data("default"))
				{
					console.log(val);
					pass=false;
					return false;
				}
			});

			if(pass)
			{
				inputs.prop("disabled",true);

				setTimeout(function(){
					inputs.prop("disabled", false);
				}, 200);
			}
		}
	});
}