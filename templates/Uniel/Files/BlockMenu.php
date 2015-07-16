<?php
namespace CMS;
/** Оформление содержимого блока "Вертикальное многоуровневое меню"
 * @var string $var_0 строка меню, без начального <ul>, представляет собой последовательность
 * <li><a...>...</a><ul><li>...</li></ul></li></ul> */
defined('CMS\STARTED')||die;

$GLOBALS['scripts'][]=Template::$http['static'].'js/menu_multilevel.js';
$u=uniqid();

echo'<nav><ul id="',$u,'" class="blockmenu">',$var_0,'</ul></nav><script>//<![CDATA[
$(function(){
	var li=$("#',$u,'").MultiLevelMenu({type:"col"}).children("li"),
		h=li.outerHeight();
	li.end().height(h*li.length);
});//]]></script>';