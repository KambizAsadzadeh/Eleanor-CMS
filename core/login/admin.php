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

if(!class_exists('LoginBase',false))
	include dirname(__file__).'/base.php';

class LoginAdmin extends LoginBase implements LoginClass
{	const
		MAX_SESSIONS=1,#������������ ����� ������
		UNIQUE='admin';

	/**
	 * �������������� �� ������������ �������� ����������, ��������, �� ������ � ������
	 *
	 * @param array $data ������ � �������
	 * @throws EE
	 */
	public static function Login(array$data,array$extra=array())
	{
		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::UNIT);
		static::AuthByName($data['name'],$data['password'],$extra);
		if(!static::CheckPermission())
		{
			static::Logout();
			throw new EE('ACCESS_DENIED',EE::UNIT);
		}

		#:-)
		if(extension_loaded('ionCube Loader'))
			new Settings;

		$data+=array('rememberme'=>true);
		Eleanor::SetCookie(self::UNIQUE,base64_encode((isset(static::$user['login_key']) ? static::$user['login_key'] : '').'|'.static::$user['id']),$data['rememberme'] ? false : 0,true);
		static::$login=true;
	}

	/**
	 * ����������� ������������: �������� �������� �� ������������ �������������
	 *
	 * @param bool $hard ����� �������� ���������, ��� ������ ���� ��������� true
	 * @return bool
	 */
	public static function IsUser($hard=false)
	{		if(isset(static::$login) and !$hard)
			return static::$login;

		if(!$cookie=Eleanor::GetCookie(self::UNIQUE))
			return static::$login=false;

		list($k,$id)=explode('|',base64_decode($cookie),2);

		if(!$k or !$id or !static::AuthByKey($id,$k))
			return static::$login=false;

		if(!static::CheckPermission())
		{			static::Logout();
			return static::$login=false;
		}
		return static::$login=true;
	}

	/**
	 * ������������ ������ �� ������� ������ ������������
	 *
	 * @param string $name ��� ������������
	 * @param string $id ID ������������
	 * @return string|FALSE
	 */
	public static function UserLink($username,$uid=0)
	{
		if(!static::IsUser())
			return false;
		return$uid ? Eleanor::$services['admin']['file'].'?'.Eleanor::getInstance()->Url->Construct(array('section'=>'management','module'=>'users','edit'=>$uid),false,false,false) : '';
	}

	/**
	 * ���������� ������, ��� �������� � �������: ���������� ������� ��� ������������, ��������� �������� �����, �������� ������������ � �.�.
	 */
	public static function ApplyCheck(){}

	/**
	 * �������� ������� � ������������ ����� ������� � ������ ��������������
	 */
	private static function CheckPermission()
	{
		return in_array(1,Eleanor::GetPermission('access_cp',__class__));
	}
}