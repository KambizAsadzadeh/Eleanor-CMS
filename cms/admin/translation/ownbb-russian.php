<?php
namespace CMS;
return[
	#Для /cms/admin/modules/ownbb.php
	'list'=>'Список BB кодов',
	'deleting'=>'Подтверждение удаления',
	'creating'=>'Создание BB тега',
	'editing'=>'Редактирование BB тега',

	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Описание не задано'.($l ? ' (для '.join(', ',$l).')' : '');
	},
];