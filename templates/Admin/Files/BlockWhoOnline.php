<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\DynUrl,CMS\Eleanor;
defined('CMS\STARTED')||die;

if(\CMS\AJAX)
{
	/** Содержимое блока "Кто онлайн"
	 * @var array $sessions Сессии пользователя в формате сервис=>users|bots|guests=>[], ключи:
	 *  [int user_id] ID пользователя
	 *  [string enter] Дата и время входа
	 *  [string ip_guest] IP гостя и бота
	 *  [string ip_user] IP пользователя
	 *  [string service] Название сервиса
	 *  [string botname] Имя бота
	 *  [string name] Имя пользоватя
	 *  [string _style] стиль группы пользователя
	 * @var array $by_service Если переданы не все сессии , здесь в формате будет сервис=>всего сессий
	 * @var array $by_type Если переданы не все сессии, здесь в формате сервис=>(user|bot|guest)=>число сессий всего*/
	$lang=Eleanor::$Language->Load(__DIR__.'/../translation/block-who-online-*.php',false);

	$t=time();
	$result='';

	foreach($sessions as $sid=>$session)
	{
		#Чтобы избегать проверок if(isset($v['users'|'bots'|'guests']))
		$session+=['users'=>[], 'guests'=>[], 'bots'=>[]];
		foreach($session['users'] as &$user)
		{
			$title=call_user_func($lang['min_left'], floor(($t-strtotime($user['enter']))/60));
			$style=$user['_style'] ? ' style="'.$user['_style'].'"' : '';
			$user['name']=htmlspecialchars($user['name'], \CMS\ENT, \Eleanor\CHARSET);
			$user=<<<HTML
	<a class="entry" href="{$user['_aedit']}" data-uid="{$user['user_id']}" data-s="{$sid}" title="{$title}"{$style}>{$user['name']}</a>
HTML;
		}
		unset($user);
		foreach($session['guests'] as &$guest)
		{
			$title=call_user_func($lang['min_left'], floor(($t-strtotime($guest['enter']))/60));
			$guest=<<<HTML
	<span class="entry" data-ip="{$guest['ip_guest']}" data-s="{$sid}" title="{$title}">{$guest['ip_guest']}</span>
HTML;
		}
		unset($guest);
		foreach($session['bots'] as &$bot)
		{
			$title=call_user_func($lang['min_left'], floor(($t-strtotime($bot['enter']))/60));
			$bot['botname']=htmlspecialchars($bot['botname'], \CMS\ENT, \Eleanor\CHARSET);
			$bot=<<<HTML
	<span class="entry" data-ip="{$bot['ip_guest']}" data-s="{$sid}" title="{$title}">{$bot['botname']}</span>
HTML;
		}
		unset($bot);

		#Количество ботов, пользователей, гостей и всего по сервису
		$b=isset($by_type[$sid]['bot']) ? $by_type[$sid]['bot'] : count($session['bots']);
		$u=isset($by_type[$sid]['user']) ? $by_type[$sid]['user'] : count($session['users']);
		$g=isset($by_type[$sid]['guest']) ? $by_type[$sid]['guest'] : count($session['guests']);
		$total=isset($by_service[$sid]) ? $by_service[$sid] : $u+$b+$g;

		#Список пользователей
		if($u>0)
		{
			$num=call_user_func($lang['users'], $u);
			$dots=isset($by_type[$sid]['user'])&&count($session['users'])<$by_type[$sid]['user'] ? ' ...' : '';
			$users=join(', ', $session['users']);
			$users=<<<HTML
	<div><h4>{$num}</h4>{$users}{$dots}</div>
HTML;
		}
		else
			$users='';

		#Список гостей
		if($g>0)
		{
			$num=call_user_func($lang['guests'], $g);
			$guests=join(', ', $session['guests']);
			$dots=isset($by_type[$sid]['guest'])&&count($session['guests'])<$by_type[$sid]['guest'] ? ' ...' : '';
			$guests=<<<HTML
	<div><h4>{$num}</h4>{$guests}{$dots}</div>
HTML;
		}
		else
			$guests='';

		#Список ботов
		if($b>0)
		{
			$num=call_user_func($lang['bots'], $b);
			$bots=join(', ', $session['bots']);
			$dots=isset($by_type[$sid]['bot'])&&count($session['bots'])<$by_type[$sid]['bot'] ? ' ...' : '';
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

/** @var string $var_0 Ссылка на ajax запросы */
$REQUEST=htmlspecialchars_decode($var_0,\CMS\ENT);

/** Оформление для содержимого блока пользователей онлайн */?>
<div id="who-online"><?=T::$lang['loading']?></div><a href="#"><b><?=T::$lang['update']?></b></a>
<a href="<?=DynUrl::$base?>section=management&amp;module=users&amp;do=online" style="float:right"><b><?=T::$lang['alls']?></b></a>
<script>
$(function(){
	$("#who-online + a").click(function(e){
		CORE.Ajax("<?=$REQUEST?>",
			function(r)
			{
				$("#who-online").html(r);
			}
		);

		e.preventDefault();
	}).click();
})</script>