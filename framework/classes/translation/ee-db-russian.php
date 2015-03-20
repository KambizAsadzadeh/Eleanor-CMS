<?php
$ent=ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED;
return[
	'connect'=>function($p)use($ent){
		return'Невозможно подключиться к базе данных '.$p['db']
			.($p['errno']
				?': <b>'.htmlspecialchars($p['error'],$ent,Eleanor\CHARSET,false).'</b> (error #<b>'.$p['errno'].'</b>)'
				:'.');
	},
	'query'=>function($p)use($ent){
		return'SQL запрос выполнился неудачно: <b>'
			.htmlspecialchars($p['error'],$ent,Eleanor\CHARSET,false)
			.'</b> (error #'.$p['errno'].')';
	},
];