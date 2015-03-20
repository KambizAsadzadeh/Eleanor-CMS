<?php
namespace CMS;
return[
	#Для /cms/admin/modules/tasks.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Название не задано'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список задач',
	'deleting'=>'Подтверждение удаления',
	'creating'=>'Создание задачи',
	'editing'=>'Редактирование задачи',
];