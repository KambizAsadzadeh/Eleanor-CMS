<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor, Eleanor\Classes\Html;

/** Шаблоны раздела "Модули", управление модулями  */
class Modules
{
	/** @var array Языковые значения */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню */
	protected static function Menu($act='')
	{
		$links=&$GLOBALS['Eleanor']->module['links'];

		T::$data['navigation']=[
			[$links['list'],Eleanor::$Language['modules']['list'],'act'=>$act=='list'],
			[$links['create'],static::$lang['create'],'act'=>$act=='create'],
		];
	}

	/** Шаблон раздела "Модули"
	 * @param array $modules Перечень модулей, ключи:
	 *  [string title] Название
	 *  [string descr] Описание
	 *  [array miniature] Миниатюра-логотип
	 *  [bool status] Флаг активного модуля
	 *  [array links] Ссылки на запуск модуля в разлизных сервисах: сервис=>ссылка
	 * @return string */
	public static function ModulesCover($modules)
	{
		$t_lang=T::$lang;

		#SpeedBar
		T::$data['speedbar']=[ Eleanor::$Language['main']['modules'] ];

		static::Menu('list');

		$delete=false;
		$content='
		<!-- Список модулей -->
		<div class="card-list">';

		foreach($modules as $id=>$module)
		{
			if($module['miniature'])
			{
				if(!isset($module['miniature']['http']))
				{
					ksort($module['miniature'],SORT_STRING);
					$module['miniature']=end($module['miniature']);
				}

				$module['miniature']=$module['miniature']['http'];
			}

			$img=$module['miniature']
				? '<div class="card-img"><img src="'.$module['miniature'].'" alt=""></div>'
				: ItemAvatar($module['title']);

			if($module['status']==1 and $module['_links'])
			{
				$mainlink=$class='';
				$foot='<div class="services over">';

				foreach($module['_links'] as $service=>$a)
				{
					if(!$mainlink)
						$mainlink=$a;

					$foot.='<a href="'.$a.'">'.$service.'</a>, ';
				}

				$foot=rtrim($foot,', ').'</div>';
			}
			else
			{
				$class=' card-off';
				$mainlink='#';
				$foot=static::$lang['off'];

			}

			if($module['_adel'])
				$delete=true;

			$del_tog=$module['protected']==1 ? ''
				: '<li><a href="'.$module['_atoggle'].'">'
					.($module['status']==1 ? static::$lang['turn-off'] : static::$lang['turn-on'])
					.'</a></li><li><a href="'.$module['_adel'].'" class="delete">'.T::$lang['delete'].'</a></li>';

			$content.=<<<HTML

			<!-- Карточка модуля -->
			<div class="card-item{$class}" id="it{$id}">
				<div class="cover">
					{$img}
					<span class="card-overlay"></span>
				</div>
				<div class="card-info">
					<h4 class="title">{$module['title']}</h4>
					<div class="text">{$module['descr']}</div>
				</div>
				<a class="card-link" href="{$mainlink}"></a>
				<div class="card-foot">
					{$foot}
				</div>
				<div class="card-set">
					<a class="card-set-link" data-toggle="dropdown" href="#">
						<b class="btn-dotted"><i></i><i></i><i></i></b>
					</a>
					<div class="card-menu" role="menu">
						<div class="card-menu-in">
							<ul>
								<li><a href="{$module['_aedit']}">{$t_lang['edit']}</a></li>
								{$del_tog}
							</ul>
						</div>
						<span class="card-menu-overlay"></span>
					</div>
				</div>
			</div>
			<!-- / Карточка модуля -->

HTML;
		}

		$content.='		</div>
		<!-- / Список модулей -->';

		if($delete)
		{
			$back=Html::Input('back',\Eleanor\SITEDIR.\CMS\Url::$current,['type'=>'hidden']);
			$lang=static::$lang;
			$content.=<<<HTML
	<!-- Окно подтверждение удаления -->
	<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">{$t_lang['delete-confirm']}</h4>
				</div>
				<div class="modal-body">{$lang['delete-text-span']}</div>
				<div class="modal-footer"><form method="post">{$back}
					<button type="button" class="btn btn-default" data-dismiss="modal">{$t_lang['cancel']}</button>
					<button type="submit" class="btn btn-danger" name="ok">{$t_lang['delete']}</button>
				</form></div>
			</div>
		</div>
	</div>
<script>$(function(){
	$("a.delete").click(function(e){
		e.preventDefault();
		var th=$(this);

		$("#delete-title").text( th.closest(".card-item").find(".title").text() );
		$("#delete").find("form").attr("action",th.attr("href")).end().modal("show");
	});
})</script>
HTML;


		}

		return$content;
	}

