<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Шаблоны сервисов */
class Services
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
			[$links['list'],Eleanor::$Language['services']['list'],'act'=>$act=='list'],
			$links['create'] ? [$links['create'],static::$lang['create'],'extra'=>['class'=>'iframe'.($act=='create' ? ' active' : '')]] : null,
		];
	}

	/** Список сервисов (файлов, с которых производится запуск) системы
	 * @param array $items Перечень сервисов. Формат: ID=>[], ключи:
	 *  [string name] Имя
	 *  [string file] Файд
	 *  [string login] Логин
	 *  [string theme] Шаблон оформления
	 *  [bool protected] Флаг защищенного сервиса
	 *  [string _aedit] Ссылка на редактирование
	 *  [string|null _adel] Ссылка на удаление
	 * @param bool $notempty Флаг того, что сервисы существуют, несмотря на настройки фильтра
	 * @param int $cnt Количество сервисов всего
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_name] Ссылка на сортировку списка имени сервиса
	 *  [string sort_file] Ссылка на сортировку списка по имени файла
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
			$Items=TableList(4)
				->head(
					[static::$lang['name'],$query['sort']=='name' ? $query['order'] : false,$links['sort_name'],'col_sort'],
					[static::$lang['file'],$query['sort']=='file' ? $query['order'] : false,$links['sort_file'],'col_sort'],
					static::$lang['theme'],
					static::$lang['protected']
				);

			foreach($items as $k=>$v)
			{
				$Items->item(
					$Items('main',
						ucfirst($k),
						[ [$v['_aedit'], T::$lang['edit'], 'extra'=>['class'=>'iframe']],
							$v['_adel'] ? [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']] : [] ]
					),
					$v['file'],
					$v['theme'],
					$v['protected'] ? '<span style="color:green">'.static::$lang['yes'].'</span>' : static::$lang['no']
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
				'name'=>'',
				'file'=>'',
			];

			if($links['nofilter'] and isset($query['fi']))
			{
				$caption=T::$lang['change-filter'];
				$applied=[];

				foreach($query['fi'] as $k=>$v)
					switch($k)
					{
						case'name':
							$applied[]=static::$lang['by-name'];
							$filters['name']=$v;
						break;
						case'file':
							$applied[]=static::$lang['by-file'];
							$filters['file']=$v;
					}

				$nofilter='<p class="filters-text grey">'.sprintf(static::$lang['applied-by%'],join(', ',$applied))
					.'<a class="filters-reset" href="'.$links['nofilter'].'">&times;</a></p>';
			}
			else
			{
				$caption=T::$lang['apply-filter'];
				$nofilter='';
			}

			$filters['name']=Html::Input('fi[name]',$filters['name'],['placeholder'=>static::$lang['filter-by-name'],
				'class'=>'form-control','id'=>'fi-name']);
			$filters['file']=Html::Input('fi[file]',$filters['file'],['placeholder'=>static::$lang['filter-by-file'],
				'class'=>'form-control','id'=>'fi-file']);

			$filters=<<<HTML
					<!-- Фильтры -->
					<div class="filters">
						{$nofilter}
						<div class="dropdown">
							<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
							<form class="dropdown-menu dropform pull-right" method="post">
								<div class="form-group">
									<label for="fi-name">{$c_lang['name']}</label>
									{$filters['name']}
								</div>
								<div class="form-group">
									<label for="fi-file">{$c_lang['file']}</label>
									{$filters['file']}
								</div>
								<button type="submit" class="btn btn-primary">{$t_lang['apply']}</button>
							</form>
						</div>
					</div>
HTML;
		}
		else
			$filters='';

		$create='<a href="'.$GLOBALS['Eleanor']->module['links']['create'].'" class="btn btn-default iframe">'
			.static::$lang['create'].'</a>'.T::$T->IframeLink();

		return<<<HTML
	<div class="list-top">
		{$filters}{$create}
	</div>
	{$Items}
HTML;
	}

	/** Страница добавления/редактирования сервиса
	 * @param string $service Имя редактируемого кода, если равно 0, значит сервис добавляется
	 * @param array $values Значения полей формы:
	 *  [string name] Имя сервиса
	 *  [string file] Файл запуска
	 *  [string login] Основной логин сервиса
	 *  [bool protected] Флаг защищенного сервиса (только для $service=0)
	 * @param array $data Данные для заполенения форм, ключи:
	 *  [array logins] Перечень логинов
	 * 	[array files] Перечень доступных файлов в корне
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 * @return string */
	public static function CreateEdit($service,$values,$data,$errors,$back,$links)
	{
		include __DIR__.'/Select2.php';

		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		$c_lang=static::$lang;

		static::Menu($service ? 'edit' : 'create');

		#Логины и файлы
		$logins=$files='';
		foreach($data['logins'] as $v)
			$logins.=Html::Option($v, false, $v==$values['login']);

		foreach($data['files'] as $v)
			$files.=Html::Option($v);
		#/Логины и файлы

		$input=[
			'login'=>Html::Select('login',$logins,['class'=>'form-control need-tabindex pim','id'=>'login']),
			'file'=>Html::Input('file',$values['file'],['class'=>'pim form-control need-tabindex','id'=>'file','list'=>'files'])
				.'<datalist id="files">'.$files.'</datalist>',
			'name'=>Html::Input('name',$values['name'],['class'=>'pim form-control need-tabindex','id'=>'name','placeholder'=>static::$lang['name']]),
			'protected'=>Html::Check('protected',$values['protected'],['class'=>'need-tabindex','disabled'=>(bool)$service]),
		];

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Errors
		$er_name=$er_login=$er_def='';

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
				case'EMPTY_NAME':
				case'NAME_EXISTS':
					$er_name=$error;
				break;
				case'LOGIN_MISSED':
					$er_login=$error;break;
				default:
					$er_def=$error;
			}
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		#Кнопки
		$success=$service ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete'] ? '<button type="button" onclick="window.location=\''.$links['delete']
			.'\'" class="ibtn ib-delete need-tabindex"><i class="ico-del"></i><span class="thd">'
			.T::$lang['delete'].'</span></button>' : '';
		#/Кнопки

		return<<<HTML
		{$er_def}
			<form method="post">
				<div class="block">
					{$er_name}
					{$input['name']}
					<br />
					<div class="form-group">
						<label for="file">{$c_lang['file']}</label>
						{$input['file']}
					</div>
					{$er_login}
					<div class="form-group">
						<label for="login">{$c_lang['login']}</label>
						{$input['login']}
					</div>
					<fieldset>
						<div class="checkbox"><label>{$input['protected']} {$c_lang['protected_']}</label></div>
					</fieldset>
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>
					{$back}{$delete}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){ {$pim} })</script>
HTML;
	}

	/** Страница удаления сервиса
	 * @param array $service Данные удаляемого сервиса
	 *  [string name] Название
	 *  [string file] Файл
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($service,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],
			ucfirst($service['name']).' ('.$service['file'].')'),$back);
	}
}
Services::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/services-*.php',false);

return Services::class;