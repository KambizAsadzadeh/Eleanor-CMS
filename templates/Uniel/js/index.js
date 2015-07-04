/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
$(function(){
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
	});
});