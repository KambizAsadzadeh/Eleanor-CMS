<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;

/** Вставка в текст исполняемого PHP кода */
class PHP extends \CMS\Abstracts\OwnBbCode
{
	/** @var string Входные параметры генерируемой функции */
	public static $input;

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		if(strpos($p,'noparse')===false)
		{
			ob_start();

			$F=create_function('$params,$input',$c);

			if(!$F)
			{
				$r='['.$t.']'.ob_get_contents().'[/'.$t.']';
				ob_end_clean();
				return $r;
			}

			$c=$F($p,self::$input);

			$c.=ob_get_contents();
			ob_end_clean();

			return$c;
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		return"[{$t}]{$c}[/{$t}]";
	}
}

return PHP::class;