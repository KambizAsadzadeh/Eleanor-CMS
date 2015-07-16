<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
use CMS\Eleanor, Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

/** Вставка скрытого по умолчанию текста, который проявляется если пользователь того захочет */
class Spoiler extends \CMS\Abstracts\OwnBbCode
{
	/** @var string Название шаблона скрытого текста */
	public static $template='Spoiler';

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

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		try
		{
			$tpl=static::$template;
			return Eleanor::$Template->$tpl($c,$p);
		}
		catch(EE$E)
		{
			return"<!-- {$c} -->";
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

return Spoiler::class;