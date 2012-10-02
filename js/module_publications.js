/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.Publications={
				te=$($(this).data("more"));
			if(th.data("has"))
			{
				if(te.is(":visible"))
					te.fadeOut();
				else
					te.fadeIn();

				th.toggleClass("getmore getmore-active");
			}
			else
				CORE.Ajax(
					{
						event:"getmore",
						id:th.data("id")
					function(r)
					{
						th.toggleClass("getmore getmore-active").data("has",true);
					}
				);
			return false;
}