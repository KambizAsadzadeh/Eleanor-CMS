<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html, CMS\UserManager, Eleanor\Classes\Types;

defined('CMS\STARTED')||die;

/** Шаблоны управления пользователями в админке */
class Users
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language['users'];
		$links=$GLOBALS['Eleanor']->module['links'];

		T::$data['navigation']=[
			[$links['list'],$lang['list'],'act'=>$act=='list'],
			[$links['create'],static::$lang['create'],'act'=>$act=='create'],
			[$links['online'],$lang['online-list'],'act'=>$act=='online'],
			[$links['letters'],$lang['letters'],'act'=>$act=='letters'],
			[$links['options'],T::$lang['options'],'act'=>$act=='options'],
		];
	}

	/** Страница правки форматов писем
	 * @param array $controls Перечень контролов
	 * @param array $values HTML код контролов
	 * @param bool $saved Флаг успешного сохранения
	 * @param array $errors Ошибки заполнения формы
	 * @return string*/
	public static function Letters($controls,$values,$saved,$errors)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu('letters');

		#Errors
		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];
		#/Errors

		$info=$errors || $saved ? T::$T->Alert($saved && !$errors ? static::$lang['save-success'] : $errors,$errors ? 'danger' : 'success',true) : '';

		#Content
		$content='';
		$new_vars=true;
		foreach($controls as $k=>&$v)
			if($v and is_array($v) and !empty($values[$k]))
			{
				if($new_vars)
				{
					$new_vars=false;
					$vars=explode('_',$k,2)[0];
					$vars=static::$lang['vars-'.$vars];
					$content.=<<<HTML
<div class="alert alert-info" role="alert">{$vars}</div>
HTML;
				}

				$control=T::$T->LangEdit($values[$k], null);
				$content.=<<<HTML
<div class="form-group">
	<label>{$v['title']}</label>
	{$control}
</div>
HTML;
			}
			elseif(is_string($v))
			{
				$new_vars=true;

				if($content)
					$content.='</div>';

				$content.=<<<HTML
<div class="block">
	<h4>{$v}</h4>
HTML;
			}

		$content.='</div>';
		#/Content

		$c_lang=static::$lang;
		return<<<HTML
			{$info}
			<section id="content">
				<form method="post">
					{$content}
					<button type="submit" class="btn btn-success"><b>{$c_lang['letters-save']}</b></button>
				</form>
			</section>
