<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$opts=[];
foreach(Eleanor::$vars['templates'] as $v)
{
	$f=Template::$path['templates'].$v.'/settings.php';

	if(!file_exists($f))
		continue;

	$settings=include$f;
	$opts[$v]=is_array($settings) && isset($settings['name']) ? FilterLangValues((array)$settings['name']) : $v;
}

try
{
	return count($opts)>1 ? Eleanor::$Template->BlockThemeSel($opts,null) : false;
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockThemeSel does not exists.';
}