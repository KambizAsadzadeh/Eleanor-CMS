<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Types;

defined('CMS\STARTED')||die;

/** Шаблоны загрузчика файлов */
class Uploader
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Шаблон загрузчика
	 * @param string $query Путь, куда отправлять AJAX запросы
	 * @param array $commands Доступные команды управления файлами. Описание в классе Uploader->commands
	 * @param int $maxupload Максимальный размер размер файлов, загружаемых за раз файлов
	 * @param int $maxfiles Максимальное число файлов, загружаемых за раз
	 * @param array $types Файлы, доступные для загрузки
	 * @param string $sid Идентификатор сессии
	 * @param string $current Текущие каталог
	 * @param string $uniq Флаг уникальности
	 * @return string */
	public static function Uploader($query,$commands,$maxupload,$maxfiles,$types,$sid,$current,$uniq)
	{
		$GLOBALS['head']['fancybox.css']='<link rel="stylesheet" href="//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.css" type="text/css" media="screen" />';

		array_push($GLOBALS['scripts'],
			T::$http['static'].'js/uploader.js',
			'//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.pack.js'
		);

		$c_lang=static::$lang;
		$t_lang=T::$lang;
		$commands=array_keys($commands,true);
		$commands=$commands ? '"'.join('","',$commands).'"' : '';
		$accept='';

		foreach($types as $v)
			$accept.=Types::MimeTypeByExt($v).',';

		$accept=$accept ? 'accept="'.rtrim($accept,',').'"' : '';
		$types=$types ? '"'.join('","',$types).'"' : '';
		$limits=$maxupload===false ? '' : '<p class="alert alert-warning" data-ng-hide="can_upload">'.static::$lang['limits-exceed'].'</p>';

		return<<<HTML
<!-- Загрузчик -->
		<div id="uploader{$uniq}" class="el-uploader ng-cloak" data-ng-module="Admin" data-ng-controller="UploaderFiles">
			<div class="upl-loadframe" data-ng-show="can_upload">
				<div class="text-right small" data-ng-show="progress>0"><a href="#" data-ng-click="Cancel();\$event.preventDefault()">{$t_lang['cancel']}</a></div>
				<progressbar class="progress-striped active" data-ng-show="progress>0" value="progress" type="success"><b>{{progress}}%</b></progressbar>
				<input type="file" style="display:none" multiple {$accept}/>
				<button type="button" class="btn btn-primary"><b>{$c_lang['select-files']}</b></button>
				<p class="upl-loadframe-text">
					<b>{$c_lang['drag-and-drop']}</b><br>
					<small data-ng-controller="UploaderFilesHelper">{{Status(\$parent.max_upload,\$parent.max_files)}}</small>
				</p>
			</div>
			{$limits}
			<div class="upl-top">
				<b>{$c_lang['available']}</b>
				<button class="ubtn" type="button" data-ng-if="commands.indexOf('update')>-1" title="{$c_lang['update']}" data-ng-click="Go()"><i class="ico-reload"></i></button>
				<button class="ubtn" type="button" data-ng-if="commands.indexOf('create_folder')>-1" title="{$c_lang['create-folder']}" data-ng-click="CreateFolder()"><i class="ico-addfolder"></i></button>
				<button class="ubtn" type="button" data-ng-if="commands.indexOf('create_file')>-1" title="{$c_lang['create-file']}" data-ng-click="CreateFile()"><i class="ico-plus"></i></button>
				<button class="ubtn" type="button" data-ng-if="commands.indexOf('watermark')>-1" title="Watermark" data-ng-class="{'ubtn-check':!watermark}" data-ng-click="watermark=!watermark"><i class="ico-watermark"></i></button>
			</div>
			<div class="upl-files-box" data-ng-show="dirs.length>0 || files.length>0 || pathway.length>0">
				<div class="upl-breadcrumb" data-ng-if="pathway.length>0">
					<a class="upl-back" title="{$c_lang['go-back']}" href="#" data-ng-click="Go('..');\$event.preventDefault()"><i class="ico-arrow-left"></i></a>
					<ul>
						<li data-ng-repeat-start="item in pathway" data-ng-if="!\$last"><a href="#" data-ng-click="Go(item.jump);\$event.preventDefault()">{{item.name}}</a> </li>
						<li data-ng-repeat-end data-ng-if="\$last">{{item.name}}</li>
					</ul>
				</div>
				<div class="upl-files-list" data-ng-show="dirs.length>0 || files.length>0" data-ng-controller="UploaderFilesHelper">
					<div class="fl-item" data-ng-repeat="dir in dirs">
						<div class="fl-item-inn">
							<div class="fl-icon fl-folder" data-ng-click="GoHelp(\$event,\$parent.Go,dir.name)">
								<!-- edit -->
								<div class="min-edit" data-ng-if="dir.commands.length>0">
									<button class="dropdown-toggle" data-toggle="dropdown" type="button"><i class="ico-setting"></i></button>
									<ul class="dropdown-menu menu-fix-1">
										<li data-ng-if="dir.commands.indexOf('rename')>-1"><a href="#" data-ng-click="Rename(dir,\$index);\$event.preventDefault()">{$c_lang['rename']}</a></li>
										<li data-ng-if="dir.commands.indexOf('delete')>-1"><a href="#" data-ng-click="Delete(dir,\$index);\$event.preventDefault()">{$c_lang['delete']}</a></li>
									</ul>
								</div>
								<!-- edit [e] -->
							</div>
							<span class="fl-name">{{dir.name}}</span><span class="fl-type">{$c_lang['folder']}</span>
						</div>
					</div>
					<div class="fl-item" data-ng-repeat="file in files" data-ng-init="_class=Class(file.name);_bg_image=BgImage(file);_preview=Preview(file)">
						<div class="fl-item-inn">
							<div class="fl-icon" data-ng-class="_class" data-ng-click="InsertHelp(\$event,\$parent.Insert,file,\$index)" data-ng-style="_bg_image">
								<!-- edit -->
								<div class="min-edit" data-ng-if="file.commands.length>0">
									<button class="dropdown-toggle" data-toggle="dropdown" type="button"><i class="ico-setting"></i></button>
									<ul class="dropdown-menu menu-fix-1">
										<li data-ng-if="file.commands.indexOf('attach')>-1"><a href="#" data-ng-click="Insert(file,true,\$index,\$event);\$event.preventDefault()">{$c_lang['as-object']}</a></li>
										<li data-ng-if="_preview"><a href="{{file.http||file.download}}" class="uploader-fancybox" data-fancybox-group="uploader{$uniq}" data-fancybox-title="{{file.name}}">{$c_lang['preview']}</a></li>
										<li data-ng-if="!_preview"><a href="#" data-ng-click="Download(file,\$index);\$event.preventDefault()">{$c_lang['download']}</a></li>
										<li data-ng-if="file.commands.indexOf('rename')>-1"><a href="#" data-ng-click="Rename(file,\$index);\$event.preventDefault()">{$c_lang['rename']}</a></li>
										<li data-ng-if="file.commands.indexOf('delete')>-1"><a href="#" class="fancybox-delete" data-ng-click="Delete(file,\$index);\$event.preventDefault()">{$c_lang['delete']}</a></li>
										<li data-ng-if="file.commands.indexOf('edit')>-1"><a href="#" data-ng-click="Edit(file,\$index);\$event.preventDefault()">{$c_lang['edit']}</a></li>
									</ul>
								</div>
								<!-- edit [e] -->
							</div>
							<span class="fl-name">{{file.name.split(".").slice(0,-1).join(".")}}</span><span class="fl-type">.{{file.name.split(".").slice(-1).join("")}}</span>
						</div>
					</div>
				</div>
			</div>
			<pagination data-ng-show="total>pp" data-total-items="total" data-ng-model="page" data-items-per-page="pp" data-max-size="7" data-num-pages="pages" data-ng-change="Go()" direction-links="false" class="pagination-sm"></pagination>
		</div>
<script data-keep-on>$(function(){
	angular.element( $("#uploader{$uniq} :file")[0] ).scope().Constructor({
		query:"{$query}",
		commands:[{$commands}],
		max_uploads:{$maxupload},
		max_files:{$maxfiles},
		types:[{$types}],
		sid:"{$sid}",
		uniq:"{$uniq}",
		current:"{$current}",
		pp:70,
		preview:[64,64],
		CreateEdit:function(r,Save,Cancel){
			$("body").append(r.modal);

			var modal=$("#create-edit-file").removeAttr("id"),
				iframe=modal.find("iframe"),
				w=iframe.get(0).contentWindow,
				d=w.document;

			d.open("text/html","replace");
			d.write(r.html);
			d.close();

			modal.on("shown.bs.modal",function(){
				iframe.height($("html",d).height());
			}).modal('show').on("hidden.bs.modal",function(){
				Cancel();
				modal.remove();
			}).on("click",".btn-primary",function(){
				Save(w.EDITOR.Get("editor"),function(){ modal.modal('hide'); });
			});
		}
	});
	$(".uploader-fancybox").fancybox({
		afterShow:function(){
			var a=this.element;

			$('<a href="#" class="child glyphicon glyphicon-trash" style="float: left; color: red;" title="{$c_lang['delete']}"> </a>')
				.click(function(e){
					e.preventDefault();
					angular.element( a.closest("ul").find("a.fancybox-delete")[0] ).triggerHandler('click');

					if($.fancybox.current.group.length>1)
					{
						$.fancybox.current.group.splice($.fancybox.current.index,1);

						if($.fancybox.current.index>$.fancybox.current.group.length)
						{
							$.fancybox.current.index--;
							$.fancybox.prev();
						}
						else
							$.fancybox.next();
					}
					else
						$.fancybox.close();
				}).prependTo( $.fancybox.skin.find(".fancybox-title") );

			/*var alt = this.element.find('img').attr('alt');
			this.inner.find('img').attr('alt', alt);
			this.title = alt;*/
            /* Add watermark */
            /*$('<div class="watermark"></div>')
                .prependTo( $.fancybox.inner ); */
	    }
	});
})</script>
<!-- Загрузчик [E] -->
HTML;
	}

	/** Интерфейс создания / правки файла
	 * @param \Eleanor\Classes\StringCallback $Editor Редактор
	 * @param string $filename Имя файла
	 * @param string $content Содержимое редактора
	 * @return array */
	public static function CreateEditFile($Editor,$filename,$content)
	{
		include_once __DIR__.'/../../html.php';

		$c_lang=static::$lang;
		$editor=$Editor('editor',$content,['tabindex'=>1]);
		$head=\CMS\Templates\GetHead(false,false);

		return[
			'modal'=><<<HTML
<!-- Modal -->
<div class="modal fade" tabindex="-1" id="create-edit-file" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">{$filename}</h4>
			</div>
			<div class="modal-body">
				<iframe style="width:100%;border:0"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{$c_lang['cancel']}</button>
				<button type="button" class="btn btn-primary">{$c_lang['save']}</button>
			</div>
		</div>
	</div>
</div>
HTML
,
			'html'=><<<HTML
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap.min.css" type="text/css">
	<script src="//cdn.jsdelivr.net/g/jquery,bootstrap@3"></script>
	{$head}
</head>
<body>{$editor}</body>
</html>
HTML
			,
		];
	}
}
Uploader::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/uploader-*.php',false);

return Uploader::class;