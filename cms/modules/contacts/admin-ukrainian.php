<?php
namespace CMS;
return[
	#Для admin/index.php
	'EMPTY_SUBJECT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано тему листа'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Формат тексту листа не задано'.($l ? ' (для '.join(', ',$l).')' : '');
	},
];