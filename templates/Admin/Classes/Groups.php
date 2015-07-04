<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Шаблоны управления группами пользователей в админке */
class Groups
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню */
	protected static function Menu($act='')
	{
		$links=&$GLOBALS['Eleanor']->module['links'];

		T::$data['navigation']=[
			[$links['list'],Eleanor::$Language['groups']['list'],'act'=>$act=='list'],
			[$links['create'],static::$lang['create'],'act'=>$act=='create','extra'=>['class'=>'iframe'.($act=='create' ? ' active' : '')]],
		];
	}

	/** Список "Да" - "Нет" для списка
	 * @param bool $flag Флаг "ОК"
	 * @param bool $green_no Флаг отрицательного позитивного значения
	 * @return string */
	protected static function YesNo($flag,$green_no=true)
	{
		$text=$flag ? static::$lang['yes'] : static::$lang['no'];

		if($flag xor $green_no)
			return<<<HTML
<span class="glyphicon glyphicon-minus text-danger" title="{$text}"> </span>
HTML;

		return<<<HTML
<span class="glyphicon glyphicon-ok text-success" title="{$text}"> </span>
HTML;
	}

	/** Список групп пользователей
	 * @param array $items Перечень групп пользователей. Формат: ID=>[], ключи:
	 *  [string title] Название
	 *  [string descr] Описание
	 *  [bool protected] Флаг защищенной системной группы
	 *  [string _aedit] Ссылка на редактирование
	 *  [string|null _adel] Ссылка на удаление
	 *  [string _achildren] Ссылка на просмотр подгрупп
	 *  [string _acreate] Ссылка на добавление подгруппы
	 *  [string style] Стиль группы
	 *  [bool is_admin] Флаг доступа в админпанель
	 *  [int max_upload] Максимальный размер загружаемых файлов (в KB); 1 - нет ограничения; 0 - запретить загрузку файлов
	 *  [bool captcha] Флаг отображения капчи группе
	 *  [bool moderate] Флаг перемеодерации
	 *  [bool banned] Флаг забаненой группы
	 * @param array $navi Хлебные крошки навигации. Формат ID=>[], ключи:
	 *  [string title] Название
	 *  [string|null _a] Ссылка
	 * @param bool $notempty Флаг того, что группы существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество групп (всего)
	 * @param int $pp Количество групп на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$navi,$notempty,$cnt,$pp,$query,$page,$links)
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
			$Items=TableList(5)
				->head(
					$t_lang['name'],
					$c_lang['admin'],
					$c_lang['captcha'],
					$c_lang['pre-moderation'],
					$c_lang['banned']
				);

			$l_subs=static::$lang['subgroups'];

			foreach($items as $k=>$v)
				$Items->item(
					$Items('main',
						$v['title'],
						[ $v['_aedit'],
							$v['_achildren'] ? [$v['_achildren'], $l_subs($v['children']),'extra'=>['class'=>'td_collapse_link']] : false,
							[$v['_aedit'], T::$lang['edit'], 'extra'=>['class'=>'iframe']],$v['_adel'] ? [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']] : [],
							[$v['_acreate'], static::$lang['create-subgroup']]]
					)+['tr-extra'=>['id'=>'item'.$k]],
					static::YesNo($v['is_admin']),
					static::YesNo($v['captcha']),
					static::YesNo($v['moderate']),
					static::YesNo($v['banned'])
				);

			$Items->end()->subitems(4)->foot('',$cnt,$pp,$page,$links);

			$back=Html::Input('back',\Eleanor\SITEDIR.\CMS\Url::$current,['type'=>'hidden']);
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
				<button type="submit" class="btn btn-danger once" name="ok">{$t_lang['delete']}</button>
			</form></div>
		</div>
	</div>
</div>
<script>$(ItemsDelete)</script>
HTML;

		}
		else
			$Items=T::$T->Alert(static::$lang['not_found'],'info');

		if($notempty)
		{
			$filters=['title'=>''];

			if($links['nofilter'] and isset($query['fi']['title']))
			{
				$caption=T::$lang['change-filter'];
				$filters=$query['fi'];
				$nofilter=<<<HTML
<p class="filters-text grey">{$c_lang['filter-by-title']}<a class="filters-reset" href="{$links['nofilter']}">&times;</a></p>
HTML;
			}
			else
			{
				$caption=T::$lang['apply-filter'];
				$nofilter='';
			}

			$filters=Html::Input('fi[title]',$filters['title'],['placeholder'=>T::$lang['filter-by-name'],'title'=>T::$lang['filter-by-name'],
				'class'=>'form-control','id'=>'fi-title']);
			$filters=<<<HTML
					<!-- Фильтры -->
					<div class="filters">
						{$nofilter}
						<div class="dropdown">
							<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
							<form class="dropdown-menu dropform pull-right" method="post">
								<div class="form-group">
									<label for="fi-title">{$t_lang['title']}</label>
									{$filters}
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
		$create=$links['parent_create'] ? $links['parent_create'] : $links['create'];
		$nav='';

		foreach($navi as $v)
			$nav.=is_array($v) ? '<li><a href="'.$v['_a'].'">'.$v['title'].'</a></li>' : '<li class="active">'.$v.'</li>';

		if($nav)
			$nav=<<<HTML
<ol class="breadcrumb"><li><a href="{$links['list']}">.</a></li>{$nav}</ol>
HTML;

		$nav.=T::$T->IframeLink();

		return<<<HTML
	{$nav}
	<div class="list-top">
		{$filters}
		<a href="{$create}" class="btn btn-default iframe">{$c_lang['create']}</a>
	</div>
	{$Items}
HTML;
	}

	/** AJAX дозагрузка подгрупп
	 * @param array $items Перечень подгрупп пользователей. Описание смотрите в методе ShowList
	 * @return string */
	public static function LoadSubGroups($items)
	{
		#ToDo! Удалить:
		T::$data['speedbar']=[];

		if($items)
		{
			$Items=TableList(5)->empty_head(5);
			$l_subgr=static::$lang['subgroups'];

			foreach($items as $k=>$v)
				$Items->item(
					$Items('main',
						$v['title'],
						[ $v['_aedit'],
							$v['_achildren'] ? [$v['_achildren'], $l_subgr($v['children']),'extra'=>['class'=>'td_collapse_link']] : false,
							[$v['_aedit'], T::$lang['edit'], 'extra'=>['class'=>'iframe']],$v['_adel'] ? [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']] : [],
							[$v['_acreate'], static::$lang['create-subgroup']]]
					)+['tr-extra'=>['id'=>'item'.$k]],
					static::YesNo($v['is_admin']),
					static::YesNo($v['captcha']),
					static::YesNo($v['moderate']),
					static::YesNo($v['banned'])
				);

			return(string)$Items->end();
		}

		return T::$T->Alert(static::$lang['not_found'],'info');
	}

	/** Страница создания/редактирования группы
	 * @param int $id ID редактируемой группы, если равно 0, значит группа создается
	 * @param array $values Значения полей формы (совпадают с ключами $controls) плюс:
	 *  [array|string title] Название группы
	 *  [array|string descr] Описание группы
	 *  [string style] Стиль группы
	 *  [array _inherit] Перечень наследуемых свойств
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param array $controls Перечень элементов формы вероятные ключи:
	 *  [string type] Тип элемента: check - флажок, input - поле и т.п.
	 *  [string label-for] Идентификатор свойства for для label-а
	 * @param string $parents Перечень возможных родителей группы в виде <option>...</option>:
	 * @param callback $Controls2Html Генератор html из $controls
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 * @return string */
	public static function CreateEdit($id,$values,$Editor,$controls,$parents,$Controls2Html,$errors,$back,$links)
	{
		include __DIR__.'/Select2.php';

		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		$c_lang=static::$lang;
		$t_lang=T::$lang;

		static::Menu($id ? 'edit' : 'create');

		#Элементы формы
		$html=$Controls2Html();
		$input=[
			'style'=>Html::Input('style',$values['style'],['id'=>'style','class'=>'form-control need-tabindex pim','placeholder'=>$c_lang['style_plh'],'title'=>$c_lang['style_plh']]),
			'style_inherit'=>Html::Check('_inherit[]',in_array('style',$values['_inherit']),['title'=>$c_lang['inherit'],'value'=>'style','class'=>'inherit need-tabindex']),
			'parent'=>Select2::Select('parent','<option></option>'.$parents,['class'=>'need-tabindex pim','id'=>'parent','placeholder'=>$t_lang['no-parent'],'title'=>$t_lang['no-parent'],'disabled'=>!$parents],'{allowClear:true}'),
		];

		if(Eleanor::$vars['multilang'])
		{
			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['title'][$lng]=Html::Input('title['.$lng.']',$values['title'][$lng],
					['class'=>'form-control need-tabindex pim input-lg','id'=>'title-'.$lng,'placeholder'=>$c_lang['title'],'title'=>$c_lang['title']]);
				$input['descr'][$lng]=$Editor('descr['.$lng.']',$values['descr'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'descr-'.$lng]);
			}

			$input['title']=T::$T->LangEdit($input['title'],'title');
			$input['descr']=T::$T->LangEdit($input['descr'],'descr');
		}
		else
			$input+=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control pim need-tabindex input-lg','placeholder'=>$c_lang['title'],'title'=>$c_lang['title']]),
				'descr'=>$Editor('descr',$values['descr'],['id'=>'descr','class'=>'form-control pim need-tabindex']),
			];
		#/Элементы формы

		#Errors
		$er_title=$er_def='';

		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error))
			{
				$type=$error;
				if(isset(static::$lang[$error]))
					$error=static::$lang[$error];
			}

			$error=T::$T->Alert($error,'danger',true);;

			switch($type)
			{
				case'EMPTY_TITLE':
					$er_title=$error;
				break;
				default:
					$er_def.=$error;
			}
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		#Формирование блоков с настройками
		$parts=[];
		$part='';

		foreach($controls as $k=>$control)
		{
			if(is_string($control))
			{
				$parts[$k]='';
				$part=&$parts[$k];
				continue;
			}
			elseif(!isset($html[$k]))
				continue;

			$check=$control['type']=='check';

			if($check)
			{
				$hint=isset($control['descr']) ? ' title="'.$control['descr'].'"' : '';
				$html[$k]=<<<HTML
<label class="group-checkbox"{$hint}>{$html[$k]} {$control['title']}</label>
HTML;
			}

			$checked=in_array($k,$values['_inherit']) ? ' checked' : '';
			$html[$k]=<<<HTML
<div class="input-group">
	{$html[$k]}
	<span class="input-group-addon">
		<input type="checkbox" name="_inherit[]" value="{$k}"{$checked} title="{$c_lang['inherit']}" class="inherit need-tabindex" />
	</span>
</div>
HTML;

			if($check)
			{
				$part.=$html[$k];
				continue;
			}

			$for=isset($control['label-for']) ? ' for="'.$control['label-for'].'"' : '';
			$hint=isset($control['descr']) ? '<p class="text-muted">'.$control['descr'].'</p>' : '';
			$part.=<<<HTML
<div class="form-group">
	<label{$for}>{$control['title']}</label>
	{$html[$k]}
</div>{$hint}
HTML;
		}

		#Форматирование непосредственно HTML блоков
		foreach($parts as $k=>&$part)
			$part=<<<HTML
