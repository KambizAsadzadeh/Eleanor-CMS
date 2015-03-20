<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Uniel;
use \CMS\Template, \CMS\Eleanor;

/** Несущий класс шаблона */
class T extends Template
{
	public static
		/** @var Template Основной объект шаблона */
		$T,

		/** @var \Eleanor\Classes\Language Основные языковые параметры */
		$lang,

		/** @var array Место хранение служебных данных */
		$data;
}

$name=basename(__DIR__);
T::$lang=Eleanor::$Language->Load(__DIR__.'/translation/*.php',false);

T::$T=new Template(__DIR__.'/Files');
T::$T->classes=__DIR__.'/Classes/';
T::$T->default=[
	'css'=>Template::$http['templates'].$name.'/css/',#HTTP путь к css шаблона
	'images'=>Template::$http['templates'].$name.'/images/',#HTTP путь к картинкам шаблона
];

return T::$T;