<script>$(function(){
	$("#content input:not(:checkbox,[class*=select2]),textarea").addClass("form-control");
})</script>
HTML;
	}

	/** Список сессий онлайн
	 * @param array $items Перечень сессий онлайн. Формат: ID=[], ключи:
	 *  [string type] Тип сессии: user, guest или bot (пользователь, гость или поисковый бот)
	 *  [int|null user_id] ID пользователя, только для пользовательских сессий
	 *  [string enter] Дата входа
	 *  [string expire] Дата истечения сессии
	 *  [bool _online] Флаг того, что сессия пока не истекла (онлайн)
	 *  [string ip_guest] IP адрес для гостя и бота
	 *  [string ip_user] IP адрес для пользователя
	 *  [string service] идентификатор сервиса
	 *  [string browser] USER AGENT устройства
	 *  [string location] URL последнего запроса сессии
	 *  [string botname] Имя бота
	 *  [array groups] ID групп пользователя
	 *  [string name] Имя пользователя (небезопасный HTML)
	 *  [string full_name] Полное имя пользователя
	 *  [string _aedit] Ссылка на редактирование пользователя
	 *  [string|null _adel] Ссылка на удаление пользователя либо false
	 *  [array avatar] Аватар пользователя
	 * @param array $groups Перечень групп пользователей для $items. Формат: ID=[], ключи:
	 *  [string title] Название группы
	 *  [string style] Стиль группы
	 * @param bool $notempty Флаг того, что сессии существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество страниц ошибок (всего)
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_ip] Ссылка на сортировку списка по ip
	 *  [string sort_enter] Ссылка на сортировку списка по дате входа
	 *  [string sort_expire] Ссылка на сортировку списка по дате истечения сессии
	 *  [string sort_location] Ссылка на сортировку списка по URL последнего запроса
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества сессий отображаемых на странице
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function OnlineList($items,$groups,$notempty,$cnt,$pp,$query,$page,$links)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu('online');

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		if($items)
		{
			$br_img=T::$http['static'].'images/browsers/';
			$browser=[
				'opera'=>['opera.png','Opera'],
				'firefox'=>['firefox.png','Mozilla Firefox'],
				'chrome'=>['chrome.png','Google Chrome'],
				'safari'=>['safari.png','Apple Safari'],
				'msie'=>['ie.png','Microsoft Internet Explore'],
			];

			$Items=TableList(4)
				->head(
					static::$lang['who'],
					['IP','sort'=>$query['sort']=='ip' ? $query['order'] : false,'href'=>$links['sort_ip']],
					[static::$lang['enter'],'sort'=>$query['sort']=='enter' ? $query['enter'] : false,'href'=>$links['sort_enter']],
					[static::$lang['location'],'sort'=>$query['sort']=='location' ? $query['sort'] : false,'href'=>$links['sort_location']]
				);

			foreach($items as $k=>$session)
			{
				#Иконка браузера
				$icon='';

				foreach($browser as $bk=>$bv)
					if(stripos($session['browser'],$bk)!==false)
					{
						$icon=<<<HTML
<img title="{$bv[1]}" src="{$br_img}{$bv[0]}" />
HTML;
						break;
					}
				#/Иконка браузера

				#Стиль группы
				$style=null;
				$title='';

				if($session['groups'])
				{
					$group=reset($session['groups']);

					if(isset($groups[$group]))
					{
						$title=' title="'.$groups[$group]['title'].'"';
						$style=' style="'.$groups[$group]['style'].'"';
					}
				}
				#/Стиль группы

				$menu=<<<HTML
<li><a href="{$session['_adetail']}" class="iframe">{$c_lang['details']}</a></li>
HTML;

				switch($session['type'])
				{
					case'bot':
						$session['botname']=htmlspecialchars($session['botname'],\CMS\ENT,\Eleanor\CHARSET);
						$avatar=ItemAvatar($session['botname']);
						$name=<<<HTML
<a href="{$session['_adetail']}" class="iframe"><i>{$session['botname']}</i></a>
HTML;
					break;
					case'user':
						$session['name']=htmlspecialchars($session['name'],\CMS\ENT,\Eleanor\CHARSET);
						$avatar=$session['avatar']
							? <<<HTML
<a class="zoom-thumb iframe" href="{$session['_adetail']}"><span class="thumb" style="background-image: url({$session['avatar']['http']});"></span></a>
HTML
							: ItemAvatar($session['name']);
						$name=<<<HTML
<a href="{$session['_adetail']}" class="iframe"{$style}{$title}>{$session['name']}</a>
HTML;
						$menu.=<<<HTML
<li><a href="{$session['_a']}">{$t_lang['goto']}</a></li>
<li><a href="{$session['_aedit']}">{$t_lang['edit']}</a></li>
HTML;
						if($session['_adel'])
							$menu.=<<<HTML
<li><a href="{$session['_adel']}">{$t_lang['delete']}</a></li>
HTML;
					break;
					default:
						$avatar=ItemAvatar($session['ip_guest']);
						$name=<<<HTML
<a href="{$session['_adetail']}" class="iframe"><i>{$c_lang['guest']}</i></a>
HTML;
				}

				$session['location']=htmlspecialchars($session['location'],\CMS\ENT,\Eleanor\CHARSET,false);
				$ip=$session['ip_guest'] ? $session['ip_guest'] : $session['ip_user'];

				$avatar.=<<<HTML
<div class="col_item-text"><h4>{$name}</h4><ul class="inline-menu">{$menu}</ul></div>
HTML;

				$Items->item(
					[$avatar,
						'class'=>'col_item',
						'tr-extra'=>['id'=>'item'.$k]],
					<<<HTML
<a href="http://eleanor-cms.ru/whois/{$ip}" target="_blank">{$ip}</a>
HTML
					,Eleanor::$Language->Date($session['enter'],'fdt'),
					<<<HTML
<div class="ellipsis">{$icon} <a href="{$session['location']}" target="_blank" style="text-overflow: ellipsis">{$session['location']}</a></div>
HTML
				);
			}

			$Items->end()->foot('',$cnt,$pp,$page,$links);

			$back=Html::Input('back',\Eleanor\SITEDIR.\CMS\Url::$current,['type'=>'hidden']);
			$t_lang=T::$lang;
			$c_lang=static::$lang;
			$Items.=<<<HTML
<!-- Окно подтверждение удаления -->
<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{$t_lang['delete-confirm']}</h4>
			</div>
			<div class="modal-body">{$c_lang['delete-text-span']}</div>
			<div class="modal-footer"><form method="post" id="delete-form">{$back}
				<button type="button" class="btn btn-default" data-dismiss="modal">{$t_lang['cancel']}</button>
				<button type="submit" class="btn btn-danger" name="ok">{$t_lang['delete']}</button>
			</form></div>
		</div>
	</div>
</div>
<script>$(ItemsDelete)</script>
HTML;

			$Items.=T::$T->IframeLink();
		}
		else
			$Items=T::$T->Alert(static::$lang['session_not_found'],'info');

		if($notempty)
		{
			$filters=[
				'ip'=>'',
				'offline'=>'',
				'user_id'=>'',
				'user'=>'',
			];

			if($links['nofilter'] and isset($query['fi']))
			{
				$caption=T::$lang['change-filter'];
				$applied=[];

				foreach($query['fi'] as $k=>$v)
					switch($k)
					{
						case'ip':
							$applied[]=static::$lang['by-ip'];
							$filters['ip']=$v;
						break;
						case'offline':
							$applied[]=static::$lang['by-offline'];
							$filters['offline']=$v;
						break;
						case'user':
							$applied['user']=static::$lang['by-username'];
							$filters['user']=$v;
						break;
						case'user_id':
							$applied['user']=static::$lang['by-username'];
							$filters['user_id']=$v;
					}

				$nofilter='<p class="filters-text grey">'.sprintf(static::$lang['applied-by%'],join(', ',$applied))
					.'<a class="filters-reset" href="'.$links['nofilter'].'">&times;</a></p>';
			}
			else
			{
				$caption=T::$lang['apply-filter'];
				$nofilter='';
			}

			$opts=Html::Option('&mdash;','',!$filters['offline'],[],2)
				.Html::Option(static::$lang['include'],'include',$filters['offline']=='include')
				.Html::Option(static::$lang['only'],'only',$filters['offline']=='only');
			$filters['offline']=Html::Select('fi[offline]',$opts,['class'=>'form-control','id'=>'fi-offline']);
			$filters['ip']=Html::Input('fi[ip]',$filters['ip'],['placeholder'=>static::$lang['filter-by-ip'],'title'=>static::$lang['filter-by-ip'],
				'class'=>'form-control','id'=>'fi-ip']);
			$filters['name']=T::$T->Author([$filters['user'],$filters['user_id']],
				['placeholder'=>static::$lang['filter-by-user'],'title'=>static::$lang['filter-by-user'],'id'=>'fi-user','name'=>'fi[user]']);
			$filters=<<<HTML
					<!-- Фильтры -->
					<div class="filters">
						{$nofilter}
						<div class="dropdown">
							<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
							<form class="dropdown-menu dropform pull-right" method="post">
								<div class="form-group">
									<label for="fi-ip">IP</label>
									{$filters['ip']}
								</div>
								<div class="form-group">
									<label for="fi-offline">{$c_lang['offline']}</label>
									{$filters['offline']}
								</div>
								<div class="form-group has-feedback author cloneable">
									<label class="control-label" for="fi-user">{$c_lang['name']}</label>
									{$filters['name']}
								</div>
								<button type="submit" class="btn btn-primary">{$t_lang['apply']}</button>
							</form>
						</div>
					</div>
HTML;
		}
		else
			$filters='';

		$links=$GLOBALS['Eleanor']->module['links'];

		return<<<HTML
	<div class="list-top">
		{$filters}
		<a href="{$links['create']}" class="btn btn-default">{$c_lang['create']}</a>
	</div>
	{$Items}
HTML;
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
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu('online');

		$t=time();
		$s_lang=static::$lang;

		if($session)
		{
			$ip=$session['ip_guest'] ? $session['ip_guest'] : $session['ip_user'];
			$loc=\Eleanor\SITEDIR.htmlspecialchars($session['location'],\CMS\ENT,\Eleanor\CHARSET,false);
			$enter=call_user_func(static::$lang['min_left'],floor(($t-strtotime($session['enter']))/60));
			$browser=htmlspecialchars($session['browser'],\CMS\ENT,\Eleanor\CHARSET,false);

			#Имя сессии
			if($session['name'])
			{
				$style=$session['style'] ? ' style="'.$session[ 'style' ].'"' : '';
				$session['name']=htmlspecialchars($session['name'],\CMS\ENT, \Eleanor\CHARSET);
				$title=<<<HTML
<h3{$style}>{$session['name']}</h3>
HTML;
			}
			elseif($session['botname'])
				$title=<<<HTML
<h3>{$session['botname']}</h3>
HTML;
			else
				$title=<<<HTML
<h3>{$session['ip_guest']}</h3>
HTML;

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
{$title}
<ul>
	<li><b>IP</b> <a href="http://eleanor-cms.ru/whois/{$ip}" target="_blank">{$ip}</a></li>
	<li><b>{$s_lang['activity']}</b> {$enter}</li>
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

		#Удалим заголовок у iframe. Без него симпатичнее.
		if(isset($_GET['iframe']))
			$result.=<<<HTML
<script>$(function(){
	$("head title").html("&nbsp;");
})</script>
HTML;

		return$result;
	}

	/** Список пользователей
	 * @param array $items Перечень пользователей. Формат: ID=[], ключи:
	 *  [string name] Имя пользователя (небезопасный HTML)
	 *  [string full_name] Полное имя пользователя
	 *  [string email] E-mail пользователя
	 *  [array groups] ID групп пользователя
	 *  [string ip] IP адрес пользователя
	 *  [string last_visit] Дата последнего визита пользователя
	 *  [string _a] Ссылка на просмотр пользователя в пользовательской части
	 *  [string _aedit] Ссылка на редактирование пользователя
	 *  [string|null _adel] Ссылка на удаление пользователя либо false
	 *  [array avatar] Аватар пользователя
	 * @param array $groups Перечень групп пользователей для $items. Формат: ID=[], ключи:
	 *  [string title] Название группы
	 *  [string style] Стиль группы
	 * @param int $cnt Суммарное количество пользователей (всего)
	 * @param int $pp Количество пользователей на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_name] Ссылка на сортировку списка по имени пользователя
	 *  [string sort_email] Ссылка на сортировку списка по e-mail
	 *  [string sort_last_visit] Ссылка на сортировку списка по дате последнего входа
	 *  [string sort_ip] Ссылка на сортировку списка по ip пользователя
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пользователей отображаемых на странице
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$groups,$cnt,$pp,$query,$page,$links)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title']
		];

		static::Menu('list');

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		if($items)
		{
			$Items=TableList(6)->form()
				->head(
					[static::$lang['username'],'sort'=>$query['sort']=='name' ? $query['order'] : false,'href'=>$links['sort_name']],
					['E-mail','sort'=>$query['sort']=='email' ? $query['order'] : false,'href'=>$links['sort_email']],
					static::$lang['groups'],
					[static::$lang['last_visit'],'sort'=>$query['sort']=='last_visit' ? $query['order'] : false,'href'=>$links['sort_last_visit']],
					['IP','sort'=>$query['sort']=='ip' ? $query['order'] : false,'href'=>$links['sort_ip']],
					[Html::Check('mass',false,['id'=>'mass-check']),'class'=>'col_check']
				);

			foreach($items as $k=>$v)
			{
				$grs='';
				foreach($v['groups'] as $g)
					if(isset($groups[$g]))
					{
						$style=$groups[$g]['style'] ? ' style="'.$groups[$g]['style'].'"' : '';
						$grs.=<<<HTML
, <a href="{$groups[$g]['_aedit']}"{$style}>{$groups[$g]['title']}</a>
HTML;
					}

				$Items->item(
					$Items('main',
						htmlspecialchars($v['name'],\CMS\ENT,\Eleanor\CHARSET),
						[ $v['_aedit'],[ $v['_a'], T::$lang['goto'],'extra'=>['target'=>'_blank'] ], [$v['_aedit'], T::$lang['edit']],
							$v['_adel'] ? [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']] : false],
						$v['avatar'] ? $v['avatar']['http'] : false
					)+['tr-extra'=>['id'=>'item'.$k]],
					$v['email'] ? "<a href='mailto:{$v['email']}'>{$v['email']}</a>" : '&mdash;',
					ltrim($grs,', '),
					(int)$v['last_visit']>0 ? Eleanor::$Language->Date($v['last_visit'],'fdt') : '&mdash;',
					$v['ip'] ? '<a href="mailto:'.$v['email'].'">'.$v['ip'] : '&mdash;',
					[Html::Check('items[]',false,['value'=>$k]),'col_check']
				);
			}

			$Items->end()->foot(Html::Option(T::$lang['delete'],'delete'),$cnt,$pp,$page,$links)->endform()->checks();

			$back=Html::Input('back',\Eleanor\SITEDIR.\CMS\Url::$current,['type'=>'hidden']);
			$t_lang=T::$lang;
			$c_lang=static::$lang;
			$Items.=<<<HTML
<!-- Окно подтверждение удаления -->
<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{$t_lang['delete-confirm']}</h4>
			</div>
			<div class="modal-body">{$c_lang['delete-text-span']}</div>
			<div class="modal-footer"><form method="post" id="delete-form">{$back}
				<button type="button" class="btn btn-default" data-dismiss="modal">{$t_lang['cancel']}</button>
				<button type="submit" class="btn btn-danger" name="ok">{$t_lang['delete']}</button>
			</form></div>
		</div>
	</div>
</div>
<script>$(ItemsDelete)</script>
HTML;

		}
		else
			$Items=T::$T->Alert(static::$lang['not_found'],'info');

		$filters=[
			'name'=>'',
			'full_name'=>'',
			'id'=>'',
			'group'=>'',
			'last_visit_from'=>'',
			'last_visit_to'=>'',
			'register_from'=>'',
			'register_to'=>'',
			'ip'=>'',
			'email'=>'',
		];

		if($links['nofilter'] and isset($query['fi']))
		{
			$caption=T::$lang['change-filter'];
			$applied=[];

			foreach($query['fi'] as $k=>$v)
				switch($k)
				{
					case'name':
						$applied[]=static::$lang['by-username'];
						$filters['name']=$v;
					break;
					case'id':
						$applied[]=static::$lang['by-id'];
						$filters['id']=$v;
					break;
					case'full_name':
						$applied[]=static::$lang['by-full-name'];
						$filters['full_name']=$v;
					break;
					case'group':
						$applied[]=static::$lang['by-group'];
						$filters['group']=$v;
					break;
					case'last_visit_from':
						$applied['last_visit']=static::$lang['by-last-visit'];
						$filters['last_visit_from']=$v;
					break;
					case'last_visit_to':
						$applied['last_visit']=static::$lang['by-last-visit'];
						$filters['last_visit_to']=$v;
					break;
					case'register_from':
						$applied['register']=static::$lang['by-register'];
						$filters['register_from']=$v;
					break;
					case'register_to':
						$applied['register']=static::$lang['by-register'];
						$filters['register_to']=$v;
					break;
					case'ip':
						$applied[]=static::$lang['by-ip'];
						$filters['ip']=$v;
					break;
					case'email':
						$applied[]=static::$lang['by-email'];
						$filters['email']=$v;
				}

			$nofilter='<p class="filters-text grey">'.sprintf(static::$lang['applied-by%'],join(', ',$applied))
				.'<a class="filters-reset" href="'.$links['nofilter'].'">&times;</a></p>';
		}
		else
		{
			$caption=T::$lang['apply-filter'];
			$nofilter='';
		}

		$filters['id']=Html::Input('fi[id]',$filters['id'],['placeholder'=>static::$lang['filter-by-id'],'title'=>static::$lang['filter-by-id'],'type'=>'number',
			'class'=>'form-control','id'=>'fi-name','min'=>0]);
		$filters['name']=Html::Input('fi[name]',$filters['name'],['placeholder'=>static::$lang['filter-by-name'],'title'=>static::$lang['filter-by-name'],
			'class'=>'form-control','id'=>'fi-name']);
		$filters['full_name']=Html::Input('fi[full_name]',$filters['full_name'],['placeholder'=>static::$lang['filter-by-name'],'title'=>static::$lang['filter-by-name'],
			'class'=>'form-control','id'=>'fi-full-name']);
		$filters['group']=Html::Select('fi[group]',UserManager::GroupsOpts($filters['full_name']),['class'=>'form-control','id'=>'fi-group']);
		$filters['last_visit_from']=T::$T->DatePicker('fi[last_visit_from]',$filters['last_visit_from'],true,['placeholder'=>static::$lang['from'],'title'=>static::$lang['from'],
			'class'=>'form-control','id'=>'fi-last-visit-from'],false);
		$filters['last_visit_to']=T::$T->DatePicker('fi[last_visit_to]',$filters['last_visit_to'],true,['placeholder'=>static::$lang['to'],'title'=>static::$lang['to'],
			'class'=>'form-control','id'=>'fi-last-visit-to'],false);
		$filters['register_from']=T::$T->DatePicker('fi[register_from]',$filters['register_from'],true,['placeholder'=>static::$lang['from'],'title'=>static::$lang['from'],
			'class'=>'form-control','id'=>'fi-register-from'],false);
		$filters['register_to']=T::$T->DatePicker('fi[register_to]',$filters['register_to'],true,['placeholder'=>static::$lang['to'],'title'=>static::$lang['to'],
			'class'=>'form-control','id'=>'fi-register-to'],false);
		$filters['ip']=Html::Input('fi[ip]',$filters['ip'],['placeholder'=>static::$lang['filter-by-ip'],'title'=>static::$lang['filter-by-ip'],
			'class'=>'form-control','id'=>'fi-ip']);
		$filters['email']=Html::Input('fi[email]',$filters['email'],['placeholder'=>static::$lang['filter-by-email'],'title'=>static::$lang['filter-by-email'],
			'class'=>'form-control','id'=>'fi-email']);
		$filters=<<<HTML
				<!-- Фильтры -->
				<div class="filters">
					{$nofilter}
					<div class="dropdown">
						<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
						<form class="dropdown-menu dropform pull-right" method="post">
							<div class="form-group">
								<label for="fi-id">ID</label>
								{$filters['id']}
							</div>
							<div class="form-group">
								<label for="fi-name">{$c_lang['name']}</label>
								{$filters['name']}
							</div>
							<div class="form-group">
								<label for="fi-full-name">{$c_lang['full-name']}</label>
								{$filters['full_name']}
							</div>
							<div class="form-group">
								<label for="fi-group">{$c_lang['group']}</label>
								{$filters['group']}
							</div>
							<div class="form-group">
								<label for="fi-last-visit-from">{$c_lang['last_visit']}</label>
								<div class="row">
									<div class="col-xs-6">
										{$filters['last_visit_from']}
									</div>
									<div class="col-xs-6">
										{$filters['last_visit_to']}
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="fi-last-visit-to">{$c_lang['register']}</label>
								<div class="row">
									<div class="col-xs-6">
										{$filters['register_from']}
									</div>
									<div class="col-xs-6">
										{$filters['register_to']}
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="fi-ip">IP</label>
								{$filters['ip']}
							</div>
							<div class="form-group">
								<label for="fi-email">E-mail</label>
								{$filters['email']}
							</div>
							<button type="submit" class="btn btn-primary">{$t_lang['apply']}</button>
						</form>
					</div>
				</div>
				<script>$(function(){
//Взаимосвязь селектов по выбору даты
$("#fi-last-visit-from").on("dp.change",function(e){
	$('#fi-last-visit-to').data("DateTimePicker").setMinDate(e.date);
});
$("#fi-last-visit-to").on("dp.change",function(e){
	$('#fi-last-visit-from').data("DateTimePicker").setMaxDate(e.date);
});
$("#fi-register-from").on("dp.change",function(e){
	$('#fi-register-to').data("DateTimePicker").setMinDate(e.date);
});
$("#fi-register-to").on("dp.change",function(e){
	$('#fi-register-from').data("DateTimePicker").setMaxDate(e.date);
});
			})</script>
HTML;

		$links=$GLOBALS['Eleanor']->module['links'];

		return<<<HTML
	<div class="list-top">
		{$filters}
		<a href="{$links['create']}" class="btn btn-default">{$c_lang['create']}</a>
	</div>
	{$Items}
HTML;
	}

	/** Страница создания/редактирования пользователя
	 * @param int $id ID редактируемого пользователя, если $id==0 значит пользователь создается
	 * @param array $values Значения полей формы:
	 *  [string name] Имя пользователя (логин)
	 *  [string full_name] Полное имя пользователя (ФИО)
	 *  [string email] E-mail пользователя
	 *  [string timezone] Часовой пояс пользователя
	 *  [int _group] ID основной группы пользователя
	 *  [array groups] ID дополнительных групп пользователя
	 *  [string language] Язык пользователя на сайте
	 *  [string banned_until] Дата до которой пользователь заблокирован на сайте (пустое в случае отсутствия блокировки)
	 *  [string ban_explain] Причина блокировки
	 *  [array|null avatar] Аватар пользователя, строится по принципу миниатюры
	 *  [string _password] Пароль пользователя
	 *  [array _letter] Флажки отправки писем: new - уведомление о новом аккаунте, name - имени, pass - пароле
	 *  [bool _clean_logins] Флаг очистки неудачных попыток авторизации
	 *  [array _groups_overload_method] Перезагрузка прав групп пользователя
	 *  [array _external_auth] Внешние авторизации, ключи:
	 * 	 [string provider] ID внешнего сервиса
	 *   [string provider_uid] ID пользователя на внешнем сервисе
	 *   [string identity] Ссылка на пользователя на внешнем сервисе
	 *  [bool _online] Не значение, а просто флаг: пользователь онлайн на сайте или нет
	 *  [array login_keys] Сессии пользователя на сайте: Класс авторизации -> Ключ авторизации
	 *  -> [expire, ip, bindip (bool), useragent,'_del'=>Флаг возможности удаления]
	 *  [array failed_logins] Перечень неудачных авторизаций: [timestamp, service, user agent, ip]
	 * @param callback $GroupsOpts Генератор перечня группы в виде <option>...</option>:
	 * @param array $groups Перечень элементов формы для перезагрузки параметров групп, вероятные ключи:
	 *  [string type] Тип элемента: check - флажок, input - поле и т.п.
	 *  [string label-for] Идентификатор свойства for для label-а
	 * @param callback $Groups2Html Генератор html из $groups
	 * @param array $extra Перечень элементов формы экстрапараметров пользователя, вероятные ключи:
	 *  [string type] Тип элемента: check - флажок, input - поле и т.п.
	 *  [string label-for] Идентификатор свойства for для label-а
	 * @param callback $Extra2Html Генератор html из $users
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 * @param int $maxupload Максимально доспустимый размер файла для загрузки
	 * @return string */
	public static function CreateEdit($id,$values,$GroupsOpts,$groups,$Groups2Html,$extra,$Extra2Html,$errors,$back,$links,$maxupload)
	{
		#Select2
		include __DIR__.'/Select2.php';

		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		$c_lang=static::$lang;
		$t_lang=T::$lang;

		static::Menu($id ? 'edit' : 'create');

		#Подключение скриптов для поля пароля
		$GLOBALS['head']['passfield']=<<<'HTML'
<script src="//cdn.rawgit.com/antelle/passfield/master/js/passfield.js"></script>
<script src="//cdn.rawgit.com/antelle/passfield/master/js/locales.js"></script>
<link rel="stylesheet" href="//cdn.rawgit.com/antelle/passfield/master/css/passfield.css" />
HTML;

		#Элементы формы
		$extra_html=$Extra2Html();
		$groups_html=$Groups2Html();
		$input=[
			'name'=>Html::Input('name',$values['name'],['id'=>'name','class'=>'form-control need-tabindex input-lg pim','placeholder'=>$c_lang['input_name'],'title'=>$c_lang['input_name'],'required'=>true]),
			'full_name'=>Html::Input('full_name',$values['full_name'],['id'=>'full_name','class'=>'form-control need-tabindex pim']),
			'email'=>Html::Input('email',$values['email'],['id'=>'email','type'=>'email','class'=>'form-control need-tabindex pim','required'=>true]),
			'language'=>'',
			'_password'=>Html::Input('_password',$values['_password'],['id'=>'password','type'=>'password','class'=>'form-control need-tabindex pim']),
			'_group'=>Select2::Select('_group',$GroupsOpts($values['_group']),['id'=>'group','class'=>'need-tabindex pim']),
			'groups'=>Select2::Items('groups',$GroupsOpts($values['groups']),['id'=>'groups','class'=>'need-tabindex pim']),
			'banned_until'=>Eleanor::$Template->DatePicker('banned_until',$values['banned_until'],true,['id'=>'banned-until','class'=>'form-control need-tabindex pim']),
			'ban_explain'=>Html::Text('ban_explain',$values['ban_explain'],['placeholder'=>$c_lang['input-ban-explain'],'title'=>$c_lang['input-ban-explain'],'id'=>'ban-explain','class'=>'form-control need-tabindex pim']),
		];

		#Выбор языка
		if(Eleanor::$vars['multilang'])
		{
			$input['language'].=Html::Option($c_lang['auto'],'',$values['language']=='');

			foreach(\CMS\Eleanor::$langs as $k=>$v)
				$input['language'].=Html::Option($v['name'],$k,$k==$values['language'],['lang'=>$v['d']]);

			$input['language']=Html::Select('language',$input['language'],['id'=>'language','class'=>'form-control need-tabindex pim']);
		}
		#/Элементы формы

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Errors
		$er_def='';
		$er_extra=isset($errors['extra']) ? (array)$errors['extra'] : [];
		$er_group=isset($errors['groups']) ? (array)$errors['groups'] : [];

		unset($errors['extra'],$errors['groups']);
		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error) and isset(static::$lang[$error]))
				$error=static::$lang[$error];

			$er_def=T::$T->Alert($error,'danger',true);;
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		$parts=[
			'extra'=>[],
			'groups'=>[],
		];

		#Формирование блоков с экстра-параметрами
		$part='';

		foreach($extra as $k=>$control)
		{
			if(is_string($control))
			{
				$parts['extra'][$k]='';
				$part=&$parts['extra'][$k];
				continue;
			}
			elseif(!isset($extra_html[$k]))
				continue;

			$check=$control['type']=='check';

			if($check)
			{
				$hint=isset($control['descr']) ? ' title="'.$control['descr'].'"' : '';
				$part.=<<<HTML
<label class="group-checkbox"{$hint}>{$extra_html[$k]} {$control['title']}</label>
HTML;

				continue;
			}

			$error=isset($er_extra[$k]) ? T::$T->Alert($er_extra[$k],'danger',true) : '';
			$for=isset($control['label-for']) ? " for='{$control['label-for']}'" : '';
			$hint=isset($control['descr']) ? "<p class='text-muted'>{$control['descr']}</p>" : '';
			$part.=<<<HTML
<div class="form-group">
	<label{$for}>{$control['title']}</label>{$error}
	{$extra_html[$k]}{$hint}
</div>
HTML;
		}

		#Форматирование непосредственно HTML блоков
		foreach($parts['extra'] as $k=>&$part)
			$part=<<<HTML
