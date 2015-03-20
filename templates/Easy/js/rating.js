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
{	var Rate=function(m,id,obj)
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

	$(".rate-bt:has(a.rt-bt-min)").each(function(){		var th=$(this)
			id=th.data("id");

		th.on("click","a.rt-bt-min",function(){			Rate(min,id,th);
			return false;		}).on("click","a.rt-bt-pls",function(){
			Rate(max,id,th);
			return false;
		})	});}