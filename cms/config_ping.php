<?php
#http://www.weblogs.com/api.html
return[
	#http://blogs.yandex.ru/pings/info/
	'yandex'=>[
		'url'=>'http://ping.blogs.yandex.ru/RPC2',
		'methods'=>'weblogUpdates.ping',
	],

	#http://www.google.com/help/blogsearch/pinging_API.html
	'google'=>[
		'url'=>'http://blogsearch.google.com/ping/RPC2',
		'methods'=>'weblogUpdates.extendedPing',
	]
];