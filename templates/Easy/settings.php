<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;

return[
	'service'=>['index'],#Шаблон для пользователей
	'creation'=>'2012-27-06',#Дата создания
	'author'=>'Eleanor CMS team',#Автор шаблона
	'name'=>'Стандартный шаблон первой версии',#Название шаблона
	'info'=><<<'INFO'
	Информация
INFO
,
	'license'=><<<'LICENSE'
Стандартная тема оформления пользовательской части. Пользоваться ею в составе Eleanor CMS можно бесплатно. Использовать этот шаблон или его части в сторонних разработках - строго запрещено!
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
			'extra'=>'416,107,182,270,2',
		],
		'center_up'=>[
			'title'=>[
				'russian'=>'Верхние центральные',
				'english'=>'Up central',
				'ukrainian'=>'Верхні центральні',
			],
			'extra'=>'50,0,548,101,3',
		],
		'center_down'=>[
			'title'=>[
				'russian'=>'Нижние центральные',
				'english'=>'Down central',
				'ukrainian'=>'Нижні центральні',
			],
			'extra'=>'50,393,548,101,4',
		],
	],

	'options'=>[#Опции в общесистемном виде
		'eleanor'=>[
			'title'=>'Отображать рекламу Eleanor CMS',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>[
				'extra'=>['class'=>'need-tabindex'],
			],
		],
		'downtags'=>[
			'title'=>'Отображать облако тегов снизу',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>[
				'extra'=>['class'=>'need-tabindex'],
			],
		],
	]
];