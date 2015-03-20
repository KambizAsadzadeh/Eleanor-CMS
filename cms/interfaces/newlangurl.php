<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS\Interfaces;

/** Генерация новых URL при смене языка сайта */
interface NewLangUrl
{
	/** Получение нового языкового URL
	 * @param string $section Секция модуля
	 * @param array $oldurl Старый URL, разбитый через explode('/')
	 * @param array $oldquery Старые динамические параметры
	 * @param string $oldlang Название старого языка
	 * @param string $newlang Название нового языка
	 * @param \CMS\Url $Url Объект URL с нужным префиксом
	 * @return string Новый URL относительно корня сайта без начальной / */
	public function GetNewLangUrl($section,$oldurl,$oldquery,$oldlang,$newlang,$Url);
}