<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'n'=>'errors',#Модуля
	't'=>P.'errors',#Имя таблицы с контентом
	'tl'=>P.'errors_l',#Имя таблицы с языковым контентом

	'services'=>['admin','index'],#Сервисы, в которых работает модуль
	'sections'=>['errors'],#Секции модуля (общесистемная опция)

	#Пути к загрузкам
	'uploads-http'=>Template::$http['uploads'].'errors/',
	'uploads-path'=>Template::$path['uploads'].'errors/',

	#Пути к галерее
	'gallery-http'=>Template::$http['static'].'images/errors/',
	'gallery-path'=>Template::$path['static'].'images/errors/',
];