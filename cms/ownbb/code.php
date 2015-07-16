<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use CMS\Eleanor, Eleanor\Classes\EE, Eleanor\Classes\Strings, CMS\OwnBB;

/** Вставка кода на страницу с подсветкой синтаксиса */
class Code extends \CMS\Abstracts\OwnBbCode
{
	/** @var string Название шаблона цитаты */
	public static $template='Code';

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		try
		{
			$tpl=static::$template;
			return Eleanor::$Template->$tpl($c,$p);
		}
		catch(EE$E)
		{
			return"<pre><code>{$c}</code></pre>";
		}
	}

	/** Обработка информации перед её правкой
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreEdit($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : [];

		if(isset($p[$t]) and $p[$t]=='no-highlight')
			unset($p[$t]);

		if(!empty(OwnBB::$opts['visual']))
		{
			$c=str_replace("\t",'    ',$c);
			$c=str_replace(' ','&nbsp;',$c);
			$c=nl2br($c);
		}
		else
			$c=htmlspecialchars_decode($c,\CMS\ENT);

		return parent::PreSave($t,$p,$c,true);
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		if(!empty(OwnBB::$opts['visual']))
		{
			$c=preg_replace('#<br ?/?>#i',"\n",$c);
			$c=strip_tags($c,'<span><a><img><input><b><i><u><s><em><strong>');
		}
		else
			$c=htmlspecialchars($c,\CMS\ENT,\Eleanor\CHARSET);

		$c=\Eleanor\Classes\SafeHtml::Make($c);

		return parent::PreSave($t,$p,$c,$cu);
	}
}

return Code::class;