<?php
/*
	�������� ����������/�������������� ����������� ��������
	@var �������� ��������� � ������������ � ������� Controls. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
	@var �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
	@var ������, ���� ������ ������ - ������ �� ���
*/
$controls=&$v_0;
$values=&$v_1;

$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
foreach($controls as $k=>&$v)
	if(is_array($v))
		$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
	else
		$Lst->head($v);

$Lst->button(Eleanor::Button('OK','submit',array('tabindex'=>10)))->end()->endform();
return Eleanor::$Template->Cover($Lst,$v_2,'error');