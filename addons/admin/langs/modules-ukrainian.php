<?php
return array(
	#��� /addons/admin/modules/modules.php
	'list'=>'�����',
	'delc'=>'ϳ����������� ���������',
	'adding'=>'��������� ������',
	'editing'=>'����������� ������',
	'empty_title'=>function($l){ return'����� ������ �� ���� ���� ��������'.($l ? ' (��� '.$l.')' : ''); },
	'sec_exists'=>function($s){ return'������ � '.Ukrainian::Plural(count($s),array('�������','��������','��������')).' &quot;'.join('&quot;, &quot;',$s).'&quot; ��� ����'; },
);