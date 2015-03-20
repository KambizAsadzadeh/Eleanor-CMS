<?php
/** Оформление содержимого блока "Вертикальное раскрывающееся меню"
 * @var string $images HTTP путь к картинкам шаблона
 * @var string $var_0 меню, без начального <ul>, представляет собой последовательность
 * <li><a...>...</a><ul><li>...</li></ul></li></ul> */
defined('CMS\STARTED')||die;

$u=uniqid();
echo'<nav><ul class="blockcategories" id="',$u,'">',$var_0,'</nav><script>//<![CDATA[
$(function(){
	$("#',$u,' li:has(ul)").addClass("subcat").each(function(i){
		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"',$images,
		'minus.gif",title:"+"}).prependTo(this).click(function(){
			if(localStorage.getItem("bm"+i))
			{
				$(this).prop({src:"',$images,'images/plus.gif",title:"+"}).next().next().hide();
				localStorage.removeItem("bm"+i);
			}
			else
			{
				$(this).prop({src:"',$images,'images/minus.gif",title:"&minus;"}).next().next().show();
				try
				{
					localStorage.setItem("bm"+i,"1");
				}catch(e){}
			}
		});
		if(!localStorage.getItem("bm"+i))
			img.prop({src:"',$images,'images/plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");
})//]]></script>';