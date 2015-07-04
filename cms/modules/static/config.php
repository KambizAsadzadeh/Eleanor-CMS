<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'n'=>'static',#Имя модуля
	't'=>P.'static',#Имя таблицы с контентом
	'tl'=>P.'static_l',#Имя таблицы с языковым контентом

	'services'=>['admin','download','index','rss'],#Сервисы, в которых работает модуль
	'sections'=>['static'],#Секции модуля (общесистемная опция)
	'optgroup'=>'module_static',#Название группы опций (общесистемная опция)

	#Пути к загрузкам
	'uploads-http'=>Template::$http['uploads'].'static/',
	'uploads-path'=>Template::$path['uploads'].'static/',

	'pv'=>'m_static_',#Префикс настроек
];