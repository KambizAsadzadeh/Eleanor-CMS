<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates;
use CMS, CMS\Eleanor, CMS\Templates\Uniel\T;

/** Шаблон страницы закрытого на профилактику сайта
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images */
defined('CMS\STARTED')||die;
include_once __DIR__.'/../../html.php';
$GLOBALS['title']=T::$lang['site_close_text'];
?><!DOCTYPE html>
<html>
<head>
	<script src="//cdn.jsdelivr.net/g/jquery"></script>
	<?=GetHead()?>
<style>
body {
	margin: auto;
	padding: 0;
	text-align: center;
	height: 100%;
	font-family: Tahoma, Arial, Sans-serif;
}
html { height: 100%; }
h1 { font-weight: normal; font-size: 18px; color: #4f4f4f;}
.syscopyright { font-size: 10px; color: #c0c0c0; margin-top:10px}
.syscopyright a { color: #c0c0c0; }
</style>
</head>

<body>
<div style="padding-top: 20%;"><img src="<?=$images?>denied.png" alt="" title="<?=T::$lang['site_close_text']?>" /><br />
<?=empty(Eleanor::$vars['site_close_mes']) ? '<h1>'.T::$lang['site_close_text'].'</h1>' : CMS\OwnBB::Parse(Eleanor::$vars['site_close_mes'])?>
</div>
<div class="syscopyright">Powered by <?=
	#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
	CMS\COPYRIGHT,CMS\RUNTASK ? '<img src="'.CMS\RUNTASK.'" alt="" />' : ''?></div>
</body>
</html>