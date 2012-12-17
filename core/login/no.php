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
class LoginNo extends BaseClass implements LoginClass
{	/**
	 * �������������� �� ������������ �������� ����������, ��������, �� ������ � ������
	 *
	 * @param array $data ������ � �������
	 * @throws EE
	 */	public static function Login(array$b)
	{		return false;
	}

	/**
	 * ����������� ������������: �������� �������� �� ������������ �������������
	 *
	 * @param bool $hard ����� �������� ���������, ��� ������ ���� ��������� true
	 * @return bool
	 */
	public static function IsUser($a=false)
	{
		return false;
	}

	/**
	 * �������������� ������ �� ID ������������
	 *
	 * @param int $id ID ������������
	 * @throws EE
	 */
	public static function Auth($id){}

	/**
	 * ���������� ������, ��� �������� � �������: ���������� ������� ��� ������������, ��������� �������� �����, �������� ������������ � �.�.
	 */
	public static function ApplyCheck()
	{

	}

	/**
	 * ����� ������������ �� ������� ������
	 */
	public static function Logout()
	{		return false;
	}

	/**
	 * ������������ ������ �� ������� ������ ������������
	 *
	 * @param string $name ��� ������������
	 * @param string $id ID ������������
	 * @return string|FALSE
	 */
	public static function UserLink($a,$b=0)
	{		return false;
	}

	/**
	 * ��������� �������� ����������������� ���������
	 *
	 * @param array|string $key ���� ��� ��������� ����������, �������� ������� ����� ��������
	 * @return array|string � ����������� �� ���� ���������� ����������
	 */
	public static function GetUserValue($name,$id=0)
	{		return false;
	}

	/**
	 * ��������� �������� ����������������� ���������. ����� �� ������ ��������� ����� ������������ � ��! ������ �� ����� ������ �������
	 *
	 * @param array|string $key ��� ���������, ���� ������ � ���� $key=>$value
	 * @param mixed $value ��������
	 */
	public static function SetUserValue($key,$value=null)
	{

	}
}