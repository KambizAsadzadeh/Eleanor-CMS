<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Админка системного модуля статических страниц */
class StaticPage
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language[ $GLOBALS['Eleanor']->module['config']['n'] ];
		$links=$GLOBALS['Eleanor']->module['links'];
		$options=isset($links['options']) && is_array($links['options']) ? $links['options'] : false;

		T::$data['navigation']=[
			[$links['list'],$lang['list'],'act'=>$act=='list'],
			[$links['parent_create'] ? $links['parent_create'] : $links['create'],static::$lang['create'],'act'=>$act=='create'],
			[$links['files'],$lang['fp'],'act'=>$act=='files'],
			$options ? $options : [$links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'],
		];
	}

	/** Список статических страниц
	 * @param array $items Перечень статических страниц. Формат: ID=>[], ключи:
	 *  [string title] Название
	 *  [int status] Флаг активности
	 *  [string _atoggle] Ссылка-тумблер на переключение активности
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 *  [string _achildren] Ссылка на просмотр подстраниц
	 *  [string _a] Ссылка на просмотр страницы в пользовательской части
	 *  [string|null _aup] Ссылка на поднятие статической страницы на 1 пункт выше, null - страница уже в самом верху
	 *  [string|null _adown] Ссылка на опускание статической страницы на 1 пункт ниже, null - страница уже в самом низу
	 *  [string _acreate] Ссылка на добавление подстраницы
	 * @param array $navi Хлебные крошки навигации. Формат ID=>[], ключи:
	 *  [string title] Название
	 *  [string|null _a] Ссылка
	 * @param bool $notempty Флаг того, что статические страницы существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество статических страниц (всего)
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_status] Ссылка на сортировку списка по статусу активности
	 *  [string sort_title] Ссылка на сортировку списка по названию
	 *  [string sort_pos] Ссылка на сортировку списка по позиции
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$navi,$notempty,$cnt,$pp,$query,$page,$links)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			$GLOBALS['Eleanor']->module['title']
		];

		static::Menu('list');

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		if($items)
		{
			$posasc=count($items)>1 && (!$query['sort'] || $query['sort']=='pos' && $query['order']=='asc');
			$Items=TableList(5)->form()
				->head(
					[T::$lang['status'],$query['sort']=='status' ? $query['order'] : false,$links['sort_status'],'col_status'],
					['<span class="glyphicon glyphicon-sort"></span>',$posasc ? 'asc' : false,$posasc ? false : $links['sort_pos'],'col_pos'],
					[T::$lang['title'],$query['sort']=='title' ? $query['order'] : false,$links['sort_title'],'col_item'],
					[Html::Check('mass',false,['id'=>'mass-check']),'class'=>'col_check']
				);
			$l_subs=static::$lang['subpages'];
			$children=false;

			foreach($items as $k=>$v)
			{
				if($v['_achildren'])
					$children=true;

				$Items->item(
					$Items('status',$v['status'],$v['status'] ? T::$lang['deactivate'] : T::$lang['activate'],$v['_atoggle'])
					+['tr-extra'=>['id'=>'item'.$k]],
					$posasc ? ['<span class="pos-lines"><i></i><i></i><i></i></span>','col_pos'] : false,
					$Items('main',
						$v['title'],
						[ $v['_aedit'],
							$v['_achildren'] ? [$v['_achildren'], $l_subs($v['children']),'extra'=>['class'=>'td_collapse_link']] : false,
							[ $v['_a'], T::$lang['goto'],'extra'=>['target'=>'_blank'] ],
							[$v['_aedit'], T::$lang['edit']], [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']],
							[$v['_acreate'], static::$lang['create-subpage']]]
					)+['colspan'=>$posasc ? false : 2],
					[Html::Check('items[]',false,['value'=>$k]),'col_check']
				);
			}

			$Items->end()->subitems(1)->foot(Html::Option(T::$lang['delete'],'delete')
				.Html::Option(T::$lang['activate'],'activate')
				.Html::Option(T::$lang['deactivate'],'deactivate'),$cnt,$pp,$page,$links)->endform()->checks();

			if($children or $posasc)
			{
				$GLOBALS['scripts'][]='//cdn.jsdelivr.net/sortable/latest/Sortable.min.js';
				$GLOBALS['head']['sortable-table']=<<<'HTML'
<script>$(function(){
var tbody=$(".table.table-list tbody:first");
new Sortable(tbody.get(0), {
	handle:".pos-lines",
	draggable:"tr",
	filter:function(el){
		return !$(el).closest("tbody").is(tbody);
	},
	onUpdate:function(){
		var order=[];
		tbody.find(".col_check input:checkbox").each(function(){
			order.push( $(this).val() );
		});

		$.post(location,{order:order.join(",")},function(r){
			if(r!="ok")
				location.reload();
		},"text");
	}
});
$(document).on('subitems','.td_collapse_link',function(e,whole){
	var th=$(this),
		tbody2=whole.find(".table.table-list tbody:first");

	new Sortable(tbody2.get(0), {
		handle:".pos-lines",
		draggable:"tr",
		filter:function(el){
			return !$(el).closest("tbody").is(tbody2);
		},
		onUpdate:function(e){
			e.stopPropagation();

			var order=[];
			tbody2.find(".col_check input:checkbox").each(function(){
				order.push( $(this).val() );
			});

			$.post(th.attr("href"),{order:order.join(",")},function(r){
				if(r!="ok")
					location=th.attr("href");
			},"text");
		}
	});
})
})</script>
HTML;
			}


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
		{
			$links2=$GLOBALS['Eleanor']->module['links'];
			$Items=T::$T->Alert($notempty ? static::$lang[ 'not_found' ] : sprintf(static::$lang[ 'empty_list%' ], $links2['parent_create'] ? $links2['parent_create'] : $links2['create']), 'info');
		}

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

			$filters=Html::Input('fi[title]',$filters['title'],['placeholder'=>T::$lang['filter-by-name'],'title'=>T::$lang['filter-by-name'],'class'=>'form-control','id'=>'fi-title']);
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

		return<<<HTML
	{$nav}
	<div class="list-top">
		{$filters}
		<a href="{$create}" class="btn btn-default">{$c_lang['create-static-page']}</a>
	</div>
	{$Items}
HTML;
	}

	/** AJAX дозагрузка подстраниц
	 * @param array $items Перечень подстраниц. Описание смотрите в методе ShowList
	 * @param array $query Параметры запроса
	 * @return string */
	public static function LoadSubPages($items,$query)
	{
		if($items)
		{
			$posasc=count($items)>1 && (!$query['sort'] || $query['sort']=='pos' && $query['order']=='asc');
			$Items=TableList(5)->empty_head(4);
			$l_subs=static::$lang['subpages'];

			foreach($items as $k=>$v)
				$Items->item(
					$Items('status',$v['status'],$v['status'] ? T::$lang['deactivate'] : T::$lang['activate'],$v['_atoggle'])
					+['tr-extra'=>['id'=>'item'.$k]],
					$posasc ? ['<span class="pos-lines"><i></i><i></i><i></i></span>','col_pos'] : false,
					$Items('main',
						$v['title'],
						[ $v['_aedit'],
							$v['_achildren'] ? [$v['_achildren'], $l_subs($v['children']),'extra'=>['class'=>'td_collapse_link']] : false,
							[ $v['_a'], T::$lang['goto'],'extra'=>['target'=>'_blank'] ],
							[$v['_aedit'], T::$lang['edit']], [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']],
							[$v['_acreate'], static::$lang['create-subpage']]]
					)+['colspan'=>$posasc ? false : 2],
					[Html::Check('items[]',false,['value'=>$k]),'col_check']
				);

			return(string)$Items->end();
		}

		return T::$T->Alert(static::$lang['not_found'],'info');
	}

	/** Страница создания/редактирования статической страницы
	 * @param int $id ID редактируемой страницы, если равно 0, значит страница создается
	 * @param array $values Значения полей формы:
	 *  [string|array title] Название
	 *  [string|array uri] URI
	 *  [string|array text] Текст
	 *  [string|array document_title] Document title
	 *  [string|array meta_descr] Meta description
	 *  [int|null parent] ID родителя
	 *  [int pos] Позиция
	 *  [int status] Статус: 1 - акивировано, 0 - деактивировано
	 * Только при включенной мультиязычности:
	 *  [bool single-lang] Флаг одной языковой версии (сквозной для всех языков)
	 *  [array language] Перечень языковых версий
	 * @param array $data Данные для заполенения форм, ключи:
	 *  [array parents] Перечень родителей: родитель => id => Название
	 *  [array $poses] Перечень позиций "после"
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param string $draft Флаг наличия черновика
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 *  [string|null delete-draft] Ссылка на удаление черновика
	 *  [string draft] Ссылка на сохранение черновиков (для фоновых запросов)
	 * @return string */
	public static function CreateEdit($id,$values,$data,$Editor,$Uploader,$errors,$back,$draft,$links)
	{
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

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['title'][$lng]=Html::Input("title[{$lng}]",$values['title'][$lng],
					['class'=>'form-control need-tabindex input-lg pim','id'=>'title-'.$lng,'placeholder'=>static::$lang['title-placeholder'],'title'=>static::$lang['title-placeholder']]);
				$input['uri'][$lng]=Html::Input("uri[{$lng}]",$values['uri'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'uri-'.$lng]);
				$input['text'][$lng]=$Editor("text[{$lng}]",$values['text'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'text-'.$lng,'rows'=>20]);
				$input['document_title'][$lng]=Html::Input("document_title[{$lng}]",$values['document_title'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'docuemnt-title-'.$lng]);
				$input['meta_descr'][$lng]=Html::Input("meta_descr[{$lng}]",$values['meta_descr'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'meta-descr-'.$lng]);
			}

			$input['title']=T::$T->LangEdit($input['title'],'title');
			$input['uri']=T::$T->LangEdit($input['uri'],'uri');
			$input['text']=T::$T->LangEdit($input['text'],'text');
			$input['document_title']=T::$T->LangEdit($input['document_title'],'document-title');
			$input['meta_descr']=T::$T->LangEdit($input['meta_descr'],'meta-descr');
			$input['language']=T::$T->LangChecks($values['single-lang'],$values['language']);

			$input['language']=<<<HTML
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b2">{$t_lang['languages']}</p>
							<div id="b2" class="collapse in">
								<div class="bcont">
									<div class="form-group">
										{$input['language']}
									</div>
								</div>
							</div>
						</div>
HTML;

		}
		else
			$input=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control need-tabindex input-lg pim','placeholder'=>static::$lang['title-placeholder'],'title'=>static::$lang['title-placeholder']]),
				'uri'=>Html::Input('uri',$values['uri'],['id'=>'uri','class'=>'form-control need-tabindex pim']),
				'text'=>$Editor('text',$values['text'],['class'=>'form-control need-tabindex pim','id'=>'text','rows'=>20]),
				'document_title'=>Html::Input('document_title',$values['document_title'],['class'=>'form-control need-tabindex pim','id'=>'document-title']),
				'meta_descr'=>Html::Input('meta_descr',$values['meta_descr'],['class'=>'form-control need-tabindex pim','id'=>'meta-descr']),
				'language'=>''
			];

		$uri=T::$T->Uri();

		#Родители
		$input['parent']=[];

		foreach($data['parents'] as $base_pid=>$parent)
		{
			$input['parent'][$base_pid]='';
			$opts=&$input['parent'][$base_pid];

			foreach($parent as $pid=>$v)
				$opts.=Html::Option($v['title'],$pid,isset($data['parents'][$pid]) or $pid==$values['parent'],['data-children'=>$v['children']]);
		}

		foreach($input['parent'] as $k=>&$parent)
			$parent=Select2::Select('parent','<option></option>'.$parent,['data-parent'=>$k,'id'=>'parent-'.$k,'class'=>'parents','placeholder'=>T::$lang['to-root'],'title'=>T::$lang['to-root'],'disabled'=>!$parent],
				'{allowClear:true}');

		$input['parent']=join('',$input['parent']);
		unset($opts,$parent);
		#/Родители

		#Позиция
		$input['pos']='';
		foreach($data['poses'] as $pos=>$v)
			$input['pos'].=Html::Option($v['title'],$pos,$values['pos']==$pos);

		$input['pos']=Select2::Select('pos',Html::Option(static::$lang['to-begin'],0,$values['pos']==0)
			.($input['pos'] ? Html::Optgroup(static::$lang['after'],$input['pos']) : ''),['id'=>'pos']);
		#/Позиция

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
				$er_def.=$error;
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
							<label id="label-text" for="text">{$c_lang['text']}</label>
							{$er_text}
							{$input['text']}
						</div>
					</div>

					<div class="modal fade draggable" id="modal-uploader" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="myModalLabel">{$t_lang['uploader']}</h4>
								</div>
								<div class="modal-body">
									{$Uploader}
								</div>
							</div>
						</div>
					</div>
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
							</div>
						</div>
					</div>
{$input['language']}
					<div class="block-t expand">
						<p class="btl collapsed" data-toggle="collapse" data-target="#b3">{$c_lang['position']}</p>
						<div id="b3" class="collapse">
							<div class="bcont">
								<div class="form-group">
									<label for="parent-0">{$t_lang['parent']}</label>
									{$input['parent']}
								</div>
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
					<select name="status" class="form-control pim">{$stopts}</select>{$delete}{$draft}
					<button type="button" class="btn btn-primary pull-right" id="modal-uploader-trigger">{$t_lang['uploader']}</button>
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){
$("#modal-uploader-trigger").click(DraggableModal($("#modal-uploader")));
ParentsWithPos();{$pim}{$uri} })</script>
HTML;
	}

	/** Дозагрузка детей и позиций при смене родителя
	 * @param array $children Дети
	 * @param array $poses Позиции
	 * @return array['children'=>Дети, 'poses'=> Позиции]*/
	public static function AjaxLoadChildren($children,$poses)
	{
		$opts=$posopts='';

		#Родители
		foreach($children as $pid=>$v)
			$opts.=Html::Option($v['title'],$pid,false,['data-children'=>$v['children']]);

		#Позиция
		foreach($poses as $pos=>$v)
			$posopts.=Html::Option($v['title'],$pos);

		return['children'=>$opts ? '<option></option>'.$opts : '',
			'poses'=>Html::Option(T::$lang['to-root'],0,true).($posopts ? Html::Optgroup(static::$lang['after'],$posopts) : '')];
	}

	/** Страница редактирования статических страниц на файлах, создается на базе стандартного загрузчика файлов
	 * @param string $Uploader Загрузчик файлов
	 * @return string */
	public static function Files($Uploader)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu('files');
		return$Uploader;
	}

	/** Страница удаления статической страницы
	 * @param array $static Данные удаляемой статической страницы
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($static,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$static['title']),$back);
	}

	/** Обертка для интерфейса настроек
	 * @param string $options Интерфейс настроек
	 * @return string */
	public static function Options($options)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu('options');
		return(string)$options;
	}
}
StaticPage::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/static-*.php',false);

return StaticPage::class;