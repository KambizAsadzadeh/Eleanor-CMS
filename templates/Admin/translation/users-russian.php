<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

$newpass='<b>{site}</b> - название сайта<br />
<b>{name}</b> - имя пользователя<br />
<b>{fullname}</b> - полное имя пользователя<br />
<b>{userlink}</b> - ссылка на пользователя<br />
<b>{pass}</b> - пароль пользователя<br />
<b>{link}</b> - ссылка на сайт';

return[
	#Для шаблона Classes/Users.php
	'username'=>'Имя пользователя',
	'groups'=>'Группы',
	'last_visit'=>'Дата последнего визита',
	'create'=>'Создать пользователя',
	'save'=>'Сохранить пользователя',
	'not_found'=>'Пользователи не найдены',
	'delete-text-span'=>'Вы действительно хотите удалить "<span id="delete-title"></span>"?',
	'delete-text%'=>'Вы действительно хотите удалить "%s"?',
	'form-errors'=>'Допущены ошибки при заполнении формы',
	'name-placeholder'=>'Укажите имя пользователя',
	'by-id'=>'по ID',
	'by-username'=>'по имени',
	'by-full-name'=>'по полному имени',
	'by-group'=>'по группе',
	'by-last-visit'=>'по последнему визиту',
	'by-register'=>'по дате регистрации',
	'by-ip'=>'по ip',
	'by-offline'=>'по офлайну',
	'by-email'=>'по e-mail',
	'applied-by%'=>'Применен фильтр %s',
	'filter-by-id'=>'Укажите id',
	'filter-by-ip'=>'Укажите ip полностью',
	'filter-by-name'=>'Укажите имя пользователя',
	'filter-by-email'=>'Укажите e-mail или его часть',
	'filter-by-user'=>'По пользователя',
	'http-code'=>'HTTP статус-код ошибки',
	'save-success'=>'Успешно сохранено',
	'letters-save'=>'Сохранить',
	'from'=>'от',
	'to'=>'до',
	'full-name'=>'Полное имя',
	'group'=>'Группа',
	'register'=>'Дата регистрации',
	'vars-created'=>$newpass,
	'vars-newpass'=>$newpass,
	'vars-renamed'=>'<b>{site}</b> - название сайта<br />
<b>{oldname}</b> - старое имя пользователя<br />
<b>{name}</b> - новое имя пользователя<br />
<b>{fullname}</b> - полное имя пользователя<br />
<b>{userlink}</b> - ссылка на пользователя<br />
<b>{link}</b> - ссылка на сайт',
	'who'=>'Кто',
	'enter'=>'Дата входа',
	'location'=>'Адрес страницы',
	'guest'=>'Гость',
	'only'=>'Исключительно',
	'include'=>'Отображать',
	'session_not_found'=>'Сессии не найдены',
	'offline'=>'Офлайн',
	'name'=>'Имя',
	'details'=>'Подробности',
	'activity'=>'Активность',
	'now_onp'=>'Сейчас на странице',
	'r'=>'Перешел с',
	'browser'=>'Браузер',
	'service'=>'Сервис',
	'c'=>'Поддержка кодировок',
	'e'=>'Поддерживаемые типы данных',
	'ips'=>'IP дополнительные',
	'session_nf'=>'Сессия не найдена',
	'go'=>'Перейти',
	'input_name'=>'Введите имя пользователя (логин входа)',
	'full_name'=>'Полное имя пользователя (ФИО)',
	'password'=>'Пароль',
	'password-leave'=>'Можно не вводить - система сгенерирует автоматически',
	'avatar'=>'Аватар',
	'main-group'=>'Основная группа',
	'other-groups'=>'Вторичные группы',
	'letter-new-name'=>'Уведомить пользователя об изменении имени',
	'letter-new-pass'=>'Уведомить пользователя о новом пароле',
	'letter-new-account'=>'Уведомить пользователя о создании учетной записи',
	'is-online'=>'Пользователь сейчас онлайн',
	'auto'=>'Автовыбор',
	'rights-on-site'=>'Права на сайте',
	'localization'=>'Локализация',
	'language'=>'Язык',
	'timezone'=>'Часовой пояс',
	'server-time'=>'Время сервера',
	'blocking'=>'Блокировка',
	'banned-until'=>'Заблокировать до',
	'ban-explain'=>'Причина блокировки',
	'input-ban-explain'=>'Укажите причину блокировки',
	'socials'=>'Социальные сервисы',
	'failed-login'=>'Неудачные попытки авторизации',
	'clean'=>'Очистить',
	'active-sessions'=>'Активные сессии',
	'replace'=>'Заменить',
	'min_left'=>function($n){
		return$n.Russian::Plural($n,[' минуту назад',' минуты назад',' минут назад']);
	},

	#Внешняя авторизация
	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'Яндекс',
	'vk.com'=>'ВКонтакте',
	'odnoklassniki.ru'=>'Одноклассники',
	'mail.ru'=>'Mail.ru',

	#Errors
	'SITE_ERROR'=>'Адрес сайта введен некорректно',
	'SHORT_ICQ'=>'Номер ICQ должен содержать как минимум 5 цифр',
	'ERROR_BAN_DATE'=>'Некорректно введена дата блокировки пользователя',
	'AVATAR_NOT_EXISTS'=>'Выбранного аватара не существует',
	'EMPTY_NAME'=>'Имя пользователя не заполнено',
	'EMAIL_ERROR'=>'Введен некорректный e-mail',
	'NAME_EXISTS'=>'Пользователь с таким именем уже существует',
	'EMAIL_EXISTS'=>'Пользователь с таким e-mail уже существует',
	'EMPTY_EMAIL'=>'E-mail не задан',
];