	/** Страница создается/редактирования модуля
	 * @param int $id ИД редактируемого модуля, если равно 0 значит модуль создается
	 * @param array $values значения полей формы, ключи:
	 *  [array|string title] Название модуля
	 *  [array|string descr] Описание модуля
	 *  [int status] Статус модуля: 1 = активирован, 0 - деактивирован
	 *  [string path] Пусть до каталога модуля, относительно каталога /cms/
	 *  [string file] Пусть к файлу запуска файла, относительно каталога модуля
	 *  [string api] Путь к файлу API модуля, относительно каталога модуля
	 *  [string config] Пусть к файлу конфигураций (должен возвращать массив), относительно каталога модуля
	 *  [string miniature] Путь к файлу с логотипом модуля, относительно каталога static/images/modules/
	 *  [array uris] URI идентификаторы модуля. Формат: секция(из конфига)=>язык(может быть '')=>URI модуля
	 * @param array $uris Секции модуля (из конфига) в формате имя=>название
	 * @param int $maxupload Максимально доспустимый размер файла для загрузки
	 * @param array $errors Ошибки заполнения формы
	 * @param string $back URL возврата
	 * @param array $links Перечень необходимых ссылок, ключи:
	 *  [string|null delete] Ссылка на удаление категории
	 * @return string */
	public static function CreateEdit($id,$values,$uris,$maxupload,$errors,$back,$links)
	{
		include __DIR__.'/Select2.php';
		$c_lang=static::$lang;

		#SpeedBar
		T::$data['speedbar']=[
			[$GLOBALS['Eleanor']->module['links']['list'], Eleanor::$Language['main']['modules']],
			end($GLOBALS['title'])
		];

		static::Menu($id ? 'edit' : 'create');

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['title'][$lng]=Html::Input('title['.$lng.']',$values['title'][$lng],
					['class'=>'form-control need-tabindex pim input-lg','id'=>'title-'.$lng,'placeholder'=>$c_lang['name'],'title'=>$c_lang['name']]);
				$input['descr'][$lng]=Html::Text('descr['.$lng.']',$values['descr'][$lng],
					['class'=>'form-control need-tabindex pim','id'=>'descr-'.$lng]);
			}

			$input['title']=T::$T->LangEdit($input['title'],'title');
			$input['descr']=T::$T->LangEdit($input['descr'],'descr');
		}
		else
			$input=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control input-lg pim need-tabindex','placeholder'=>$c_lang['name'],'title'=>$c_lang['name']]),
				'descr'=>Html::Text('descr',$values['descr'],['id'=>'descr','class'=>'form-control pim need-tabindex']),
			];

		$uri_html='';
		foreach($uris as $name=>$title)
		{
			if(Eleanor::$vars['multilang'])
			{
				$uri=[];

				foreach($values['uris'][$name] as $k=>$v)
					$uri[$k]=Select2::Tags('uris['.$name.']['.$k.']',$v,
						['id'=>'uri-'.$name.'-'.$k,'class'=>'pim form-control need-tabindex']);

				$uri=T::$T->LangEdit($uri,'uri-'.$name);
			}
			else
				$uri=Select2::Tags('uris['.$name.']',$values['uris'][$name],
					['id'=>'uri-'.$name,'class'=>'pim form-control need-tabindex']);

			$uri_html.=<<<HTML
									<div class="form-group">
										<label id="label-uri-{$name}" for="uri-{$name}">{$title}</label>
										{$uri}
									</div>
HTML;
		}

		if($back)
			$back=Html::Input('back',$back,['type'=>'hidden']);

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete'] ? '<button type="button" onclick="location=\''.$links['delete']
			.'\'" class="ibtn ib-delete"><i class="ico-del"></i><span class="thd">'
			.T::$lang['delete'].'</span></button>' : '';
		#/Кнопки

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Errors
		$er_title=$er_uris=$er_file=$er_path=$er_api=$er_conf=$er_def='';

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
				case'WRONG_PATH':
				case'UNFILLED_PATH':
					$er_path=$error;
				break;
				case'FILE_DOES_NOT_EXISTS':
				case'UNFILLED_FILE':
					$er_file=$error;
				break;
				case'API_DOES_NOT_EXISTS':
					$er_api=$error;
				break;
				case'CONFIG_DOES_NOT_EXISTS':
					$er_conf=$error;
				break;
				case'URI_EXISTS':
					$er_uris=$error;
				break;
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

		#Пути
		$extra=$id && $values['protected'] ? ['disabled'=>true] : [];
		$path=Html::Input('path',$values['path'],['id'=>'path','class'=>'form-control input-group pim need-tabindex',
				'data-toggle'=>'tooltip','data-placement'=>'left','title'=>static::$lang['tip-rel-cms'],'list'=>'paths',
			'required'=>true]+$extra).'<datalist id="paths"></datalist>';
		$file=Html::Input('file',$values['file'],['id'=>'file','class'=>'form-control input-group pim need-tabindex',
				'data-toggle'=>'tooltip','data-placement'=>'left','title'=>static::$lang['tip-rel-module'],'list'=>'files',
			'required'=>true]+$extra).'<datalist id="files"></datalist>';
		$api=Html::Input('api',$values['api'],['id'=>'api','class'=>'form-control input-group pim need-tabindex',
				'data-toggle'=>'tooltip','data-placement'=>'left','title'=>static::$lang['tip-rel-module'],'list'=>'files'
			]+$extra);
		$config=Html::Input('config',$values['config'],['id'=>'config','class'=>'form-control input-group pim need-tabindex',
				'data-toggle'=>'tooltip','data-placement'=>'left','title'=>static::$lang['tip-rel-module'],'list'=>'files'
			]+$extra);


		#Миниаююра
		$image=T::$T->Miniature($values['miniature'],null,null,$maxupload);

		#Статус
		if($extra)
			$status='';
		else
			$status=Html::Select('status',
				Html::Option(T::$lang['active'],1,$values['status']==1).Html::Option(T::$lang['inactive'],0,$values['status']==0),
			['class'=>'form-control pim']);

		return<<<HTML
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
					</div>

					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#uris">{$c_lang['uri']}</p>
						<div id="uris" class="collapse in">
							<div class="bcont">
								{$er_uris}
								{$uri_html}
							</div>
						</div>
					</div>
				</div>
				<div id="rightbar">
{$image}
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#b2">{$c_lang['paths']}</p>
						<div id="b2" class="collapse in">
							<div class="bcont">
								<div class="form-group">
									<label for="path">{$c_lang['path']}</label>
									{$er_path}
									{$path}
								</div>
								<div class="form-group">
									<label for="file">{$c_lang['file']}</label>
									{$er_file}
									{$file}
								</div>
								<div class="form-group">
									<label for="api">API</label>
									{$er_api}
									{$api}
								</div>
								<div class="form-group">
									<label for="config">{$c_lang['config']}</label>
									{$er_conf}
									{$config}
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}{$delete}
					<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>{$status}
				</div>
				<!-- FootLine [E] -->
			</form>
			<script>
