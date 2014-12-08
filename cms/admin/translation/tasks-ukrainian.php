<?php
namespace CMS;
return[
	#Для /cms/admin/modules/tasks.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Не задано назву'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список задач',
	'deleting'=>'Підтвердження видалення',
	'creating'=>'Створення задачі',
	'editing'=>'Редагування задачі',
];