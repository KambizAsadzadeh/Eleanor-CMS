<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Interfaces;

/** Обработка содержимого страницы непосредственно перед выдачей её сервером */
interface HtmlParser
{
	/** Последняя обработка HTML кода сгенерированной страницы
	 * @param string $s Обрабатываемый HTML текст (страница)
	 * @return string */
	public static function Parse($s);
}