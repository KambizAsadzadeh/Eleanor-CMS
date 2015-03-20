<?php
defined('CMS\STARTED')||die;

return[
	#For Classes/UsersOnline.php
	'user_info'=>'Information about the visitor',
	'users'=>function($n){return $n.($n>1 ? ' users' : ' user');},
	'min_left'=>function($n){return $n.($n>1 ? ' minutes ago' : ' minute ago');},
	'bots'=>function($n){return $n.' search '.($n>1 ? 'bots' : 'bot');},
	'guests'=>function($n){return $n.($n>1 ? ' guests' : ' guest');},
	'activity'=>'Activity',
	'now_onp'=>'Now on page',
	'r'=>'Referrer',
	'browser'=>'Browser',
	'service'=>'Сервис',
	'c'=>'Supporting charsets',
	'e'=>'Supported data types',
	'ips'=>'Addons IPs',
	'session_nf'=>'Session not found',
	'go'=>'Go',
];