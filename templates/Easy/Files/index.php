<?php
/*
	Скелет основного шаблона.
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
global$Eleanor;
?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
{head}
<link rel="shortcut icon" href="favicon.ico" />
<link media="screen" href="<?=$theme?>style/styles.css" type="text/css" rel="stylesheet" />
<link media="screen" href="<?=$theme?>style/engine.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="js/menu_multilevel.js"></script>
<script type="text/javascript" src="<?=$theme?>js/libs.js"></script>
<script type="text/javascript" src="<?=$theme?>js/jquery-ui.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<?php
if(Eleanor::$Permissions->IsAdmin())
	include Eleanor::$root.'addons/blocks/block_adminheader.php';

$ms=array_keys($Eleanor->modules['sections'],'search');
$ms=reset($ms);
?>
<div id="page">
	<div class="wrp">
		<header>
			<h1 id="logo"><a class="thd" href="<?=$Eleanor->Url->special?>" title="<?=Eleanor::$vars['site_name']?>"><?=Eleanor::$vars['site_name']?></a></h1>
			<form class="searchbar" method="get" action="<?=$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ms),false)?>">
				<input placeholder="<?=$ltpl['search']?>" name="q" value="" type="text" id="search" />
				<button type="submit"><b class="thd"><?=$ltpl['find']?></b></button>
			</form>
<?php
if(Eleanor::$vars['multilang'])
{
	echo'<nav><ul class="h-links">';
	foreach(Eleanor::$langs as $k=>$v)
		if($k==Language::$main)
			echo'<li>',$v['name'],'</li>';
		else
			echo'<li><a href="',Eleanor::$filename,'?language=',$k,'">',$v['name'],'</a></li>';
	echo'</ul></nav>';
}
?>
		</header>
		<div id="topbar">
			<?php echo'<nav><ul class="topmenu">',include Eleanor::$root.'addons/menus/multiline.php','</nav>'; include Eleanor::$root.$theme.'Static/login.php'?>
		</div>
		<section id="main-content">
			<div class="lcol" id="mside">
				<?php if(isset($Eleanor->module['general']) and $CONFIG['eleanor']):?>
				<div id="slides">
					<div class="box">
						<div class="slides_container indev">
							<div><a href="#"><img src="<?=$theme?>eleanor-cms/slide1.jpg" alt="" /></a></div>
							<div><a href="#"><img src="<?=$theme?>eleanor-cms/slide2.jpg" alt="" /></a></div>
							<div><a href="#"><img src="<?=$theme?>eleanor-cms/slide3.jpg" alt="" /></a></div>
						</div>
					</div>
					<a class="prev" href="#">Назад</a>
					<a class="next" href="#">Вперед</a>
				</div>
				<?php endif;
				$bc=Blocks::Get('center_up');
				if($bc)
					echo$bc;
				elseif($CONFIG['eleanor'])
					echo Eleanor::$Template->Blocks_center_up(array('title'=>'Реклама на сайте','content'=>'<img src="'.$theme.'eleanor-cms/banner_468x60.png" alt="" />'));
				?>
				<!-- CONTEXT LINKS -->{module}<!-- /CONTEXT LINKS -->
			</div>
			<aside class="rcol" id="rside">
				<?php if($CONFIG['eleanor']):?>
				<ul class="clrfix indev" id="slab-box">
					<li><a href="#"><img src="<?=$theme?>images/slab_1.png" alt="Eleanor CMS - Форум" /></a></li>
					<li><a href="#"><img src="<?=$theme?>images/slab_2.png" alt="Eleanor CMS - Скачать бесплатно" /></a></li>
					<li><a href="#"><img src="<?=$theme?>images/slab_3.png" alt="Eleanor CMS - Как создать шаблон?" /></a></li>
					<li><a href="#"><img src="<?=$theme?>images/slab_4.png" alt="Eleanor CMS - Как создать модуль?" /></a></li>
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
<?=Blocks::Get('right').Blocks::Get('left');?>
			</aside>
		</section>
	</div>
<?php if($CONFIG['downtags'] and isset($Eleanor->module['tags'])):?>
	<div id="fside">
		<div class="wrp">
<?php
$T=clone Eleanor::$Template;
	foreach($Eleanor->module['tags'] as &$v)
		$T->Tag($v);
echo$T;
?>
		</div>
	</div>
<?php endif?>
	<div id="footer">
		<div class="wrp">
			<p id="copyright">Copyright © <a href="">YourSite.com</a> <?=idate('Y')?><br />Все права защищены</p>[page status]<p id="status">{page status}</p>[/page status]
			<ul class="counts indev">
				<li><a href="#" target="_blank"><img src="<?=$theme?>images/count.png" alt="" /></a></li>
				<li><a href="#" target="_blank"><img src="<?=$theme?>images/count.png" alt="" /></a></li>
				<li><a href="#" target="_blank"><img src="<?=$theme?>images/count.png" alt="" /></a></li>
			</ul>
		</div>
	</div>
	<div id="footlinks" class="wrp">
<?php
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
#echo ELEANOR_COPYRIGHT
?>
		<a class="eleanor-copy" target="_blank" href="http://eleanor-cms.ru">Powered by <span>Eleanor CMS © <?=idate('Y')?></span></a>
		<a class="centroarts" target="_blank" href="http://centroarts.com">Designed by <span>CENTROARTS</span></a>
		<!-- noindex --><a rel="nofollow" class="thd html5" href="http://validator.w3.org/check?uri=referer" target="_blank" title="Css3 + Html 5">Css3 + Html 5 Valid</a><!-- /noindex -->
[debug]		<div>{debug}</div>
[/debug]	</div>
	<a id="up-page" href="#" class="thd">Подняться наверх</a>
</div>
</body>
</html>