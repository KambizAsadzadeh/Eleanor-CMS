<?php
/*
	������� �������. ��������� ���������� ������������� ������� "��" � "���" (�������� ��� ���������).

	@var ���� "��" ��� "���"
*/
if(!defined('CMS'))die;
$yes=!empty($v_0);
$t=$yes ? Eleanor::$Language['tpl']['yes'] : Eleanor::$Language['tpl']['no'];
return'<img src="'.($yes ? Eleanor::$Template->default['theme'].'images/active.png' : Eleanor::$Template->default['theme'].'images/inactive.png').'" alt="" title="'.$t.'" />';