<?php
/*
	������� ��� �������� "������ ���������".

	@var array ������ ���������, ������ ������� �������� - ������ � �������:
		0 - ��� ��������
		1 - ��������
*/
if(!defined('CMS'))die;
$html='';
foreach($v_0 as &$v)
	$html.='<label>'.$v[0].' '.$v[1].'</label><br />';
return$html ? substr($html,0,-6) : '';