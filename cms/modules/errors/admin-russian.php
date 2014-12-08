<?php
namespace CMS;
return[
	#Для admin/index.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Название не задано'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Содержимое не задано'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список страниц ошибок',
	'letters'=>'Форматы писем',
	'creating'=>'Создание страницы ошибки',
	'editing'=>'Редактирование страницы ошибки',
	'deleting'=>'Подтверждение удаления',
];