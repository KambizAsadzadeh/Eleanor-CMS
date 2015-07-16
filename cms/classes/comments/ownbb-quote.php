<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use CMS\Eleanor, Eleanor\Classes\EE;

/** Цитата для комментариев */
class CommentsQoute extends \CMS\Abstracts\OwnBbCode
{
	public static
		/** @var Callable Генерации ссылки на цитируемый комментарий */
		$findlink,

		/** @var string Название шаблона цитаты */
		$template='CommentsQuote';

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? \Eleanor\Classes\Strings::ParseParams($p,'c') : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		$id=isset($p['c']) ? (int)$p['c'] : false;
		$fl=static::$findlink;
		$tpl=static::$template;

		try
		{
			return Eleanor::$Template->$tpl([
				'date'=>isset($p['date']) ? $p['date'] : null,
				'name'=>isset($p['name']) ? $p['name'] : null,
				'id'=>$id,
				'find'=>$id&&is_callable($fl) ? call_user_func($fl, $id) : null,
				'text'=>$c,
			]);
		}
		catch(EE$E)
		{
			return"<blockquote>{$c}</blockquote>";
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