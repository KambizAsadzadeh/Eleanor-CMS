<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �� ��������� ��� ������������� ������ "�������� �����"
	������������� ����������� ���� ���� � templates/[������ ���������������� �����]/Classes/[��� ����� �����] � ��� ��� �������� �������.
	� ������ ���� ����� ���� ��� ���������� - ������� ���.
*/
class TplUserContacts
{	/*
		�������� �������� �������� �����

		$canupload - ���� ����������� �������� �����
		$info - ���������� �� �������� �����, ����������� � �������
		$whom - ������ ������ ���������� ������. ������ id=>��� ����������
		$values - ������ �������� �����, �����:
			subject - ���� ���������
			message - ����� ���������
			whom - ������������� ����������
			sess - ������������� ������
		$bypost - ���� �������� ����������� �� POST �������
		$errors - ������ ������
		$captcha - captcha ��� �������� ���������
	*/	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$captcha)
	{
	}

	/*
		�������� � ����������� � ���, ��� ��������� ������� ����������
	*/
	public static function Sent()
	{	}
}