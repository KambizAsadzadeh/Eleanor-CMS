<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

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

	/** Список пользователей
		$items - массив пользователей. Формат: ID=>array(), ключи внутреннего массива:
			name - имя пользователя (не безопасных HTML)
			full_name - полное имя пользователя
			email - e-mail пользователя
			groups - массив групп пользователя
			ip - IP адрес пользователя
			last_visit - дата последнего визита пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя либо false
		$groups - массив групп пользователей. Формат: ID=>array(), ключи внутреннего массива:
			title - название группы
			style - стиль группы
		$cnt - количество пользователей всего
		$pp - количество пользователей на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_name - ссылка на сортировку списка $items по имени пользователя (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_email - ссылка на сортировку списка $items по email (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_group - ссылка на сортировку списка $items по группе (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_visit - ссылка на сортировку списка $items по последнему визиту (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_ip - ссылка на сортировку списка $items по ip (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внутри которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$groups,$cnt,$pp,$query,$page,$links)
	{
		static::Menu('list');
		$ltpl=T::$lang;
		$GLOBALS['scripts'][]='js/checkboxes.js';

		$query+=[''=>[]];
		$query['']+=['fi'=>[]];
		$fs=(bool)$query['']['fi'];
		$query['']['fi']+=[
			'name'=>false,
			'namet'=>false,
			'sname'=>false,
			'snamet'=>false,
			'group'=>false,
			'lvto'=>false,
			'lvfrom'=>false,
			'regto'=>false,
			'regfrom'=>false,
			'ip'=>false,
			'email'=>false,
			'id'=>false,
		];

		$Lst=Eleanor::LoadListTemplate('table-list',7)
			->begin(
				[static::$lang['name'],'sort'=>$query['sort']=='name' ? $query['so'] : false,'href'=>$links['sort_name']],
				['E-mail','sort'=>$query['sort']=='email' ? $query['so'] : false,'href'=>$links['sort_email']],
				[static::$lang['group'],'sort'=>$query['sort']=='groups' ? $query['so'] : false,'href'=>$links['sort_group']],
				[static::$lang['last_visit'],'sort'=>$query['sort']=='last_visit' ? $query['so'] : false,'href'=>$links['sort_visit']],
				['IP','sort'=>$query['sort']=='ip' ? $query['so'] : false,'href'=>$links['sort_ip']],
				[$ltpl['functs'],'sort'=>$query['sort']=='id' ? $query['so'] : false,80,'href'=>$links['sort_id']],
				[Eleanor::Check('mass',false,['id'=>'mass-check']),20]
			);

		if($items)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as &$v)
			{
				$grs='';
				foreach($v['groups'] as &$gv)
					if(isset($groups[$gv]))
						$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['style'].$groups[$gv]['title'].'</a>, ';
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
					[$v['email'],'center'],
					rtrim($grs,' ,'),
					[Eleanor::$Language->Date($v['last_visit'],'fdt'),'center'],
					[$v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'hrefextra'=>['target'=>'_blank']],
					$Lst('func',
						[$v['_aedit'],$ltpl['edit'],$images.'edit.png'],
						$v['_adel'] ? [$v['_adel'],$ltpl['delete'],$images.'delete.png'] : false
					),
					Eleanor::Check('mass[]',false,['value'=>$v['id']])
				);
			}
		}
		else
			$Lst->empty(static::$lang['unf']);

		$fisnamet=$finamet='';
		$namet=[
			'b'=>static::$lang['begins'],
			'q'=>static::$lang['match'],
			'e'=>static::$lang['endings'],
			'm'=>static::$lang['contains'],
		];
		foreach($namet as $k=>&$v)
			$finamet.=Eleanor::Option($v,$k,$query['']['fi']['namet']==$k);
		foreach($namet as $k=>&$v)
			$fisnamet.=Eleanor::Option($v,$k,$query['']['fi']['snamet']==$k);

		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.static::$lang['name'].'</b><br />'.Eleanor::Select('fi[namet]',$finamet,['style'=>'width:30%']).Eleanor::Input('fi[name]',$query['']['fi']['name'],['style'=>'width:68%']).'</td>
					<td><b>'.static::$lang['fullname'].'</b><br />'.Eleanor::Select('fi[snamet]',$finamet,['style'=>'width:30%']).Eleanor::Input('fi[sname]',$query['']['fi']['sname'],['style'=>'width:68%']).'</td>
				</tr>
				<tr>
					<td><b>IDs</b><br />'.Eleanor::Input('fi[id]',$query['']['fi']['id']).'</td>
					<td><b>'.static::$lang['group'].'</b><br />'.Eleanor::Select('fi[group]',Eleanor::Option(static::$lang['not_imp'],0).UserManager::GroupsOpts($query['']['fi']['group'])).'</td>
				</tr>
				<tr>
					<td><b>'.static::$lang['last_visit'].'</b> '.static::$lang['from-to'].'<br />'.Dates::Calendar('fi[lvfrom]',$query['']['fi']['lvfrom'],true,['style'=>'width:35%']).' - '.Dates::Calendar('fi[lvto]',$query['']['fi']['lvto'],true,['style'=>'width:35%']).'</td>
					<td><b>'.static::$lang['register'].'</b> '.static::$lang['from-to'].'<br />'.Dates::Calendar('fi[regfrom]',$query['']['fi']['regfrom'],true,['style'=>'width:35%']).' - '.Dates::Calendar('fi[regto]',$query['']['fi']['regto'],true,['style'=>'width:35%']).'</td>
				</tr>
				<tr>
					<td><b>E-mail</b><br />'.Eleanor::Input('fi[email]',$query['']['fi']['email']).'</td>
					<td><b>IP</b><br />'.Eleanor::Input('fi[ip]',$query['']['fi']['ip']).'</td>
				</tr>
				<tr>
					<td style="text-align:center;vertical-align:middle" colspan="2">'.Eleanor::Button($ltpl['apply']).'</td>
				</tr>
			</table>
<script>//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
});//]]></script>
		</form>
		<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['upp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'d')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,[$links['pages'],$links['first_page']]));
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
			avatar_location - расположение аватара
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
	public static function AddEditUser($id,$values,$overload,$ovv,$upavatar,$extra,$exv,$bypost,$errors,$back,$links)
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
			->item(static::$lang['name'],Eleanor::Input('name',$values['name'],array('id'=>'name','tabindex'=>1)))
			->item(static::$lang['fullname'],Eleanor::Input('full_name',$values['full_name'],array('id'=>'full-name','tabindex'=>2)));

		if($id)
			$Lst->item(array(static::$lang['slname'],Eleanor::Check('_slname',$values['_slname'],array('tabindex'=>3,'id'=>'slname')),'tip'=>static::$lang['slname_']));

		$Lst->item(array(static::$lang['pass'],Eleanor::Input('pass',$values['pass'],array('type'=>'password','id'=>'pass','tabindex'=>4)),'tip'=>static::$lang['pass_']))
			->item(static::$lang['passc'],Eleanor::Input('pass2',$values['pass2'],array('type'=>'password','id'=>'pass2','tabindex'=>5)));

		if($id)
			$Lst->item(array(static::$lang['slpass'],Eleanor::Check('_slpass',$values['_slpass'],array('tabindex'=>6,'id'=>'slpass')),'tip'=>static::$lang['slpass_']));
		else
			$Lst->item(array(static::$lang['slnew'],Eleanor::Check('_slnew',$values['_slnew'],array('tabindex'=>6)),'tip'=>static::$lang['slnew_']));

		$Lst->head(static::$lang['account'])
			->item('E-mail',Eleanor::Input('email',$values['email'],array('tabindex'=>7)))
			->item(static::$lang['group'],Eleanor::Select('_group',UserManager::GroupsOpts($values['_group']),array('tabindex'=>8)))
			->item(static::$lang['agroups'],Eleanor::Items('groups',UserManager::GroupsOpts($values['groups']),array('tabindex'=>9)))
			->item(static::$lang['lang'],Eleanor::Select('language',$langs,array('tabindex'=>10)))
			->item(static::$lang['timezone'],Eleanor::Select('timezone',Eleanor::Option($lang['by_default'],'',!$values['timezone']).Types::TimeZonesOptions($values['timezone']),array('tabindex'=>11)))
			->head(static::$lang['avatar'])
			->item(
				static::$lang['alocation'],
				Eleanor::Select(
					'_atype',
					Eleanor::Option(static::$lang['agallery'],'gallery',!$values['_aupload'])
					.Eleanor::Option(static::$lang['apersonal'],'upload',$values['_aupload']),
					array('id'=>'atype','tabindex'=>14)
				)
			)
			->item(
				static::$lang['amanage'],
				Eleanor::Input('avatar_location',$values['avatar_location'],array('id'=>'avatar-input','type'=>'hidden'))
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
<script>/*<![CDATA[*/$(function(){AddEditUser('.($id ? $id : 'false').')})//]]></script>')
			->end();

		$general=(string)$Lst;

		$block=(string)$Lst->begin()
			->item(static::$lang['ban-to'],Dates::Calendar('banned_until',$values['banned_until'],true))
			->item(static::$lang['ban-exp'],$GLOBALS['Eleanor']->Editor->Area('ban_explain',$values['ban_explain'],array('post'=>$bypost)))
			->end();

		$Lst->begin();
		foreach($extra as $k=>&$v)
			if($v)
				if(is_array($v) and !empty($values[$k]))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($exv[$k],null),'tip'=>$v['descr']));
				elseif(is_string($v))
					$Lst->head($v);
		$extra=(string)$Lst->end();

		$Lst->begin();
		foreach($overload as $k=>&$v)
			if(is_array($v))
			{
				$inherited=!isset($values['_overskip'][$k]) || $values['_overskip'][$k]=='inherit';
				$Lst->item(array(
					$v['title'],
					'<div class="overload"'.($inherited ? ' style="display:none"' : '').'>'.Eleanor::$Template->LangEdit($ovv[$k],null).'</div><div class="inherit"'.($inherited ? '' : ' style="display:none"').'>---<div class="clr"></div></div>',
					'tip'=>$v['descr'],
					'descr'=>Eleanor::Select(
						'_overskip['.$k.']',
						Eleanor::Option(static::$lang['inherit'],'inherit',$inherited)
						.Eleanor::Option(static::$lang['replace'],'replace',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='replace')
						.Eleanor::Option(static::$lang['addo'],'add',isset($values['_overskip'][$k]) and $values['_overskip'][$k]=='add'),
						array('style'=>'width:100px')
					),
				));
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
				->item(static::$lang['fla'],Eleanor::Text('',$fla,array('readonly'=>'readonly','style'=>'width:95%')).'<br /><label>'.Eleanor::Check('_cleanfla',$values['_cleanfla']).' '.static::$lang['clean'].'</label>')
				->item(static::$lang['register'],Eleanor::$Language->Date($values['register'],'fdt'))
				->item(static::$lang['last_visit'],Eleanor::$Language->Date($values['last_visit'],'fdt'));
			if($axauth)
				$Lst->item(static::$lang['externals'],$axauth);

			if($values['_sessions'])
			{
				$images=Eleanor::$Template->default['theme'].'images/';
				$bicons=array(
					'opera'=>array('images/browsers/opera.png','Opera'),
					'firefox'=>array('images/browsers/firefox.png','Mozilla Firefox'),
					'chrome'=>array('images/browsers/chrome.png','Google Chrome'),
					'safari'=>array('images/browsers/safari.png','Apple Safari'),
					'msie'=>array('images/browsers/ie.png','Microsoft Internet Explore'),
				);

				$Ls=Eleanor::LoadListTemplate('table-list',4)
					->begin(
						array('Browser &amp; IP','colspan'=>2,'tableextra'=>array('id'=>'sessions')),
						static::$lang['datee'],
						array($ltpl['delete'],70)
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
								array('#',$ltpl['delete'],$images.'delete.png','extra'=>array('data-key'=>$k,'data-cl'=>$cl))
							);
							$del[1]='center';
						}
						else
							$del=array('<b title="'.static::$lang['csnd'].'">&mdash;</b>','center');

						$Ls->item(
							$icon ? array('<a href="#" data-ua="'.$ua.'"><img title="'.$iconh.'" src="'.$icon.'" /></a>','style'=>'width:16px') : array('<a href="#" data-ua="'.$ua.'">?</a>','center'),
							array($v[1],'center','href'=>'http://eleanor-cms.ru/whois/'.$v[1],'hrefextra'=>array('target'=>'_blank')),
							array(Eleanor::$Language->Date($v[0],'fdt'),'center'),
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
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->form(array('id'=>'form','data-pmm'=>static::$lang['PASSWORD_MISMATCH']))
			->tabs(
				array(static::$lang['general'],$general),
				array(static::$lang['extra'],$extra),
				array(static::$lang['special'],$special),
				array(static::$lang['block'],$block),
				$id ? array(static::$lang['statistics'],$stats) : false
			)
			->submitline($back.Eleanor::Button($id ? static::$lang['save'] : static::$lang['add']).($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors,'error');
	}

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

	/*
		Шаблон страницы просмотра пользователей онлайн
		$items - массив сессий на сайте. Ключи внутренних массивов:
			type - тип сессии: user, guest или bot (пользователь, гость или поисковый бот) в зависимости от того, кто работает
			user_id - ID пользователя
			enter - дата входа
			expire - дата истечения сессии
			_online - признак пользователя онлайн
			ip_guest - IP адрес для гостя и бота
			ip_user - IP адрес для пользователя
			service - идентификатор сервиса
			browser - USER AGENT устройства пользователя
			location - местоположение пользователя
			botname - имя для бота
			groups - массив групп пользователя
			name - имя для пользователя (не безопасный HTML)
			full_name - полное имя для пользователя
			_aedit - ссылка на редактирование пользователя
			_adel - ссылка на удаление пользователя либо false
		$groups - массив групп для пользователей. Формат: ID=>array(), ключи внутреннего массива:
			title - название группы
			style - стиль группы
		$cnt - количество сессий всего
		$pp - количество сессий на страницу
		$qs - массив параметров для адресной строки
		$links - перечень необходимых ссылок, массив с ключами:
			sort_ip - ссылка на сортировку списка $items по ip (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_enter - ссылка на сортировку списка $items по дате входа (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_location - ссылка на сортировку списка $items по местоположению (возрастанию/убыванию в зависимости от текущей сортировки)
			pp - фукнция-генератор ссылок на изменение количества пользователей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function UsersOnline($items,$groups,$notempty,$cnt,$pp,$query,$page,$links)
	{
		static::Menu('online');
		$ltpl=T::$lang;
		$sess=[
			static::$lang['awo'],
			static::$lang['alls'],
			static::$lang['allg']
		];

		$query+=[''=>[]];
		$query['']+=['fi'=>[]];
		$fs=(bool)$query['']['fi'];
		$query['']['fi']+=[
			'online'=>false,
		];

		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->begin(
				[static::$lang['who'],'colspan'=>2,'tableextra'=>['id'=>'onlinelist']],
				['IP','sort'=>$query['sort']=='ip' ? $query['so'] : false,'href'=>$links['sort_ip']],
				[static::$lang['ets'],'sort'=>$query['sort']=='enter' ? $query['so'] : false,'href'=>$links['sort_enter']],
				[static::$lang['pl'],'sort'=>$query['sort']=='location' ? $query['so'] : false,'href'=>$links['sort_location']],
				[$ltpl['functs'],80]
			);

		if($items)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			$bicons=[
				'opera'=>['images/browsers/opera.png','Opera'],
				'firefox'=>['images/browsers/firefox.png','Mozilla Firefox'],
				'chrome'=>['images/browsers/chrome.png','Google Chrome'],
				'safari'=>['images/browsers/safari.png','Apple Safari'],
				'msie'=>['images/browsers/ie.png','Microsoft Internet Explore'],
			];

			foreach($items as &$v)
			{
				$user=$icon=$iconh=false;
				foreach($bicons as $br=>$brv)
					if(stripos($v['browser'],$br)!==false)
					{
						$icon=$brv[0];
						$iconh=$brv[1];
						break;
					}

				switch($v['type'])
				{
					case'bot':
						$name='<span class="entry" data-gip="'.$v['ip_guest'].'" data-s="'.$v['service'].'">'.htmlspecialchars($v['botname'],ELENT,CHARSET).'</span>';
					break;
					case'user':
						$name='<a class="entry" href="'.$v['_aedit'].'" data-uid="'.$v['user_id'].'" data-s="'.$v['service'].'"'
							.(isset($v['_group'],$groups[$v['_group']]) ? ' title="'.$groups[$v['_group']]['title'].'">'.$groups[$v['_group']]['style'].htmlspecialchars($v['name'],ELENT,CHARSET) : '>'.htmlspecialchars($v['name'],ELENT,CHARSET))
							.'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>');
						$user=true;
					break;
					default:
						$name='<i class="entry" data-gip="'.$v['ip_guest'].'" data-s="'.$v['service'].'">'.static::$lang['guest'].'</i>';
				}
				$v['location']=htmlspecialchars($v['location'],ELENT,CHARSET,false);
				$ip=$v['ip_guest'] ? $v['ip_guest'] : $v['ip_user'];
				$loc='<a href="'.$v['location'].'" target="_blank">'.Strings::CutStr($v['location'],100).'</a>';
				$Lst->item(
					$icon ? ['<img title="'.$iconh.'" src="'.$icon.'" />','style'=>'width:1px'] : false,
					$icon ? $name : [$name,'colspan'=>2],
					[$ip,'center','href'=>'http://eleanor-cms.ru/whois/'.$ip,'hrefextra'=>['target'=>'_blank']],
					[($v['_online'] ? '<span style="color:green" title="'.sprintf(static::$lang['expire'],Eleanor::$Language->Date($v['expire'],'fdt')).'">' : '<span style="color:red" title="'.sprintf(static::$lang['expired'],Eleanor::$Language->Date($v['expire'],'fdt')).'">').Eleanor::$Language->Date($v['enter'],'fdt').'</span>','center'],
					$user ? $loc : [$loc,'colspan'=>2],
					$user ? $Lst('func',
						[$v['_aedit'],$ltpl['edit'],$images.'edit.png'],
						$v['_adel'] ? [$v['_adel'],$ltpl['delete'],$images.'delete.png'] : false
					) : false
				);
			}
		}
		else
			$Lst->empty(static::$lang['snf']);

		$fisess='';
		foreach($sess as $k=>&$v)
			$fisess.=Eleanor::Option($v,$k,$query['']['fi']['online']==$k);

		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.static::$lang['sshow'].'</b><br />'.Eleanor::Select('fi[online]',$fisess).'</td>
					<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
				</tr>
			</table>
<script>//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
});//]]></script>
		</form>'
		.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['spp'],$Lst->perpage($pp,$links['pp'])).'</div></div>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,[$links['pages'],$links['first_page']]));
	}

	/*
		Шаблон страницы с редактированием форматов писем
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
	*/
	public static function Letters($controls,$values)
	{
		static::Menu('letters');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if($v)
				if(is_array($v) and !empty($values[$k]))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
				elseif(is_string($v))
					$Lst->head($v);
		return Eleanor::$Template->Cover($Lst->button(Eleanor::Button(T::$lang['save']))->end()->endform());
	}

	/*
		Страница удаления пользователя
		$a - массив удаляемого пользователя, ключи:
			name - имя пользователя
			full_name - полное имя пользователя

		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['name'],$a['full_name']),$back));
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{
		static::Menu('options');
		return$c;
	}
}
Users::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/users-*.php',false);

return Users::class;