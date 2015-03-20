<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;
global$Eleanor;
$c=$Eleanor->Settings->GetInterface('full');
$Eleanor->module['title']=Eleanor::$Language['main']['options'];
$Eleanor->module['descr']=end($GLOBALS['title']);
Start();
echo$c;