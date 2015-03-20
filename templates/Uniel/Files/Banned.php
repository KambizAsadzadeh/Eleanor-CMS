<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

#ToDo! Вынести в языковой файл
$title='Вы забанены';

/** Скелет шаблона забаненого пользователя
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $explain Объяснение бана
 * @var bool|string $term Дата снятия бана*/
?><!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=<?=\Eleanor\CHARSET?>" />
<title><?=$title?></title><base href="<?=\Eleanor\SITEDIR?>">
<style type="text/css">/*<![CDATA[*/
body, div { color:#1d1a15; font-size: 11px; font-family: Tahoma, Helvetica, sans-serif; }
body { text-align: left; height: 100%; line-height: 142%; padding: 0; margin: 20px; background-color: #FFFFFF; }
hr { height: 1px; border: solid #d8d8d8 0; border-top-width: 1px; }
.copyright { position:fixed; bottom:10px; right:10px; }
a { text-decoration:none; }
h1 { font-size:18px }
/*]]>*/</style></head><body>
<div class="copyright">Powered by <a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> &copy; <?=idate('Y')?></div>
<h1><?=$title?></h1><hr />
<?=isset($explain) ? $explain : '<i>Причина не указана</i>',
isset($term) ? 'Блокировка будет снята '.Eleanor::$Language->Date($term,'fdt') : ''?></body></html>