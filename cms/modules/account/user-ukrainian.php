<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

return[
	#Для user/groups.php
	'groups'=>'Групи користувачів',

	#Для user/online.php
	'who_online'=>'Хто онлайн',

	#Для user/guest/index.php
	'cabinet'=>'Особистий кабінет',

	#Для user/guest/lostpass.php
	'reminderpass'=>'Відновлення пароля',
	'wait_pass1'=>'Проверьте e-mail',
	'new_pass'=>'Новий пароль для %s',
	'successful'=>'Успіх',

	#Для user/guest/login.php
	'TEMPORARILY_BLOCKED'=>'У зв\'язку з частим введенням неправильного пароля, аккаунт заблоковано!<br />Повторіть спробу через %s хвилин(и).',

	#Для user/guest/register.php
	'NAME_TOO_LONG'=>function($l,$e){
		return'Довжина імені користувача не повинна перевищувати '.$l
			.Ukrainian::Plural($l,[' символ',' символи',' символів']).' символів. Ви ввели '.$e
			.Ukrainian::Plural($e,[' символ',' символи',' символів']).' символів.';
	},
	'PASS_TOO_SHORT'=>function($l,$e){
		return'Довжина пароля повинна бути мінімум '.$l
			.Ukrainian::Plural($l,[' символ',' символи',' символів']).' символів. Ви ввели тільки '.$e
			.Ukrainian::Plural($e,[' символ',' символи',' символів']).' символів.';
	},
	'form_reg'=>'Форма реєстрації',
	'reg_fin'=>'Реєстрація завершена!',
	'wait_act'=>'Очікування активації',

	#Для user/user/activate.php
	'reactivation'=>'Повторна активація',
	'activate'=>'Активація',

	#Для user/user/changepass.php
	'changing_email'=>'Зміна e-mail адреси',

	#Для user/user/changepass.php
	'changing_pass'=>'Зміна пароля',

	#Для user/user/externals.php
	'externals'=>'Зовнішні сервіси',

	#Для user/user/settings.php
	'site'=>'Сайт',
	'site_'=>'Введіть адресу сайту, починаючи з http://',
	'lang'=>'Мова',
	'theme'=>'Тема оформлення',
	'timezone'=>'Часовий пояс',
	'personal'=>'Особисте',
	'siteopts'=>'Налаштування сайту',
	'by_default'=>'За замовчуванням',
	'full_name'=>'Повне ім\'я',
	'editor'=>'Редактор',
	'gender'=>'Стать',
	'male'=>'Чоловік',
	'female'=>'Жінка',
	'nogender'=>'Не скажу',
	'bio'=>'Біографія',
	'interests'=>'Інтереси',
	'location'=>'Звідки',
	'location_'=>'Розташування: країна, місто',
	'signature'=>'Підпис',
	'connect'=>'Зв\'язок',
	'vk'=>'ВКонтакті',
	'vk_'=>'Будь ласка введіть лише свій id, або ім\'я',
	'twitter_'=>'Будь ласка введіть лише свій нік',
	'settings'=>'Налаштування профілю',
];