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

#����� ����������� ���������� ������� �� ���������� ���������
class Integration
{	/**
	 * �������� ������������. ������ ���������� ID, ������� ����� ������� � ������� ������������� ������� � ���� forum_id
	 *
	 * @param array $data ��������� � ����������� � ���������� ���� ������ ������������ ������������
	 * @param array $raw "�����" ������ (������ � ��� ����, � ������� ��� �������� ������ Usermanager::Add
	 */
	public static function Add($data,$raw)
	{	}

	/**
	 * ���������� �������������
	 *
	 * @param array $data ��������� � ����������� � ���������� ���� ������ ������������ ������������
	 * @param array $raw "�����" ������ (������ � ��� ����, � ������� ��� �������� ������ Usermanager::Add
	 * @param array|int $ids �������������(�) ������������ ������������
	 */
	public static function Update($data,$raw,$ids)
	{	}

	/**
	 * �������� �������������
	 *
	 * @param array|int $ids �������������(�) ���������� ������������
	 */
	public static function Delete($ids)
	{	}
}