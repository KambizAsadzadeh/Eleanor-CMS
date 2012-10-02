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
{
		$Instance;

	public static function getInstance()
	{
			self::$Instance=new self;
		return self::$Instance;

	public function __get($n)
	{
		{
				include Eleanor::$root.'core/permissions.php';
			return $this->$n=new Permissions($this);
		return parent::__get($n);
	}

	protected function __construct(){}

	public function Login(array $b)
	{
	}

	public function IsUser($a=false)
	{
		return false;
	}

	public function Auth($id){}

	public function ApplyCheck()
	{
		return false;
	}

	public function Logout()
	{
	}

	public function UserLink($a,$b=0)
	{
	}

	public function GetUserValue($a,$b=true)
	{
	}
}