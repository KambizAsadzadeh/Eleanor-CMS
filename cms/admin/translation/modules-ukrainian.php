<?php
namespace CMS;
return[
	#Для /cms/admin/modules/modules.php
	'list'=>'Перелік модулів',
	'deleting'=>'Підтвердження видалення',
	'creating'=>'Створення модуля',
	'editing'=>'Редагування модуля',

	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Назва модуля не може бути порожньою'.($l ? ' (для '.join(', ',$l).')' : '');
	},
	'URI_EXISTS'=>function($s){
		return'Модуль з URI &quot;'.join('&quot;, &quot;',$s).'&quot; вже існує';
	},
];