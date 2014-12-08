<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'n'=>'news',#Модуля

	't'=>P.'news',#Имя таблицы с контентом
	'tl'=>P.'news_l',#Имя таблицы с языковым контентом
	'tt'=>P.'news_tags',#Имя таблицы с тегами
	'rt'=>P.'news_rt',#Имя таблицы с тегами => новостями (Related tags)
	'c'=>P.'news_categories',#Имя таблицы с категориями

	'services'=>['admin','index','rss','xml','cron'],#Сервисы, в которых работает модуль
	'sections'=>['news'],#Секции модуля (общесистемная опция)
	'optgroup'=>'module_news',#Название группы опций (общесистемная опция)

	'pv'=>'m_news_',#Префикс настроек
	'secret'=>crc32(__FILE__),#Для подписи новостей, добавленных гостями
];