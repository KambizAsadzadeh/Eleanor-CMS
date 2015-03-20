<?php
namespace CMS;
return[
	#Для /cms/admin/modules/ownbb.php
	'list'=>'Список BB тегів',
	'deleting'=>'Підтвердження видалення',
	'creating'=>'Створення BB тегу',
	'editing'=>'Редагування BB тегу',

	#Для /cms/admin/modules/tasks.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано опис'.($l ? ' (для '.join(', ',$l).')' : '');
	},
];