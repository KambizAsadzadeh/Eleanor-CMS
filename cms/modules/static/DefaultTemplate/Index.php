<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\StaticTemplate;
defined('CMS\STARTED')||die;

/** Шаблон по умолчанию для публичной части модуля статических страниц. Рекомендуется скопировать этот файл в
 * templates/[шаблон публичной части]/Classes/StaticPage.php и там уже его править. В случае если такой файл уже
 * существует - правьте его. */
class Index
{
	/** Страница отображения статической страницы
	 * @param int|string $id Идентификатор: числовой для страницы из базы данных, строка - для файловых страниц.
	 * @param array $data Данные статической страницы, ключи:
	 *  string title Название
	 *  string text Тест (содержимое)
	 *  array navi Хлебные крошки, двумерный массив, ключи:
	 *   int 0 Текст крошки
	 *   int|null 1 Ссылка крошки
	 *  array seealso Ссылки блока "смотри еще", двумерный массив, ключи:
	 *   int 0 Текст ссылки
	 *   int 1 Ссылка
	 * @return string */
	public static function StaticShow($id,$data)
	{

	}

	/** Вывод статических страниц на главной (когда модуль установлен на главную)
	 * @param array $statics Перечень страниц для вывода, ключи:
	 *  string title Название
	 *  string text Текст
	 * @return string */
	public static function StaticGeneral($statics)
	{

	}

	/** Вывод содержания (перечень всех страниц)
	 * @param array $statics Перечень страниц для вывода, ключи:
	 *  string _a Ссылка на страницу
	 *  string uri Идентификатор
	 *  string title Название
	 *  string parents Идентификаторы родителей (parents), разделенных запятыми (если они, конечно, есть)
	 *  int pos Позиция страницы
	 * @return string */
	public static function StaticSubstance($statics)
	{

	}
}

return Index::class;