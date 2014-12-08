<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Abstracts;
defined('CMS\STARTED')||die;
use \CMS\Eleanor, \Eleanor\Classes\Strings;

/** Class OwnBbCode. Функции, которым для обработки необходимо передать весь массив текста - каждый дочерний класс
 * должен содержать следующие методы:
 * public static function TotalPreSave($s,$ts,$cu){ return$s; }
 * public static function TotalPreEdit($s,$ts,$cu){ return$s; }
 * public static function TotalPreDisplay($s,$ts,$cu){ return$s; }
 * параметры:
 *  string $s Весь текст
 *  array $ts Массив тегов, которые необходимо обрабатывать
 *  bool $cu Флаг возможности использования тега
 * @package CMS\Abstracts
 */
abstract class OwnBbCode extends \Eleanor\BaseClass
{
	/** Флаг одиночного тега. По умолчанию все теги являются двойными */
	const SINGLE=false;

	/** Вывод заглушки, в случае когда использование тега запрещено ограничениями группы */
	public static function RestrictDisplay()
	{
		return Eleanor::$Template->RestrictedSection(Eleanor::$Language['ownbb']['restrict']);
	}

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @return string */
	public static function PreDisplay($t,$p,$c)
	{
		return$c;
	}

	/** Обработка информации перед её правкой
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @return string */
	public static function PreEdit($t,$p,$c)
	{
		return static::PreSave($t,$p,$c,true);
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		if(!is_array($p))
			$p=$p ? Strings::ParseParams($p,$t) : [];

		$tp=isset($p[$t]) ? '' : ' ';

		if(!$cu or isset($p['noparse']))
		{
			unset($p['noparse']);
			$cu=false;
		}

		foreach($p as $k=>$v)
		{
			if($v==$k)
			{
				$tp.=$k.' ';
				continue;
			}

			if(strpos($v,' ')===false)
				$q=$v;
			elseif(strpos($v,'\'')===false)
				$q='"'.$v.'"';
			elseif(strpos($v,'"')===false)
				$q='\''.$v.'\'';
			else
				$q='"'.str_replace('"','&quot;',$v).'"';

			if($k==$t)
				$tp='='.$q.' '.$tp;
			else
				$tp.=$k.'='.$q;

			$tp.=' ';
		}

		return'['.$t.rtrim($tp).($cu ? '' : ' noparse').']'.$c.(static::SINGLE ? '' : '[/'.$t.']');
	}
}