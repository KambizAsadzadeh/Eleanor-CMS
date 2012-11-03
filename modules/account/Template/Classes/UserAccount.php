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
class TplUserAccount
{	/*
		������ �������� ������ ���� ����� �������������
		$groups - ������ ���� �����. ������: id=>array(), ����� ����������� �������:
			title - �������� ������
			descr - �������� ������
			html_pref - HTML ������� ������
			html_end - HTML ��������� ������
	*/
	public static function AcGroups($groups)
	{

	}

	/*
		������ �������� ������������� ������
		$items - ������ ������ ������������� �����
			type - ��� ���������������� ������: guest - �����, user - ������������, bot - ���������� ����
			user_id - ������������� ������������ ��� ���������������� ������
			enter - ����� �����
			ip_guest - IP ����� ��� �������� ������
			ip_user - IP ������������ ��� ���������������� ������
			browser - USER AGENT ���������� ������������
			location - �������������� ������������
			botname - ��� ���� ��� ������ ���������� ����
			_group - ������ ������������ ��� ���������������� ������
			name - ��� ������������ ��� ���������������� ������
			full_name - ������ ��� ������������ ��� ���������������� ������
		$groups - ������ ���� �����. ������: id=>array(), ����� ����������� �������:
			title - �������� ������
			html_pref - HTML ������� ������
			html_end - HTML ��������� ������
		$cnt - ���������� ������ �����
		$pp - ������ �� ��������
		$page - ����� ������� ��������
		$links - ������ ������, �����:
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function AcUsersOnline($items,$groups,$cnt,$pp,$page,$links)
	{

	}

	/*
		������ �������� �������� ��������.
		������ ����� ��������������� ��� ����� ��������, ����������� �� �������
		$sessions - �������� ������ ������������, ������: ����=>������, ����� ����������� �������:
			0 - TIMESTAMP ��������� ����������
			1 - IP �����
			2 - USER AGENT ��������
			_candel - ���� ����������� �������� ������
	*/
	public static function AcMain($sessions)
	{

	}

	/*
		������ �������� ����� ����� ������������
		$values - ������ �������� �����:
			name - ��� ������������
			password - ������ ������������
		$back - URL ��������
		$errors - ������ ������
		$captcha - ����� ��� �����
		$links - ������ ������, �����:
			login - ������ �� ������, �������������� ������ �� ����� �����
	*/
	public static function AcLogin($values,$back,$errors,$captcha,$links)
	{

	}

	/*
		������ �������� ����������� ������������
		$values - ������ �������� �����, �����:
			_external - ������, �������� ������ ��� ����������� � �������������� �������� �������, �����:
				nickname - ��� ������������ �� ������� �������
			name - ��� ������������
			full_name - ������ ���
			email - e-mail ������������
			password - ������
			password - ���������� ������
		$captcha - �����
		$errors - ������ ������. ������ int=>code, ���� code=>error, ��� int - ����� ����� �� ������� �������� ��������� � ������, ��������� code:
			PASSWORD_MISMATCH - ������ �� ���������
			PASS_TOO_SHORT - ������ ������� ��������
			EMPTY_EMAIL - ����� e-mail �����
			EMAIL_EXISTS - e-mail ��� ����� ������ �������������
			EMAIL_BLOCKED - e-mail ������������
			NAME_TOO_LONG - ��� ������� �������
			EMPTY_NAME - ������ ���
			NAME_EXISTS - ��� ��� ������ ������ �������������
			NAME_BLOCKED - ��� �������������
			WRONG_CAPTCHA - ������������ �������� ���
	*/
	public static function AcRegister($values,$captcha,$errors)
	{

	}

	/*
		������ �������� ��������� ���������� �����������
	*/
	public static function AcSuccessReg()
	{

	}

	/*
		������ �������� ���������� �����������: �������� ��������� ������� ������.
		$byadmin - ���� ��������� ������� ������ ���������������
	*/
	public static function AcWaitActivate($byadmin)
	{

	}

	/*
		������ �������� ������� ���� �������������� ������: �����
		$values - ������ �������� �����, �����:
			name - ��� ������������
			email - e-mail ������������
		$captcha - �����, ���� false
		$errors - ������ ������
	*/
	public static function AcRemindPass($values,$captcha,$errors)
	{

	}

	/*
		�������� ������� ���� �������������� ������: ��� ���������� ���������� ������� �� ������, ������������ �� ����
	*/
	public static function AcRemindPassStep2()
	{

	}

	/*
		������ �������� �������� (�����������) ����: ����� ������ ������ ����� ����, ��� ������������ ������� �� ������ � ������
		$values - ������ �������� �����, �����:
			password - ������
			password2 - ���������� ������
		$captcha - �����
		$errors - ������ ������. ������ int=>code, ���� code=>error, ��� int - ����� ����� �� ������� �������� ��������� � ������, ��������� code:
			PASSWORD_MISMATCH - ������ �� ���������
			PASS_TOO_SHORT - ������ ������� ��������
			WRONG_CAPTCHA - ������������ �������� ���
	*/
	public static function AcRemindPassStep3($values,$captcha,$errors=array())
	{

	}

