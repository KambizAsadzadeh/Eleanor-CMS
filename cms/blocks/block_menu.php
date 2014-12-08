<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;
if(isset($config_['parent']))
	$parent=$config_['parent'];
$menu=include Eleanor::$root.'addons/menus/multiline.php';
try
{
	return$menu ? Eleanor::$Template->BlockMenu($menu,null) : false;
}
catch(EE$E)
{
	return'Template BlockMenu does not exists.';
}