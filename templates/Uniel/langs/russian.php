<?php
return array(
	#��� index.php
	'loading'=>'��������. ����������, ���������...',
	'to_top'=>'�����',
	'login'=>'�����:',
	'pass'=>'������:',
	'enter'=>'�����',
	'hello'=>'����� ����������, %s!',
	'adminka'=>'�����-������',
	'exit'=>'�����',
	'register'=>'�����������',
	'lostpass'=>'������ ������?',
	'msjump'=>'-�������-',

	#��� Confirm.php
	'no'=>'���',
	'yes'=>'��',

	#��� Denied.php
	'site_close_text'=>'���� �������� ����������! ������� ���������� ������',

	#��� EditDelete.php
	'delete'=>'�������',
	'edit'=>'�������',

	#��� LangChecks.php
	'for_all_langs'=>'��� ���� ������',

	#��� Rating.php
	'average_mark'=>'������� ������: %s; �������������: %s',

	#��� Pages.php
	'pages'=>'��������:',
	'goto_page'=>'������� �� ��������',

	#��� Message.php
	'warning'=>'��������������',
	'error'=>'������',
	'errors'=>'������',
	'info'=>'����������',

	#��� Captcha.php
	'captcha'=>'��������, ����� �������� ������ �����',

	#��� BlockWhoOnline.php
	'users'=>function($n){ return$n.Russian::Plural($n,array(' ������������:',' ������������:',' �������������:')); },
	'minutes_ago'=>function($n){ return$n.Russian::Plural($n,array(' ������ �����',' ������ �����',' ����� �����')); },
	'bots'=>function($n){ return$n.Russian::Plural($n,array(' ��������� ���:',' ��������� ����:',' ��������� �����:')); },
	'guests'=>function($n){ return$n.Russian::Plural($n,array(' �����',' �����',' ������')); },
	'alls'=>'������ ������',

	#��� BlockArchive.php
	'year-'=>'��� �����',
	'year+'=>'��� ������',
	'mon'=>'��',
	'tue'=>'��',
	'wed'=>'��',
	'thu'=>'��',
	'fri'=>'��',
	'sat'=>'��',
	'sun'=>'��',
	'_cnt'=>function($n){return$n.Russian::Plural($n,array(' �������',' �������',' ��������'));},
	'total'=>function($n){return'����� - '.$n.Russian::Plural($n,array(' �������',' �������',' ��������'));},
	'no_per'=>'�������� �� ���� ������ ���',

	#��� Editor.php
	'smiles'=>'������',
);