<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html, Eleanor\Classes\Output, Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

if(!AJAX or !function_exists('CMS\Error'))
{
	OutPut::SendHeaders('text',404);
	Output::Gzip('Not found');
	die;
}

try
{
	switch(isset($_GET['special']) ? (string)$_GET['special'] : '')
	{
		case'uploader':
			Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Uploader.php';
			Uploader_BackEnd::Process(isset($_REQUEST['uniq']) ? (string)$_REQUEST['uniq'] : '');
		break;
		/*case'controls':
			$data=array('session'=>isset($_POST['session']) ? (string)$_POST['session'] : '');
			if(isset($_POST['newtype']))
				$data['type']=(string)$_POST['newtype'];
			if(isset($_POST['options']))
				$data['options']=(array)$_POST['options'];
			if(isset($_POST['service']))
				BeAs((string)$_POST['service']);
			Result($Eleanor->Controls_Manager->ConfigureControl($data,true,!empty($_POST['onlyprev'])));
			break;
		case'uploadimage':
			include Eleanor::$root.'core/others/controls.php';
			include Eleanor::$root.'core/controls/uploadimage.php';
			ControlUploadImage::DoAjax();
		break;
		case'uploader':
			if(isset($_POST['service']))
				BeAs((string)$_POST['service']);
		break;*/
		default:
			if(isset($_POST['preview']))
			{
				$Saver=new Saver('bb',isset($_GET['smiles']),isset($_GET['ownbb']));
				$text=$Saver->Save((string)$_POST['preview']);
				$text=Eleanor::$Template->BBPreview($text);
				OutPut::SendHeaders('application/json');
				Output::Gzip(Html::JSON($text));
			}
			else
				Error(Eleanor::$Language['ajax']['unknown_event']);
	}
}
catch(EE$E)
{
	Error($E->getMessage());
}