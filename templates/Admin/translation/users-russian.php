<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

$newpass='<span class="alert-link">{site}</span> - название сайта<br />
<span class="alert-link">{name}</span> - имя пользователя<br />
<span class="alert-link">{fullname}</span> - полное имя пользователя<br />
<span class="alert-link">{userlink}</span> - ссылка на пользователя<br />
<span class="alert-link">{pass}</span> - пароль пользователя<br />
<span class="alert-link">{link}</span> - ссылка на Ваш сайт';

return[
	#Для шаблона UsersOnline.php
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
	'vars-renamed'=>'<span class="alert-link">{site}</span> - название сайта<br />
<span class="alert-link">{oldname}</span> - старое имя пользователя<br />
<span class="alert-link">{name}</span> - новое имя пользователя<br />
<span class="alert-link">{fullname}</span> - полное имя пользователя<br />
<span class="alert-link">{userlink}</span> - ссылка на пользователя<br />
<span class="alert-link">{link}</span> - ссылка на Ваш сайт',
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




	'add'=>'Создать пользователя',
	'not_imp'=>'-Не важно-',
	'begins'=>'Начинается с',
	'contains'=>'Содержит',
	'match'=>'Совпадает',
	'endings'=>'Заканчивается на',
	'fullname'=>'Полное имя',

	'agroups'=>'Дополнительные группы',
	'from-to'=>'от - до ГГГГ-ММ-ДД ЧЧ:ММ:СС',
	'unf'=>'Пользователи не найдены',
	'upp'=>'Пользователей на страницу: %s',
	'deleting'=>'Вы действительно хотите удалить пользователя <b>%s</b> (%s)?',
	'lap'=>'Имя и пароль',
	'general'=>'Основное',
	'extra'=>'Личное',
	'special'=>'Специальное',
	'block'=>'Блокировка',
	'statistics'=>'Статистика',
	'pass'=>'Пароль',
	'passc'=>'Еще раз пароль',
	'pass_'=>'Можно не вводить - система сгенерирует автоматически',
	'slname'=>'Информировать об изменении имени',
	'slname_'=>'Пользователю будет выслано сообщение с информацией о новом имени',
	'slpass'=>'Информировать об изменении пароля',
	'slpass_'=>'Пользователю будет выслано сообщение с информацией о новом пароле',
	'slnew'=>'Информировать о создании учетной записи',
	'slnew_'=>'Пользователю будет выслано сообщение с информацией о его новой учетной записи на Вашем сайте',
	'account'=>'Учётная запись',
	'lang'=>'Язык',
	'timezone'=>'Часовой пояс',
	'inherit'=>'Наследовать',
	'addo'=>'Добавить',
	'replace'=>'Заменить',
	'ban-to'=>'Забанить до',
	'ban-exp'=>'Причина бана',
	'fla'=>'Неудачные попытки авторизации',
	'clean'=>'Очистить',
	'avatar'=>'Аватар',
	'alocation'=>'Размещение',
	'apersonal'=>'Загрузить',
	'agallery'=>'Из галереи',
	'amanage'=>'Управление',
	'gallery_select'=>'Выбрать',
	'noavatar'=>'Нет аватара',
	'sessions'=>'Открытые сессии пользователя',
	'find'=>'Найти',
	'externals'=>'Внешние сервисы',
	'awo'=>'всех, кто онлайн',
	'alls'=>'всех',
	'allg'=>'всех, кто заходил',
	'sshow'=>'Отображать',
	'ets'=>'Вход на сайт',
	'expire'=>'Истекает %s',
	'expired'=>'Истекла %s',
	'spp'=>'Сессий на страницу: %s',
	'no_avatars'=>'Доступных аватаров нет',
	'cancel_avatar'=>'Отменить',
	'togals'=>'К галереям',
	'datee'=>'Дата истечения',
	'csnd'=>'Текущую сессию нельзя удалять',
	'snf'=>'Сессии не найдены',

	#Errors
	'SITE_ERROR'=>'Адрес сайта введен некорректно!',
	'SHORT_ICQ'=>'Номер ICQ должен содержать как минимум 5 цифр',
	'ERROR_BANDATE'=>'Некорректно введена дата блокировки пользователя',
	'PASSWORD_MISMATCH'=>'Пароли не совпадают',
	'AVATAR_NOT_EXISTS'=>'Выбранного аватара не существует',
	'EMPTY_NAME'=>'Имя пользователя не заполнено',
	'EMAIL_ERROR'=>'Введен некорректный e-mail',
	'EMPTY_PASSWORD'=>'Пароль не задан',
	'NAME_EXISTS'=>'Пользователь с таким именем уже существует',
	'EMAIL_EXISTS'=>'Пользователь с таким e-mail уже существует',
	'EMPTY_EMAIL'=>'E-mail не задан',

	#Внешняя авторизация
	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'Яндекс',
	'vkontakte.ru'=>'VK',
];