	/*
		������ �������� ���������� (������������) ���� ����� ������ ������������: ����� ���������� �� �������� ��������
		$passsent - ���� ����� ����� ������ ������ �� e-mail � ��� ��������� ����� ������, ��������� ��������� e-mail
		$user - ������ ������ ������������, �����:
			name - ��� ������������ (�� ���������� HTML)
			full_name - ������ ��� ������������
			email - e-mail ������������
	*/
	public static function AcRemindPassSent($passsent,$user)
	{

	}

	/*
		������ �������� � ����������� ��������� ������� ������
		$success - ���� �������� ���������
	*/
	public static function AcActivate($success)
	{

	}

	/*
		������ �������� � ������ ��������� ��������
		$sent - ���� �������� ��������� ���������
		$captcha - �����
		$errors - ������ ������
		$hours - ��� ���������� ����� $sent �������� ���������� �����, ���������� ��� ���������
	*/
	public static function AcReactivation($sent,$captcha,$errors,$hours)
	{

	}

	/*
		������ �������� ��������� ����������� �����
		$values - ����� �������� �����, �����:
			email - ����������� �����
		$captcha - �����
		$errors - ������ ������
	*/
	public static function AcEmailChange($values,$captcha,$errors)
	{

	}

	/*
		������ �������� ���� 1 � 2 ��������� ����������� �����.
		������ ��� - �������� �������� �� ������, ������������ �� ������ e-mail.
		������ ��� - �������� �������� �� ������, ������������ �� ����� (���������) e-mail.
		$step - ������������� ����: 1 ��� 2.
	*/
	public static function AcEmailChangeSteps12($step)
	{

	}

	/*
		������ �������� ��������� ���������� ��������� e-mail ������
	*/
	public static function AcEmailChangeSuccess()
	{

	}

	/*
		������ �������� ��������� ������
	*/
	public static function AcNewPass($success,$errors,$values)
	{

	}

	/*
		������, ����������� ����� ����. ���� ���������� ����� �� ������������ ������ Menu() �������, ������� ��������� � ������� $GLOBALS['Eleanor']->module['handlers'],
		����� �������� �������� ���������� ������������, � �������� - ������� �������, ������� ��������� ������ �����������.
		$section - ������ ������. ��� ����� ���� user ��� guest
		$ih - ������ ����������� ��������� ������ ����
		$im - ������ ������ ����
	*/
	protected static function Menu($section='',$ih='',$im='')
	{

	}

	public static function AcOptions($controls,$values,$avatar,$errors,$saved)
	{

	}

	/*
		�������� ��������� ������������. ������ ������������ ����� ����� �� ������� $GLOBALS['Eleanor']->module['user'], �����:
			id - ������������� ������������
			full_name - ������ ��� ������������
			name - ����� ������������ (�� ���������� HTML)
			register - ���� �����������
			last_visit - ���� ���������� ������
			language - ���� �����������
			timezone - ������� ����
			+��� ���� �� ������� users_extra
		$groups - ������ ������������, ������: id=>array(), ����� ����������� �������:
			html_pref - HTML ������� ������
			html_end - HTML ��������� ������
			title - �������� ������
			_a - ������ �� �������� ���������� � ������
			_main - ���� �������� ������
	*/
	public static function AcUserInfo($groups)
	{

	}

	/*
		������� �������: �������� �������
		$galleries - ������ �������, ������ ������� ������� - ������ � �������:
			n - ��� �������
			i - ���� � �������� ������������ ����� �����
			d - �������� �������
	*/
	public static function Galleries($galleries)
	{

	}

	/*
		������ �������: �������� ��������
		$avatar - ������ ��������, ������ ������� ������� - ������ � �������:
			p - ���� � �����, ������������ ����� �����, � ����������� ������
			f - ��� �����
	*/
	public static function Avatars($avatars)
	{

	}

	#Loginza
	/*
		�������� ��������� ������� �����������, ��� ���������� � �������� loginza.ru
		$items - ������ ���� ������� �����������, ������ ������� - ������ � �������:
			identity - ������ �� ������������ �������� �������
			provider - �������� ���������� ������� �����������
		$added - ������ ����������� ������� �����������, ������ � �������:
			identity - ������ �� ������������ �������� �������
			provider - �������� ���������� ������� �����������
		$error - ������, ���� ������ - ������ ������ ���
	*/
	public static function Loginza($items,$added,$error)
	{

	}

	/*
		������ �������������� ��� ������ ������� loginza.
		$loginza - ������, ���������� � �������
	*/
	public static function LoginzaError($loginza)
	{

	}
}