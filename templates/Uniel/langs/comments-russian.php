<?php
return array(
	#��� Classes/Comments.php
	'vc'=>'����������� �����������',
	'nc'=>'������������ ���� ����� �� �����.',
	'anc'=>'������� �� ���� ����������� ���� ����� �� �����.',
	'lnp'=>'��������� ����� �����������',
	'addc'=>'�������� �����������',
	'yn'=>'���� ���',
	'yc'=>'�����������',
	'needch'=>'��� ����������� ������ ������������ ������ ����� ��������.',
	'captcha'=>'�������� ���',
	'captcha_'=>'������� ������� � ��������',
	'cite'=>'������ %s',
	'stmodwait'=>'���� ����������� ������� ��������',
	'stblocked'=>'���� ����������� ������������',
	'answer'=>'��������',
	'qquote'=>'������� ������',
	'withsel'=>'-� �����������-',
	'doact'=>'������������',
	'toblock'=>'�������������',
	'tomod'=>'�� ���������',
	'save'=>'���������',
	'cancel'=>'������',
	'answers'=>function($n){return $n.Russian::Plural($n,array(' �����',' ������',' �������'));},
	'added_after'=>function($y,$m,$d,$h,$i,$s)
	{
		return rtrim('��������� ����� '.($y>0 ? $y.' '.Russian::Plural($y,array(' ���',' ����',' ���')) : '')
			.($m>0 ? $m.Russian::Plural($m,array(' ����� ',' ������ ',' ������ ')) : '')
			.($d>0 ? $d.Russian::Plural($d,array(' ���� ',' ��� ',' ���� ')) : '')
			.($h>0 ? $h.Russian::Plural($h,array(' ��� ',' ���� ',' ����� ')) : '')
			.($i>0 ? $i.Russian::Plural($i,array(' ������ ',' ������ ',' ����� ')) : '')
			.($s>0 ? $s.Russian::Plural($s,array(' �������',' �������',' ������')) : ''));
	},
);