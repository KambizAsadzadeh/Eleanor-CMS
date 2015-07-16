<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
use CMS\Eleanor, Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

/** Визуальное выделение опредленного участка в теге [code] */
class Marker extends \CMS\Abstracts\OwnBbCode
{
	public static
		/** @var string Название шаблона маркера */
		$template='Marker',

		/** @var string Цвет выделяемого текста */
		$color='red',

		/** @var string Цвет фона */
		$background='yellow';

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? \Eleanor\Classes\Strings::ParseParams($p) : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		if(!isset($p['color']) or preg_match('%^[#a-z0-9\-]+$%i',$p['color'])==0)
			$p['color']=static::$color;

		if(!isset($p['background']) or preg_match('%^[#a-z0-9\-]+$%i',$p['background'])==0)
			$p['background']=static::$background;

		try
		{
			$tpl=static::$template;
			return Eleanor::$Template->$tpl($c,$p);
		}
		catch(EE$E)
		{
			return <<<HTML
<span style="color:{$p['color']};background-color:{$p['background']}">{$c}</span>
HTML;
		}
	}
}

return Marker::class;