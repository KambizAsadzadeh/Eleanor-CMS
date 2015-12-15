<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Невидимый элемент шаблона, автоматически прописывает URI
 * @var bool $var_0 Включение транслитерации
 * @var string $var_1 Имя поля с названием
 * @var string $var_2 Имя поля с uri */

$trans=Eleanor::$vars['trans_uri'] || !empty($var_0);
$title=isset($var_1) ? $var_1 : 'title';
$uri=isset($var_2) ? $var_2 : 'uri';

if($trans)
	if(Eleanor::$vars['multilang'])
		$GLOBALS['scripts'][]=Template::$http['static'].'js/'.Language::$main.'.js';
	else
		foreach(Eleanor::$langs as $k=>$v)
			if($k!='english')
				$GLOBALS['scripts'][]=Template::$http['static'].'js/'.$k.'.js';

if(Eleanor::$vars['multilang'])
	foreach(Eleanor::$langs as $k=>$v)
		echo'Source2Uri($("#',$title,'-',$k,'"),$("#',$uri,'-',$k,'"),',
		$trans && $k!='english' ? 'CORE.'.ucfirst(Language::$main).'.Translit' : 'false',
		',"',Eleanor::$vars['url_rep_space'],'");';
else
	echo'Source2Uri($("#',$title,'"),$("#',$uri,'"),',
		$trans ? 'CORE.'.ucfirst(Language::$main).'.Translit' : 'false',
		',"',Eleanor::$vars['url_rep_space'],'");';