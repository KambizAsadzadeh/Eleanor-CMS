<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use CMS\Eleanor, Eleanor\Classes\EE, Eleanor\Classes\Html;

/** Вставка цитаты */
class Quote extends \CMS\Abstracts\OwnBbCode
{
	/** @var string Название шаблона цитаты */
	public static $template='Quote';

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? \Eleanor\Classes\Strings::ParseParams($p,'t') : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		unset($p['noparse']);

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		try
		{
			$tpl=static::$template;
			return Eleanor::$Template->$tpl($c,$p);
		}
		catch(EE$E)
		{
			$p=isset($p['cite']) ? Html::TagParams(['cite'=>$p['cite']]) : '';
			return"<blockquote{$p}>{$c}</blockquote>";
		}
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		$c=preg_replace("#^(\r?\n?<br />\r?\n?)+#i",'<br />',$c);
		$c=preg_replace("#(\r?\n?<br />\r?\n?)+$#i",'<br />',$c);

		return parent::PreSave($t,$p,$c,$cu);
	}
}

return Quote::class;