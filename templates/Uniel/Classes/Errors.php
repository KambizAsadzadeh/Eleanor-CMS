<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Uniel;
use CMS\Eleanor, Eleanor\Classes\Html;

include_once __DIR__.'/../../html.php';

/** Шаблон для публичной части системного модуля страниц ошибок */
class Errors
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Вывод страницы ошибки
	 * @param array $error Ошибка, ключи:
	 *  [string http_code] HTTP код ошибки
	 *  [array miniature] Миниатюра-логотип
	 *  [string email] E-mail для обратной связи
	 *  [string title] Название
	 *  [string text] Содержимое страницы
	 * @param bool $sent Флаг успешной отправки сообщения
	 * @param array $values Значения полей формы:
	 *  [string text] Текст сообщения
	 *  [string name] Имя. Только для гостя
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param string $captcha Капча
	 * @return string */
	public static function ShowError($error,$sent,$values,$Editor,$errors,$back,$captcha)
	{
		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];
		unset($v);

		if($sent)
			$tosend='<hr /><br />'.T::$T->Message(static::$lang['sent'],'info');
		elseif($error['email'])
			$tosend='<hr />'.($errors ? T::$T->Message($errors,'error') : '')
				.'<form method="post">'
				.(isset($values['name'])
					? '<div class="errorinput"><span>'.static::$lang['yourname'].'</span><br />'
						.Html::Input('name',$values['name'] || $errors ? $values['name'] : static::$lang['guest']).'</div><br />'
					: '')
				.'<div class="errorinput"><span>'
				.static::$lang['tell_us'].'</span><br />'.$Editor('text',$values['text']).'</div>'
				.($back ? Html::Input('back',$back,['type'=>'hidden']) : '')
				.($captcha ? '<br /><div class="errorinput"><span>'.static::$lang['captcha'].'</span><br /><span class="small">'.static::$lang['captcha_'].'</span><br />'.$captcha.'</div>' : '')
				.'<div style="text-align:center;"><a href="#" onclick="$(this).closest(\'form\').submit();return false;" class="button">'.static::$lang['send'].'</a></div></form>';
		else
			$tosend='';

		$back=$back ? '<a href="'.$back.'"><b>'.static::$lang['back'].'</b></a><br />' : '';
		$error['text']=\CMS\Templates\Content($error['text']);
		$image=$error['miniature'] ? <<<HTML
<img style="float:left;margin-right:10px;" src="{$error['miniature']['http']}" alt="{$error['title']}" title="{$error['title']}" />
HTML
		: '';

		return<<<HTML
<div class="base">
	<div class="heading2">
		<div class="binner">
			<h6>{$error['title']}</h6>
			<div class="clr"></div>
		</div>
	</div>
	<div class="maincont">
		<div class="binner">{$image}
			{$error['text']}
			<div class="clr"></div>
			{$tosend}
			<div class="clr"></div>
		</div>
	</div>
	<div class="morelink">
		<div class="binner">
			{$back}
			<div class="clr"></div>
		</div>
	</div>
</div>
HTML;
	}
}
Errors::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/errors-*.php',false);

return Errors::class;