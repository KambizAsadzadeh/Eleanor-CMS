/*
	Copyright В© Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var module;
$(function(){
	$("#main-content").on("click",".getmore",function(){
		var th=$(this);
		if(!th.data("id") || !th.data("more"))
			return;

		var te=$(th.data("more")),
			l=th.data("lang");
		if(th.data("has"))
		{
			if(te.is(":visible"))
				te.fadeOut();
			else
				te.fadeIn();

			th.data("lang",th.text()).text(l);
		}
		else
			CORE.Ajax(
				{
					module:module,
					event:"getmore",
					id:th.data("id")
				},
				function(r)
				{
					te.html(r).fadeIn();
					th.data("has",true).data("lang",th.text()).text(l);
				}
			);
		return false;
	})
})