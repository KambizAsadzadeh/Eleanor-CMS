<?php
namespace CMS;
return[
	#Для /cms/admin/modules/modules.php
	'list'=>'Перечень модулей',
	'deleting'=>'Подтверждение удаления',
	'creating'=>'Создание модуля',
	'editing'=>'Редактирование модуля',

	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Название модуля не может быть пустым'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'URI_EXISTS'=>function($s){
		return'Модуль с URI &quot;'.join('&quot;, &quot;',$s).'&quot; уже существует';
	},
];