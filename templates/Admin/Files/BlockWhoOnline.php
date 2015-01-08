<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\DynUrl;
defined('CMS\STARTED')||die;
/** Оформление для содержимого блока пользователей онлайн. Содержимое блока в файле Classes/Users.php UsersOnline */?>
<div id="who-online"><?=T::$lang['loading']?></div><a href="#"><b><?=T::$lang['update']?></b></a>
<a href="<?=DynUrl::$base?>section=management&amp;module=users&amp;do=online" style="float:right"><b><?=T::$lang['alls']?></b></a>
<script>//<![CDATA[
$(function(){
	var old=CORE.loading,
		F=function(e){
			var w=500,h=250,
				win=window.open('','win'+$(this).data("uid")+$(this).data("gip"),'height='+h+',width='+w+',toolbar=no,directories=no,menubar=no,scrollbars=no,status=no,top='+Math.round((screen.height-h)/2)+',left='+Math.round((screen.width-w)/2));
			CORE.Ajax("<?=\Eleanor\SITEDIR,html_entity_decode(DynUrl::$base)?>section=management&module=users&do=details",
				{
					ip:$(this).data("ip")||"",
					id:$(this).data("uid")||0,
					service:$(this).data("s")
				},
				function(r)
				{
					win.document.open('text/html','replace');
					win.document.write(r);
					win.document.close();
				}
			);
			e.preventDefault();
		};
	CORE.loading=false;
	$("#onlinelist").on("click",".entry",F)
	$("#who-online").on("click",".entry",F).next().click(function(e){
		CORE.Ajax("<?=\Eleanor\SITEDIR,html_entity_decode(DynUrl::$base)?>section=management&module=users&do=online",
			function(r)
			{
				$("#who-online").html(r);
			}
		);
		e.preventDefault();
	}).click();
	CORE.loading=old;
})//]]></script>
<?php
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