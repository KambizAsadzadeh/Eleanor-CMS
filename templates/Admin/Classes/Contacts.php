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
	 * @param array $recipient Перечень пользователей для списка получателей:
	 *  [string name] Логин
	 *  [string _a] Ссылка на пользователя
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param bool $saved Флаг успешного сохранения
	 * @return string */
	public static function Contacts($values,$recipient,$Editor,$Uploader,$errors,$saved)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=modules',Eleanor::$Language['main']['modules']],
			end($GLOBALS['title'])
		];

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		$GLOBALS['scripts'][]='//cdn.jsdelivr.net/sortable/latest/Sortable.min.js';
		$GLOBALS['head']['sortable-table']=<<<HTML
<script>/*<![CDATA[*/$(function(){
	new Sortable($("#recipients tbody").get(0), {
		handle:".pos-lines",
		draggable:"tr",
		onUpdate:function(){
			var order=[];
			$("tbody tr").each(function(){
				order.push( $(this).data("id") );
			});
		}
	});
})//]]></script>
HTML;

		#Errors
		$er_subject=$er_text=$er_def=$er_email='';

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
			elseif($type=='INCORRECT_EMAIL')
				$er_email=$error;
			else
				$er_def.=$error;
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		elseif($saved)
			$er_def=T::$T->Alert(static::$lang['successfully-saved'],'success',true);
		#/Errors

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
			{
				$input['subject'][$lng]=Html::Input("subject[{$lng}]",$values['subject'][$lng],
					['class'=>'form-control need-tabindex input-lg','id'=>'subject-'.$lng,'title'=>$c_lang['subject']]);
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
				'subject'=>Html::Input('subject',$values['subject'],['id'=>'subject','class'=>'form-control need-tabindex input-lg','title'=>$c_lang['subject']]),
				'text'=>$Editor('text',$values['text'],['class'=>'form-control need-tabindex','id'=>'text','rows'=>5]),
				'document_title'=>Html::Input('document_title',$values['document_title'],['class'=>'form-control need-tabindex','id'=>'document-title']),
				'meta_descr'=>Html::Input('meta_descr',$values['meta_descr'],['class'=>'form-control need-tabindex','id'=>'meta-descr']),
				'info'=>$Editor('info',$values['info'],['class'=>'form-control need-tabindex','id'=>'info','rows'=>10]),
			];

		#Получатели
		$Recipient=TableList(3)
			->head(
				['<span class="glyphicon glyphicon-sort"></span>','class'=>'col_pos'],
				[static::$lang['recipient']],
				static::$lang['email'],
				''
			);
		$n=0;

		if(!$values['recipient'])
			$values['recipient']=[''=>Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : ''];

		foreach($values['recipient'] as $k=>$v)
		{
			#Удалим удаленных пользователей
			if(is_int($k) and !isset($recipient[$k]))
				continue;

			$n++;

			if(Eleanor::$vars['multilang'])
			{
				$res_tit=[];

				foreach(Eleanor::$langs as $lng=>$_)
					$res_tit[$lng]=Html::Input("recipient[{$n}][title][{$lng}]",$v[$lng],
						['class'=>'form-control need-tabindex','id'=>"recipient-{$n}-{$lng}"]);

				$res_tit=T::$T->LangEdit($res_tit,'recipient-'.$n);
			}
			else
				$res_tit=Html::Input("recipient[{$n}][title]",$v,['class'=>'form-control need-tabindex','id'=>'recipient-'.$n]);

			$author=T::$T->Author(isset($recipient[$k]) ? [$recipient[$k]['name'],$k,$recipient[$k]['_a']] : $k,
				['name'=>"recipient[{$n}][email]",'class'=>'form-control need-tabindex','placeholder'=>$c_lang['email-or-user'],'title'=>$c_lang['email-or-user']]);

			$Recipient->item(
				['<span class="pos-lines"><i></i><i></i><i></i></span>','col_pos'],
				$res_tit,
				<<<HTML
<div class="form-group has-feedback author cloneable">{$author}</div>
HTML
,<<<HTML
<button type="button" class="ibtn ib-delete need-tabindex pull-right">
	<i class="ico-del"></i><span class="thd">{$t_lang['delete']}</span>
</button>
HTML
			);
		}

		$Recipient->s.=<<<HTML
<tfoot data-last="{$n}">
	<tr>
		<td colspan="4" class="text-center"><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close" id="add-contact">{$c_lang['add-contact']}</button></td>
	</tr>
</tfoot>
HTML;

		$Recipient->end();
		#/Получатели

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
					{$er_email}
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#recipients">{$c_lang['recipients']}</p>
						<div id="recipients" class="collapse in">
							{$Recipient}
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
$("#recipients").on("click",".ib-delete",function(){
	var tr=$(this).closest("tr");

	if($("#recipients .ib-delete").size()>1)
		tr.remove();
	else
		tr.find(":input").val("");
});

var n=$("#recipients tfoot").data("last");
$("#add-contact").click(function(){
	n++;

	$("#recipients tr:has(.ib-delete):first").clone(true,true)
		.find(":input").val("").end()
		.find("[id]").attr("id",function(i,v){
			return v.replace(/\-\d+\-/,"-"+n+"-");
		}).end()
		.find("[name]").attr("name",function(i,v){
			return v.replace(/\[\d+\]/,"["+n+"]");
		}).end()
		.find("a[href^='#']").attr("href",function(i,v){
			return v.replace(/\-\d+\-/,"-"+n+"-");
		}).end()
	.appendTo("#recipients tbody")
		.find(".cloneable").trigger("clone");
});
})</script>
HTML;
	}
}
Contacts::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/contacts-*.php',false);

return Contacts::class;