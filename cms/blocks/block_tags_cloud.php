<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

if(!isset($options) or !is_array($options))
	$options=[];

try
{
	return(string)Eleanor::$Template->BlockTagCloud($options,null);
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockTagCloud does not exists.';
}