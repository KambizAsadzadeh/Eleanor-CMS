<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Генератор динамических ссылок */
class DynUrl extends \Eleanor\Classes\Url
{
	/** @var string Базовый префикс URL-ов, содержит исполняемый файл, знак ?, идентификатор языка */
	public static $base='';

	/** @var string Префикс для всех генерируемых URL */
	public $prefix='';

	/** Конструктор
	 * @param string|null $prefix Префикс для всех генерируемых URL */
	public function __construct($prefix=null)
	{
		$this->prefix=$prefix ? $prefix : static::$base;
	}

	/** Конструктор URL-ов
	 * @param array $query Параметры запроса
	 * @return string */
	public function __invoke(array$query=[])
	{
		$query=static::Query($query);
		return$query ? $this->prefix.$query : $this->__toString();
	}

	/** Получение префикса ссылки (метод вырезает все &amp; и ? в конце нее)
	 * @return string */
	public function __toString()
	{
		return preg_replace('#(&amp;|\?|&)$#','',$this->prefix);
	}
}

DynUrl::$base=basename($_SERVER['PHP_SELF']).'?';