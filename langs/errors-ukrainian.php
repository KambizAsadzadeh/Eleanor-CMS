<?php
return array(
	#��� ��� ������� Error() ������ ������� �������
	'happened'=>'������� �������',
	'you_are_banned'=>'��� �����������!',
	'banlock'=>function($date,$reason){		return '���� �������������: '.($date ? Eleanor::$Language->Date($date) : '�������').'.<br />�������: '.($reason ? $reason : '�������'),	}
);