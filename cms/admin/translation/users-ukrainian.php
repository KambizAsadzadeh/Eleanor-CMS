<?php
namespace CMS;
use Eleanor\Classes\Language\Ukrainian;
defined('CMS\STARTED')||die;

return[
	#Для /cms/admin/modules/users/index.php
	'list'=>'Список користувачів',
	'deleting'=>'Підтвердження видалення',
	'creating'=>'Створення користувача',
	'editing'=>'Редагування користувача',
	'letters'=>'Формати листів',
	'letter4created'=>'Лист при створенні нового користувача',
	'letter4renamed'=>'Лист при заміні імені користувача',
	'letter4newpass'=>'Лист при замініїє пароля користувача',
	'letter-title'=>'Тема листа',
	'letter-descr'=>'Текст листа',
	'online-list'=>'Хто онлайн',

	#Для /cms/admin/modules/users/extra.php
	'personal'=>'Особисте',
	'gender'=>'Стать',
	'no-gender'=>'Неівдомо',
	'female'=>'Жіноча',
	'male'=>'Чоловіча',
	'bio'=>'Біографія',
	'site'=>'Сайт',
	'site_'=>'Введіть адресу сайту, починаючи с http://',
	'interests'=>'Інтереси',
	'location'=>'Розташування',
	'signature'=>'Підпис',
	'connect'=>'Зв&apos;зок',
	'vk'=>'ВКонтакте',
	'vk_'=>'Будь-ласка, введіть тільки свій id, чи нік',
	'twitter_'=>'Будь-ласка, введіть тільки нік',
	'theme'=>'Шаблон',
	'by_default'=>'По замовчуванню',
	'editor'=>'Редактор',

	#Errors
	'NAME_TOO_LONG'=>function($l,$e){
		return'Довжина імені користувача не повинна перевищувати '.$l.Ukrainian::Plural($l,[' символ',' символа',' символів'])
		.'. Ви ввели тільки '.$e.Ukrainian::Plural($e,[' символ',' символа',' символов']);
	},
	'PASS_TOO_SHORT'=>function($l,$e){
		return'Минимальная длина пароля '.$l.Ukrainian::Plural($l,[' символ',' символа',' символів'])
		.'. Ви ввели тільки '.$e.Ukrainian::Plural($e,[' символ',' символа',' символів']);
	},
];