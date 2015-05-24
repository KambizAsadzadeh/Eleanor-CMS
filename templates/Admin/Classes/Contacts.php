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
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param bool $saved Флаг успешного сохранения
	 * @return string */
	public static function Contacts($values,$Editor,$Uploader,$errors,$saved)
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

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['subject'][$lng]=Html::Input("subject[{$lng}]",$values['subject'][$lng],
					['class'=>'form-control need-tabindex input-lg','id'=>'subject-'.$lng]);
				$input['text'][$lng]=$Editor("text[{$lng}]",$values['text'][$lng],
					['class'=>'form-control need-tabindex','id'=>'text-'.$lng,'rows'=>5]);
				$input['document_title'][$lng]=Html::Input("document_title[{$lng}]",$values['document_title'][$lng],
					['class'=>'form-control need-tabindex','id'=>'docuemnt-title-'.$lng]);
				$input['meta_descr'][$lng]=Html::Input("meta_descr[{$lng}]",$values['meta_descr'][$lng],
					['class'=>'form-control need-tabindex','id'=>'meta-descr-'.$lng]);
				$input['info'][$lng]=$Editor("info[{$lng}]",$values['info'][$lng],
					['class'=>'form-control need-tabindex','id'=>'info-'.$lng,'rows'=>10]);
			}

			$input['subject']=T::$T->LangEdit($input['subject'],'subject');
			$input['text']=T::$T->LangEdit($input['text'],'text');
			$input['document_title']=T::$T->LangEdit($input['document_title'],'document-title');
			$input['meta_descr']=T::$T->LangEdit($input['meta_descr'],'meta-descr');
			$input['info']=T::$T->LangEdit($input['info'],'info');
		}
		else
			$input=[
				'subject'=>Html::Input('subject',$values['subject'],['id'=>'subject','class'=>'form-control need-tabindex input-lg']),
				'text'=>$Editor('text',$values['text'],['class'=>'form-control need-tabindex','id'=>'text','rows'=>5]),
				'document_title'=>Html::Input('document_title',$values['document_title'],['class'=>'form-control need-tabindex','id'=>'document-title']),
				'meta_descr'=>Html::Input('meta_descr',$values['meta_descr'],['class'=>'form-control need-tabindex','id'=>'meta-descr']),
				'info'=>$Editor('info',$values['info'],['class'=>'form-control need-tabindex','id'=>'info','rows'=>10]),
			];

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$er_subject}
						{$input['subject']}
						<br />
						<div class="form-group">
							<label id="label-text" for="text">{$c_lang['text']}</label>
							{$er_text}
							{$input['text']}
						</div>
						<div class="alert alert-info">{$c_lang['vars']}</div>
					</div>
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#info">{$c_lang['info']}</p>
						<div id="info" class="collapse in">
							<div class="bcont">
								{$input['info']}
							</div>
						</div>
					</div>
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#recipients">{$c_lang['recipients']}</p>
						<div id="recipients" class="collapse in">
							<div class="bcont">
								<div class="form-group">

								</div>
							</div>
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
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					<button type="submit" class="btn btn-success need-tabindex"><b>{$t_lang['save']}</b></button>
					<button type="button" class="btn btn-primary pull-right" id="modal-uploader-trigger">{$t_lang['uploader']}</button>
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){
$("#modal-uploader-trigger").click(DraggableModal($("#modal-uploader")));
})</script>
HTML;
	}
}
Contacts::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/contacts-*.php',false);

return Contacts::class;