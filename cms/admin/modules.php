<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** @var array $modules Набор служебных модулей админки. Описания ключей:
 * string name Имя модуля
 * string title Название модуля
 * string descr Описание модуля
 * string image Логотипчик модуля
 * bool main Флаг главного модуля: на странице Управление выводится в верхней части
 * bool hidden Флаг скрытого модуля */

$lang=Eleanor::$Language->Load(DIR.'admin/modules-*.php',false);
$modules=[
	'services'=>[
		'title'=>$lang['sessm'],
		'descr'=>$lang['sessm_'],
		'image'=>'services-*.png',
		'main'=>true,
	],
	'multisite'=>[
		'title'=>$lang['multisite'],
		'descr'=>$lang['multisite_'],
		'image'=>'multisite-*.png',
		'main'=>true,
		'file'=>'multisite.php',
	],
	'blocks'=>[
		'title'=>$lang['blokm'],
		'descr'=>$lang['blokm_'],
		'image'=>'blocks-*.png',
		'main'=>true,
	],
	'groups'=>[
		'title'=>$lang['grm'],
		'descr'=>$lang['grm_'],
		'image'=>'groups-*.png',
		'main'=>true,
	],
	'users'=>[
		'title'=>$lang['userm'],
		'descr'=>$lang['userm_'],
		'image'=>'users-*.png',
		'main'=>true,
	],
	'smiles'=>[
		'title'=>$lang['smm'],
		'descr'=>$lang['smm_'],
		'image'=>'smiles-*.png',
	],
	'sitemap'=>[
		'title'=>$lang['smg'],
		'descr'=>$lang['smg_'],
		'image'=>'sitemap-*.png',
	],
	'themes'=>[
		'title'=>$lang['tmpe'],
		'descr'=>$lang['tmpm_'],
		'image'=>'themes_editor-*.png',
	],
	'ownbb'=>[
		'title'=>$lang['ownb'],
		'descr'=>$lang['ownb_'],
		'image'=>'ownbb-*.png',
	],
	'tasks'=>[
		'title'=>$lang['tasks'],
		'descr'=>$lang['tasks_'],
		'image'=>'tasks-*.png',
		'main'=>true,
	],
	'spam'=>[
		'title'=>$lang['spam'],
		'descr'=>$lang['spam_'],
		'image'=>'spam-*.png',
	],
	/*'installer'=>[
		'title'=>$lang['installer'],
		'descr'=>$lang['installer_'],
		'image'=>'installer-*.png',
	],*/
	'comments'=>[
		'title'=>$lang['comm'],
		'descr'=>$lang['comm_'],
		'image'=>'comments-*.png',
	],
	'autocomplete'=>[
		'title'=>'AutoComplete',
		'descr'=>'',
		'image'=>'',
		'hidden'=>true,
	],
];
uasort($modules,function($a,$b){ return strcmp($a['title'],$b['title']); });
return$modules;