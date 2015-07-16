<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use CMS\Eleanor, Eleanor\Classes\EE;

/** Вставка скрытого (обычно от гостей) участка текста на странице */
class Hide extends \CMS\Abstracts\OwnBbCode
{
	/** @var string Название шаблона скрытого текста */
	public static $template='HiddenText';

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		if(strpos($p,'noparse')!==false)
			return"[{$t}]{$c}[/{$t}]";

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		$p=$p ? \Eleanor\Classes\Strings::ParseParams($p,'g') : [];

		if(isset($p['g']) ? array_intersect(explode(',',$p['g']),\CMS\GetGroups()) : Eleanor::$Login->IsUser())
			return$c;

		$l=Eleanor::$Language['ownbb'];

		try
		{
			$tpl=static::$template;
			return Eleanor::$Template->$tpl($l['hidden']);
		}
		catch(EE$E)
		{
			return"[{$l['hidden']}]";
		}
	}
}

return Hide::class;