<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use CMS;

/** Вставка HTML разметки (без фильтрации) в редактор */
class Html extends CMS\Abstracts\OwnBbCode
{
	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		if(strpos($p,'noparse')!==false)
		{
			$c=htmlspecialchars($c, CMS\ENT, \Eleanor\CHARSET, false);

			return"[{$t}]{$c}[/{$t}]";
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		return$c;
	}
}

return Html::class;