<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Output, Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

try
{
	Uploader_BackEnd::Process(isset($_REQUEST['uniq']) ? (string)$_REQUEST['uniq'] : '');
}
catch(EE$E)
{
	OutPut::SendHeaders('text',403);
	Output::Gzip($E->getMessage());
}