<?php
return array(
	#Для index.php
	'search'=>'Поиск по сайту',
	'find'=>'Найти',
	'enter'=>'Войти',
	'yllg'=>'Вы вошли как гость!',
	'auth'=>'Аутентификация',
	'el'=>'Введите логин',
	'ep'=>'Введите пароль',
	'exit'=>'Выйти',
	'msjump'=>'-Перейти на сайт-',
	'remember'=>'Чужой компьютер',
	'reg'=>'Регистрация',
	'lostp'=>'Забыли пароль?',
	'myacc'=>'Мой аккаунт',
	'chav'=>'Сменить аватар',
	'adminka'=>'Админпанель',

	#Для BlockWhoOnline
	'users'=>function($n){ return$n.Russian::Plural($n,array(' пользователь:',' пользователя:',' пользователей:')); },
	'minutes_ago'=>function($n){ return$n.Russian::Plural($n,array(' минуту назад',' минуты назад',' минут назад')); },
	'bots'=>function($n){ return$n.Russian::Plural($n,array(' поисковый бот:',' поисковых бота:',' поисковых ботов:')); },
	'guests'=>function($n){ return$n.Russian::Plural($n,array(' гость',' гостя',' гостей')); },
	'alls'=>'Полный список',

	#Для Pages
	'selpage'=>'Выбор страницы',
	'<<'=>'Назад',
	'>>'=>'Вперед',

	#Для BlockArchive
	'year-'=>'Год назад',
	'year+'=>'Год вперед',
	'mon'=>'Пн',
	'tue'=>'Вт',
	'wed'=>'Ср',
	'thu'=>'Чт',
	'fri'=>'Пт',
	'sat'=>'Сб',
	'sun'=>'Вс',
	'_cnt'=>function($n){return$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'total'=>function($n){return'Всего - '.$n.Russian::Plural($n,array(' новость',' новости',' новостей'));},
	'no_per'=>'Новостей за этот период нет',

	#Для Captcha
	'captcha'=>'Кликните, чтобы показать другие цифры',
);