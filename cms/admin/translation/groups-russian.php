<?php
namespace CMS;
return[
	#Для /cms/admin/modules/groups.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Пожалуйста, введите название группы'.($l ? ' (для '.join(', ',$l).')' : '');
	},

	'list'=>'Список групп',
	'deleting'=>'Подтверждение удаления',
	'creating'=>'Создание группы',
	'editing'=>'Редактирование группы',
];