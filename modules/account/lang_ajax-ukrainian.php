<?php
return array(
	#��� ajax/user/index.php
	'error_email'=>'������� ������������� e-mail!',
	'email_in_use'=>'��� e-mail ��� ��������������� ����� ������������!',

	#��� ajax/user/register.php
	'name_too_long'=>function($n)
	{		return'������� ���� �������� ���������� ���� � '.$n.Ukrainian::Plural($n,array(' ������',' �������',' �������'));
	},
	'error_name'=>'������� ������������� ��',
	'name_in_use'=>'��� �� ��� ��������������� ����� ������������!',

	#��� �������
	'noavatars'=>'��������� ������� ����',
	'cancel'=>'���������',
	'togals'=>'�� �������',
);