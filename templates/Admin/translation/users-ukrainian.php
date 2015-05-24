<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

$newpass='<b>{site}</b> - назва сайту<br />
<b>{name}</b> - ім&apos;я користувача<br />
<b>{fullname}</b> - повне ім&apos;я користувача<br />
<b>{userlink}</b> - посилання на користувача<br />
<b>{pass}</b> - пароль користувача<br />
<b>{link}</b> - посилання на сайт';

return[
	#Для шаблона Classes/Users.php
	'username'=>'ім&apos;я користувача',
	'groups'=>'Групи',
	'last_visit'=>'Дата останнього візиту',
	'create'=>'Створити користувача',
	'save'=>'Зберегти користувача',
	'not_found'=>'Користувачів не знайдено',
	'delete-text-span'=>'Вы дійсно хочете видалити "<span id="delete-title"></span>"?',
	'delete-text%'=>'Вы дійсно хочете видалити "%s"?',
	'form-errors'=>'Допущені помилки при заповненні форми',
	'name-placeholder'=>'Вкажіть ім&apos;я користувача',
	'by-id'=>'по ID',
	'by-username'=>'по імені',
	'by-full-name'=>'по повному імені',
	'by-group'=>'по групі',
	'by-last-visit'=>'по останньому візіту',
	'by-register'=>'по даті реєстріції',
	'by-ip'=>'по ip',
	'by-offline'=>'по офлайну',
	'by-email'=>'по e-mail',
	'applied-by%'=>'Застосовано фільтр %s',
	'filter-by-id'=>'Вкажіть id',
	'filter-by-ip'=>'Вкажіть ip повністю',
	'filter-by-name'=>'Вкажіть ім&apos;я користувача',
	'filter-by-email'=>'Вкажіть e-mail чи його частину',
	'filter-by-user'=>'По користувачеві',
	'http-code'=>'HTTP статус-код помилки',
	'save-success'=>'Успішно збережено',
	'letters-save'=>'Зберегти',
	'from'=>'від',
	'to'=>'до',
	'full-name'=>'Повне ім&apos;я',
	'group'=>'Група',
	'register'=>'Дата реєстріції',
	'vars-created'=>$newpass,
	'vars-newpass'=>$newpass,
	'vars-renamed'=>'<b>{site}</b> - назва сайту<br />
<b>{oldname}</b> - старе ім&apos;я користувача<br />
<b>{name}</b> - нове ім&apos;я користувача<br />
<b>{fullname}</b> - повне ім&apos;я користувача<br />
<b>{userlink}</b> - посилання на користувача<br />
<b>{link}</b> - посилання на сайт',
	'who'=>'Хто',
	'enter'=>'Дата входу',
	'location'=>'Адреса сторінки',
	'guest'=>'Гість',
	'only'=>'Виключно',
	'include'=>'Відображати',
	'session_not_found'=>'Сессій не знайдено',
	'offline'=>'Офлайн',
	'name'=>'ім&apos;я',
	'details'=>'Подробиці',
	'activity'=>'Активність',
	'now_onp'=>'Зараз на сторінці',
	'r'=>'Перейшов з',
	'browser'=>'Браузер',
	'service'=>'Сервіс',
	'c'=>'Підтримка кодувань',
	'e'=>'Типи даних, що підтримуються',
	'ips'=>'IP додаткові',
	'session_nf'=>'Сесію не знайдено',
	'go'=>'Перейти',
	'input_name'=>'Введіть ім&apos;я користувача (логін входу)',
	'full_name'=>'Повне ім&apos;я користувача (ПІБ)',
	'password'=>'Пароль',
	'password-leave'=>'Можна не вводити - система сгенерує автоматично',
	'avatar'=>'Аватар',
	'main-group'=>'Основна група',
	'other-groups'=>'Вторичні групи',
	'letter-new-name'=>'Повідомити користувача про заміну імені',
	'letter-new-pass'=>'Повидомити користувача про заміну пароля',
	'letter-new-account'=>'Повідомити користувача про створення облікового запису',
	'is-online'=>'Користувач зараз онлайн',
	'auto'=>'Автовибір',
	'rights-on-site'=>'Права на сайті',
	'localization'=>'Локалізація',
	'language'=>'Мова',
	'timezone'=>'Часовий пояс',
	'server-time'=>'Серверний час',
	'blocking'=>'Блокування',
	'banned-until'=>'Заблокувати до',
	'ban-explain'=>'Причина блокування',
	'input-ban-explain'=>'Вкажіть причину блокування',
	'socials'=>'Социальні сервіси',
	'failed-login'=>'Невдалі спроби авторизації',
	'clean'=>'Очистити',
	'active-sessions'=>'Активні сесії',
	'replace'=>'Заменить',
	'min_left'=>function($n){
		return$n.Ukrainian::Plural($n,[' хвилину тому:',' хвилини тому',' хвилин тому']);
	},

	#Внешняя авторизация
	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'Яндекс',
	'vk.com'=>'ВКонтакте',
	'odnoklassniki.ru'=>'Одноклассники',
	'mail.ru'=>'Mail.ru',

	#Errors
	'SITE_ERROR'=>'Адреса сайту введена некоректно',
	'SHORT_ICQ'=>'Номер ICQ повинен вміщувати як мінімум 5 цифр',
	'ERROR_BAN_DATE'=>'Некоректно введена дата блокування користувача',
	'AVATAR_NOT_EXISTS'=>'Обраного аватара не існує',
	'EMPTY_NAME'=>'ім&apos;я користувача не заповнено',
	'EMAIL_ERROR'=>'Уведений некоректний e-mail',
	'NAME_EXISTS'=>'Користувач з таким ім&apos;ям вже існує',
	'EMAIL_EXISTS'=>'Користувач з таким e-mail вже існує',
	'EMPTY_EMAIL'=>'E-mail не задано',
];