<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Interfaces;
use Eleanor\Classes\EE as EE;

/** Аунтентификация и авторизация в системе */
interface Login#Интерфейс для создания медов авторизации
{
	/** Получение дополнительной информации для формы показа логина (показ капчи и т.п.)
	 * @return array*/
	public static function BeforeLogin();

	/** Аутентификация по определенным входящим параметрам, например, по логину и паролю
	 * @param array $data Данные. Для каждого класса аутентификации они свои
	 * @throws EE */
	public static function Login(array$data);

	/** Аутентификация только по ID пользователя. Может использоваться в случае аутентификации через социальные сети.
	 * @param int|string $id ID пользователя
	 * @throws EE */
	public static function Auth($id);

	/** Авторизация пользователя: проверка является ли пользователь пользователем
	 * @return bool */
	public static function IsUser();

	/** Выход пользователя из учетной записи */
	public static function Logout();

	/** Получение значения пользовательского параметра.
	 * @param string|array $key Один или несколько параметров, значения которых нужно получить
	 * @return mixed */
	public static function Get($key);
}
