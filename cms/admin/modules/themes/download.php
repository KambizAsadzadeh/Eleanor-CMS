<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;
do
{
	if(!isset($_GET['f']))
		break;
	$rp=Eleanor::$root.'templates'.DIRECTORY_SEPARATOR;
	$path=realpath($rp.Files::Windows(trim($_GET['f'],'/\\')));
	if(!$path or strncmp($path,$rp,strlen($rp))!=0 or !is_file($path))
		break;
	return Files::OutputStream(array('file'=>$path));
}while(false);
GoAway();