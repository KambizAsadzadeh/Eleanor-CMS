<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\DynUrl;
defined('CMS\STARTED')||die;
/** Оформление для содержимого блока пользователей онлайн. Содержимое блока в файле Classes/Users.php UsersOnline */?>
<div id="who-online"><?=T::$lang['loading']?></div><a href="#"><b><?=T::$lang['update']?></b></a>
<a href="<?=DynUrl::$base?>section=management&amp;module=users&amp;do=online" style="float:right"><b><?=T::$lang['alls']?></b></a>
<script>//<![CDATA[
$(function(){
	var old=CORE.loading,
		F=function(e){
			var w=500,h=250,
				win=window.open('','win'+$(this).data("uid")+$(this).data("gip"),'height='+h+',width='+w+',toolbar=no,directories=no,menubar=no,scrollbars=no,status=no,top='+Math.round((screen.height-h)/2)+',left='+Math.round((screen.width-w)/2));
			CORE.Ajax("<?=\Eleanor\SITEDIR,html_entity_decode(DynUrl::$base)?>section=management&module=users&do=details",
				{
					ip:$(this).data("ip")||"",
					id:$(this).data("uid")||0,
					service:$(this).data("s")
				},
				function(r)
				{
					win.document.open('text/html','replace');
					win.document.write(r);
					win.document.close();
				}
			);
			e.preventDefault();
		};
	CORE.loading=false;
	$("#onlinelist").on("click",".entry",F)
	$("#who-online").on("click",".entry",F).next().click(function(e){
		CORE.Ajax("<?=\Eleanor\SITEDIR,html_entity_decode(DynUrl::$base)?>section=management&module=users&do=online",
			function(r)
			{
				$("#who-online").html(r);
			}
		);
		e.preventDefault();
	}).click();
	CORE.loading=old;
})//]]></script>