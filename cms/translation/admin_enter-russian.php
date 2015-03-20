<?php
namespace Eleanor\Classes\Language;
return[
	#Для /admin.php
	'TEMPORARILY_BLOCKED'=>function($name,$minutes){
		return'В связи с частым вводом неправильного пароля, аккаунт пользователя <b>'.$name
			.'</b> был заблокирован! Повторите попытку через '.$minutes
			.Russian::Plural($minutes,[' минут.',' минуты.',' минуты.']);
	},
	'enter_to'=>'Вход в панель администратора',
];