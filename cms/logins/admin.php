<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Logins;
defined('CMS\STARTED')||die;
use CMS, Eleanor\Classes\EE;

/** Авторизация в Панель Администратора */
class Admin extends Base implements CMS\Interfaces\Login
{
	const
		/** Максимальное число сессий */
		MAX_SESSIONS=1,

		/** Упрощение наследования: наследуем класс, меняем константу и вуаля! - У нас новый класс логина системы */
		UNIQUE='admin';

	/** @static данные пользователя */
	protected static $user;

	/** Аутентификация по входящим параметрам, например, по логину и паролю
	 * @param array $data Данные
	 * @param array $extra Дополнительные параметры
	 * @throws EE */
	public static function Login(array$data,array$extra=[])
	{
		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::UNIT);

		static::AuthByName($data['name'],$data['password'],$extra);
		if(!static::CheckPermission())
		{
			static::Logout();
			throw new EE('ACCESS_DENIED',EE::UNIT);
		}

		$data+=['remember'=>true];
		CMS\SetCookie(static::UNIQUE,static::$user['login_key'].'|'.static::$user['id'],
			$data['remember'] ? null : 0,true);
	}

	/** Авторизация пользователя: проверка является ли пользователь пользователем
	 * @return bool */
	public static function IsUser()
	{
		if(isset(static::$user))
			return(bool)static::$user;

		if(!$cookie=CMS\GetCookie(static::UNIQUE))
		{
			Out:
			static::$user=[];
			return false;
		}

		list($k,$id)=explode('|',$cookie,2);

		if(!$k or !$id or !static::AuthByKey($id,$k) or !static::CheckPermission())
		{
			static::Logout();
			goto Out;
		}

		return true;
	}

	/** Проверка наличия у пользователя права входить в панель администратора
	 * @return bool */
	private static function CheckPermission()
	{#Здесь нельзя обращаться к $Permissions, ибо он использует IsUser(), может возникнуть бесконечный цикл
		$over=static::$user['groups_overload'];

		if(!$over or !isset($over['method']['is_admin'],$over['value']['is_admin'])
			or $over['method']['is_admin']=='inherit')
			return in_array(1,CMS\Permissions::ByGroup(static::$user['groups'],'is_admin'));

		$add=$over['method']['is_admin']=='replace';
		$res=$add ? [$over['value']['is_admin']] : CMS\Permissions::ByGroup(static::$user['groups'],'is_admin');

		if(!$add)
			$res[]=$over['value']['is_admin'];

		return in_array(1,$res);
	}
}