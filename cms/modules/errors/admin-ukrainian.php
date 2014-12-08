<?php
namespace CMS;
return[
	#Для admin/index.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано назву'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано вміст'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список сторінок помилок',
	'letters'=>'Формати листів',
	'creating'=>'Створення сторінки помилки',
	'editing'=>'Редагування сторінки помилки',
	'deleting'=>'Підтвердження видалення',
];