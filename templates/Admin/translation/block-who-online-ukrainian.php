<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

return[
	#Для шаблону Classes/UsersOnline.php
	'user_info'=>'Інформація про відвідувача',
	'users'=>function($n){
		return$n.Ukrainian::Plural($n,[' користувач:',' користувача:',' користувачів:']);
	},
	'min_left'=>function($n){
		return$n.Ukrainian::Plural($n,[' хвилину тому:',' хвилини тому',' хвилин тому']);
	},
	'bots'=>function($n){
		return$n.Ukrainian::Plural($n,[' пошуковий бот',' пошукових бота',' пошукових ботів']);
	},
	'guests'=>function($n){
		return$n.Ukrainian::Plural($n,[' гість',' гостя',' гостей']);
	},
	'activity'=>'Активність',
	'now_onp'=>'Зараз на сторінці',
	'r'=>'Перейшов з',
	'browser'=>'Браузер',
	'service'=>'Сервіс',
	'c'=>'Підтримка кодувань',
	'e'=>'Підтримувані типи даних',
	'ips'=>'IP додаткові',
	'session_nf'=>'Сесія не знайдена',
	'go'=>'Перейти',
];