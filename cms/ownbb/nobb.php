<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;

/** Внутри этого тега, обработка всех BB кодов отключается */
class NoBB extends \CMS\Abstracts\OwnBbCode
{
	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		if(strpos($p,'noparse')===false)
			return$c;

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		return"[{$t}]{$c}[/{$t}]";
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		$c=\Eleanor\Classes\SafeHtml::Make($c);
		return parent::PreSave($t,$p,$c,$cu);
	}
}

return NoBB::class;