<?php
return array(
	#��� index.php
	'loading'=>'������������. ��������� ���� �����...',
	'to_top'=>'�����',
	'login'=>'����:',
	'pass'=>'������:',
	'enter'=>'�����',
	'hello'=>'������� �������, %s!',
	'adminka'=>'����-������',
	'exit'=>'�����',
	'register'=>'���������',
	'lostpass'=>'������ ������?',
	'msjump'=>'-�������-',

	#��� Confirm
	'no'=>'ͳ',
	'yes'=>'���',

	#��� Denied	'site_close_text'=>'���� ��������� �����������! ����������� ����� ������',

	#��� EditDelete
	'delete'=>'��������',
	'edit'=>'����������',

	#��� LangChecks
	'for_all_langs'=>'��� ��� ���',

	#��� Rating
	'average_mark'=>'������ ������: %s; �������������: %s',

	#��� Pages
	'pages'=>'�������:',
	'goto_page'=>'������� �� �������',

	#��� Message
	'warning'=>'������������',
	'error'=>'�������',
	'error�'=>'�������',
	'info'=>'����������',

	#��� Captcha
	'captcha'=>'������, ��� �������� ���� �����',

	#��� BlockWhoOnline
	'users'=>function($n){ return$n.Ukrainian::Plural($n,array(' ����������:',' �����������:',' ������������:')); },
	'minutes_ago'=>function($n){ return$n.Ukrainian::Plural($n,array(' ������� ����:',' ������� ����',' ������ ����')); },
	'bots'=>function($n){ return$n.Ukrainian::Plural($n,array(' ��������� ���',' ��������� ����',' ��������� ����')); },
	'guests'=>function($n){ return$n.Ukrainian::Plural($n,array(' ����',' �����',' ������')); },
	'alls'=>'������ ������',

	#��� BlockArchive
	'year-'=>'г� �����',
	'year+'=>'г� ������',
	'mon'=>'��',
	'tue'=>'��',
	'wed'=>'��',
	'thu'=>'��',
	'fri'=>'��',
	'sat'=>'��',
	'sun'=>'��',
	'_cnt'=>function($n){return$n.Ukrainian::Plural($n,array(' ������',' ������',' �����'));},
	'total'=>function($n){return'������ - '.$n.Ukrainian::Plural($n,array(' ������',' ������',' �����'));},
	'no_per'=>'����� �� ��� ����� ����',
);