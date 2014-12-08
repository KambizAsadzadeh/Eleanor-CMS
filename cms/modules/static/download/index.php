<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\OutPut;
defined('CMS\STARTED')||die;

/** @var Logins\Admin $class */
$class='\CMS\Logins\\'.Eleanor::$services['admin']['login'];

do
{
	if(!$class::IsUser() or !isset($_GET['download']))
		break;

	$file=Url::Decode($_GET['download']);
	$file=\Eleanor\Classes\Files::Windows($file);
	$file=__DIR__.'/../DIRECT/'.trim($file,'/\\');
	$file=realpath($file);

	if(!$file or !is_file($file) or strpos($file,dirname(__DIR__).DIRECTORY_SEPARATOR.'DIRECT')!==0)
		break;

	$etag=md5($file);
	$mtime=filemtime($file);

	if(!Output::TryReturnCache($etag,$mtime))
		return Output::Stream([ 'file'=>$file, 'etag'=>$etag, 'last-modified'=>$mtime ]);
}while(false);

ExitPage();