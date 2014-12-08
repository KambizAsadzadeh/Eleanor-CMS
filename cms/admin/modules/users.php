<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

Eleanor::$Language->Load(DIR.'admin/translation/users-*.php','users');
Eleanor::$Template->queue['users']=Eleanor::$Template->classes.'Users.php';

include AJAX ? __DIR__.'/users/ajax.php' : __DIR__.'/users/index.php';