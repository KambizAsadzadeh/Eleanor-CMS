<?php
return array(
	#Для index.php
	'search'=>'Search on site',
	'find'=>'Find',
	'enter'=>'Sing in',
	'yllg'=>'You are not logged in!',
	'auth'=>'Authentication',
	'el'=>'Enter login',
	'ep'=>'Enter password',
	'exit'=>'Logout',
	'msjump'=>'-Go to site-',
	'remember'=>'Do not remember me',
	'reg'=>'Sing up',
	'lostp'=>'Lost password?',
	'myacc'=>'My account',
	'chav'=>'Change avatar',
	'adminka'=>'Adminpanel',

	#Для BlockWhoOnline
	'users'=>function($n){ return$n.($n>1 ? ' users:' : ' user:'); },
	'minutes_ago'=>function($n){ return$n.($n>1 ? ' minutes ago' : ' minute ago'); },
	'bots'=>function($n){ return$n.' search '.($n>1 ? 'bots:' : 'bot:'); },
	'guests'=>function($n){ return$n.($n>1 ? ' guests' : ' guest'); },
	'alls'=>'Full list',

	#Для Pages
	'selpage'=>'Select page',
	'<<'=>'Backward',
	'>>'=>'Forward',

	#For BlockArchive
	'year-'=>'Year forward',
	'year+'=>'Year backward',
	'mon'=>'Mon',
	'tue'=>'Tue',
	'wed'=>'Web',
	'thu'=>'Thu',
	'fri'=>'Fri',
	'sat'=>'Sat',
	'sun'=>'Sun',
	'_cnt'=>function($n){return$n.' news';},
	'total'=>function($n){return'Totaly '.$n.' news';},
	'no_per'=>'No news in this period',

	#For Captcha
	'captcha'=>'Click to show more digits',
);