$(function(){
	//Инциализация подсказки
	$("#path,#file,#api,#config").tooltip();

	//Управление изображениями
	var format=function(state)
		{
			return "<img class='flag' src='"+$(state.element).data("img")+"' alt='' /> " + state.text;
		};

	//Путь к файлу
	var path=$("#path"),
		paths=$("#paths"),
		files=$("#files"),
		cache={},
		cache_files={},
		oldval;
	path.on("input",function(){
		var th=$(this),
			val=th.val().replace(/\/[^\/]*$/, '');

		if(val!==oldval)
		{
			oldval=val;
			if(val in cache)
			{
				paths.html(cache[val]);
				files.html(cache_files[val]);
			}
			else
				$.get(location,{path:val},function(r){
					cache[val]="";
					cache_files[val]="";

					$.each(r.dirs,function(i,v){
						cache[val]+="<option>"+v+"<option>";
					});

					$.each(r.files,function(i,v){
						cache_files[val]+="<option>"+v+"<option>";
					});

					paths.html(cache[val]);
					files.html(cache_files[val]);
				},"json");
		}
	}).trigger("input");

	//Подгрузка секций модуля исходя из конфига
	var uris_cache={},
		uris=$("#uris"),
		to;
	$("#config").on("input",function(){
		var th=$(this);
		clearTimeout(to);
		to=setTimeout(function(){
			var pval=path.val(),
				val=th.val();

			if((pval in uris_cache) && (val in uris_cache[pval]) && false)
				uris.children(".bcont").detach().end().append(uris_cache[pval][val]);
			else if(pval && val)
			{
				CORE.ShowLoading();
				$.get(location,{path:pval,config:val},function(r){
					if(!(pval in uris_cache))
						uris_cache[pval]={};

					uris_cache[pval][val]=uris.children().detach().end().html(r).children();
				},"text").always(function(){
					CORE.HideLoading();
				});
			}
		},300);
	});
	{$pim}
});</script>
HTML;
	}

	/** Подгрузка URIS на Ajax
	 * @param array $uris Секции модуля (из конфига) в формате имя=>название
	 * @param array $values URI идентификаторы модуля. Формат: секция(из конфига)=>язык(может быть '')=>URI модуля
	 * @return string */
	public static function AjaxUris($uris,$values)
	{
		include __DIR__.'/Select2.php';

		#ToDo! удалить
		T::$data['speedbar']=[];

		$uri_html='';
		foreach($uris as $name=>$title)
		{
			if(Eleanor::$vars['multilang'])
			{
				$uri=[];

				foreach($values[$name] as $k=>$v)
					$uri[$k]=Select2::Tags("uris[{$name}][{$k}]",$v,
						['id'=>"uri-{$name}-{$k}"]);

				$uri=T::$T->LangEdit($uri,'uri-'.$name);
			}
			else
				$uri=Select2::Tags("uris[{$name}]",$values['uris'][$name],
					['id'=>'uri-'.$name]);

			$uri_html.=<<<HTML
									<div class="form-group">
										<label id="label-uri-{$name}" for="uri-{$name}">{$title}</label>
										{$uri}
									</div>
HTML;
		}

		return <<<HTML
								<div class="bcont">{$uri_html}</div>
HTML;

	}

	/** Страница удаления модуля
	 * @param array $module Удаляемый модуль, ключи:
	 *  string title название удаляемого модуля
	 * @param string $back URL возврата */
	public static function Delete($module,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[$GLOBALS['Eleanor']->module['links']['list'], Eleanor::$Language['main']['modules']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$module['title']),$back);
	}
}

Modules::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/modules-*.php',false);

return Modules::class;