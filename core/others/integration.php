<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

#����� ��� ����������� ���������� ������� �� ���������� ���������
class Integration
{	/*
		������� �������� ������������. ������ ���������� ID, ������� ����� ������� � ������� ������������� ������� � ���� forum_id
		$data - ������ ������������, �������� ���������
		$raw - "�����" ������, ��������� � �����, ��� ������������ ���������
	*/
	public static function Add($data,$raw)
	{	}

	/*
		������� ���������� �������������
		$data - ������, ������� ���������� ���������
		$raw - "�����" ������, ��������� � �����, ��� ������������ ���������
		$ids - ��� ������������� (������ ��� ��)
	*/
	public static function Update($data,$raw,$ids)
	{	}

	/*
		������� �������� �������������
		$ids - ��� ������������� (������ ��� ��)
	*/
	public static function Delete($ids)
	{	}
}