<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$config=include __DIR__.'/config.php';

switch(Eleanor::$service)
{
	case'admin':
		array_push(Eleanor::$Template->queue,Eleanor::$Template->classes.'Errors.php',
			__DIR__.'/DefaultTemplate/Admin.php');

		include __DIR__.'/admin/index.php';
	break;
	case'index':
		array_push(Eleanor::$Template->queue,Eleanor::$Template->classes.'Errors.php',
			__DIR__.'/DefaultTemplate/Index.php');

		include __DIR__.'/index/index.php';
	break;
	default:
		$f=__DIR__.'/'.Eleanor::$service.'/index.php';

		if(is_file($f))
			include$f;
		else
			trigger_error('Request is not supported',E_USER_ERROR);
}