<?php
return array(
	#��� user/groups.php
	'groups'=>'����� ������������',

	#��� user/online.php
	'who_online'=>'��� ������',

	#��� user/guest/index.php
	'cabinet'=>'��������� ������',

	#��� user/guest/lostpass.php
	'reminderpass'=>'³��������� ������',
	'wait_pass1'=>'��������� e-mail',
	'new_pass'=>'����� ������ ��� %s',
	'successful'=>'����',

	#��� user/guest/login.php	'TEMPORARILY_BLOCKED'=>'� ��\'���� � ������ ��������� ������������� ������, ������� �����������!<br />�������� ������ ����� %s ������(�).',

	#��� user/guest/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'������� ���� ����������� �� ������� ������������ '.$l.Ukrainian::Plural($l,array(' ������',' �������',' �������')).' �������. �� ����� '.$e.Ukrainian::Plural($e,array(' ������',' �������',' �������')).' �������.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'������� ������ ������� ���� ����� '.$l.Ukrainian::Plural($l,array(' ������',' �������',' �������')).' �������. �� ����� ����� '.$e.Ukrainian::Plural($e,array(' ������',' �������',' �������')).' �������.'; },
	'form_reg'=>'����� ���������',
	'reg_fin'=>'��������� ���������!',
	'wait_act'=>'���������� ���������',

	#��� user/user/activate.php
	'reactivation'=>'�������� ���������',
	'activate'=>'���������',

	#��� user/user/changepass.php
	'changing_email'=>'���� e-mail ������',

	#��� user/user/changepass.php
	'changing_pass'=>'���� ������',

	#��� user/user/externals.php
	'externals'=>'������ ������',

	#��� user/user/settings.php
	'site'=>'����',
	'site_'=>'������ ������ �����, ��������� � http://',
	'lang'=>'����',
	'theme'=>'���� ����������',
	'timezone'=>'������� ����',
	'personal'=>'��������',
	'siteopts'=>'������������ �����',
	'by_default'=>'�� �������������',
	'full_name'=>'����� ��\'�',
	'editor'=>'��������',
	'staticip'=>'��������� IP',
	'staticip_'=>'��� ������� ���� �� ����, ���� ����� ���� ����\'����� �� IP.',
	'gender'=>'�����',
	'male'=>'������',
	'female'=>'Ƴ���',
	'nogender'=>'�� �����',
	'bio'=>'���������',
	'interests'=>'��������',
	'location'=>'�����',
	'location_'=>'������������: �����, ����',
	'signature'=>'ϳ����',
	'connect'=>'��\'����',
	'vk'=>'��������',
	'vk_'=>'���� ����� ������ ���� ��� id, ��� ��\'�',
	'twitter_'=>'���� ����� ������ ���� ��� ��',
	'settings'=>'������������ �������',
);