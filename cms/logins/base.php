<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Logins;
defined('CMS\STARTED')||die;
use Eleanor\Classes\EE, CMS, CMS\Eleanor, CMS\UserManager;

/** Базовый класс авторизации, используется преимущественно в пользовательской части (index сервис) */
class Base extends \Eleanor\BaseClass implements CMS\Interfaces\Login
{
	const
		/** Максимальное число сессий */
		MAX_SESSIONS=10,

		/** Упрощение наследования: наследуем класс, меняем константу и вуаля! - У нас новый класс логина системы */
		UNIQUE='user';

	/** @static данные пользователя */
	protected static $user;

	/** Получение дополнительной информации для формы показа логина (показ капчи и т.п.)
	 * @return array*/
	public static function BeforeLogin()
	{
		$cookie=CMS\GetCookie('Captcha_'.ltrim(strrchr(get_called_class(),'\\'),'\\'));
		return$cookie ? ['captcha'=>true] : [];
	}

	/** Аутентификация по входящим параметрам, например, по логину и паролю
	 * @param array $data Данные:
	 *  string name Имя пользователя
	 *  string password Пароль пользователя
	 *  bool remember Флаг "Своего компьютера" - куки сохраняются после закрытия браузера. По умолчанию true
	 * @param array $extra Дополнительные параметры:
	 *  bool captcha Флаг корректно введенной капчи. Значение не проверяется
	 *  bool bindip Флаг связывания сессии с IP адресом
	 * @throws EE */
	public static function Login(array$data,array$extra=[])
	{
		if(!isset($data['name'],$data['password']))
			throw new EE('EMPTY_DATA',EE::UNIT);

		static::AuthByName($data['name'],$data['password'],$extra);

		$data+=['remember'=>true];
		CMS\SetCookie(static::UNIQUE,static::$user['login_key'].'|'.static::$user['id'],
			$data['remember'] ? null : 0,true);
	}

