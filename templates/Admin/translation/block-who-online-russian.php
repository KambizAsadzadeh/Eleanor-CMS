<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

return[
	'users'=>function($n){
		return$n.Russian::Plural($n,[' пользователь:',' пользователя:',' пользователей:']);
	},
	'min_left'=>function($n){
		return$n.Russian::Plural($n,[' минуту назад',' минуты назад',' минут назад']);
	},
	'bots'=>function($n){
		return$n.Russian::Plural($n,[' поисковый бот:',' поисковых бота:',' поисковых ботов:']);
	},
	'guests'=>function($n){
		return$n.Russian::Plural($n,[' гость',' гостя',' гостей']);
	},
];