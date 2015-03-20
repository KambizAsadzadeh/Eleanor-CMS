<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;
return [
	'service'=>['admin'],#Шаблон для админки
	'creation'=>'2009-12-29',#Дата создания
	'author'=>'Eleanor CMS team',#Автор шаблона
	'title'=>'Стандартный шаблон панели администратора',#Название шаблона
	'info'=><<<'INFO'
	Информация
INFO
,
	'license'=><<<'LICENSE'
Стандартная тема оформления панели администратора. Пользоваться ею в составе Eleanor CMS можно бесплатно. Использовать этот шаблон или его части в сторонних разработках - строго запрещено!
LICENSE
,#Лицензия

	#Места блоков
	'places'=>[
		'right'=>[
			'title'=>[
				'russian'=>'Правые блоки',
				'english'=>'Right blocks',
				'ukrainian'=>'Праві блоки',
			],
			'extra'=>'276,10,160,229,0',
		],
	],

	'options'=>[
		'sizethm'=>[
			'title'=>'Тип размера админчасти',
			'descr'=>'По умолчанию: Резиновый',
			'default'=>1,
			'options'=>[
				'options'=>['r'=>'Резиновый','f'=>'Фиксированный'],
				'class'=>'need-tabindex',
			],
			'type'=>'select',
		],
		'colorbg'=>[
			'title'=>'Цвет фона',
			'descr'=>'По умолчанию: #2d2f30',
			'default'=>'#2d2f30',
			'type'=>'input',
			'options'=>['class'=>'need-tabindex'],
		],
		'imagebg'=>[
			'title'=>'Фоновое изображение',
			'descr'=>'По умолчанию: templates/Audora/images/pagebg.png',
			'default'=>'templates/Audora/images/pagebg.png',
			'type'=>'input',
			'options'=>['class'=>'need-tabindex'],
		],
		'positionimg'=>[
			'title'=>'Позиция фонового изображения',
			'descr'=>'В формате X Y, где X - позиция по оси x, Y - позиция по оси y. Например: 50% 5px. По умолчанию: 0 0.',
			'default'=>'0 0',
			'type'=>'input',
			'options'=>['class'=>'need-tabindex'],
		],
		'bgattachment'=>[
			'title'=>'Прокрутка фонового изображения',
			'descr'=>'По умолчанию: отключена',
			'default'=>false,
			'type'=>'check',
			'options'=>['class'=>'need-tabindex'],
		],
		'bgrepeat'=>[
			'title'=>'Повтор фонового изображения',
			'descr'=>'По умолчанию: По горизонтали',
			'default'=>'repeat-x',
			'options'=>[
				'options'=>['repeat'=>'По горизонтали и вертикали','repeat-x'=>'По горизонтали','repeat-y'=>'По вертикали','no-repeat'=>'Не повторять'],
				'class'=>'need-tabindex',
			],
			'type'=>'select',
		],
	],
];