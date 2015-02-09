<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html, CMS\UserManager;

defined('CMS\STARTED')||die;

/** Шаблон "своих" BB кодов */
class OwnBB
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$links=$GLOBALS['Eleanor']->module['links'];
		T::$data['navigation']=[
			[$links['list'],Eleanor::$Language['ownbb']['list'],'act'=>$act=='list'],
			$links['create'] ? [$links['create'],static::$lang['create'],'extra'=>['class'=>'iframe'.($act=='create' ? ' active' : '')]] : null,
		];
	}

	/** Список OwnBB кодов
	 * @param array $items Перечень OwnBB кодов. Формат: ID=>[], ключи:
	 *  [int status] Флаг активности
	 *  [string title] Название
	 *  [string handler] Обработчик
	 *  [bool no_parse] Флаг отлкючения парсинга других тегов внутри текущего
	 *  [array tags] Перечень названий обрабатываемых тегов
	 *  [bool special] Флаг специального тега (по умолчанию не обрабатывается)
	 *  [array sp_tags] Перечень специальных тегов, которые будут обрабатываться внутри текущего
	 *  [bool sb] Флаг отображения тега внизу редактора
	 *  [string _atoggle] Ссылка-тумблер на переключение активности
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 * @param bool $notempty Флаг того, что OwnBB коды существуют, несмотря на настройки фильтра
	 * @param int $cnt Количество OwnBB кодов всего
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_handler] Ссылка на сортировку списка названию файла обработчика
	 *  [string sort_pos] Ссылка на сортировку списка по позиции
	 *  [string sort_status] Ссылка на сортировку списка по фдагу активности
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [string first_page] Ссылка на первую страницу
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$notempty,$cnt,$pp,$query,$page,$links)
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
			$posasc=count($items)>1 && (!$query['sort'] || $query['sort']=='pos' && $query['order']=='asc');

			if($posasc)
			{
				$GLOBALS['scripts'][]=T::$http['3rd'].'static/sortable.min.js';
				$GLOBALS['head']['sortable-table']=<<<HTML
<script>/*<![CDATA[*/$(function(){
	new Sortable($(".table.table-list tbody").get(0), {
		handle:".pos-lines",
		draggable:"tr",
		onUpdate:function(){
			var order=[];
			$("tbody tr").each(function(){
				order.push( $(this).data("id") );
			});

			$.post(location,{order:order.join(",")},function(r){
				if(r!="ok")
					location.reload();
			},"text");
		}
	});
})//]]></script>
HTML;
			}

			$Items=TableList(4)
				->head(
					[T::$lang['status'],$query['sort']=='status' ? $query['order'] : false,$links['sort_status'],'col_status'],
					['<span class="glyphicon glyphicon-sort"></span>',$posasc ? 'asc' : false,$posasc ? false : $links['sort_pos'],'col_pos'],
					[static::$lang['handler'],$query['sort']=='handler' ? $query['order'] : false,$links['sort_handler'],'col_item'],
					static::$lang['tags']
				);

			foreach($items as $k=>$v)
			{
				$Items->item(
					$Items('status',$v['status'],$v['status'] ? T::$lang['deactivate'] : T::$lang['activate'],$v['_atoggle'])
					+['tr-extra'=>['id'=>'item'.$k,'data-id'=>$k]],
					$posasc ? ['<span class="pos-lines"><i></i><i></i><i></i></span>','col_pos'] : false,
					$Items('main',
						ucfirst($v['handler']).($v['title'] ? ' &mdash; '.$v['title'] : ''),
						[ [$v['_aedit'], T::$lang['edit'], 'extra'=>['class'=>'iframe']],
							[ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']] ]
					)+['colspan'=>$posasc ? false : 2],
					join('; ',$v['tags'])
				);
			}

			$Items->end()->foot('',$cnt,$pp,$page,$links);

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
			$filters=[
				'handler'=>'',
				'tags'=>'',
				'title'=>'',
			];

			if($links['nofilter'] and isset($query['fi']))
			{
				$caption=T::$lang['change-filter'];
				$applied=[];

				foreach($query['fi'] as $k=>$v)
					switch($k)
					{
						case'title':
							$applied[]=static::$lang['by-title'];
							$filters['title']=$v;
						break;
						case'handler':
							$applied[]=static::$lang['by-handler'];
							$filters['handler']=$v;
						break;
						case'tags':
							$applied[]=static::$lang['by-tags'];
							$filters['tags']=$v;
					}

				$applied=sprintf(static::$lang['applied-by%'],join(', ',$applied));
				$nofilter=<<<HTML
<p class="filters-text grey">{$applied}<a class="filters-reset" href="{$links['nofilter']}">&times;</a></p>
HTML;
			}
			else
			{
				$caption=T::$lang['apply-filter'];
				$nofilter='';
			}

			$filters['title']=Html::Input('fi[title]',$filters['title'],['placeholder'=>static::$lang['filter-by-title'],
				'class'=>'form-control','id'=>'fi-title']);
			$filters['handler']=Html::Input('fi[handler]',$filters['handler'],['placeholder'=>static::$lang['filter-by-handler'],
				'class'=>'form-control','id'=>'fi-handler']);
			$filters['tags']=Html::Input('fi[tags]',$filters['tags'],['placeholder'=>static::$lang['filter-by-tags'],
				'class'=>'form-control','id'=>'fi-tags']);

			$filters=<<<HTML
					<!-- Фильтры -->
					<div class="filters">
						{$nofilter}
						<div class="dropdown">
							<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
							<form class="dropdown-menu dropform pull-right" method="post">
								<div class="form-group">
									<label for="fi-title">{$c_lang['title']}</label>
									{$filters['title']}
								</div>
								<div class="form-group">
									<label for="fi-handler">{$c_lang['handler']}</label>
									{$filters['handler']}
								</div>
								<div class="form-group">
									<label for="fi-tags">{$c_lang['tags']}</label>
									{$filters['tags']}
								</div>
								<button type="submit" class="btn btn-primary">{$t_lang['apply']}</button>
							</form>
						</div>
					</div>
HTML;
		}
		else
			$filters='';

		$create=$GLOBALS['Eleanor']->module['links']['create']
			? '<a href="'.$GLOBALS['Eleanor']->module['links']['create'].'" class="btn btn-default iframe">'.static::$lang['create'].'</a>'
			: '';

		if($create or $items)
			$create.=T::$T->IframeLink();

		return<<<HTML
	<div class="list-top">
		{$filters}{$create}
	</div>
	{$Items}
HTML;
	}

	/** Страница создается/редактирования BB кода
	 * @param int $id ID редактируемого кода, если равно 0, значит BB код создается
	 * @param array $values Значения полей формы:
	 *  [array title] Название
	 *  [string handler] Обработчик
	 *  [int pos] Позиция
	 *  [array tags] Перечень названий обрабатываемых тегов
	 *  [bool no_parse] Флаг отлкючения парсинга других тегов внутри текущего
	 *  [bool special] Флаг специального тега (по умолчанию не обрабатывается)
	 *  [array sp_tags] Перечень специальных тегов, которые будут обрабатываться внутри текущего
	 *  [array gr_use] Перечень групп, которые могут использовать данный тег
	 *  [array gr_see] Перечень групп, которым виден данный тег
	 *  [bool sb] Флаг отображения тега внизу редактора
	 *  [int status] Статус: 1 - акивировано, 0 - деактивировано
	 * @param array $data Данные для заполенения форм, ключи:
	 *  [array handlers] Перечень возможных обработчиков задачи
	 *  [array ownbb] Перечень существующих тегов для sp_tags
	 *  [array poses] Перечень возможных позиций
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 * @return string */
	public static function CreateEdit($id,$values,$data,$errors,$back,$links)
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

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
				$input['title'][$lng]=Html::Input("title[{$lng}]",$values['title'][$lng],
					['class'=>'form-control need-tabindex input-lg','id'=>'title-'.$lng,'placeholder'=>static::$lang['title-plh']]);

			$input['title']=T::$T->LangEdit($input['title'],'title');
		}
		else
			$input=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control need-tabindex input-lg','placeholder'=>static::$lang['title-plh']]),
			];

		#Обработчики
		$handler=$ownbb='';
		foreach($data['handlers'] as $v)
			$handler.=Html::Option($v, false, $v==$values[ 'handler' ]);

		foreach($data['ownbb'] as $h=>$v)
			$ownbb.=Html::Option($v['title'] ? $v['title'] : $h,$h,in_array($h,$values['sp_tags']));
		#/Обработчики

		$input+=[
			'handler'=>Html::Select('handler',$handler,['class'=>'form-control need-tabindex pim','id'=>'handler']),
			'tags'=>Select2::Tags('tags',$values['tags'],['class'=>'pim form-control need-tabindex','id'=>'tags']),
			'no_parse'=>Html::Check('no_parse',$values['no_parse'],['class'=>'need-tabindex']),
			'special'=>Html::Check('special',$values['special'],['class'=>'need-tabindex']),
			'sb'=>Html::Check('sb',$values['sb'],['class'=>'need-tabindex']),
			'sp_tags'=>Select2::Items('sp_tags',$ownbb,['class'=>'form-control need-tabindex pim','id'=>'sp-tags']),
			'gr_use'=>Select2::Items('gr_use','<option></option>'.UserManager::GroupsOpts($values['gr_use']),['class'=>'form-control need-tabindex pim','id'=>'gr-use','placeholder'=>static::$lang['all']]),
			'gr_see'=>Select2::Items('gr_see','<option></option>'.UserManager::GroupsOpts($values['gr_see']),['class'=>'form-control need-tabindex pim','id'=>'gr-see','placeholder'=>static::$lang['all']]),
		];

		#Позиция
		$input['pos']='';
		foreach($data['poses'] as $pos=>$v)
			$input['pos'].=Html::Option($v['title'] ? $v['title'] : $v['handler'],$pos,$values['pos']==$pos);

		$input['pos']=Select2::Select('pos',Html::Option(static::$lang['to-begin'],0,$values['pos']==0)
			.($input['pos'] ? Html::Optgroup(static::$lang['after'],$input['pos']) : ''),['id'=>'pos']);
		#/Позиция

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Errors
		$er_title=$er_def=$er_tags='';

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
				case'EMPTY_TAGS':
					$er_tags=$error;
				break;
				default:
					$er_def=$error;
			}
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete']
			? <<<HTML
