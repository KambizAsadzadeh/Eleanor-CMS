<?php
return array(
	'no'=>'�',
	'yes'=>'���',
	'all'=>'��',
	'ok'=>'��',
	'update'=>'�������',
	'delete'=>'��������',
	'edit'=>'����������',
	'category'=>'��������',
	'loading'=>'������������. ��������� ���� �����...',
	'to_top'=>'�����',
	'login'=>'����:',
	'pass'=>'������:',
	'enter'=>'�����',
	'search'=>'�����',
	'cancel'=>'���������',
	'tags'=>'����',
	'site_close_text'=>'���� ��������� �����������! ����������� ����� ������',
	'hello'=>'������� �������, ',
	'adminka'=>'����-������',
	'exit'=>'�����',
	'register'=>'���������',
	'lostpass'=>'������ ������?',
	'users'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' ����������:',' �����������:',' ������������:'));
	},
	'minutes_ago'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' ������� ����:',' ������� ����',' ������ ����'));
	},
	'bots'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' ��������� ���',' ��������� ����',' ��������� ����'));
	},
	'guests'=>function($n)
	{
		return$n.Ukrainian::Plural($n,array(' ����',' �����',' ������'));
	},
	'alls'=>'������ ������',
	'back'=>'�����',
	'captcha'=>'������, ��� �������� ���� �����',
	'warning'=>'������������',
	'error'=>'�������',
	'error�'=>'�������',
	'info'=>'����������!',
	'pages'=>'�������:',
	'goto_page'=>'������� �� �������',
	'average_mark'=>'������ ������: %s; �������������: %s',
	'for_all_langs'=>'��� ��� ���',
	'msjump'=>'-�������-',
);