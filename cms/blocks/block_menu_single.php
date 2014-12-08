<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

if(isset($config_['parent']))
	$parent=$config_['parent'];

$menu=include DIR.'menus/single.php';
try
{
	return$menu ? Eleanor::$Template->BlockMenuSingle($menu,null) : '';
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockMenuSingle does not exists.';
}