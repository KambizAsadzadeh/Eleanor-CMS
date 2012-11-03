<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �� ��������� ��� ������� ������ ����
	������������� ����������� ���� ���� � templates/[������ �������]/Classes/[��� ����� �����] � ��� ��� �������� �������.
	� ������ ���� ����� ���� ��� ���������� - ������� ���.
*/

class TplAdminMenu
{	/*
		�������� ����������� ���� ������� ����
		$items - ������ ����������� �������. ������: ID=>array(), ����� ����������� �������:
			title - ��������� ������ ����
			pos - ����� �����, ��������������� ������� ������ ����
			status - ������ ���������� ������ ����
			_aswap - ������ �� ��������� / ���������� ���������� ������ ����
			_aedit - ������ �� �������������� ������ ����
			_adel - ������ �� �������� ������ ����
			_aparent - ������ �� �������� ���������� ������� ������ ����
			_aup - ������ �� �������� ������ ���� �����, ���� ����� false - ������ ����� ���� ��� � ��� ��������� � ����� �����
			_adown - ������ �� ��������� ������ ���� ����, ���� ����� false - ������ ����� ���� ��� � ��� ��������� � ����� ����
			_aaddp - ������ �� ���������� ���������� � ������� ������
		$subitems - ������ ���������� ���� ��� ������� �� ������� $items. �����: ID=>array(id=>array(), ...), ��� ID - ������������� ������ ����, id - ������������� ����������� �����������. ����� ������� ����������� �����������:
			title - ��������� ������ ����
			_aedit - ������ �� �������������� ������ ����
		$navi - ������, ������� ������ ���������. ������ ID=>array(), �����:
			title - ��������� ������
			_a - ������ ��������� ������ ������. ����� ���� ����� false
		$cnt - ����� ������� ���� �����
		$pp - ���������� ������� ���� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_status - ������ �� ���������� ������ $items �� ������� ���������� (�����������/�������� � ����������� �� ������� ����������)
			sort_title - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_pos - ������ �� ���������� ������ $items �� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ������������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links)
	{	}

	/*
		�������� ����������/�������������� ������ ����
		$id - ������������� �������������� ������ ����, ���� $id==0 ������ ����� ���� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$hasdraft - ������� ����, ��� � ������ ���� ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$hasdraft,$links)
	{
	}
}