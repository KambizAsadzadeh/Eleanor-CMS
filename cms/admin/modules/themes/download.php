<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files, Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

do
{
	if(!isset($_GET['file']))
		break;

	$tpl_path=realpath(DIR.'../templates'.DIRECTORY_SEPARATOR);
	$path=realpath($tpl_path.Files::Windows(trim($_GET['file'],'/\\')));

	if(!$path or strncmp($path,$tpl_path,strlen($tpl_path))!=0 or !is_file($path))
		break;

	return Output::Stream(['file'=>$path]);

}while(false);
GoAway();