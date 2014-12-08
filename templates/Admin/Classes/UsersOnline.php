<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor;

defined('CMS\STARTED')||die;

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

	/** Всплывающее окно с подробной информацией о сессии
	 * @param array $session Данные сесси. Если массив пустой - сессия не найдена. Ключи:
	 *  [string type] Тип сессии (user|bot|guest)
	 *  [string enter] Дата и время входа
	 *  [string ip_guest] IP гостя
	 *  [string ip_user] IP пользователя
	 *  [array info] Ключи:
	 *    [string r] Ссылка на страницу, откуда пришел пользователь
	 *    [string c] Кодировки, поддерживаемые клиентским устройством
	 *    [string e] Форматы приема данных, поддерживаемые клиентским устройством
	 *    [array ips] Все возможные IP пользователя, ключ не всегда доступен
	 *  [string service] Название сервиса
	 *  [string browser] USER_AGENT клиентского устройства
	 *  [string location] Адрес нахождения пользователя на сайте
	 *  [string botname] Имя бота
	 *  [string name] Имя пользователя
	 *  [string _style] Стиль группы пользователя
	 * @return string */
	public static function SessionDetail($session)
	{
		$GLOBALS['title'][]=static::$lang['user_info'];
		$t=time();
		$s_lang=static::$lang;

		if($session)
		{
			$ip=$session['ip_guest'] ? $session['ip_guest'] : $session['ip_user'];
			$loc=\Eleanor\SITEDIR.htmlspecialchars($session['location'],\CMS\ENT,\Eleanor\CHARSET,false);
			$activity=call_user_func(static::$lang['min_left'],floor(($t-strtotime($session['enter']))/60));
			$browser=htmlspecialchars($session['browser'],\CMS\ENT,\Eleanor\CHARSET,false);

			#Имя сессии
			if($session['name'])
			{
				$style=$session['style'] ? ' style="'.$session[ 'style' ].'"' : '';
				$session['name']=htmlspecialchars($session['name'],\CMS\ENT, \Eleanor\CHARSET);
				$session['name']=<<<HTML
<h1>{$session['name']}</h1><hr />
HTML;
			}

			$info='';
			foreach($session['info'] as $param=>$value)
			{
				if(!$value)
					continue;

				if($param!='ips')
					$value=htmlspecialchars($value,\CMS\ENT,\Eleanor\CHARSET);

				switch($param)
				{
					case'ips':
						$ips='';
						/** @var array $value */
						foreach($value as $k_=>&$v_)
							$ips.=$k_.'='.$v_.', ';

						$ips=rtrim($ips,', ');
						$value=$ips;
					break;
					case'r':
						$value=<<<HTML
<a href="{$value}" target="_blank" title="{$s_lang['go']}">{$value}</a>
HTML;
				}

				$info.=<<<HTML
<li><b>{$s_lang[$param]}</b> {$value}</li>
HTML;
			}

			#Формирование окончательного результата
			$result=<<<HTML
<ul style="list-style-type:none">
	<li><b>IP</b> <a href="http://eleanor-cms.ru/whois/{$ip}" target="_blank">{$ip}</a></li>
	<li><b>{$s_lang['activity']}</b> {$activity}</li>
	<li><b>{$s_lang['now_onp']}</b> <a href="{$loc}" target="_blank" title="{$s_lang['go']}">{$loc}</a></li>
	<li><b>{$s_lang['browser']}</b> {$browser}</li>
	<li><b>{$s_lang['service']}</b> {$session['service']}</li>
	{$info}
</ul>
HTML;
		}
		else#Сессия не найдена
			$result=<<<HTML
<div style="text-align:center"><b>{$s_lang['session_nf']}</b></div>
HTML;

		return Eleanor::$Template->SimplePage($result);
	}
}
UsersOnline::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/users-*.php',false);

return UsersOnline::class;