<?php
return array(
	#��� ajax/user/index.php
	'error_email'=>'������� ������������� e-mail!',
	'email_in_use'=>'��� e-mail ��� ��������������� ����� ������������!',

	#��� ajax/user/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'������� ���� ����������� �� ������� ������������ '.$l.Ukrainian::Plural($l,array(' ������',' �������',' �������')).' �������. �� ����� '.$e.Ukrainian::Plural($e,array(' ������',' �������',' �������')).' �������.'; },
	'error_name'=>'������� ������������� ��',
	'name_in_use'=>'��� �� ��� ��������������� ����� ������������!',
);