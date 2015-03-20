<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Невидимый элемент шаблона, автоматически прописывает URI */

if(Eleanor::$vars['trans_uri'])
	if(Eleanor::$vars['multilang'])
		$GLOBALS['scripts'][]=Template::$http['static'].'js/'.Language::$main.'.js';
	else
		foreach(Eleanor::$langs as $k=>$v)
			if($k!='english')
				$GLOBALS['scripts'][]=Template::$http['static'].'js/'.$k.'.js';

if(Eleanor::$vars['multilang'])
	foreach(Eleanor::$langs as $k=>$v)
		echo'Source2Uri($("#title-',$k,'"),$("#uri-',$k,'"),',
		Eleanor::$vars['trans_uri'] && $k!='english' ? 'CORE.'.ucfirst(Language::$main).'.Translit' : 'false',
		',"',Eleanor::$vars['url_rep_space'],'");';
else
	echo'Source2Uri($("#title"),$("#uri"),',
		Eleanor::$vars['trans_uri'] ? 'CORE.'.ucfirst(Language::$main).'.Translit' : 'false',
		',"',Eleanor::$vars['url_rep_space'],'");';