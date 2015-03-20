<?php
return array(
	#Для index.php
	'search'=>'Пошук на сайті',
	'find'=>'Знайти',
	'enter'=>'Увійти',
	'yllg'=>'Ви увійшли як гість!',
	'auth'=>'Аутентифікація',
	'el'=>'Введіть логін',
	'ep'=>'Введіть пароль',
	'exit'=>'Вийти',
	'msjump'=>'-Перейти на сайт-',
	'remember'=>'Чужий комп\'ютер',
	'reg'=>'Реєстрація',
	'lostp'=>'Забули пароль?',
	'myacc'=>'Мій акаунт',
	'chav'=>'Змінити аватар',
	'adminka'=>'Адмінпанель',

	#Для BlockWhoOnline
	'users'=>function($n){ return$n.Ukrainian::Plural($n,array(' користувач:',' користувача:',' користувачів:')); },
	'minutes_ago'=>function($n){ return$n.Ukrainian::Plural($n,array(' хвилину тому:',' хвилини тому',' хвилин тому')); },
	'bots'=>function($n){ return$n.Ukrainian::Plural($n,array(' пошуковий бот',' пошукових бота',' пошукових ботів')); },
	'guests'=>function($n){ return$n.Ukrainian::Plural($n,array(' гість',' гостя',' гостей')); },
	'alls'=>'Повний список',

	#Для Pages
	'selpage'=>'Вибір сторінки',
	'<<'=>'Назад',
	'>>'=>'Вперед',

	#Для BlockArchive
	'year-'=>'Рік назад',
	'year+'=>'Рік вперед',
	'mon'=>'Пн',
	'tue'=>'Вт',
	'wed'=>'Ср',
	'thu'=>'Чт',
	'fri'=>'Пт',
	'sat'=>'Сб',
	'sun'=>'Нд',
	'_cnt'=>function($n){return$n.Ukrainian::Plural($n,array(' новина',' новини',' новин'));},
	'total'=>function($n){return'Всього - '.$n.Ukrainian::Plural($n,array(' новина',' новини',' новин'));},
	'no_per'=>'Новин за цей період немає',

	#Для Captcha
	'captcha'=>'Нажміть, щоб показати інші цифри',
);