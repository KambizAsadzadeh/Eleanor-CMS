/*
	Copyright В© Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

function Rating(module,min,max)
{
	{
		CORE.Ajax(
			{
				module:module,
				event:"rating",
				mark:m,
				id:id
			},
			function(r)
			{
				obj.replaceWith(r);
			}
		);
	}

	$(".rate-bt:has(a.rt-bt-min)").each(function(){
			id=th.data("id");

		th.on("click","a.rt-bt-min",function(){
			return false;
			Rate(max,id,th);
			return false;
		})