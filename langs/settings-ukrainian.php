<?php
return array(
	#��� /core/others/settings/simple.php & /core/others/settings/full.php
	'setting_og'=>'��������� ������� ����������� �����',
	'reset_g_con'=>'�������� ����������� �����',
	's_phrase_len'=>'������� �������� ����� ������� ���� ����� ���� �������!',
	'ops_not_found'=>'�� ������ &quot;%s&quot; ������������ �� �������',
	'cnt_seaop'=>'����� ����������� (�������� %s)',
	'f_not_load'=>'���� �� ������������',
	'import_result'=>function($gd,$od,$ag,$ug,$ao,$uo)
	{
		return rtrim(($gd>0 ? $gd.Ukrainian::Plural($gd,array(' �����',' �����',' ����')).' ��������, ' : '')
			.($od>0 ? $od.Ukrainian::Plural($od,array(' ������������',' ������������',' �����������')).' ��������, ' : '')
			.($ag>0 ? $ag.Ukrainian::Plural($ag,array(' �����',' �����',' ����')).' ������, ' : '')
			.($ug>0 ? $ug.Ukrainian::Plural($ug,array(' �����',' �����',' ����')).' ��������, ' : '')
			.($ao>0 ? $ao.Ukrainian::Plural($ao,array(' ������������',' ������������',' �����������')).' ������, ' : '')
			.($uo>0 ? $uo.Ukrainian::Plural($uo,array(' ������������',' ������������',' �����������')).' ��������' : ''),', ');
	},
	'error_in_code'=>'������� � ���',
	'op_errors'=>'������� �������',
	'grlist'=>'������ ����',
	'nooptions'=>'������������ ������',
	'options'=>'������������',
	'ops_without_g'=>'������������ ��� ����',
	'import'=>'������ �����������',
	'export'=>'������� �����������',
	'incorrect_s_file'=>'������ ��������� �����! (%s)',
	'im_nogrname'=>'� ���� � ���� ������ ��\'�!',
	'im_noopname'=>'� ���� � ��������� ������ ��\'�!',

	#��� /core/others/settings/full.php
	'delc'=>'ϳ����������� ���������',
	'empty_gt'=>function($l=''){ return'����� ����� �� ���������'.($l ? ' (��� '.$l.')' : ''); },
	'adding_g'=>'��������� ����� �����������',
	'editing_g'=>'����������� ����� �����������',
	'adding_opt'=>'��������� ������������',
	'editing_opt'=>'����������� ������������',
	'empty_ot'=>function($l=''){ return'�� ��������� ����� ������������'.($l ? ' (��� '.$l.')' : ''); },
);