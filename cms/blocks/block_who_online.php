<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$index=Eleanor::$service=='index';

if($index)
{
	$users=$bots=[];
	$g=$u=$b=0;
	$limit=30;

	$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`name` `botname`,`us`.`groups`,`us`.`name`
FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id`
WHERE `s`.`expire`>\''.date('Y-m-d H:i:s').'\' AND `s`.`service`=\''.Eleanor::$service
		.'\' ORDER BY `s`.`expire` DESC LIMIT '.$limit);
	while($session=$R->fetch_assoc())
	{
		$limit--;

		if($session['user_id']>0 and $session['type']=='user')
		{
			if($session['groups'])
			{
				$gs=[(int)ltrim($session['groups'],',')];
				$s=join(Permissions::ByGroup($gs,'style'));
			}
			else
				$s='';

			$users[$session['user_id']]=[
				's'=>$s,
				'n'=>$session['name'],
				't'=>$session['enter'],
			];
			$u++;
		}
		elseif($session['botname'])
		{
			if(isset($bots[ $session['botname'] ]))
				$bots[ $session['botname'] ]['cnt']++;
			else
				$bots[ $session['botname'] ]=[
					'cnt'=>1,
					't'=>$session['enter'],
				];
			$b++;
		}
		else
			$g++;
	}

	if($limit<=0)
	{
		$R=Eleanor::$Db->Query('SELECT `type`, COUNT(`type`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''
			.date('Y-m-d H:i:s').'\' AND `service`=\''.Eleanor::$service.'\' GROUP BY `type`');
		while($session=$R->fetch_row())
			$ucnt[$session[0]]=$session[1];

		if(isset($ucnt['guest']))
			$g=$ucnt['guest'];

		if(isset($ucnt['user']))
			$u=$ucnt['user'];

		if(isset($ucnt['bot']))
			$b=$ucnt['bot'];
	}
}

try
{
	if($index)
		return(string)Eleanor::$Template->BlockWhoOnline($users,$bots,$u,$b,$g);

	return(string)Eleanor::$Template->BlockWhoOnline();
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockWhoOnline does not exists.';
}

/** Шаблон для вывода пользователей онлайн (в блоке) */
class UsersOnline
{
	/** @var array Языковые значения */
	public static $lang;

	/** Содержимое блока "Кто онлайн"
	 * @param array $sessions Сессии пользователя в формате сервис=>users|bots|guests=>[], ключи:
	 *  [int user_id] ID пользователя
	 *  [string enter] Дата и время входа
	 *  [string ip_guest] IP гостя и бота
	 *  [string ip_user] IP пользователя
	 *  [string service] Название сервиса
	 *  [string botname] Имя бота
	 *  [string name] Имя пользоватя
	 *  [string _style] стиль группы пользователя
	 * @param array $ser_cnt Если переданы не все сессии , здесь в формате будет сервис=>всего сессий
	 * @param array $ss_cnt Если переданы не все сессии, здесь в формате сервис=>(user|bot|guest)=>число сессий всего
	 * @return string */
	public static function BlockOnline($sessions,$ser_cnt,$ss_cnt)
	{
		$t=time();
		$result='';

		foreach($sessions as $sid=>$session)
		{
			#Чтобы избегать проверок if(isset($v['users'|'bots'|'guests']))
			$session+=['users'=>[],'guests'=>[],'bots'=>[]];

			foreach($session['users'] as &$user)
			{
				$title=call_user_func(static::$lang[ 'min_left' ], floor(($t - strtotime($user[ 'enter' ]))/60));
				$style=$user['_style'] ? ' style="'.$user['_style'].'"' : '';
				$user['name']=htmlspecialchars($user[ 'name' ], \CMS\ENT, \Eleanor\CHARSET);
				$user=<<<HTML
<a class="entry" href="{$user['_aedit']}" data-uid="{$user['user_id']}" data-s="{$sid}" title="{$title}"{$style}>{$user['name']}</a>
HTML;
			}
			unset($user);

			foreach($session['guests'] as &$guest)
			{
				$title=call_user_func(static::$lang[ 'min_left' ], floor(($t - strtotime($guest[ 'enter' ]))/60));
				$guest=<<<HTML
<span class="entry" data-ip="{$guest['ip_guest']}" data-s="{$sid}" title="{$title}">{$guest['ip_guest']}</span>
HTML;
			}
			unset($guest);

			foreach($session['bots'] as &$bot)
			{
				$title=call_user_func(static::$lang['min_left'], floor(($t - strtotime($bot[ 'enter' ]))/60));
				$bot['botname']=htmlspecialchars($bot['botname'],\CMS\ENT,\Eleanor\CHARSET);

				$bot=<<<HTML
<span class="entry" data-ip="{$bot['ip_guest']}" data-s="{$sid}" title="{$title}">{$bot['botname']}</span>
HTML;
			}
			unset($bot);

			#Количество ботов, пользователей, гостей и всего по сервису
			$b=isset($ss_cnt[$sid]['bot']) ? $ss_cnt[$sid]['bot'] : count($session['bots']);
			$u=isset($ss_cnt[$sid]['user']) ? $ss_cnt[$sid]['user'] : count($session['users']);
			$g=isset($ss_cnt[$sid]['guest']) ? $ss_cnt[$sid]['guest'] : count($session['guests']);
			$total=isset($ser_cnt[$sid]) ? $ser_cnt[$sid] : $u+$b+$g;

			#Список пользователей
			if($u>0)
			{
				$num=call_user_func(static::$lang['users'],$u);
				$dots=isset($ss_cnt[$sid]['user']) && count($session['users'])<$ss_cnt[$sid]['user'] ? ' ...' : '';
				$users=join(', ',$session['users']);
				$users=<<<HTML
<div><h4>{$num}</h4>{$users}{$dots}</div>
HTML;
			}
			else
				$users='';

			#Список гостей
			if($g>0)
			{
				$num=call_user_func(static::$lang['guests'],$g);
				$guests=join(', ',$session['guests']);
				$dots=isset($ss_cnt[$sid]['guest']) && count($session['guests'])<$ss_cnt[$sid]['guest'] ? ' ...' : '';
				$users=<<<HTML
<div><h4>{$num}</h4>{$guests}{$dots}</div>
HTML;
			}
			else
				$guests='';

			#Список ботов
			if($b>0)
			{
				$num=call_user_func(static::$lang['bots'],$b);
				$bots=join(', ',$session['bots']);
				$dots=isset($ss_cnt[$sid]['bot']) && count($session['bots'])<$ss_cnt[$sid]['bot'] ? ' ...' : '';
				$bots=<<<HTML
<div><h4>{$num}</h4>{$bots}{$dots}</div>
HTML;
			}
			else
				$bots='';

			#Сливаем все сессии в один список
			$result.=<<<HTML
<div><h2>{$sid} ({$total})</h2>{$users}{$guests}{$bots}</div>
HTML;
		}

		return$result;
	}
}
//UsersOnline::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/users-*.php',false);

//return UsersOnline::class;
/*	'users'=>function($n){
		return$n.Russian::Plural($n,[' пользователь:',' пользователя:',' пользователей:']);
	},
	'min_left'=>function($n){
		return$n.Russian::Plural($n,[' минуту назад',' минуты назад',' минут назад']);
	},
	'bots'=>function($n){
		return$n.Russian::Plural($n,[' поисковый бот:',' поисковых бота:',' поисковых ботов:']);
	},
	'guests'=>function($n){
		return$n.Russian::Plural($n,[' гость',' гостя',' гостей']);
	},*/