<div class="block-t expand">
	<p class="btl collapsed" data-toggle="collapse" data-target="#extra-{$k}">{$extra[$k]}</p>
	<div id="extra-{$k}" class="collapse">
		<div class="bcont">
			{$part}
		</div>
	</div>
</div>
HTML;
		unset($part);
		#/Формирование блоков с экстра-параметрами

		#Формирование блоков с переопределением параметров групп
		$part='';

		foreach($groups as $k=>$control)
		{
			if(is_string($control))
			{
				$parts['groups'][$k]='';
				$part=&$parts['groups'][$k];
				continue;
			}
			elseif(!isset($groups_html[$k]))
				continue;

			$check=$control['type']=='check';

			if($check)
			{
				$hint=isset($control['descr']) ? ' title="'.$control['descr'].'"' : '';
				$groups_html[$k]=<<<HTML
<label class="group-checkbox"{$hint}>{$groups_html[$k]} {$control['title']}</label>
HTML;
			}

			$error=isset($er_group[$k]) ? T::$T->Alert($er_group[$k],'danger',true) : '';
			$checked=isset($values['_groups_overload_method'][$k]) && $values['_groups_overload_method'][$k]!='inherit' ? ' checked' : '';
			$groups_html[$k]=<<<HTML
<div class="input-group">
	{$error}{$groups_html[$k]}
	<span class="input-group-addon">
		<input type="checkbox" name="_groups_overload_method[{$k}]" value="replace"{$checked} title="{$c_lang['replace']}" class="replace need-tabindex" />
	</span>
</div>
HTML;

			if($check)
			{
				$part.=$groups_html[$k];
				continue;
			}

			$for=isset($control['label-for']) ? " for='{$control['label-for']}'" : '';
			$hint=isset($control['descr']) ? "<p class='text-muted'>{$control['descr']}</p>" : '';
			$part.=<<<HTML
<div class="form-group">
	<label{$for}>{$control['title']}</label>
	{$groups_html[$k]}{$hint}
</div>
HTML;
		}

		#Форматирование непосредственно HTML блоков
		foreach($parts['groups'] as $k=>&$part)
			$part=<<<HTML