<button type="button" onclick="window.location='{$links['delete']}'" class="ibtn ib-delete need-tabindex"><i class="ico-del"></i><span class="thd">{$t_lang['delete']}</span></button>
HTML
			: '';

		$stopts=Html::Option(T::$lang['active'],1,$values['status']==1)
			.Html::Option(T::$lang['inactive'],0,$values['status']==0);
		#/Кнопки

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$er_title}
						{$input['title']}
						<br />
						<div class="form-group">
							<label for="handler">{$c_lang['handler']}</label>
							{$input['handler']}
						</div>
						{$er_tags}
						<div class="form-group">
							<label for="tags">{$c_lang['h-tags']}</label>
							{$input['tags']}
						</div>
						<div class="form-group">
							<label for="sp-tags">{$c_lang['sp-tags']}</label>
							{$input['sp_tags']}
						</div>
						<fieldset>
							<div class="checkbox"><label>{$input['no_parse']} {$c_lang['no-parse']}</label></div>
							<div class="checkbox"><label>{$input['special']} {$c_lang['special']}</label></div>
							<div class="checkbox"><label>{$input['sb']} {$c_lang['sb']}</label></div>
						</fieldset>
					</div>
				</div>
				<div id="rightbar">
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#opts">{$c_lang['visibility']}</p>
						<div id="opts" class="collapse in">
							<div class="bcont">
								<div class="form-group">
									<label for="gr-use">{$c_lang['gr-use']}</label>
									{$input['gr_use']}
								</div>
								<div class="form-group">
									<label for="gr-see">{$c_lang['gr-see']}</label>
									{$input['gr_see']}
								</div>
							</div>
						</div>
					</div>
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#b3">{$c_lang['position']}</p>
						<div id="b3" class="collapse">
							<div class="bcont">
								<div class="form-group">
									<label for="pos">{$c_lang['pos']}</label>
									{$input['pos']}
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>
					<select name="status" class="form-control pim">{$stopts}</select>{$delete}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){ {$pim} })</script>
HTML;
	}

	/** Страница удаления OwnBB тега
	 * @param array $ownbb Данные удаляемого тега
	 *  [string title] Название
	 *  [string handler] Обработчик
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($ownbb,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],
			ucfirst($ownbb['handler']).($ownbb['title'] ? ' &mdash; '.$ownbb['title'] : '')),$back);
	}
}
OwnBB::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/ownbb-*.php',false);

return OwnBB::class;