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
	'title'=>'Стандартный шаблон',#Название шаблона
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
		'left'=>[
			'title'=>[
				'russian'=>'Левые блоки',
				'english'=>'Left blocks',
				'ukrainian'=>'Ліві блоки',
			],
			'extra'=>'50,108,184,270,1',
		],
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
		'Группа 1: текстовые поля',
		'param1'=>[
			'title'=>'Строка',
			'descr'=>'описание строки',
			'default'=>'значение по-умолчанию',
			'type'=>'input',
			'options'=>[
				'extra'=>['class'=>'need-tabindex']
			],
		],
		'param2'=>[
			'title'=>'Текстовое поле',
			'descr'=>'описание текстового поля',
			'default'=>'значение по-умолчанию',
			'type'=>'text',
			'options'=>[
				'extra'=>['class'=>'need-tabindex'],
			],
		],
		'param3'=>[
			'title'=>'Текстовый редактор',
			'descr'=>'описание текстового редактора',
			'default'=>'значение по-умолчанию',
			'type'=>'editor',
			'extra'=>[
				'no'=>['class'=>'need-tabindex'],
			]
		],
		'Группа 2: select-ы',
		'param4'=>[
			'title'=>'Выбор1',
			'descr'=>'описание выбора 1',
			'default'=>1,
			'options'=>[
				'options'=>[1,2,3,4,5],
				'extra'=>['class'=>'need-tabindex'],
			],
			'type'=>'select',
		],
		'param5'=>[
			'title'=>'Выбор2',
			'descr'=>'описание выбора 2',
			'default'=>2,
			'options'=>[
				'options'=>[1,2,3,4,5],
				'extra'=>['class'=>'need-tabindex','size'=>10],
			],
			'type'=>'select',
		],
		'param6'=>[
			'title'=>'Множественный выбор',
			'descr'=>'описание множественного выбора',
			'default'=>[0,1,2],
			'options'=>[
				'options'=>[1,2,3,4,5],
				'extra'=>['class'=>'need-tabindex'],
			],
			'type'=>'items',
		],
		'Группа 3: Chechbox',
		'param8'=>[
			'title'=>'Checkbox',
			'descr'=>'описание checkbox-a',
			'default'=>true,
			'type'=>'check',
			'options'=>[
				'extra'=>['class'=>'need-tabindex'],
			],
		],
		'param9'=>[
			'title'=>'Checkboxes',
			'descr'=>'описание множественного выбора',
			'default'=>[0,1,2],
			'options'=>[
				'options'=>[1,2,3,4,5],
			],
			'type'=>'checks',
		],
		'Группа 4: Что угодно',
		'param10'=>[
			'title'=>'Что угодно',
			'descr'=>'описание чего угодно',
			'options'=>[
				'content'=>'Что угодно здесь можно поместить',
			],
			'type'=>'',
		],
	]
];