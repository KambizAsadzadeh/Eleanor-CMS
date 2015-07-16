<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Easy;
use \CMS\Template, \CMS\Eleanor;

/** Несущий класс шаблона */
class T extends Template
{
	public static
		/** @var array Конфиг шаблона */
		$config,

		/** @var Template Основной объект шаблона */
		$T,

		/** @var \Eleanor\Classes\Language Основные языковые параметры */
		$lang,

		/** @var array Место хранение служебных данных */
		$data;
}

$name=basename(__DIR__);
T::$lang=Eleanor::$Language->Load(__DIR__.'/translation/*.php',false);
T::$config=include __DIR__.'/config.php';

T::$T=new Template(__DIR__.'/Files');
T::$T->classes=__DIR__.'/Classes/';
T::$T->default=[
	'cms'=>Template::$http['templates'].$name.'/eleanor-cms/',#HTTP путь к рекламным материалам Eleanor CMS
	'css'=>Template::$http['templates'].$name.'/css/',#HTTP путь к css шаблона
	'js'=>Template::$http['templates'].$name.'/js/',#HTTP путь к js шаблона
	'images'=>Template::$http['templates'].$name.'/images/',#HTTP путь к картинкам шаблона
];

return T::$T;
