<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

use Eleanor\Classes\EE, Eleanor\Classes\Files, Eleanor\Classes\Html;

/** Работа с пользователями */
class UserManager extends \Eleanor\BaseClass
{
	const
		/** Группа пользователей, ожидающих активации (стандартная группа, получаемая после регистрации) */
		GROUP_USER=5,
	
		/** Группа пользователей на сайте (стандартная группа, получаемая после подтверждения регистрации) */
		GROUP_WAIT=2;

	/** Создание пользователя в системе
	 * @param array $user Данные пользователя. Ключи должны совпадать с названиями полей в БД, исключения:
	 *  [string _password] Пароль пользователя
	 *  [array groups] Группы пользователя
	 * @throws EE
	 * @return int */
	public static function Add(array$user=[])
	{
		if(!isset(Eleanor::$vars['min_pass_length']))
			LoadOptions('user-profile');

		if(!isset($user['name']) or $user['name']=='')
			throw new EE('EMPTY_NAME',EE::DEV);

		if(!isset($user['_password']) or $user['_password']=='')
			throw new EE('EMPTY_PASSWORD',EE::DEV);

		$user['name']=str_replace(["\r","\n","\t"],'',$user['name']);
		$user['name']=trim($user['name']);

		static::IsNameBlocked($user['name']);

		if(empty($user['email']))
			throw new EE('EMPTY_EMAIL',EE::DEV);

		if(!filter_var($user['email'],FILTER_VALIDATE_EMAIL))
			throw new EE('EMAIL_ERROR',EE::DEV);

		static::IsEmailBlocked($user['email']);

		$len=(int)Eleanor::$vars['max_name_length'];
		if($len>0 and ($l=mb_strlen($user['name']))>$len)
			throw new EE('NAME_TOO_LONG',EE::DEV,['max'=>$len,'you'=>$l]);

		$len=(int)Eleanor::$vars['min_pass_length'];
		if($len>0 and ($l=mb_strlen($user['_password']))<$len)
			throw new EE('PASS_TOO_SHORT',EE::DEV,['min'=>$len,'you'=>$l]);

		$R=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `name`='
			.Eleanor::$UsersDb->Escape($user['name']).' LIMIT 1');
		if($R->num_rows>0)
			throw new EE('NAME_EXISTS',EE::DEV);

		$R=Eleanor::$Db->Query('SELECT `email` FROM `'.P.'users_site` WHERE `email`='
			.Eleanor::$UsersDb->Escape($user['email']).' LIMIT 1');
		if($R->num_rows>0)
			throw new EE('EMAIL_EXISTS',EE::UNIT);

		if(isset($user['full_name']))
		{
			$user['full_name']=str_replace(["\r","\n","\t"],'',$user['full_name']);
			$user['full_name']=trim($user['full_name']);
		}
		else
			$user['full_name']=\htmlspecialchars($user['name'],ENT,\Eleanor\CHARSET);

		$todb=[
			'name'=>$user['name'],
			'full_name'=>$user['full_name'],
			'password_hash'=>password_hash($user['_password'],PASSWORD_DEFAULT),
			'register'=>isset($user['register']) ? $user['register'] : date('Y-m-d H:i:s'),
		];

		foreach(['last_visit','banned_until','ban_explain','language','timezone'] as $v)
			if(array_key_exists($v,$user))
				$todb[$v]=$user[$v];

		$tosite=[
			'name'=>$todb['name'],
			'full_name'=>$todb['full_name'],
			'register'=>$todb['register'],
		];
		foreach(['last_visit','integration','groups_overload','login_keys','failed_logins','ip','language','email',
				'timezone'] as $v)
			if(array_key_exists($v,$user))
				$tosite[$v]=$user[$v];

		if(!isset($todb['language']))
			$todb['language']=$tosite['language']='';

		if(isset($tosite['ip']) and strpbrk($tosite['ip'],'.:')!==false)
			$tosite['ip']=inet_pton($tosite['ip']);

		$tosite['groups']=isset($user['groups']) ? static::DoGroups($user['groups']) : ','.static::GROUP_USER.',';

		if(strpos($tosite['groups'],','.static::GROUP_WAIT.',')!==false)
			$todb['temp']=1;

		$id=Eleanor::$UsersDb->Insert(USERS_TABLE,$todb);
		Eleanor::$UsersDb->Replace(USERS_TABLE.'_updated',['id'=>$id,'!date'=>'NOW()']);
		$tosite['id']=$toextra['id']=$id;

		foreach($user as $k=>$v)
			if(!array_key_exists($k,$tosite) and !array_key_exists($k,$todb) and $k[0]!='_')
				$toextra[$k]=$v;

