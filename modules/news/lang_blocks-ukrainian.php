<?php
return array(
	#�����
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