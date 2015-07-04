<?php
/**
	Eleanor CMS © 2015
	http://elanor-cms.ru
	info@eleanor-ecms.ru
*/
namespace CMS\Templates\Uniel;
use CMS\Eleanor, Eleanor\Classes\Html;

include_once __DIR__.'/../../html.php';

/** Шаблон для пользователей модуля "обратная связь" */
class Contacts
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Основная страница обратной связи
	 * @param string $info Текстовая информация для связи
	 * @param int|bool $canupload Максимальный размер загружаемых файлов в байтах, false - загружать файлы нельзя
	 * @param array $recipient Получатели
	 * @param array $values Значения полей формы:
	 *  [string subject] Тема письма
	 *  [string message] Текст письма
	 *  [int recipient] ID получателя
	 *  [string session] ID сессии (hidden поле)
	 *  [string|null from] e-mail отправителя, ключ присутствует только если пользователь не залогинен (гость на сайте)
	 * @param array $errors Ошибки формы
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Interfaces\Captcha | \Eleanor\Interfaces\Captcha_Image | null $captcha Капча
	 * @return string */
	public static function Contacts($info,$maxupload,$recipient,$values,$errors,$Editor,$captcha)
	{
		$content=T::$T->Menu([
			'title'=>is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title'],
		]);

		if($info)
			$content.=T::$T->OpenTable().\CMS\Templates\Content($info).T::$T->CloseTable();

		if($recipient)
		{
			if($errors)
			{
				foreach($errors as $k=>&$v)
					if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
						$v=static::$lang[$v];

				$content.=T::$T->Message($errors,'error');
			}

			$rec='';
			if(count($recipient)>1)
				foreach($recipient as $k=>&$v)
					$rec.=Html::Option($v,$k,$k==$values['recipient']);

			$c_lang=static::$lang;
			$enctype=$maxupload ? ' enctype="multipart/form-data"' : '';
			$ti=0;
			$guest=isset($values['from']);
			$input=[
				'from'=>$guest ? Html::Input('from',$values['from'],['type'=>'email','tabindex'=>++$ti]) : null,
				'recipient'=>$rec ? Html::Select('whom',$rec,['tabindex'=>++$ti]) : null,
				'subject'=>Html::Input('subject',$values['subject'],['tabindex'=>++$ti,'required'=>true]),
				'message'=>$Editor('message',$values['message'],['tabindex'=>++$ti,'required'=>true]),
				'file'=>$maxupload ? Html::Input('file[]',false,['type'=>'file','tabindex'=>++$ti,'multiple'=>true]) : null,
				'button'=>Html::Input('session',$values['session'],['type'=>'hidden']).Html::Button(static::$lang['send'],'submit',['tabindex'=>++$ti]),
			];
			$max_text=is_bool($maxupload) ? '' : '<br /><span class="small">'.sprintf(static::$lang['max-upload%'],\Eleanor\Classes\Files::BytesToSize($maxupload)).'</span>';

			$from=$guest ? <<<HTML
<tr>
	<td class="class">{$c_lang['email']}<br /><span class="small">{$c_lang['email_']}</span></td>
	<td>{$input['from']}</td>
</tr>
HTML
				: '';
			$recipient=$rec ? <<<HTML
<tr>
	<td class="class">{$c_lang['whom']}</td>
	<td>{$input['recipient']}</td>
</tr>
HTML
				: '';
			$file=$maxupload ? <<<HTML
<tr>
	<td class="class">{$c_lang['files']}{$max_text}</td>
	<td>{$input['file']}</td>
</tr>
HTML
				: '';

			if($captcha)
				$captcha=<<<HTML
<tr>
	<td class="class">{$c_lang['captcha']}<br /><span class="small">{$c_lang['captcha_']}</span></td>
	<td class="captcha">{$captcha}</td>
</tr>
HTML;

			$content.=<<<HTML
<form id="contacts-form" method="post"{$enctype}>
<table class="tabstyle tabform">{$from}{$recipient}
<tr>
	<td class="class">{$c_lang['subject']}</td>
	<td>{$input['subject']}</td>
</tr>
<tr>
	<td class="class">{$c_lang['message']}</td>
	<td>{$input['message']}</td>
</tr>{$file}{$captcha}
</table>
<div class="submitline">{$input['button']}</div>
</form>
HTML;

			if($maxupload)
				$content.=<<<HTML
<script>$(function(){
	$("#contacts-form").submit(function(e){
		var i,current=0;

		$(":file",this).each(function(){
			for(i in this.files)
				current+=this.files[i].size;
		});

		if(current>{$maxupload})
		{
			e.preventDefault();
			alert("{$c_lang['FILES_TOO_BIG']}");
		}
	});
})</script>
HTML;
		}

		return$content;
	}

	/** Страница с информацией о том, что сообщение успешно отправлено
	 * @param array $links Ссылки
	 *  [string send] Ссылка на "отправить еще сообщение"
	 * @return string */
	public static function Sent($links)
	{
		return T::$T->Menu([
			'title'=>is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title'],
		])->Message(sprintf(static::$lang['sent%'],$links['send']),'info');
	}
}
Contacts::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/contacts-*.php',false);

return Contacts::class;