<h4>{$groups[$k]}</h4>{$part}
HTML;
		unset($part);
		#/Формирование блоков с переопределением параметров групп

		foreach($parts as &$v)
			$v=join('',$v);
		unset($v);

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete'] ? <<<HTML
<button type="button" onclick="window.location='{$links['delete']}'" class="ibtn ib-delete need-tabindex">
	<i class="ico-del"></i><span class="thd">{$t_lang['delete']}</span>
</button>
HTML
				 : '';
		#/Кнопки

		#Миниаююра
		$avatar=T::$T->Miniature($values['avatar'],null,'avatar',$maxupload,static::$lang['avatar']);

		#Чекбоксы с письмами для отправки
		$letter=[
			'new'=>'',
			'name'=>'',
			'pass'=>"<p class='text-muted'>{$c_lang['password-leave']}</p>",
		];

		if($id)
		{
			$letter['name']=Html::Check('_letter[]',in_array('name',$values['_letter']),['value'=>'name','class'=>'need-tabindex']);
			$letter['name']=<<<HTML
<div class="checkbox change-name">
	<label>
		{$letter['name']}
		{$c_lang['letter-new-name']}
	</label>
</div>
HTML;

			$letter['pass']=Html::Check('_letter[]',in_array('pass',$values['_letter']),['value'=>'pass','class'=>'need-tabindex']);
			$letter['pass']=<<<HTML
<div class="checkbox change-pass">
	<label>
		{$letter['pass']}
		{$c_lang['letter-new-pass']}
	</label>
</div>
HTML;
		}
		else
		{
			$letter['new']=Html::Check('_letter[]', in_array('new', $values['_letter']), ['value'=>'new', 'class'=>'need-tabindex']);
			$letter['new']=<<<HTML
<div class="checkbox-inline">
	<label>
		{$letter['new']}
		{$c_lang['letter-new-account']}
	</label>
</div>
HTML;
		}
		#/Чекбоксы с письмами для отправки

		#Пользователь онлайн
		$online=$values['_online'] ? "<span class='pull-right h3 text-info'>{$c_lang['is-online']}</span>" : '';

		#Локализация
		$tz=Select2::Select('timezone','<option></option>'.Types::TimeZonesOptions($values['timezone']),['id'=>'timezone','class'=>'form-control need-tabindex pim','placeholder'=>$c_lang['server-time']],'{allowClear: true}');

		if($input['language'])
			$input['language']=<<<HTML
