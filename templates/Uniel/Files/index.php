<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates;
use CMS, CMS\Eleanor, CMS\Language, CMS\Templates\Uniel\T;

defined('CMS\STARTED')||die;

/** Скелет основного шаблона
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $content Содержимое модуля*/
include_once __DIR__.'/../../html.php';

$GLOBALS['scripts'][]=T::$http['static'].'js/menu_multilevel.js';?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
	<script src="//cdn.jsdelivr.net/g/jquery"></script>
	<?=GetHead()?>
<link media="screen" href="<?=$css?>main.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="favicon.ico" />
</head>

<body class="page_bg">
<div id="loading">
	<span><?=T::$lang['loading']?></span>
</div><script>//<![CDATA[
$(function(){
	$("#loading").on("show",function(){
		$(this).css({
			left:Math.round(($(window).width()-$(this).width())/2),
			top:Math.round(($(window).height()-$(this).height())/2)
		});
	}).triggerHandler("show");
	$(window).resize(function(){
		$("#loading").triggerHandler("show");
	});

	//Подсветим активные пункты меню
	var now="";
	with(location)
	{
		now+=protocol+"//"+hostname+(port ? ":"+port : "")+CORE.dir;
		now=href.substr(now.length);
	}
	$("nav a").filter(function(){
		return $(this).attr("href")==now && now!="#";
	}).addClass("active");
});//]]></script>
<?php
if(Eleanor::$Permissions->IsAdmin())
	include \CMS\DIR.'blocks/block_adminheader.php';
?>
<div class="wrapper">

<div id="headerboxic"><div class="dleft"><div class="dright">
	<a class="logotype" href="">
		<img src="<?=$images?>eleanorcms.png" alt="<?=Eleanor::$vars['site_name']?>" title="<?=Eleanor::$vars['site_name']?>" />
	</a>
	<span class="headbanner">
		<!-- Баннер 468x60-->
		<!-- <a href="link.html" title="Ваш баннер"><img src="<?=$images?>spacer.png" alt="Ваш баннер" /></a> -->
	</span>
</div></div></div>

<div id="menuhead"><div class="dleft"><div class="dright">
	<div class="language">
	<?php
	if(Eleanor::$vars['multilang'])
	{
		$langs=Eleanor::$langs;
		unset($langs[Language::$main]);
		foreach($langs as $k=>$v)
			echo'<a href="?language=',$k,'" title="',$v['name'],'"><b>',substr($k,0,3),'</b></a>';
	}
	?>
	</div>
	<nav><ul class="topmenu">
	<?=include CMS\DIR.'menus/multiline.php'?>
	</nav><script>/*<![CDATA[*/$(function(){ $(".topmenu").MultiLevelMenu(); });//]]></script>
</div></div></div>

<div class="container">
	<div class="mainbox">
<?php
$blocks=CMS\Blocks::Get(['right','left','center_up','center_down']);
echo'<div id="maincol',$blocks['right'] ? 'R' : '','">
	<div class="baseblock"><div class="dtop"><div class="dbottom">
		<div class="dcont">',
		$blocks['center_up'],
		'<!-- CONTEXT LINKS -->',$content,'<!-- /CONTEXT LINKS -->',
		$blocks['center_down'],
		'</div>
	</div></div></div>
</div>',$blocks['right'] ? '<div id="rightcol">'.$blocks['right'].'</div>' : '';
?>
	</div>

	<div id="leftcol">
	<?php
		include __DIR__.'/../static/login.php';
		echo$blocks['left']; ?>
	</div>

	<div class="clr"></div>
</div>

<div id="footmenu"><div class="dleft"><div class="dright">
	<a title="<?=T::$lang['to_top']?>" onclick="scroll(0,0); return!1;" href="#" class="top-top"><img src="<?=$images?>top-top.png" alt="" /></a>
	<span class="menu"><?=join('',include CMS\DIR.'menus/single.php'); ?></span>
</div></div></div>

<div id="footer"><div class="dleft"><div class="dright">
	<div class="count">
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 88px;"><!-- кнопка, счетчик --></span>
		<span style="width: 60px;">  <a href="http://validator.w3.org/check?uri=referer" rel="nofollow"><img src="<?=$images?>html5_valid.png" alt="Valid HTML 5" title="Valid HTML 5" width="60" height="31" /></a></span>

	</div>
	<!-- КОПИРАЙТЫ -->
	<span class="copyright">&copy; Copyright</span>
	<div class="clr"></div>
</div></div></div>

<div id="syscopyright">
	<span class="centroarts"><a href="http://centroarts.com" rel="nofollow" title="Шаблон разработан студией CENTROARTS.com">Designed by CENTROARTS.com</a></span>
	<div>Powered by <?=
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
	CMS\COPYRIGHT,CMS\RUNTASK ? '<img src="'.CMS\RUNTASK.'" alt="" />' : ''?></div>
	<div><?=GetPageInfo(T::$lang['page_status'])?></div>
	<?php if(Eleanor::$debug) echo'<div>',GetDebugInfo(),'</div>'?>
</div>

</div>
</body>
</html>