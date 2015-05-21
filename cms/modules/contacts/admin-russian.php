<?php
namespace CMS;
return[
	#Для admin/index.php
	'EMPTY_SUBJECT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Тема письма не задана'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Формат текста письма не задан'.($l ? ' (для '.join(', ',$l).')' : '');
	},
];