<div class="form-group">
	<label id="label-language" for="language">{$c_lang['language']}</label>
	{$input['language']}
</div>
HTML;
		#/Локализация

		#Внешние сервисы
		$socials='';

		if($values['_external_auth'])
		{
			$socials.=<<<HTML
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#socials">{$c_lang['socials']}</p>
						<div id="socials" class="collapse">
							<div class="bcont list-group">
HTML;

			foreach($values['_external_auth'] as $social)
			{
				$soctitle=isset($c_lang[ $social['provider'] ]) ? $c_lang[ $social['provider'] ] : $social['provider'];
				$checked=$social['_delete'] ? ' checked' : '';
				$socials.=<<<HTML
								<a href="{$social['identity']}" target="_blank" class="list-group-item">
									<span class="list-group-item-heading h4">{$soctitle}</span>
									<label class="pull-right checkbox-inline"><input type="checkbox" name="_kill_external[{$social['provider']}][]" value="{$social['provider_uid']}"{$checked} /> {$t_lang['delete']}</label>
								</a>
HTML;
			}

			$socials.=<<<HTML
							</div>
						</div>
					</div>
HTML;
		}
		#/Внешние сервисы

		#Неудачные попытки авторизации
		$failed='';
		if($values['failed_logins'])
		{
			foreach($values['failed_logins'] as $login)
			{
				$login[0]=Eleanor::$Language->Date($login[0],'fdt');
				$failed.=<<<HTML
{$login[0]} &mdash; {$login[1]} &mdash; {$login[3]}
{$login[2]}
---
HTML;
			}

			$checked=$values['_clean_logins'] ? ' checked' : '';
			$failed=Html::Text(false,$failed,['class'=>'form-control','readonly'=>true],2);
			$failed=<<<HTML
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#failed-logins">{$c_lang['failed-login']}</p>
						<div id="failed-logins" class="collapse">
							<div class="bcont">
								{$failed}
								<div class="checkbox"><label><input type="checkbox" name="_clean_logins" value="1"{$checked} /> {$c_lang['clean']}</label></div>
							</div>
						</div>
					</div>
HTML;
		}
		#/Неудачные попытки авторизации

		#Сессии
		$sessions='';
		if($values['login_keys'])
		{
			foreach($values['login_keys'] as $login=>$data)
				foreach($data as $key=>$sess)
				{
					if(isset($sess['_del']))
					{
						$checked=$sess['_del'] ? ' checked' : '';
						$check=<<<HTML
						<label class="pull-right checkbox-inline"><input type="checkbox" name="_kill_session[{$login}][]" value="{$key}"{$checked} /> {$t_lang['delete']}</label>
HTML;
					}
					else
						$check='';

					$s_online=$sess['_online'] ? ' list-group-item-success' : '';
					$sessions.=<<<HTML
<li class="list-group-item{$s_online}">
	<a class="list-group-item-heading h4 getip" href="https://eleanor-cms.ru/whois/{$sess[1]}" target="_blank">{$sess[1]} <span></span></a>
	{$check}
	<p class="list-group-item-text">{$sess[3]}</p>
</li>
HTML;
				}

			$sessions=<<<HTML
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#sessions">{$c_lang['active-sessions']}</p>
						<div id="sessions" class="collapse">
							<ul class="bcont list-group">
								{$sessions}
							</ul>
						</div>
					</div>
<script>$(function(){
	$("a.getip").each(function(){
		$.get("//freegeoip.net/json/"+$(this).attr("href").replace("https://eleanor-cms.ru/whois/","")+"?callback=?",function(data){
			if(data.country_name)
				$("span",this).text(data.country_name);
		}.bind(this),"json");
	});
})</script>
HTML;
		}
		#/Сессии

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$input['name']}{$letter['name']}<br />
						<div class="row">
							<div class="col-xs-6 form-group">
								<label id="label-full_name" for="full_name">{$c_lang['full_name']}</label>
								{$input['full_name']}
							</div>
							<div class="col-xs-6 form-group no-bottom-padding">
								<label id="label-password" for="password">{$c_lang['password']}</label>
								{$input['_password']}{$letter['pass']}
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6 form-group no-bottom-padding">
								<label id="label-email" for="email">E-mail</label>
								{$input['email']}
							</div>
						</div>
					</div>
					{$parts['extra']}
					{$failed}
					{$sessions}
				</div>
				<div id="rightbar">
{$avatar}
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#rights">{$c_lang['rights-on-site']}</p>
						<div id="rights" class="collapse">
							<div class="bcont">
								<div class="form-group">
									<label id="label-group" for="group">{$c_lang['main-group']}</label>
									{$input['_group']}
								</div>
								<div class="form-group">
									<label id="label-groups" for="groups">{$c_lang['other-groups']}</label>
									{$input['groups']}
								</div>
								{$parts['groups']}
							</div>
						</div>
					</div>
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#localization">{$c_lang['localization']}</p>
						<div id="localization" class="collapse">
							<div class="bcont">
								<div class="form-group">
									<label id="label-tz" for="tz">{$c_lang['timezone']}</label>
									{$tz}
								</div>
								{$input['language']}
							</div>
						</div>
					</div>
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#blocking">{$c_lang['blocking']}</p>
						<div id="blocking" class="collapse">
							<div class="bcont">
								<div class="form-group">
									<label id="label-banned-until" for="banned-until">{$c_lang['banned-until']}</label>
									{$input['banned_until']}
								</div>
								<div class="form-group">
									<label id="label-ban-explain" for="ban-explain">{$c_lang['ban-explain']}</label>
									{$input['ban_explain']}
								</div>
							</div>
						</div>
					</div>
					{$socials}
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>
					{$letter['new']}{$delete}{$online}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){ $("#password").passField();
$(".replace:checkbox").click(function(){
	var th=$(this),
		dis=!th.prop("checked")&&!th.prop("disabled");

	th.closest(".input-group").toggleClass("disabled",dis)
		.children(":input,:first").find(":input").addBack().prop("disabled",dis);
}).each(function(){
	$(this).triggerHandler("click");
});

//Преобразование в две колонки для экстра параметров
$("#gender").closest(".form-group").appendTo( $("#gender").closest(".bcont") );//Перенос пола вниз (вверху не смотрится он)
//Не трогаем пол и подпись
$("#extra-0 > .bcont > .form-group:lt(4), div[id^='extra-']:gt(0) > .bcont > .form-group").filter(":odd").each(function(i,v){
	$(this).prev().addBack().addClass("col-xs-6").wrapAll('<div class="row">');
});

//Убираем чрезмерный отступ
$(".row > .col-xs-6:has(p.text-muted)").addClass("no-bottom-padding");

$(".form-group input:not(:checkbox,[class*=select2]),textarea,select").addClass("form-control pim");
BlocksState('management-users-ce'); {$pim} })</script>
HTML;
	}

	/** Страница удаления пользователя
	 * @param array $error Данные удаляемого пользователя
	 *  [string name] Имя пользователя (небезопасный HTML)
	 *  [string full_name] Полное имя пользователя
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($error,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$error['title']),$back);
	}

	/** Обертка для интерфейса настроек
	 * @param string $options Интерфейс настроек
	 * @return string */
	public static function Options($options)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu('options');
		return(string)$options;
	}
}
Users::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/users-*.php',false);

return Users::class;