<?php
namespace Eleanor\Classes\Language;
return[
	#Для Classes/Tasks.php
	'NO_NEXT_RUN'=>'При заданому часу запуску, задача ніколи не запуститься',
	'EMPTY_HOUR'=>'Будь ласка, введіть години',
	'EMPTY_MINUTE'=>'Будь ласка, введіть хвилини',
	'EMPTY_SECOND'=>'Будь ласка, введіть секунди',
	'EMPTY_MONTH'=>'Будь ласка, введіть місяці',
	'EMPTY_DAY'=>'Будь ласка, введіть дні',

	'nextrun'=>'Наступний запуск',
	'run_time'=>'Час запуска',
	'lastrun'=>'Останній запуск',
	'months%'=>'місяці: %s; ',
	'run_month'=>function($n)
	{
		if($n==1)
			return'щомісяця ';

		return Ukrainian::Plural($n,['кожен '.$n.' місяць ','кожні '.$n.' місяця ','кожні '.$n.' місяців ']);
	},

	'wdays%'=>'дні тижня: %s; ',
	'days%'=>'дні: %s; ',
	'run_day'=>function($n)
	{
		if($n==1)
			return'щоденно ';

		return Ukrainian::Plural($n,['кожен '.$n.' день ','кожні '.$n.' дня ','кожні '.$n.' дні ']);
	},

	'hours%'=>'години: %s; ',
	'run_hour'=>function($n)
	{
		if($n==1)
			return'щогодинно ';

		return Ukrainian::Plural($n,['кожну '.$n.' годину ','кожні '.$n.' години ','кожні '.$n.' годин ']);
	},

	'minutes%'=>'хвилини: %s; ',
	'run_minute'=>function($n)
	{
		if($n==1)
			return'щохвилинно ';

		return Ukrainian::Plural($n,['кожну '.$n.' хвилину ','кожні '.$n.' хвилини ','кожні '.$n.' хвилин ']);
	},

	'seconds%'=>'секунди: %s; ',
	'run_second'=>function($n)
	{
		if($n==1)
			return'щосекундно ';

		return Ukrainian::Plural($n,['кожну '.$n.' секунду ','кожні '.$n.' секунди ','кожні '.$n.' секунд ']);
	},

	'run'=>'Запустити',
	'title-plh'=>'введіть назву задачі',
	'hour-*'=>'Будь-яку годину',
	'def-hour'=>'У задані годину',
	'minute-*'=>'Будь-яку хвилину',
	'def-minute'=>'У задані хвилини',
	'second-*'=>'Будь-яку секунду',
	'def-second'=>'У задані секунди',
	'month-*'=>'Будь-який місяц',
	'def-month'=>'У задані місяці',
	'day-*'=>'Будь-який день',
	'def-day'=>'У задані дні',
	'handler'=>'Обробник',
	'run-hours'=>'Години запуску',
	'run-minutes'=>'Хвилини запуску',
	'run-seconds'=>'Секунди запуску',
	'run-months'=>'Місяці запуску',
	'run-days'=>'Дні запуску',
	'options'=>'Опції',
	'now'=>'Запустити зараз',
	'example-plh'=>'Приклад: 1,3,5-10',
	'create'=>'Створити задачу',
	'save'=>'Зберегти задачу',
	'delete-text-span'=>'Ви дійсно хочете видалити "<span id="delete-title"></span>"?',
	'delete-text%'=>'Ви дійсно хочете видалити "%s"?',
	'form-errors'=>'Допущено помилки при заповненні форми',
	'applied-by%'=>'Застосовано фільтр %s',
	'task'=>'Задача',
	'by-task'=>'по задачі',
	'by-title'=>'по назві',
	'filter-by-task'=>'Вкажіть ім&apos;я задачі чи його частину',
	'not_found'=>'Задач не знайдено',
];