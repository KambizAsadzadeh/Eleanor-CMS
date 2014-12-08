<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Упрощенный шаблон для ситуации, когда система загружается в iframe (отображение упрощенных форм в модальном окне)
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $js Путь к каталогу js
 * @var string $ico Путь к каталогу ico
 * @var array $config Конфигурация шаблона
 * @var string $content Содержимое модуля */

include_once __DIR__.'/../../html.php';

#Фикс заголовка: берется самый важный
if(is_array($GLOBALS['title']))
	$GLOBALS['title']=end($GLOBALS['title']);

$GLOBALS['scripts'][]=$js.'admin.js';
$GLOBALS['scripts'][]=$js.'admin-angular.js';
?><!DOCTYPE html>
<html id="ng-app">
<head>
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap-theme.css" type="text/css">
	<script src="//cdn.jsdelivr.net/g/angularjs,angular.bootstrap,jquery,bootstrap@3"></script>

	<?=Templates\GetHead(true,false)?>
	<!--[if lt IE 9]><script src="//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->

	<link rel="stylesheet" href="<?=$css?>style.css" type="text/css">
	<link rel="stylesheet" href="<?=$ico?>ico.css" type="text/css">
</head>
<body class="iframebody"><?=$content?></body>
</html>