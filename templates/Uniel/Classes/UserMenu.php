<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �� ��������� ��� ������������� ������ ����.
*/
class TplUserMenu
{
	/*
		�������� ����������� ���� �����
		$a - ������ ���� �����, ������ id=>array(), ����� ����������� �������:
			url - ������
			title - �������� ������ ����
			params - ��������� ������
			parents - �������������� ���� ��������� ����, ����������� �������� (���� ���, �������, ����)
			pos - ����� �� �������� ������������� ���� � �������� ������ �������� (�� �������� � �������� ������� � 1)
	*/
	public static function GeneralMenu($a)
	{
		return Eleanor::$Template->Title(end($GLOBALS['title']))
			->OpenTable().ApiMenu::BuildMultilineMenu($a).Eleanor::$Template->CloseTable();
	}
}