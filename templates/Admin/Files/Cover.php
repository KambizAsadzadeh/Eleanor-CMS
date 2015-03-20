<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
/** Оберткая контента внутри админке. Включает в себя вывод подзаголовка и ошибки
 * @var string $var_0 Контент модуля
 * @var string $var_1 Текст ошибки
 * @var string $var_2 Тип ошибки: warning,error,info */
defined('CMS\STARTED')||die;

echo Eleanor::$Template->Title(is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title']);

if(!empty($var_1))
	echo Eleanor::$Template->Message($var_1,isset($var_2) ? $var_2 : null);

if($var_0)
	echo Eleanor::$Template->OpenTable(),$var_0,Eleanor::$Template->CloseTable();