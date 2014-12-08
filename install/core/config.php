<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

$path=defined('CMS\DIR') ? dirname(DIR) : dirname(__DIR__);

return[
#База данных
	/** Сервер базы данных */
	'db-host'=>'{db-host}',

	/** Название базы данных */
	'db'=>'{db}',

	/** Пользователь баз данных */
	'db-user'=>'{db-user}',

	/** Пароль пользователя базы данных */
	'db-pass'=>'{db-pass}',

	/** Кодировка базы данных http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html */
	'db-charset'=>'utf8',

	/** Префикс таблиц, в пользовательской части доступен через константу CMS\P */
	'prefix'=>'{prefix}',

	/** Таблица пользователей */
	'users-table'=>'{prefix}users',

	/** Отдельные данные для доступа к БД пользователей */
	#'users'=>['db-host'=>'{users-db-host}','db'=>'{users-db}','db-user'=>'{users-db-user}','db-pass'=>'{users-db-pass}'],

#Пути
	/** Префикс пути к каталогу со статическим контетом static по HTTP относительно корня сайта. Template::$http */
	'static'=>'static/',

	/** Префикс пути к каталогу со статическим контетом static для обращения из php скриптов. Template::$path */
	'static-path'=>$path.'/static/',

	/** Префикс пути к каталогу с темами оформления templates по HTTP относительно корня сайта. Template::$http */
	'templates'=>'templates/',

	/** Префикс пути к каталогу с темами оформления templates для обращения из php скриптов. Template::$path */
	'templates-path'=>$path.'/templates/',

	/** Префикс пути к каталогу загруженных файлов по HTTP относительно корня сайта. Template::$http */
	'uploads'=>'uploads/',

	/** Префикс пути к каталогу загруженных файлов для обращения из php скриптов. Template::$path */
	'uploads-path'=>$path.'/uploads/',

	/** Префикс пути к каталогу сторонних скриптов по HTTP относительно корня сайта. Template::$http */
	'3rd'=>'third-party/',

	/** Префикс пути к каталогу сторонних скриптов для обращения из php скриптов. Template::$path */
	'3rd-path'=>$path.'/third-party/',

#Языки
	/** Языки системы
	 * d - от "double", http://ru.wikipedia.org/wiki/ISO_3166-1
	 * l - locale для setlocale */
	'langs'=>[
		'russian'=>['name'=>'Русский','uri'=>'rus','d'=>'ru','l'=>'ru_RU.utf-8'],
		'english'=>['name'=>'English','uri'=>'eng','d'=>'en','l'=>'en.utf-8'],
		'ukrainian'=>['name'=>'Українська','uri'=>'ukr','d'=>'ua','l'=>'ua.utf-8'],
	],

	/** Язык сайта по умолчанию */
	'language'=>'{language}',

#Другое
	/** Режим отладки. При включенной отладке отключается кэш и отображается отладочная информация */
	'debug'=>false,

	/** Префикс cookies */
	'cookie-prefix'=>'',
];