		if(isset($toextra['avatar_location']) and !$toextra['avatar_location'] or !isset($toextra['avatar_type']))
			$toextra['avatar_type']='';

		Eleanor::$Db->Insert(P.'users_site',$tosite);
		Eleanor::$Db->Insert(P.'users_extra',$toextra);

		$iid=Integration::Add($todb+$tosite+$toextra,$user);

		if($iid)
			Eleanor::$Db->Update(P.'users_site',['integration'=>$iid],'`id`='.$id.' LIMIT 1');

		return$id;
	}

	/** Обновление пользователя в системе
	 * @param array $user Данные для обновления. Ключи должны совпадать с названиями полей в БД, исключением:
	 *  [string _password] Пароль пользователя
	 *  [array groups] Массив групп пользователя
	 * @param int|array $ids ID пользователей, в данные которых будут вносится коррективы
	 * @throws EE */
	public static function Update(array$user,$ids=[])
	{
		if($ids)
		{
			$single=!is_array($ids) || count($ids)==1;

			if(!$single)
			{
				if(isset($user['email']))#NULL значения для мыла допускаются
					unset($user['email']);

				unset($user['name']);
			}
		}
		else
		{
			$ids=isset(Eleanor::$Login) ? Eleanor::$Login->Get('id') : null;
			$single=true;
		}

		if(!$ids or !$user)
			return;

		$in=Eleanor::$UsersDb->In($ids);
		$nin=Eleanor::$Db->In($ids,true);
		$toextra=$tosite=$todb=[];

		foreach(['last_visit','full_name','banned_until','ban_explain','language','timezone'] as $v)
			if(array_key_exists($v,$user))
				$todb[$v]=$user[$v];

		foreach(['integration','full_name','email','groups_overload','login_keys','failed_logins','ip','last_visit',
			'language','timezone'] as $v)
			if(array_key_exists($v,$user))
				$tosite[$v]=$user[$v];

		if(isset($tosite['ip']) and strpbrk($tosite['ip'],'.:')!==false)
			$tosite['ip']=inet_pton($tosite['ip']);

		if(isset($user['groups']))
		{
			$tosite['groups']=static::DoGroups($user['groups']);
			$todb['temp']=strpos($tosite['groups'],','.static::GROUP_WAIT.',')!==false;
		}

		if($single)
		{
			if(isset($user['email']))
				if(filter_var($user['email'],FILTER_VALIDATE_EMAIL))
				{
					$R=Eleanor::$Db->Query('SELECT `email` FROM `'.P.'users_site` WHERE `email`='
						.Eleanor::$Db->Escape($user['email']).' AND `id`'.$nin.' LIMIT 1');
					if($R->num_rows>0)
						throw new EE('EMAIL_EXISTS',EE::UNIT);
				}
				else
					throw new EE('EMAIL_ERROR',EE::UNIT);

			if(isset($user['name']))
			{
				$user['name']=str_replace(["\r","\n","\t"],'',$user['name']);
				$user['name']=trim($user['name']);

				if($user['name']=='')
					throw new EE('EMPTY_NAME',EE::DEV);

				static::IsNameBlocked($user['name']);

				$R=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `name`='
					.Eleanor::$UsersDb->Escape($user['name']).' AND `id`'.$nin.' LIMIT 1');
				if($R->num_rows>0)
					throw new EE('NAME_EXISTS',EE::DEV);

				if(!isset($todb['full_name']))
				{
					$R=Eleanor::$UsersDb->Query('SELECT `full_name`,`name` FROM `'.USERS_TABLE.'` WHERE `id`'.$in
						.' LIMIT 1');
					if($a=$R->fetch_assoc() and $a['full_name']==htmlspecialchars($a['name'],ENT,\Eleanor\CHARSET))
						$tosite['full_name']=$todb['full_name']=htmlspecialchars($user['name'],ENT,\Eleanor\CHARSET);
				}
			}
		}

		if(isset($user['_password']))
		{
			if($user['_password']=='')
				throw new EE('EMPTY_PASSWORD',EE::DEV);

			if(!isset(Eleanor::$vars['min_pass_length']))
				LoadOptions('user-profile',false);

			$len=(int)Eleanor::$vars['min_pass_length'];
			if($len>0 and ($l=mb_strlen($user['_password']))<$len)
				throw new EE('PASS_TOO_SHORT',EE::DEV,['min'=>$len,'you'=>$l]);

			$todb['password_hash']=password_hash($user['_password'],PASSWORD_DEFAULT);
		}

		foreach($user as $k=>$v)
			if(!array_key_exists($k,$tosite) and !array_key_exists($k,$todb) and $k[0]!='_' and
				!in_array($k,['id','register']))
				$toextra[$k]=$v;

		if(isset($toextra['avatar_location']) and !$toextra['avatar_location'])
			$toextra['avatar_type']='';

		if($todb)
		{
			$num=Eleanor::$UsersDb->Update(USERS_TABLE,$todb,'`id`'.$in);

			if($num>0)
			{
				$ids=(array)$ids;
				Eleanor::$UsersDb->Replace(USERS_TABLE.'_updated',['id'=>$ids,'!date'=>array_fill(0,count($ids),'NOW()')]);
			}
		}

		if($tosite)
			Eleanor::$Db->Update(P.'users_site',$tosite,'`id`'.$in);

		if($toextra)
			Eleanor::$Db->Update(P.'users_extra',$toextra,'`id`'.$in);

		Integration::Update($todb+$tosite+$toextra,$user,$ids);
	}

	/** Удаление пользователя из системы
	 * @param int|array $ids ID удаляемого пользователя */
	public static function Delete($ids)
	{
		if(is_array($ids) and false!==$k=array_search(0,$ids))
			unset($ids[$k]);

		if(!$ids)
			return;

		$in=Eleanor::$Db->In($ids);

		$aroot=Template::$path['uploads'].'avatars/';
		$R=Eleanor::$Db->Query('SELECT `avatar_location` FROM `'.P.'users_extra` WHERE `id`'.$in
			.' AND `avatar_type`=\'upload\'');
		while($a=$R->fetch_assoc())
			if(is_file($a=$aroot.$a['avatar_location']))
				Files::Delete($a);

		#Комментарии помечаем как пользовательские.
		LoadOptions('comments',false);
		Eleanor::$Db->Update(P.'comments',['author_id'=>NULL],'`author_id`'.$in);

		#Удаляем временные проверки пользователей
		Eleanor::$Db->Delete(P.'timecheck','`author_id`'.$in);#InnoDB Удалит автоматически

		#Удаляем external_auth
		Eleanor::$Db->Delete(P.'users_external_auth','`id`'.$in);#InnoDB Удалит автоматически

		#Сюда добавлять свои удаления!

		#Удаляем пользователей
		Eleanor::$Db->Delete(P.'users_site','`id`'.$in);

		#Удаляем экстру
		Eleanor::$Db->Delete(P.'users_extra','`id`'.$in);#InnoDB Удалит автоматически

		$del=[];
		$R=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `id`'.$in);
		while($a=$R->fetch_assoc())
			$del[]=$a['id'];

		if($del)
		{
			$numdel=Eleanor::$UsersDb->Delete(USERS_TABLE,'`id`'.$in);

			if($numdel>0)
				Eleanor::$UsersDb->Replace(USERS_TABLE.'_updated',['id'=>$del,'!date'=>array_fill(0,count($del),'NOW()')]);
		}

		Integration::Delete($ids);
	}

	/** Синхронизация базы текущих пользователей системы с базой глобальных пользователей.
	 * @param array $ids ID пользователей, которых нужно синхронизировать. Допускается заполнение в виде
	 * ID => [ field1, ... ], где ID - ID пользователя, а  field1, ... - поля, взятые из глобальной таблицы для частной
	 * @param array $extra Дополнительные поля синхронизации */
	public static function Sync($ids,array$extra=[])
	{
		$ids=(array)$ids;
		#Поля, которые одинаковые для таблиц users_site и глобальной таблицы пользователей
		$fields=['full_name','name','register','language','timezone'];

		$tosite=$toextra=$sync=$update=[];
		foreach($ids as $k=>$v)
			if(is_array($v))
				$sync[$k]=$v;
			else
				$sync[$v]=[];

		$in=array_keys($sync);
		$R=Eleanor::$Db->Query('SELECT `id`,`'.join('`,`',$fields).'` FROM `'.P.'users_site` WHERE `id`'
			.Eleanor::$Db->In($in));
		while($a=$R->fetch_assoc())
		{
			$update[]=$a['id'];

			if($sync[$a['id']]==array_slice($a,1))
				unset($sync[$a['id']]);
		}

		if(!$sync)
			return;

		$R=Eleanor::$UsersDb->Query('SELECT `id`,`'.join('`,`',$fields).'` FROM `'.USERS_TABLE.'` WHERE `id`'
			.Eleanor::$UsersDb->In($in));
		while($a=$R->fetch_assoc())
			$sync[$a['id']]+=array_slice($a,1);

		foreach($sync as $k=>$v)
		{
			if(isset($v['groups']))
				$v['groups']=static::DoGroups($v['groups']);
			elseif(isset($extra[$k]['groups']))
				$v['groups']=static::DoGroups($extra[$k]['groups']);
			elseif(isset($extra['groups']))
				$v['groups']=static::DoGroups($extra['groups']);

			foreach(['last_visit','integration','groups_overload','login_keys','failed_logins','ip','email'] as $f)
				if(isset($extra[$k]) and array_key_exists($f,$extra[$k]))
					$v[$k]=$extra[$k][$f];
				elseif(array_key_exists($f,$extra))
					$v[$k]=$extra[$f];

			if(isset($v['ip']) and strpbrk($v['ip'],'.:')!==false)
				$v['ip']=inet_pton($v['ip']);

			if(in_array($k,$update))
			{
				Eleanor::$Db->Update('users_site',$v,'`id`='.$k.' LIMIT 1');
				continue;
			}

			$ts=$te=['id'=>$k];#Table Site, Table Extra
			$ts+=$v+['groups'=>','.static::GROUP_USER.','];

			if(isset($extra[$k]))
				foreach($extra[$k] as $ak=>&$av)
					if(!array_key_exists($ak,$ts))
						$te[$ak]=$av;

			foreach($extra as $ak=>&$av)
				if(!array_key_exists($ak,$sync) and !array_key_exists($ak,$ts) and !array_key_exists($ak,$te))
					$te[$ak]=$av;

			$tosite[]=$ts;
			$toextra[]=$te;
		}

		Eleanor::$Db->Insert(P.'users_site',$tosite);
		Eleanor::$Db->Insert(P.'users_extra',$toextra);
	}

	/** Определение корректности пароля определенного пользователя
	 * @param string $pass Предполагаемый пароль пользователя
	 * @param int|null ID пользователя
	 * @return bool */
	public static function MatchPass($pass,$id=null)
	{
		if(!$id)
			$id=isset(Eleanor::$Login) ? Eleanor::$Login->Get('id') : 0;

		$R=Eleanor::$UsersDb->Query('SELECT `password_hash` FROM `'.USERS_TABLE.'` WHERE `id`='.(int)$id.' LIMIT 1');
		if($R->num_rows==0)
			return false;

		list($hash)=$R->fetch_assoc();

		$verify=password_verify($pass,$hash);

		#Поддержим актуальность пароля
		if($verify and password_needs_rehash($hash,PASSWORD_DEFAULT))
			Eleanor::$UsersDb->Update(USERS_TABLE,['password_hash'=>password_hash($pass,PASSWORD_DEFAULT)],
				'WHERE `id`='.(int)$id.' LIMIT 1');

		return$verify;
	}

	/** Проверка имени пользователя на заблокированность в системе
	 * @param string $name Имя пользователя
	 * @throws EE */
	public static function IsNameBlocked($name)
	{
		if(Eleanor::$vars['blocked_names'])
		{
			$flags=\FNM_PERIOD | \FNM_CASEFOLD;

			foreach(explode(',',Eleanor::$vars['blocked_names']) as $v)
				if(\fnmatch($v,$name,$flags))
					throw new EE('NAME_BLOCKED',EE::UNIT);
		}
	}

	/** Проверка email на заблокированность в системе
	 * @param string $email Email
	 * @throws EE */
	public static function IsEmailBlocked($email)
	{
		if(Eleanor::$vars['blocked_emails'])
		{
			$flags=\FNM_PERIOD | \FNM_CASEFOLD;

			foreach(explode(',',Eleanor::$vars['blocked_emails']) as $v)
				if(\fnmatch($v,$email,$flags))
					throw new EE('EMAIL_BLOCKED',EE::UNIT);
		}
	}

	/** Генерация групп пользователей в виде иерархии option-ов для select-а
	 * @param int|array $sel Идентификаторы выделенных пунктов (наличия параметра selected в option-е)
	 * @param int|array $no Идентификатор исключаемых из списка групп (дочерние группы так же будут исключены)
	 * @param bool $optgroup Вывод групп с optgroup
	 * @return string */
	public static function GroupsOpts($sel=[],$no=[],$optgroup=true)
	{
		$mode=$optgroup ? 'optgroup_' : '';
		$r=Eleanor::$Cache->Get('groups_'.$mode.Language::$main);

		if($r===false)
		{
			if($optgroup)
			{
				$sort=$dump=[];
				$table=P.'groups';
				$R=Eleanor::$Db->Query("SELECT `id`, COALESCE(`parent`,0) `parent`,`parents`, `title_l` `title` FROM `{$table}`");
				while($a=$R->fetch_assoc())
				{
					$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'], true)) : '';
					$sort[$a['parent']][$a['id']]=$a['title'];
					$dump[$a['id']]=[
						'title'=>$a['title'],
						'parents'=>$a['parents'] ? explode(',', rtrim($a['parents'], ',')) : [],
					];
				}

				foreach($sort as $parent=>&$items)
				{
					natsort($items);
					if($parent>0)
						foreach($items as $id=>$_)
							$dump[$parent]['_'][$id]=&$dump[$id];
				}
				unset($items, $subs);

				$Make=function($parent=0, $title='') use ($sort, $dump, &$Make)
				{
					$res=$append=[];

					foreach($sort[$parent] as $id=>$_)
					{
						if($title)
						{
							$res[$parent]['title']=$title;
							$title.=' / ';
						}

						$res[$parent][$id]=$dump[$id];

						if(isset($dump[$id]['_']))
							$append=$Make($id, $title.$dump[$id]['title']);
					}

					return $res + $append;
				};

				$r=$Make();
			}
			else
			{
				$maxlen=0;
				$r=$to2sort=$to1sort=$titles=$db=[];

				$table=P.'groups';
				$R=Eleanor::$Db->Query("SELECT `id`, `title_l`, `parents` FROM `{$table}`");
				while($a=$R->fetch_assoc())
				{
					if($a['parents'])
					{
						$cnt=substr_count($a['parents'],',');
						$to1sort[$a['id']]=$cnt;
						$maxlen=max($cnt,$maxlen);
					}

					$a['title_l']=$a['title_l'] ? FilterLangValues(json_decode($a['title_l'],true)) : '';
					$db[$a['id']]=$a;
					$to2sort[$a['id']]=$a['parents'];
					$titles[$a['id']]=$a['title_l'];
				}

				asort($to1sort,SORT_NUMERIC);
				asort($titles,SORT_STRING);

				$n=[];

				foreach($titles as $k=>&$v)
				{
					if(!isset($n[$to2sort[$k]]))
						$n[$to2sort[$k]]=1;

					$to2sort[$k]=$n[$to2sort[$k]]++;
				}

				unset($titles,$n);

				foreach($to1sort as $k=>&$v)
					if($db[$k]['parents'])
					{
						$p=ltrim(strrchr(','.rtrim($db[$k]['parents'],','),','),',');

						if(isset($to2sort[$p]))
							$to2sort[$k]=$to2sort[$p].','.$to2sort[$k];
						else
							unset($to1sort[$k],$db[$k],$to2sort[$k]);
					}

				foreach($to2sort as $k=>&$v)
					$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

				natsort($to2sort);
				foreach($to2sort as $k=>&$v)
				{
					$db[$k]['parents']=$db[$k]['parents'] ? explode(',',rtrim($db[$k]['parents'],',')) : [];
					$r[(int)$db[$k]['id']]=$db[$k];
				}
			}

			Eleanor::$Cache->Put('groups_'.$mode.Language::$main,$r,86400);
		}

		$options='';
		$sel=(array)$sel;
		$no=(array)$no;

		if($optgroup)
		{
			foreach($r as $items)
			{
				$t=isset($items['title']) ? $items['title'] : false;
				$opts='';

				unset($items['title']);
				foreach($items as $id=>$item)
				{
					if(in_array($id, $no) or array_intersect($no,$item['parents']))
						continue;

					$opts.=Html::Option($item['title'],$id,in_array($id,$sel),[],2);
				}

				if($opts)
					$options.=$t ? Html::Optgroup($t,$opts) : $opts;
			}

			return$options;
		}

		foreach($r as &$v)
		{
			$p=$v['parents'];
			$p[]=$v['id'];

			if(array_intersect($no,$p))
				continue;

			$options.=\Eleanor\Classes\Html::Option(
				($v['parents'] ? str_repeat('&nbsp;',count($v['parents'])).'›&nbsp;' : '').$v['title_l'],
				$v['id'],in_array($v['id'],$sel),[],2
			);
		}

		return$options;
	}

	/** Преобразование групп пользователя в строковую последовательность для записи в БД
	 * @param array|int $g Группы пользователя
	 * @return string */
	private static function DoGroups($g)
	{
		if(!$g)
			return','.static::GROUP_USER.',';

		if(is_array($g))
		{
			$mg=reset($g);
			sort($g,SORT_NUMERIC);

			if($mg!=$g[0])
				array_unshift($g,$mg);

			return','.join(',,',$g).',';
		}

		return','.(int)$g.',';
	}
}