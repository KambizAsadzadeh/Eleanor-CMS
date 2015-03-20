<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS;
defined('CMS\STARTED')||die;

$config=include __DIR__.'/config.php';
LoadOptions($config['opts']);

switch(Eleanor::$service)
{
	case'admin':
		array_push(Eleanor::$Template->queue,Eleanor::$Template->classes.'News.php',
			__DIR__.'/DefaultTemplate/Admin.php');

		include __DIR__.'/admin/index.php';
	break;
	case'index':
		Eleanor::$Template->queue+=[
			'index'=>Eleanor::$Template->classes.'News.php',
			'def-index'=>__DIR__.'/DefaultTemplate/Index.php',
			'index-correct'=>Eleanor::$Template->classes.'NewsCorrect.php',
			'def-index-correct'=>__DIR__.'/DefaultTemplate/IndexCorrect.php',
		];
		include __DIR__.'/index/index.php';
	break;
	default:
		include __DIR__.'/'.Eleanor::$service.'/index.php';
}