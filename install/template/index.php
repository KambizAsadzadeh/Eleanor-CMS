<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Общий шаблон установщика
 * @var int $percent Процент выполнения установки
 * @var string $navi Навигационная строка
 * @var string $content Содержимое страницы*/

$lang=Eleanor::$Language['main'];

include_once __DIR__.'/../../templates/html.php';
?><!DOCTYPE html><html><head><script src="//cdn.jsdelivr.net/g/jquery"></script><?=Templates\GetHead(false,false)?>
<link type="image/x-icon" href="../favicon.ico" rel="icon" />
<link media="screen" href="template/style/styles.css" type="text/css" rel="stylesheet" />
</head><body class="pagebg">
<div class="wrapper">
	<div class="elh"><div class="elh"><div class="elh">
		<div class="head">
			<h1><a href="http://eleanor-cms.ru" title="Eleanor CMS" target="_blank">Eleanor CMS</a></h1>
			<div class="version">
				<span><span><span><?=$lang['sysver'],'<b>',sprintf('%.1F',Eleanor::VERSION),'</b>'?></span></span></span>
			</div>
		</div>
		<div class="process">
			<div class="procline"><img style="width: <?=$percent?>%" src="<?=Template::$http['static']?>images/spacer.png" alt="<?=$percent?>%" title="<?=$percent?>%" /></div>
			<div class="procinfo"><span><?=$navi?></span></div>
		</div>
	</div></div></div>
	<div class="wpbox">
		<div class="wptop"><b>&nbsp;</b></div>
		<div class="wpmid">
			<div class="wpcont"><?=$content?></div>
			<div class="clr"></div>
		</div>
		<div class="wpbtm"><b>&nbsp;</b></div>
	</div>
	<div class="elf"><div class="elf"><div class="elf">
		<div class="copyright"><?=
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
'Powered by ',COPYRIGHT?></div>
		<img class="elcd" src="<?=Template::$http['static']?>images/spacer.png" alt="" />
	</div></div></div>
</div>
</body>
</html>