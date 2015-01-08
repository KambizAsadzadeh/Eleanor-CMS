<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Шаблон админки системного модуля страниц ошибок */
class Errors
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		T::$data['navigation']=[
			[$links['list'],$lang['list'],'modules','act'=>$act=='list'],
			[$links['create'],static::$lang['create'],'act'=>$act=='create'],
			[$links['letters'],$lang['letters'],'act'=>$act=='letters'],
		];
	}

	/** Список страниц ошибок
	 * @param array $items Перечень страниц ошибок. Формат: ID=[], ключи:
	 *  [string email] E-mail, куда будут отправляться сообщения об ошибках
	 *  [array miniature] Миниатюра-логотип
	 *  [bool log] Флаг логирования ошибки
	 *  [string uri] URI
	 *  [string title] Название
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 *  [string _a] Ссылка на просмотр страницы в пользовательской части
	 * @param bool $notempty Флаг того, что статические страницы существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество страниц ошибок (всего)
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_title] Ссылка на сортировку списка по названию
	 *  [string sort_email] Ссылка на сортировку списка по e-mail
	 *  [string sort_http_code] Ссылка на сортировку списка по HTTP коду
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [string first_page] Ссылка на первую страницу
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$notempty,$cnt,$pp,$query,$page,$links)
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
			$Items=TableList(4)->form()
				->head(
					[T::$lang['title'],$query['sort']=='title' ? $query['order'] : false,$links['sort_title'],'col_item'],
					['E-mail',$query['sort']=='email' ? $query['order'] : false,$links['sort_email'],'col_item'],
					['HTTP code',$query['sort']=='http_code' ? $query['order'] : false,$links['sort_http_code'],'col_item'],
					[Html::Check('mass',false,['id'=>'mass-check']),'class'=>'col_check']
				);

			foreach($items as $k=>$v)
				$Items->item(
					$Items('main',
						$v['title'],
						[ $v['_aedit'],[ $v['_a'], T::$lang['goto'],'extra'=>['target'=>'_blank'] ], [$v['_aedit'], T::$lang['edit']], [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']]],
						$v['miniature'] ? $v['miniature']['http'] : false
					)+['tr-extra'=>['id'=>'item'.$k]],
					$v['email'] ? '<a href="mailto:'.$v['email'].'">'.$v['email'].'</a>' : '&mdash;',
					$v['http_code'] ? $v['http_code'] : '&mdash;',
					[Html::Check('items[]',false,['value'=>$k]),'col_check']
				);

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

		if($notempty)
		{
			$filters=[
				'title'=>'',
				'email'=>'',
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

			$filters['title']=Html::Input('fi[title]',$filters['title'],['placeholder'=>T::$lang['filter-by-name'],
				'class'=>'form-control','id'=>'fi-title']);
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
									<label for="fi-title">{$t_lang['title']}</label>
									{$filters['title']}
								</div>
								<div class="form-group">
									<label for="fi-title">E-mail</label>
									{$filters['email']}
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

	/** Страница добавления/редактирования страницы ошибки
	 * @param int $id ID редактируемой страницы, если равно 0, значит страница добавляется
	 * @param array $values Значения полей формы:
	 *  [string|array title] Название
	 *  [string|array uri] URI
	 *  [string|array text] Текст
	 *  [string|array document_title] Document title
	 *  [string|array meta_descr] Meta description
	 * Только при включенной мультиязычности:
	 *  [string log_language] Язык логирования
	 *  [bool single-lang] Флаг одной языковой версии (сквозной для всех языков)
	 *  [array language] Перечень языковых версий
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param string $draft Флаг наличия черновика
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 *  [string|null delete-draft] Ссылка на удаление черновика
	 *  [string draft] Ссылка на сохранение черновиков (для фоновых запросов)
	 * @param int $maxupload Максимально доспустимый размер файла для загрузки
	 * @return string */
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
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
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

	/** Страница удаления страницы ошибки
	 * @param array $error Данные удаляемой страницы ошибки
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($error,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$error['title']),$back);
	}
}
Errors::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/errors-*.php',false);

return Errors::class;