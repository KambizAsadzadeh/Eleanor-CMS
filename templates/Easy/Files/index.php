<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates;
use CMS, CMS\Eleanor, CMS\Language, CMS\Templates\Easy\T;

defined('CMS\STARTED')||die;

/** Скелет основного шаблона
 * @var string $js Путь к каталогу js
 * @var string $css Пусть к каталогу css
 * @var string $cms Пусть к рекламным материалам Eleanor CMS
 * @var string $images Путь к каталогу images
 * @var string $content Содержимое модуля*/
include_once __DIR__.'/../../html.php';

?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
	<?=GetHead()?>
<link rel="shortcut icon" href="favicon.ico" />
<link media="screen" href="<?=$css?>styles.css" rel="stylesheet" />
<link media="screen" href="<?=$css?>engine.css" rel="stylesheet" />
<script src="js/menu_multilevel.js"></script>
<script src="<?=$js?>libs.js"></script>
<script src="/yastatic.net/jquery-ui/1.11.2/jquery-ui.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<?php
#if(Eleanor::$Permissions->IsAdmin())
#	include Eleanor::$root.'addons/blocks/block_adminheader.php';

$ms=array_keys($GLOBALS['Eleanor']->modules['sections'],'search');
$ms=reset($ms);
?>
<div id="page">
	<div class="wrp">
		<header>
			<h1 id="logo"><a class="thd" href="<?=CMS\Url::$base?>" title="<?=Eleanor::$vars['site_name']?>"><?=Eleanor::$vars['site_name']?></a></h1>
			<form class="searchbar" method="get" action="<?=CMS\Url::$base.Url::Make([$ms])?>">
				<input placeholder="<?=T::$lang['search']?>" name="q" value="" type="text" id="search" />
				<button type="submit"><b class="thd"><?=T::$lang['find']?></b></button>
			</form>
<?php
if(Eleanor::$vars['multilang'])
{
	echo'<nav><ul class="h-links">';
	foreach(Eleanor::$langs as $k=>$v)
		if($k==Language::$main)
			echo'<li>',$v['name'],'</li>';
		else
			echo'<li><a href="?language=',$k,'">',$v['name'],'</a></li>';
	echo'</ul></nav>';
}
?>
		</header>
		<div id="topbar">
			<nav><ul class="topmenu"><?=include CMS\DIR.'menus/multiline.php'?></nav><?php include __DIR__.'/../static/login.php'?>
		</div>
		<section id="main-content">
			<div class="lcol" id="mside">
				<?php if(isset($GLOBALS['Eleanor']->module['general']) and T::$config['eleanor']):?>
				<div id="slides">
					<div class="box">
						<div class="slides_container indev">
							<div><a href="#"><img src="<?=$cms?>slide1.jpg" alt="" /></a></div>
							<div><a href="#"><img src="<?=$cms?>slide2.jpg" alt="" /></a></div>
							<div><a href="#"><img src="<?=$cms?>slide3.jpg" alt="" /></a></div>
						</div>
					</div>
					<a class="prev" href="#">Назад</a>
					<a class="next" href="#">Вперед</a>
				</div>
<?php endif;
$bc=CMS\Blocks::Get('center_up');
if($bc)
	echo$bc;

elseif(T::$config['eleanor'])
	echo T::$T->Blocks_center_up(['title'=>'Реклама на сайте','content'=>'<img src="'.$cms.'banner_468x60.png" alt="" />']);
?>
				<!-- CONTEXT LINKS --><?=$content?><!-- /CONTEXT LINKS -->
			</div>
			<aside class="rcol" id="rside">
				<?php if(T::$config['eleanor']):?>
				<ul class="clrfix indev" id="slab-box">
					<li><a href="#"><img src="<?=$images?>slab_1.png" alt="Eleanor CMS - Форум" /></a></li>
					<li><a href="#"><img src="<?=$images?>slab_2.png" alt="Eleanor CMS - Скачать бесплатно" /></a></li>
					<li><a href="#"><img src="<?=$images?>slab_3.png" alt="Eleanor CMS - Как создать шаблон?" /></a></li>
					<li><a href="#"><img src="<?=$images?>slab_4.png" alt="Eleanor CMS - Как создать модуль?" /></a></li>
				</ul>
				<div id="socgroup">
					<b>
						<a class="gi-rss" target="_blank" href="http://eleanor-cms.ru/rss.php">Чтение RSS ленты</a>
						<a class="gi-fb indev" target="_blank" href="#">Наша группа в Facebook</a>
						<a class="gi-vk" target="_blank" href="http://vk.com/eleanorcms">Наша группа вКонтакте</a>
						<a class="gi-tw" target="_blank" href="https://twitter.com/Eleanor_CMS">Наша группа в Twitter</a>
					</b>
					<span>Следи за нами:</span>
				</div>
				<?php endif?>
<?=\CMS\Blocks::Get('right').\CMS\Blocks::Get('left');?>
			</aside>
		</section>
	</div>
<?php if(T::$config['downtags'] and isset($GLOBALS['Eleanor']->module['tags'])):?>
	<div id="fside">
		<div class="wrp">
<?php
$T=clone T::$T;

foreach($GLOBALS['Eleanor']->module['tags'] as &$v)
	$T->Tag($v);

echo$T;
?>
		</div>
	</div>
<?php endif?>
	<div id="footer">
		<div class="wrp">
			<p id="copyright">Copyright © <a href="">YourSite.com</a> <?=idate('Y')?><br />Все права защищены</p>
			<?=GetPageInfo(T::$lang['page_status'])?>
			<ul class="counts indev">
				<li><a href="#" target="_blank"><img src="<?=$images?>count.png" alt="" /></a></li>
				<li><a href="#" target="_blank"><img src="<?=$images?>count.png" alt="" /></a></li>
				<li><a href="#" target="_blank"><img src="<?=$images?>count.png" alt="" /></a></li>
			</ul>
		</div>
	</div>
	<div id="footlinks" class="wrp">
<!--
<?=
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
CMS\COPYRIGHT
?> -->
		<a class="eleanor-copy" target="_blank" href="http://eleanor-cms.ru">Powered by <span>Eleanor CMS © <?=idate('Y')?></span></a>
		<a class="centroarts" target="_blank" href="http://centroarts.com">Designed by <span>CENTROARTS</span></a>
		<!-- noindex --><a rel="nofollow" class="thd html5" href="http://validator.w3.org/check?uri=referer" target="_blank" title="Css3 + Html 5">Css3 + Html 5 Valid</a><!-- /noindex -->
		<?=CMS\RUNTASK ? '<img src="'.CMS\RUNTASK.'" alt="" />' : ''?>
		<?php if(Eleanor::$debug) echo'<div>',GetDebugInfo(),'</div>'?>
	</div>
	<a id="up-page" href="#" class="thd">Подняться наверх</a>
</div>
</body>
</html>