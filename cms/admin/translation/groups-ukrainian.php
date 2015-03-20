<?php
namespace CMS;
return[
	#Для /cms/admin/modules/groups.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано назву групи'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список груп',
	'deleting'=>'Підтвердження видалення',
	'creating'=>'Створення групи',
	'editing'=>'Редагування групи',
];