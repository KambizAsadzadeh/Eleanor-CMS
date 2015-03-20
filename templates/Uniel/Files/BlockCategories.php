<?php
/** Оформление блока категорий
 * @var string $images HTTP путь к картинкам шаблона
 * @var string $var_0 категории, без начального <ul>, представляет собой последовательность
 * <li><a...>...</a><ul><li>...</li></ul></li></ul> */

defined('CMS\STARTED')||die;
$u=uniqid();?>
<ul class="blockcategories" id="<?=$u?>"><?=$var_0?><script>//<![CDATA[
$(function(){
	$("#<?=$u?> li:has(ul)").addClass("subcat").each(function(i){
		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"<?=$images?>.'minus.gif",title:"+"})
				.prependTo(this).click(function(){

			if(localStorage.getItem("bc"+i))
			{
				$(this).prop({src:"<?=$images?>plus.gif",title:"+"}).next().next().fadeOut("fast");
				localStorage.removeItem("bc"+i);
			}
			else
			{
				$(this).prop({src:"<?=$images?>minus.gif",title:"&minus;"}).next().next().fadeIn("fast");
				try
				{
					localStorage.setItem("bc"+i,"1");
				}catch(e){}
			}
		});

		if(!localStorage.getItem("bc"+i))
			img.prop({src:"<?=$images?>plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");
});//]]></script>';