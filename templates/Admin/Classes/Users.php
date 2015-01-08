<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html, CMS\UserManager;

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
	$("#content input:input,textarea").addClass("form-control");
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
	 *  [string first_page] Ссылка на первую страницу
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
			$filters['ip']=Html::Input('fi[ip]',$filters['ip'],['placeholder'=>static::$lang['filter-by-ip'],
				'class'=>'form-control','id'=>'fi-ip']);
			$filters['name']=T::$T->Author([$filters['user'],$filters['user_id']],
				['placeholder'=>static::$lang['filter-by-user'],'id'=>'fi-user','name'=>'fi[user]']);
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
								<div class="form-group has-feedback author">
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
	 *  [string first_page] Ссылка на первую страницу
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
<a href="{$groups[$g]['_aedit']}"{$style}>{$groups[$g]['title']}</a>,
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
					rtrim($grs,', '),
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

		$filters['id']=Html::Input('fi[id]',$filters['id'],['placeholder'=>static::$lang['filter-by-id'],'type'=>'number',
			'class'=>'form-control','id'=>'fi-name','min'=>0]);
		$filters['name']=Html::Input('fi[name]',$filters['name'],['placeholder'=>static::$lang['filter-by-name'],
			'class'=>'form-control','id'=>'fi-name']);
		$filters['full_name']=Html::Input('fi[full_name]',$filters['full_name'],['placeholder'=>static::$lang['filter-by-name'],
			'class'=>'form-control','id'=>'fi-full-name']);
		$filters['group']=Html::Select('fi[group]',UserManager::GroupsOpts($filters['full_name']),['class'=>'form-control','id'=>'fi-group']);
		$filters['last_visit_from']=T::$T->DatePicker('fi[last_visit_from]',$filters['last_visit_from'],true,['placeholder'=>static::$lang['from'],
			'class'=>'form-control','id'=>'fi-last-visit-from'],false);
		$filters['last_visit_to']=T::$T->DatePicker('fi[last_visit_to]',$filters['last_visit_to'],true,['placeholder'=>static::$lang['to'],
			'class'=>'form-control','id'=>'fi-last-visit-to'],false);
		$filters['register_from']=T::$T->DatePicker('fi[register_from]',$filters['register_from'],true,['placeholder'=>static::$lang['from'],
			'class'=>'form-control','id'=>'fi-register-from'],false);
		$filters['register_to']=T::$T->DatePicker('fi[register_to]',$filters['register_to'],true,['placeholder'=>static::$lang['to'],
			'class'=>'form-control','id'=>'fi-register-to'],false);
		$filters['ip']=Html::Input('fi[ip]',$filters['ip'],['placeholder'=>static::$lang['filter-by-ip'],
			'class'=>'form-control','id'=>'fi-ip']);
		$filters['email']=Html::Input('fi[email]',$filters['email'],['placeholder'=>static::$lang['filter-by-email'],
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

	/*
		Страница добавления/редактирования пользователя
		$id - идентификатор редактируемого пользователя, если $id==0 значит пользователь добавляется
		$values - массив значений полей для правки. Ключи:
			name - имя пользователя
			full_name - полное имя пользователя
			_slname - только при редактировании, флаг отправки письма пользователю о новом имени
			pass - поле для изменения пароля
			pass2 - повтор пароля для его изменения
			_slpass - только при редактировании, флаг отправки письма пользователю о новом пароле
			_slnew - только при добавлении: флаг отправки письма пользователю о создании ему аккаунта на сайте
			email - e-mail пользователя
			_group - основная группа пользователя
			groups - массив дополнительных групп пользователя
			language - язык пользователя
			timezone - часовая зона пользователя
			_atype - тип аватара пользователя: загруженный или локальный (из галереи)
			avatar - расположение аватара
			banned_until - дата снятия бана
			ban_explain - описание причин бана
			_overskip - данные для перезагрузки параметров разрешений групп
			_externalauth - массив внешних авторизаций. Ключи внутреннего массива:
				provider - идентификатор внешнего сервиса
				provider_uid - идентификатор пользователя на внешнем сервисе
				identity - ссылка на пользователя на внешнем сервисе
			_sessions - массив открытых сессий пользователя, формат: LoginClass=>Ключ=>array(), ключи внутреннего массива:
				0 - TIMESTAMP истечения активности
				1 - IP адрес
				2 - USER AGENT браузера
				_candel - флаг возможности удаления сессии

			не для правки, для информации:
			failed_logins - массив массивов неудачных попыток авторизации. Ключи внутренних массивов:
				0 - дата попытки
				1 - сервис
				2 - браузер
				3 - IP
		$overload - перечень контролов для перезагрузки разрешений групп в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$ovv - результирующий HTML код контролов перезагрузки разрешений групп, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $overload
		$upavatar - результирующий HTML код для загрузки аватара
		$extra - перечень контролов дополнительных полей пользователя. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$exv - результирующий HTML код дополнительных полей пользователя, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $overload
		$bypost - признак того, что данные нужно брать из POST запроса
		$errors - массив ошибок
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление пользователя или false
	*/
	public static function CreateEdit($id,$values,$Editor,$Uploader,$errors,$back,$draft,$links,$maxupload)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		$c_lang=static::$lang;
		$t_lang=T::$lang;

		static::Menu($id ? 'edit' : 'create');

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['title'][$lng]=Html::Input('title['.$lng.']',$values['title'][$lng],
					['class'=>'form-control need-tabindex input-lg pim','id'=>'title-'.$lng,'placeholder'=>static::$lang['title-placeholder']]);
				$input['uri'][$lng]=Html::Input('uri['.$lng.']',$values['uri'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'uri-'.$lng]);
				$input['text'][$lng]=$Editor('text['.$lng.']',$values['text'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'text-'.$lng,'rows'=>20]);
				$input['document_title'][$lng]=Html::Input('document_title['.$lng.']',$values['document_title'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'docuemnt-title-'.$lng]);
				$input['meta_descr'][$lng]=Html::Input('meta_descr['.$lng.']',$values['meta_descr'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'meta-descr-'.$lng]);
			}

			$input['title']=T::$T->LangEdit($input['title'],'title');
			$input['uri']=T::$T->LangEdit($input['uri'],'uri');
			$input['text']=T::$T->LangEdit($input['text'],'text');
			$input['document_title']=T::$T->LangEdit($input['document_title'],'document-title');
			$input['meta_descr']=T::$T->LangEdit($input['meta_descr'],'meta-descr');
			$input['language']=T::$T->LangChecks($values['single-lang'],$values['language']);

			$loglang='';
			foreach(Eleanor::$langs as $k=>$v)
				$loglang.=Html::Option($v['name'],$k,$k==$values['log_language']);
			$loglang=Html::Select('log_language',$loglang,['class'=>'form-control need-tabindex']);

			$input['language']=<<<HTML
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b2">{$t_lang['languages']}</p>
							<div id="b2" class="collapse in">
								<div class="bcont">
									<div class="form-group">
										{$input['language']}
									</div>
									<div class="form-group">
										<label for="log-langauge">{$c_lang['log_language']}</label>
										{$loglang}
									</div>
								</div>
							</div>
						</div>
HTML;

		}
		else
			$input=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control need-tabindex input-lg pim','placeholder'=>static::$lang['title-placeholder']]),
				'uri'=>Html::Input('uri',$values['uri'],['id'=>'uri','class'=>'form-control need-tabindex pim']),
				'text'=>$Editor('text',$values['text'],['class'=>'form-control need-tabindex pim','id'=>'text','rows'=>20]),
				'document_title'=>Html::Input('document_title',$values['document_title'],['class'=>'form-control need-tabindex pim','id'=>'document-title']),
				'meta_descr'=>Html::Input('meta_descr',$values['meta_descr'],['class'=>'form-control need-tabindex pim','id'=>'meta-descr']),
				'language'=>''
			];

		$input+=[
			'http_code'=>Html::Input('http_code',$values['http_code'],['id'=>'http-code','class'=>'form-control need-tabindex pim task','type'=>'number']),
			'email'=>Html::Input('email',$values['email'],['id'=>'email','class'=>'form-control need-tabindex pim','type'=>'email']),
			'log'=>Html::Check('log',$values['log'],['class'=>'need-tabindex']),
		];
		$uri=T::$T->Uri();

		#Pim поля, которые сабмитятся только если изменились
		$pim=$draft || $errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Errors
		$er_title=$er_text=$er_def='';

		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error))
			{
				$type=$error;
				if(isset(static::$lang[$error]))
					$error=static::$lang[$error];
			}

			$error=T::$T->Alert($error,'danger',true);;

			if(strpos($type,'EMPTY_TITLE')===0)
				$er_title=$error;
			elseif(strpos($type,'EMPTY_TEXT')===0)
				$er_text=$error;
			else
				$er_def=$error;
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		if($draft)
			$er_def.=T::$T->Alert(sprintf(static::$lang['delete-draft%'],$links['delete-draft']),'info',true);

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete'] ? '<button type="button" onclick="window.location=\''.$links['delete']
			.'\'" class="ibtn ib-delete need-tabindex"><i class="ico-del"></i><span class="thd">'
			.T::$lang['delete'].'</span></button>' : '';

		$draft=T::$T->DraftButton($links['draft'],null)
			.Html::Input('_draft',$id,['type'=>'hidden']);
		#/Кнопки

		#Миниаююра
		$image=T::$T->Miniature($values['miniature'],null,null,$maxupload);

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$er_title}
						{$input['title']}
						<br />
						<div class="form-group">
							<label id="label-text" for="text">{$c_lang['text']}</label>
							{$er_text}
							{$input['text']}
						</div>
					</div>
					{$Uploader}
				</div>
				<div id="rightbar">
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#seo">SEO</p>
						<div id="seo" class="collapse in">
							<div class="bcont">
								<div class="form-group">
									<label id="label-uri" for="uri">URI</label>
									{$input['uri']}
								</div>
								<div class="form-group">
									<label id="label-document-title" for="document-title">Document title</label>
									{$input['document_title']}
								</div>
								<div class="form-group">
									<label id="label-meta-descr" for="meta-descr">Meta description</label>
									{$input['meta_descr']}
								</div>
								<div class="form-group">
									<label for="http-code">{$c_lang['http-code']}</label>
									{$input['http_code']}
								</div>
							</div>
						</div>
					</div>
{$image}
{$input['language']}
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#notify">{$c_lang['notification']}</p>
						<div id="notify" class="collapse">
							<div class="bcont">
								<div class="checkbox"><label>{$input['log']} {$c_lang['enable_log']}</label></div>
								<div class="form-group">
									<label for="email">{$c_lang['email']}</label>
									{$input['email']}
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>
					{$draft}{$delete}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){ {$pim}{$uri} })</script>