	/** Аутентификация по ID пользователя. Прежде всего для внешней аутентификации.
	 * @param int $id ID пользователя
	 * @param array $extra Дополнительные параметры
	 *  bool remember Флаг "Своего компьютера" - куки сохраняются после закрытия браузера. По умолчанию true
	 *  bool bindip Флаг связывания сессии с IP адресом
	 * @throws EE */
	public static function Auth($id,$extra=[])
	{
		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`u`.`full_name`,`u`.`name`,`banned_until`,`ban_explain`,`u`.`language`,
`u`.`timezone`,`integration`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,`s`.`last_visit`,`theme`,
`avatar`,`avatar_type`,`editor` FROM `'.CMS\USERS_TABLE.'` `u` LEFT JOIN `'.CMS\P.'users_extra` USING(`id`)
LEFT JOIN `'.CMS\P.'users_site` `s` USING(`id`) WHERE `id`='.(int)$id.' LIMIT 1');
			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);

			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query('SELECT `integration`,`email`,`groups`,`groups_overload`,`login_keys`,
`last_visit`,`theme`,`avatar`,`avatar_type`,`editor` FROM `'.CMS\P.'users_extra`
INNER JOIN `'.CMS\P.'users_site` `s`USING(`id`) WHERE `id`='.$user['id'].' LIMIT 1');
				$user=$R->fetch_assoc()+$user;
			}
		}
		else
		{
			$R=Eleanor::$UsersDb->Query('SELECT `id`,`full_name`,`name`,`register`,`last_visit`,`banned_until`,
`ban_explain`,`language`,`timezone` FROM `'.CMS\USERS_TABLE.'` WHERE `id`='.(int)$id.' AND `temp`=0 LIMIT 1');
			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);

			UserManager::Sync([$user['id']=>['full_name'=>$user['full_name'],'register'=>$user['register'],
				'name'=>$user['name'],'language'=>$user['language']]]);
			$R=Eleanor::$Db->Query('SELECT `id`,`integration`,`email`,`groups`,`groups_overload`,`login_keys`,`ip`,
`theme`,`avatar`,`avatar_type`,`editor` FROM `'.CMS\P.'users_site` INNER JOIN `'.CMS\P.'users_extra` USING(`id`)
WHERE `id`='.$user['id'].' LIMIT 1');
			$user+=$R->fetch_assoc();
		}

		static::SetUser($user,isset($extra['bindip']) ? $extra['bindip'] : false);
		$extra+=['remember'=>true];
		CMS\SetCookie(static::UNIQUE,static::$user['login_key'].'|'.static::$user['id'],$extra['remember'] ? null : 0,true);
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

		if(!$k or !$id or !static::AuthByKey($id,$k))
		{
			static::Logout();
			goto Out;
		}

		return true;
	}

	/** Выход пользователя из учетной записи
	 * @param bool $totally Выход из всех сессий (не останется ни одного устройства, способного авторизоваться) */
	public static function Logout($totally=false)
	{
		CMS\SetCookie(static::UNIQUE,false);

		if(isset(static::$user['id']))
		{
			$table=CMS\P.'users_site';
			$uid=static::$user['id'];
			$service=Eleanor::$service;

			$R=Eleanor::$Db->Query("SELECT `login_keys` FROM `{$table}` WHERE `id`={$uid} LIMIT 1");
			if($a=$R->fetch_assoc())
			{
				$lks=$a['login_keys'] ? json_decode($a['login_keys'],true) : [];
				$cl=ltrim(strrchr(get_called_class(),'\\'),'\\');

				if($totally)
					unset($lks[$cl]);
				else
					unset($lks[$cl][ static::$user['login_key'] ]);

				Eleanor::$Db->Update($table,['login_keys'=>$lks ? json_encode($lks,CMS\JSON) : ''],"`id`={$uid} LIMIT 1");
				Eleanor::$Db->Delete(CMS\P.'sessions',"`ip_guest`='' AND `user_id`={$uid} AND `service`='{$service}'");
			}
		}

		static::$user=[];
	}

	/** Получение значения пользовательского параметра
	 * @param array|string $key Один или несколько параметров, значения которых нужно получить
	 * @return mixed */
	public static function Get($key)
	{
		$isa=is_array($key);
		$key=(array)$key;
		$new=$res=[];

		foreach($key as $v)
			if(array_key_exists($v,static::$user))
				$res[$v]=static::$user[$v];
			else
				$new[]=$v;

		if(isset(static::$user['id']) and $new)
		{
			$what=join('`,`',$new);
			$table=CMS\P.'users_site';
			$table_ex=CMS\P.'users_extra';
			$uid=(int)static::$user['id'];

			$R=Eleanor::$Db->Query("SELECT `{$what}` FROM `{$table}` INNER JOIN `{$table_ex}` USING(`id`) WHERE `id`={$uid} LIMIT 1");
			if($a=$R->fetch_assoc())
			{
				static::$user+=$a;
				$res+=$a;
			}
		}

		return$isa ? $res : reset($res);
	}

	/** Аутентификация пользователя его имени и паролю
	 * @param string $name Имя пользователя
	 * @param string $pass Пароль пользователя
	 * @param array $extra Дополнительные параметры аутентификации, возможные ключи массива:
	 *  [bool captcha] Флаг корректно введенной капчи. Значение не проверяется
	 *  [bool bindip] Флаг связывания сессии с IP адресом
	 * @throws EE */
	public static function AuthByName($name,$pass,array$extra=[])
	{
		$table=CMS\USERS_TABLE;
		$table_ex=CMS\P.'users_extra';
		$table_si=CMS\P.'users_site';
		$q_name=Eleanor::$Db->Escape($name);

		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query("SELECT `id`, `u`.`full_name`, `u`.`name`, `password_hash`, `banned_until`,
`ban_explain`, `u`.`language`, `u`.`timezone`, `integration`, `email`, `groups`, `groups_overload`, `login_keys`,
`failed_logins`, `s`.`last_visit`, `theme`, `avatar`, `avatar_type`, `editor` FROM `{$table}` `u`
LEFT JOIN `{$table_ex}` USING(`id`) LEFT JOIN `{$table_si}` `s` USING(`id`) WHERE `u`.`name`={$q_name} LIMIT 1");
			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);

			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);
				$R=Eleanor::$Db->Query("SELECT `integration`, `email`, `groups`, `groups_overload`, `login_keys`,
`failed_logins`, `last_visit`, `theme`, `avatar`, `avatar_type`, `editor` FROM `{$table_ex}`
INNER JOIN `{$table_si}` `s` USING(`id`) WHERE `id`={$user['id']} LIMIT 1");
				$user+=$R->fetch_assoc();
			}
		}
		else
		{
			$R=Eleanor::$UsersDb->Query("SELECT `id`, `full_name`, `name`, `password_hash`, `register`, `last_visit`,
`banned_until`, `ban_explain`, `language`, `timezone` FROM `{$table}` WHERE `name`={$q_name} AND `temp`=0 LIMIT 1");

			if(!$user=$R->fetch_assoc())
				throw new EE('NOT_FOUND',EE::UNIT);

			UserManager::Sync([$user['id']=>['full_name'=>$user['full_name'],'name'=>$user['name'],
				'register'=>$user['register'],'language'=>$user['language']]]);
			$R=Eleanor::$Db->Query("SELECT `id`, `integration`, `email`, `groups`, `groups_overload`, `failed_logins`,
`login_keys`, `ip`, `theme`, `avatar`, `avatar_type`, `editor` FROM `{$table_si}`
INNER JOIN `{$table_ex}` USING(`id`) WHERE `id`={$user['id']} LIMIT 1");
			$user+=$R->fetch_assoc();
		}

		$t=time();

		if(Eleanor::$vars['antibrute'])
		{
			$failed=$user['failed_logins'] ? json_decode($user['failed_logins'],true) : [];
			$abcnt=(int)Eleanor::$vars['antibrute_cnt'];
			$abtime=(int)Eleanor::$vars['antibrute_time'];

			if($failed)
			{
				usort($failed,function($a,$b){
					$a=(int)$a[0];
					$b=(int)$b[0];

					if($a==$b)
						return 0;

					return$a>$b ? -1 : 1;
				});

				if(Eleanor::$vars['antibrute']>0 and isset($failed[$abcnt-1]) and
					strtotime($user['last_visit'])<$failed[$abcnt-1][0])
				{
					$blocked=$t-$failed[$abcnt-1][0];

					if($blocked<$abtime)
					{
						$remain=$abtime - $blocked;

						if(Eleanor::$vars['antibrute']==2)
						{
							CMS\SetCookie('Captcha_'.ltrim(strrchr(get_called_class(),'\\'),'\\'),
								$failed[$abcnt-1][0]+$abtime,$remain.'s');
							if(!isset($extra['captcha']))
								throw new EE('CAPTCHA',EE::UNIT,['remain'=>$remain]);
							elseif(!$extra['captcha'])
								throw new EE('WRONG_CAPTCHA',EE::UNIT,['remain'=>$remain]);
						}
						else
							throw new EE('TEMPORARILY_BLOCKED',EE::UNIT,['remain'=>$remain]);
					}
				}
			}
		}

		#Сброс пароля: пароль прописан жестко в базе длиной до 5 символов, либо поле просто не заполнено.
		$reset=$pass!=='' && ($user['password_hash']==='' || strlen($pass)<=5 && $pass===$user['password_hash']);

		if($reset or password_verify($pass,$user['password_hash']))
		{
			#Поддержим актуальность пароля
			if($reset or password_needs_rehash($user['password_hash'],PASSWORD_DEFAULT))
				Eleanor::$UsersDb->Update(CMS\USERS_TABLE,['password_hash'=>password_hash($pass,PASSWORD_DEFAULT)],
					'`id`='.(int)$user['id'].' LIMIT 1');

			static::SetUser($user,isset($extra['bindip']) ? $extra['bindip'] : false);
		}
		else
		{
			if(Eleanor::$vars['antibrute'])
			{
				if(count($failed)>$abcnt)
					array_splice($failed,$abcnt);

				array_unshift($failed,[$t,Eleanor::$service,getenv('HTTP_USER_AGENT'),inet_ntop(Eleanor::$ip)]);

				Eleanor::$Db->Update(CMS\P.'users_site',['failed_logins'=>json_encode($failed,CMS\JSON)],"`id`={$user['id']} LIMIT 1");

				if(isset($failed[$abcnt-1]))
				{
					$blocked=$t-$failed[$abcnt-1][0];

					if($blocked<$abtime and strtotime($user['last_visit'])<$failed[$abcnt-1][0])
					{
						$remain=$abtime - $blocked;

						if(Eleanor::$vars['antibrute']==1)
							throw new EE('TEMPORARILY_BLOCKED',EE::UNIT,['remain'=>$remain]);
						else
						{
							CMS\SetCookie('Captcha_'.ltrim(strrchr(get_called_class(),'\\'),'\\'),
								$failed[$abcnt-1][0]+$abtime,$remain.'s');
							throw new EE('WRONG_PASSWORD',EE::UNIT,['captcha'=>true,'remain'=>$remain]);
						}
					}
				}
			}

			throw new EE('WRONG_PASSWORD',EE::UNIT);
		}
	}

	/** Авторизация пользователя по ключу
	 * @param int $id ID пользователя
	 * @param string $k Ключ пользователя
	 * @return bool */
	public static function AuthByKey($id,$k)
	{
		$id=(int)$id;
		$table=CMS\USERS_TABLE;
		$table_ex=CMS\P.'users_extra';
		$table_si=CMS\P.'users_site';

		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query("SELECT `id`, `u`.`full_name`, `u`.`name`, `banned_until`, `ban_explain`,
`u`.`language`, `u`.`timezone`, `integration`, `email`, `groups`, `groups_overload`, `login_keys`, `s`.`last_visit`,
`theme`, `avatar`, `avatar_type`, `editor`
FROM `{$table}` `u` LEFT JOIN `{$table_ex}` USING(`id`) LEFT JOIN `{$table_si}` `s` USING(`id`)
WHERE `id`={$id} LIMIT 1");

			if(!$user=$R->fetch_assoc())
				return false;

			#На случай, если синхронизация у нас в виде одной БД.
			if($user['groups']===null)
			{
				UserManager::Sync($user['id']);

				$R=Eleanor::$Db->Query("SELECT `integration`, `email`, `groups`, `groups_overload`, `login_keys`,
`last_visit`, `theme`, `avatar`, `avatar_type`, `editor` FROM `{$table_ex}`
INNER JOIN `{$table_si}` `s` USING(`id`) WHERE `id`={$user['id']} LIMIT 1");
				$user+=$R->fetch_assoc();
			}
		}
		else
		{
			$R=Eleanor::$UsersDb->Query("SELECT `id`, `full_name`, `name`, `register`, `last_visit`, `banned_until`,
`ban_explain`, `language`, `timezone` FROM `{$table}` WHERE `id`={$id} AND `temp`=0 LIMIT 1");

			if(!$user=$R->fetch_assoc())
				return false;

			UserManager::Sync([$user['id']=>['full_name'=>$user['full_name'],'name'=>$user['name'],
				'register'=>$user['register'],'language'=>$user['language']]]);

			$R=Eleanor::$Db->Query("SELECT `id`, `integration`, `email`, `groups`, `groups_overload`, `login_keys`,
`theme`, `avatar`, `avatar_type`, `editor` FROM `{$table_si}`
INNER JOIN `{$table_ex}` USING(`id`) WHERE `id`={$user['id']} LIMIT 1");
			$user+=$R->fetch_assoc();
		}

		$cl=ltrim(strrchr(get_called_class(),'\\'),'\\');
		$lks=$user['login_keys'] ? json_decode($user['login_keys'],true) : [];
		$user['groups_overload']=$user['groups_overload'] ? json_decode($user['groups_overload'],true) : [];
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : [];
		$user['login_key']=$k;

		if(!isset($lks[$cl][$k]) or !empty($lks[$cl][$k][2]) and inet_pton($lks[$cl][$k][1])!==Eleanor::$ip)
			return false;

		$t=time();
		$to=isset(Eleanor::$vars['time_online'],Eleanor::$vars['time_online'][$cl])
			? (int)Eleanor::$vars['time_online'][$cl] : 600;

		if($lks[$cl][$k][0]-$to<$t or strtotime($user['last_visit'])<mktime(0,0,0))
		{
			$lks[$cl][$k][0]=$to+$t;

			Eleanor::$Db->Update(CMS\P.'users_site',['login_keys'=>json_encode($lks,CMS\JSON),'!last_visit'=>'NOW()',
				'ip'=>Eleanor::$ip],"`id`={$user['id']} LIMIT 1");
			Eleanor::$UsersDb->Update(CMS\USERS_TABLE,['!last_visit'=>'NOW()'],"`id`={$user['id']} LIMIT 1");
		}

		unset($user['login_keys']);

		static::$user=$user;

		return true;
	}

	/** После успешной аутентификации пользователя: обработка данных и занос их в таблицу
	 * @param array $user Данные пользователя
	 * @param bool $bindip Флаг связывания сессии с IP адресом */
	protected static function SetUser(array$user,$bindip=false)
	{
		$cl=ltrim(strrchr(get_called_class(),'\\'),'\\');
		$user['login_key']=sha1(uniqid($user['id'],true).microtime());
		$lks=$user['login_keys'] ? json_decode($user['login_keys'],true) : [];
		$lks[$cl][ $user['login_key'] ]=[
			(isset(Eleanor::$vars['time_online'][$cl]) ? Eleanor::$vars['time_online'][$cl] : 900)+time(),
			inet_ntop(Eleanor::$ip),$bindip,getenv('HTTP_USER_AGENT')
		];
		$user['groups_overload']=$user['groups_overload'] ? json_decode($user['groups_overload'],true) : [];
		$user['groups']=$user['groups'] ? explode(',,',trim($user['groups'],',')) : [];

		if(static::MAX_SESSIONS<=$n=count($lks[$cl]))
			array_splice($lks[$cl],0,$n-static::MAX_SESSIONS);

		Eleanor::$Db->Update(CMS\P.'users_site',['login_keys'=>json_encode($lks,CMS\JSON),'ip'=>Eleanor::$ip,'!last_visit'=>'NOW()'],
			"`id`={$user['id']} LIMIT 1");
		Eleanor::$UsersDb->Update(CMS\USERS_TABLE,['!last_visit'=>'NOW()'],"`id`={$user['id']} LIMIT 1");
		Eleanor::$Db->Delete(CMS\P.'sessions','`ip_guest`=\''.Eleanor::$ip.'\'');

		unset($user['failed_logins'],$user['password_hash'],$user['login_keys']);

		if(Eleanor::$vars['antibrute']==2)
			CMS\SetCookie('Captcha_'.$cl,false);

		static::$user=$user;
	}

	/** Установка значения пользовательского параметра. Метод не должен обновлять данные пользователя в БД!
	 * Только на время работы скрипта.
	 * @param array|string $key Имя параметра, либо массив в виде $key=>$value
	 * @param mixed $value Значения */
	public static function Set($key,$value=null)
	{
		if(is_array($key))
			static::$user=$key+static::$user;
		else
			static::$user[$key]=$value;
	}
}