<div class="block-t expand">
	<p class="btl" data-toggle="collapse" data-target="#opts-{$k}">{$controls[$k]}</p>
	<div id="opts-{$k}" class="collapse in">
		<div class="bcont">
			{$part}
		</div>
	</div>
</div>
HTML;
		unset($part);
		#/Формирование блоков с настройками

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete']
			? <<<HTML
<button type="button" onclick="window.location='{$links['delete']}'" class="ibtn ib-delete need-tabindex"><i class="ico-del"></i><span class="thd">{$t_lang['delete']}</span></button>
HTML
			: '';
		#/Кнопки

		$parts=join('',$parts);

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$er_title}
						{$input['title']}
						<br />
						<div class="form-group">
							<label id="label-descr" for="descr">{$c_lang['descr']}</label>
							{$input['descr']}
						</div>
						<div class="form-group">
							<label for="parent">{$t_lang['parent']}</label>
							{$input['parent']}
						</div>
						<div class="form-group">
							<label for="style">{$c_lang['style']}</label>
							<div class="input-group">
								{$input['style']}
								<span class="input-group-addon">
									{$input['style_inherit']}
								</span>
							</div>
						</div>
					</div>
				</div>
				<div id="rightbar">
					{$parts}
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>{$delete}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){
var inherit=$(".inherit");
inherit.click(function(){
	var th=$(this),
		dis=th.prop("checked")&&!th.prop("disabled");

	th.closest(".input-group").toggleClass("disabled",dis)
		.children(":input,:first").find(":input").addBack().prop("disabled",dis);
});
$("#parent").change(function(){
	inherit.prop("disabled",!$(this).val()).each(function(){
		$(this).triggerHandler("click");
	});
}).change();
$(".form-group input:not(:checkbox,[class*=select2]),textarea").addClass("form-control pim"); {$pim} });</script>
HTML;
	}

	/** Страница удаления группы
	 * @param array $group Данные удаляемого тега
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($group,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$group['title']),$back);
	}
}
Groups::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/groups-*.php',false);

return Groups::class;