HTML;
	}
	/*public static function AddEditUser($id,$values,$overload,$ovv,$upavatar,$extra,$exv,$bypost,$errors,$back,$links)
	{
		static::Menu($id ? '' : 'add');
		#Весь JS вынесен в отдельный файл, потому что его слишком много, чтобы писать здесь
		$GLOBALS['scripts'][]='js/admin_users_ae.js';

		$lang=Eleanor::$Language['users'];
		$ltpl=T::$lang;

		$langs=Eleanor::Option($lang['by_default'],'',!$values['language']);
		foreach(Eleanor::$langs as $k=>&$v)
			$langs.=Eleanor::Option($v['name'],$k,$k==$values['language']);

		list($awidth,$aheight)=explode(' ',Eleanor::$vars['avatar_size']);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->head(static::$lang['lap'])
			->item(static::$lang['name'],Eleanor::Input('name',$values['name'],['id'=>'name','tabindex'=>1]))
			->item(static::$lang['fullname'],Eleanor::Input('full_name',$values['full_name'],['id'=>'full-name','tabindex'=>2]));

		if($id)
			$Lst->item([static::$lang['slname'],Eleanor::Check('_slname',$values['_slname'],['tabindex'=>3,'id'=>'slname']),'tip'=>static::$lang['slname_']]);

		$Lst->item([static::$lang['pass'],Eleanor::Input('pass',$values['pass'],['type'=>'password','id'=>'pass','tabindex'=>4]),'tip'=>static::$lang['pass_']])
			->item(static::$lang['passc'],Eleanor::Input('pass2',$values['pass2'],['type'=>'password','id'=>'pass2','tabindex'=>5]));

		if($id)
			$Lst->item([static::$lang['slpass'],Eleanor::Check('_slpass',$values['_slpass'],['tabindex'=>6,'id'=>'slpass']),'tip'=>static::$lang['slpass_']]);
		else
			$Lst->item([static::$lang['slnew'],Eleanor::Check('_slnew',$values['_slnew'],['tabindex'=>6]),'tip'=>static::$lang['slnew_']]);

		$Lst->head(static::$lang['account'])
			->item('E-mail',Eleanor::Input('email',$values['email'],['tabindex'=>7]))
			->item(static::$lang['group'],Eleanor::Select('_group',UserManager::GroupsOpts($values['_group']),['tabindex'=>8]))
			->item(static::$lang['agroups'],Eleanor::Items('groups',UserManager::GroupsOpts($values['groups']),['tabindex'=>9]))
			->item(static::$lang['lang'],Eleanor::Select('language',$langs,['tabindex'=>10]))
			->item(static::$lang['timezone'],Eleanor::Select('timezone',Eleanor::Option($lang['by_default'],'',!$values['timezone']).Types::TimeZonesOptions($values['timezone']),['tabindex'=>11]))
			->head(static::$lang['avatar'])
			->item(
				static::$lang['alocation'],
				Eleanor::Select(
					'_atype',
					Eleanor::Option(static::$lang['agallery'],'gallery',!$values['_aupload'])
					.Eleanor::Option(static::$lang['apersonal'],'upload',$values['_aupload']),
					['id'=>'atype','tabindex'=>14]
				)
			)
			->item(
				static::$lang['amanage'],
				Eleanor::Input('avatar',$values['avatar'],['id'=>'avatar-input','type'=>'hidden'])
				.'<div id="avatar-local">
					<div id="avatar-select"></div>
					<div id="avatar-view">
						<a class="imagebtn getgalleries" href="#">'.static::$lang['gallery_select'].'</a><div class="clr"></div>
						<span id="avatar-no" style="width:'.($awidth ? $awidth : '180').'px;height:'.($aheight ? $aheight : '145').'px;text-decoration:none;max-height:100%;max-width:100%;" class="screenblock">
							<b>'.static::$lang['noavatar'].'</b><br />
							<span>'.sprintf('<b>%s</b> <small>x</small> <b>%s</b> <small>px</small>',$awidth ? $awidth : '&infin;',$aheight ? $aheight : '&infin;').'</span>
						</span>
						<img id="avatar-image" style="border:1px solid #c9c7c3;max-width:'.($awidth>0 ? $awidth.'px' : '100%').';max-height:'.($aheight>0 ? $aheight.'px' : '100%').'" src="images/spacer.png" /><div class="clr"></div>
						<a id="avatar-delete" class="imagebtn" href="#">'.$ltpl['delete'].'</a>
					</div>
				</div>
				<div id="avatar-upload">'.$upavatar.'</div>
<script>$(function(){AddEditUser('.($id ? $id : 'false').')})//]]></script>')
			->end();

		$general=(string)$Lst;

		$block=(string)$Lst->begin()
			->item(static::$lang['ban-to'],Dates::Calendar('banned_until',$values['banned_until'],true))
			->item(static::$lang['ban-exp'],$GLOBALS['Eleanor']->Editor->Area('ban_explain',$values['ban_explain'],['post'=>$bypost]))
			->end();

		$Lst->begin();
		foreach($extra as $k=>&$v)
			if($v)
				if(is_array($v) and !empty($values[$k]))
					$Lst->item([$v['title'],Eleanor::$Template->LangEdit($exv[$k],null),'tip'=>$v['descr']]);
				elseif(is_string($v))
					$Lst->head($v);
		$extra=(string)$Lst->end();

		$Lst->begin();
		foreach($overload as $k=>&$v)
			if(is_array($v))
			{
				$inherited=!isset($values['_overskip'][$k]) || $values['_overskip'][$k]=='inherit';
				$Lst->item([
					$v['title'],
					'<div class="overload"'.($inherited ? ' style="display:none"' : '').'>'.Eleanor::$Template->LangEdit($ovv[$k],null).'</div><div class="inherit"'.($inherited ? '' : ' style="display:none"').'>---<div class="clr"></div></div>',
					'tip'=>$v['descr'],
					'descr'=>Eleanor::Select(
						'_overskip['.$k.']',
						Eleanor::Option(static::$lang['inherit'],'inherit',$inherited)
						.Eleanor::Option(static::$lang['replace'],'replace',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='replace')
						.Eleanor::Option(static::$lang['addo'],'add',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='add'),
						['style'=>'width:100px']
					),
				]);
			}
			else
				$Lst->head($v);
		$special=(string)$Lst->end();

		if($id)
		{
			$fla=$axauth='';
			foreach($values['failed_logins'] as &$v)
				$fla.='Date: '.Eleanor::$Language->Date($v[0])."\nService: ".$v[1]."\nBrowser: ".$v[2]."\nIP: ".$v[3]."\n\n";
			foreach($values['_externalauth'] as &$v)
				$axauth.='<span><a href="'.$v['identity'].'" target="_blank" class="exl">'.(isset(static::$lang[$v['provider']]) ? static::$lang[$v['provider']] : $v['provider']).'</a><a href="#" onclick="return data-provider="'.$v['provider'].'" data-providerid="'.$v['provider_uid'].'" title="'.$ltpl['delete'].'">X</a></span> ';

			$Lst->begin()
				->item(static::$lang['fla'],Eleanor::Text('',$fla,['readonly'=>'readonly','style'=>'width:95%']).'<br /><label>'.Eleanor::Check('_cleanfla',$values['_cleanfla']).' '.static::$lang['clean'].'</label>')
				->item(static::$lang['register'],Eleanor::$Language->Date($values['register'],'fdt'))
				->item(static::$lang['last_visit'],Eleanor::$Language->Date($values['last_visit'],'fdt'));
			if($axauth)
				$Lst->item(static::$lang['externals'],$axauth);

			if($values['_sessions'])
			{
				$images=Eleanor::$Template->default['theme'].'images/';
				$bicons=[
					'opera'=>['images/browsers/opera.png','Opera'],
					'firefox'=>['images/browsers/firefox.png','Mozilla Firefox'],
					'chrome'=>['images/browsers/chrome.png','Google Chrome'],
					'safari'=>['images/browsers/safari.png','Apple Safari'],
					'msie'=>['images/browsers/ie.png','Microsoft Internet Explore'],
				];

				$Ls=Eleanor::LoadListTemplate('table-list',4)
					->begin(
						['Browser &amp; IP','colspan'=>2,'tableextra'=>['id'=>'sessions']],
						static::$lang['datee'],
						[$ltpl['delete'],70]
					);

				foreach($values['_sessions'] as $cl=>&$sess)
				{
					$uses='';
					foreach(Eleanor::$services as $kk=>&$vv)
						if('Login'.ucfirst($vv['login'])==$cl)
							$uses.=$kk.', ';

					$Ls->empty($cl.' ('.rtrim($uses,', ').')');
					foreach($sess as $k=>&$v)
					{
						$icon=$iconh=false;
						foreach($bicons as $br=>$brv)
							if(stripos($v[2],$br)!==false)
							{
								$icon=$brv[0];
								$iconh=$brv[1];
								break;
							}

						$ua=htmlspecialchars($v[2],ELENT,CHARSET);
						if($v['_candel'])
						{
							$del=$Ls('func',
								['#',$ltpl['delete'],$images.'delete.png','extra'=>['data-key'=>$k,'data-cl'=>$cl]]
							);
							$del[1]='center';
						}
						else
							$del=['<b title="'.static::$lang['csnd'].'">&mdash;</b>','center'];

						$Ls->item(
							$icon ? ['<a href="#" data-ua="'.$ua.'"><img title="'.$iconh.'" src="'.$icon.'" /></a>','style'=>'width:16px'] : ['<a href="#" data-ua="'.$ua.'">?</a>','center'],
							[$v[1],'center','href'=>'http://eleanor-cms.ru/whois/'.$v[1],'hrefextra'=>['target'=>'_blank']],
							[Eleanor::$Language->Date($v[0],'fdt'),'center'],
							$del
						);
					}
				}

				$stats=(string)$Lst->head(static::$lang['sessions'])->end().$Ls->end().'<script>//<![CDATA[
$(function(){
	$("#sessions").on("click","a[data-key]",function(){
		var th=$(this);
		CORE.Ajax({
				direct:"admin",
				file:"users",
				event:"killsession",
				key:th.data("key"),
				cl:th.data("cl"),
				uid:"'.$id.'"
			},
			function()
			{
				th.closest("tr").remove();
			}
		);
		return false;
	}).on("click","a[data-ua]",function(){
		alert($(this).data("ua"));
		return false;
	});
});//]]></script>';
			}
			else
				$stats=(string)$Lst->end();
		}

		if($back)
			$back=Eleanor::Input('back',$back,['type'=>'hidden']);

		$Lst->form(['id'=>'form','data-pmm'=>static::$lang['PASSWORD_MISMATCH']])
			->tabs(
				[static::$lang['general'],$general],
				[static::$lang['extra'],$extra],
				[static::$lang['special'],$special],
				[static::$lang['block'],$block],
				$id ? [static::$lang['statistics'],$stats] : false
			)
			->submitline($back.Eleanor::Button($id ? static::$lang['save'] : static::$lang['add']).($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',['onclick'=>'window.location=\''.$links['delete'].'\'']) : ''))
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors,'error');
	}*/

	/*
		Элемент шаблона: загрузка галерей
		$galleries - массив галерей, каждый элемент массива - массив с ключами:
			n - имя галереи
			i - путь к картинке относительно корня сайта
			d - описание галереи
	*/
	public static function Galleries($galleries)
	{
		$c='';
		foreach($galleries as &$v)
			$c.='<a href="#" class="gallery" data-gallery="'.$v['n'].'"><b><img src="'.$v['i'].'" alt="" /><span>'.$v['d'].'</span></b></a>';
		return$c ? '<a class="imagebtn cancelavatar" href="#">'.static::$lang['cancel_avatar'].'</a><div class="clr"></div><div class="galleryavatars">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.static::$lang['no_avatars'].'</div>';
	}

	/*
		Элемент шаблона: загрузка аватаров
		$avatar - массив аватаров, каждый элемент массива - массив с ключами:
			p - путь к файлу, относительно корня сайта, с закрывающим слешем
			f - имя файла
	*/
	public static function Avatars($avatars)
	{
		$c='';
		foreach($avatars as &$v)
			$c.='<a href="#" class="applyavatar" title="'.$v['f'].'"><img src="'.join($v).'" /></a>';
		return$c ? '<a class="imagebtn getgalleries" href="#">'.static::$lang['togals'].'</a><a class="imagebtn cancelavatar" href="#">'.static::$lang['cancel_avatar'].'</a><div class="clr"></div><div class="avatarscover">'.$c.'</div>' : '<div class="noavatars cancelavatar">'.static::$lang['no_avatars'].'</div>';
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