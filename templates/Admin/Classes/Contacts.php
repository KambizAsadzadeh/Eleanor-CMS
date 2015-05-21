<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

/** Админка модуля обратной связи */
class Contacts
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Основная страница правки обратной связи
	 * @param array $values Значения полей формы:
	 *  [string|array info] Информация для страницы обратной связи
	 *  [array recipient] Возможные получатели письма
	 *  [string|array subject] Формат темы получаемого письма
	 *  [string|array text] Формат текста письма
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param array $errors Ошибки формы
	 * @param bool $saved Флаг успешного сохранения
	 * @return string */
	public static function Contacts($values,$Editor,$errors,$saved)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			end($GLOBALS['title'])
		];

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		#Errors
		$er_subject=$er_text=$er_def='';

		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error))
			{
				$type=$error;
				if(isset(static::$lang[$error]))
					$error=static::$lang[$error];
			}

			$error=T::$T->Alert($error,'danger',true);;

			if(strpos($type,'EMPTY_SUBJECT')===0)
				$er_subject=$error;
			elseif(strpos($type,'EMPTY_TEXT')===0)
				$er_text=$error;
			else
				$er_def.=$error;
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

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
}
Contacts::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/static-*.php',false);

return Contacts::class;