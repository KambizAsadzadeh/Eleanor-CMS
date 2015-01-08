<?php
namespace CMS;
use Eleanor\Classes\Language\Russian;
defined('CMS\STARTED')||die;

return[
	#Для /cms/admin/modules/users/index.php
	'list'=>'Список пользователей',
	'deleting'=>'Подтверждение удаления',
	'creating'=>'Создание пользователя',
	'editing'=>'Редактирование пользователя',
	'letters'=>'Форматы писем',
	'letter4created'=>'Письмо при создании нового пользователя',
	'letter4renamed'=>'Письмо при изменении имени пользователя',
	'letter4newpass'=>'Письмо при изменении пароля пользователя',
	'letter-title'=>'Тема письма',
	'letter-descr'=>'Текст письма',
	'online-list'=>'Кто онлайн',

	#Для /cms/admin/modules/users/users.php
	'personal'=>'Личное',
	'gender'=>'Пол',
	'no-gender'=>'Неизвестно',
	'female'=>'Женщина',
	'male'=>'Мужчина',
	'bio'=>'Биография',
	'site'=>'Сайт',
	'site_'=>'Введите адрес сайта, начиная с http://',
	'interests'=>'Интересы',
	'location'=>'Местоположение',
	'signature'=>'Подпись',
	'connect'=>'Связь',
	'vk'=>'ВКонтакте',
	'vk_'=>'Пожалуйста введите только свой id, либо имя',
	'twitter_'=>'Пожалуйста, введите только ник',
	'theme'=>'Шаблон',
	'by_default'=>'По умолчанию',
	'editor'=>'Редактор',

	#Errors
	'NAME_TOO_LONG'=>function($l,$e){
		return'Длина имени пользователя не должна превышать '.$l.Russian::Plural($l,[' символ',' символа',' символов'])
			.' символов. Вы ввели '.$e.Russian::Plural($e,[' символ',' символа',' символов']).' символов.';
	},
	'PASS_TOO_SHORT'=>function($l,$e){
		return'Минимальная длина пароля '.$l.Russian::Plural($l,[' символ',' символа',' символов'])
			.' символов. Вы ввели только '.$e.Russian::Plural($e,[' символ',' символа',' символов']).' символов.';
	},
];