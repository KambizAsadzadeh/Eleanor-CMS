<?php
return array(
	#�����
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
);