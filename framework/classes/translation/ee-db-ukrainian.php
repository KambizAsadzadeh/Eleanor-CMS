<?php
$ent=ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED;
return[
	'connect'=>function($p)use($ent){
		return'Неможливо підключитися до бази даних '.$p['db']
			.($p['errno']
				?': <b>'.htmlspecialchars($p['error'],$ent,Eleanor\CHARSET,false).'</b> (error #<b>'.$p['errno'].'</b>)'
				:'.');
	},
	'query'=>function($p)use($ent){
		return'SQL запит виконався невдало: <b>'.htmlspecialchars($p['error'],$ent,Eleanor\CHARSET,false)
			.'</b> (error #'.$p['errno'].')';
	},
];