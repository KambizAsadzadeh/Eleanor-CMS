<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\StaticTemplate;

/** Шаблон по умолчанию для админки системного модуля статических страниц. Рекомендуется скопировать этот файл в
 * templates/[шаблон админки]/Classes/Static.php и там уже его править. Если такой файл уже существует - правьте его.*/
class Admin
{
	/** Список статических страниц
	 * @param array $items Перечень статических страниц. Формат: ID=>[], ключи:
	 *  [string title] Название
	 *  [int status] Флаг активности
	 *  [string _atoggle] Ссылка-тумблер на переключение активности
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 *  [string _achildren] Ссылка на просмотр подстраниц
	 *  [string _a] Ссылка на просмотр страницы в пользовательской части
	 *  [string _aaddp] Ссылка на добавление подстраницы
	 * @param array $navi Хлебные крошки навигации. Формат ID=>[], ключи:
	 *  [string title] Название
	 *  [string|null _a] Ссылка
	 * @param bool $notempty Флаг того, что статические страницы существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество статических страниц (всего)
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string sort_status] Ссылка на сортировку списка по статусу активности
	 *  [string sort_title] Ссылка на сортировку списка по названию
	 *  [string sort_pos] Ссылка на сортировку списка по позиции
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [string first_page] Ссылка на первую страницу
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$navi,$notempty,$cnt,$pp,$query,$page,$links)
	{

	}

	/** AJAX дозагрузка подстраниц
	 * @param array $items Перечень подстраниц. Описание смотрите в методе ShowList
	 * @param array $query Параметры запроса
	 * @return string */
	public static function LoadSubPages($items,$query)
	{

	}

	/** Страница создается/редактирования статической страницы
	 * @param int $id ID редактируемой страницы, если равно 0, значит страница создается
	 * @param array $values Значения полей формы:
	 *  [string|array title] Название
	 *  [string|array uri] URI
	 *  [string|array text] Текст
	 *  [string|array document_title] Document title
	 *  [string|array meta_descr] Meta description
	 *  [int|null parent] ID родителя
	 *  [int pos] Позиция
	 *  [int status] Статус: 1 - акивировано, 0 - деактивировано
	 * Только при включенной мультиязычности:
	 *  [bool single-lang] Флаг одной языковой версии (сквозной для всех языков)
	 *  [array language] Перечень языковых версий
	 * @param array $data Данные для заполенения форм, ключи:
	 *  [array parents] Перечень родителей: родитель => id => Название
	 *  [array $poses] Перечень позиций "после"
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param string $draft Флаг наличия черновика
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 *  [string|null delete-draft] Ссылка на удаление черновика
	 *  [string draft] Ссылка на сохранение черновиков (для фоновых запросов)
	 * @return string */
	public static function CreateEdit($id,$values,$data,$Editor,$Uploader,$errors,$back,$draft,$links)
	{

	}

	/** Страница редактирования статических страниц на файлах, создается на базе стандартного загрузчика файлов
	 * @param string $Uploader Загрузчик файлов
	 * @return string */
	public static function Files($Uploader)
	{

	}

	/** Дозагрузка детей и позиций при смене родителя
	 * @param array $children Дети
	 * @param array $poses Позиции
	 * @return array['children'=>Дети, 'poses'=> Позиции]*/
	public static function AjaxLoadChildren($children,$poses)
	{

	}

	/** Страница удаления статической страницы
	 * @param array $static Данные удаляемой статической страницы
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($static,$back)
	{

	}

	/** Обертка для интерфейса настроек
	 * @param string $options Интерфейс настроек
	 * @return string */
	public static function Options($options)
	{

	}
}

return Admin::class;