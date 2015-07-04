<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Генератор статических ссылок */
class Url extends \Eleanor\Classes\Url
{
	/** @var string Базовый префикс URL-ов, содержит идентификатор языка */
	public static $base='';

	public
		/** @var array необработанные части Url-a, разбитые через / */
		$parts,

		/** @var string Префикс для всех генерируемых URL */
		$prefix='';

	/** @param bool $parsereq Флаг обработки запроса пользователя */
	public function __construct($parsereq=true)
	{
		$parts=$parsereq ? static::ParseRequest() : false;
		$this->parts=$parts ? explode('/',$parts) : [];
	}

	/** Конструктор URL-ов
	 * @param array|string $static Статическая часть ссылки
	 * @param string $ending Окончание ссылки
	 * @param array $query request часть ссылки
	 * @return string */
	public function __invoke($static=[],$ending='',array$query=[])
	{
		return$this->prefix.static::Make((array)$static,$ending,$query);
	}

	/** Возврат префикса */
	public function __toString()
	{
		return rtrim($this->prefix,'/');
	}

	/** Преобразование строки в корректную последовательность символов для возможности использования её в URI
	 * @param string $s Входящая строка
	 * @param string|null $l Язык транслитерации, в случае передачи null, используется текущей язык систмы
	 * @param string|null $rep Последовательность символов, которыми будут заменены пробелы
	 * @return string */
	public static function Filter($s,$l=null,$rep=null)
	{
		if(!$l)
			$l=Language::$main;

		if(Eleanor::$vars['trans_uri'] and method_exists('Eleanor\\Classes\\Language\\'.$l,'Translit'))
			$s=call_user_func(['Eleanor\\Classes\\Language\\'.$l,'Translit'],$s);

		if($rep===null)
			$rep=Eleanor::$vars['url_rep_space'];

		$qrep=preg_quote($rep,'#');
		$s=html_entity_decode($s,ENT,\Eleanor\CHARSET);
		$s=str_replace('&','and',$s);
		$s=preg_replace('`[=\s#,"\'\\/:*\?&\+<>%\|@№^]+`',$rep,$s);
		$s=preg_replace('#('.$qrep.')+#',$rep,$s);
		$s=trim($s,'-_');

		return preg_replace('#^('.$qrep.')+|('.$qrep.')+$#','',$s);
	}
}