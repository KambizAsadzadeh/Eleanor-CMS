<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\ErrorsTemplate;

/** Шаблон по умолчанию для админки системного модуля страниц ошибок. Рекомендуется скопировать этот файл в
 * templates/[шаблон админки]/Classes/Errors.php и там уже править. Если такой файл уже существует - правьте его.*/

class Admin
{
	/** Список страниц ошибок
	 * @param array $items Перечень страниц ошибок. Формат: ID=[], ключи:
	 *  [string email] E-mail, куда будут отправляться сообщения об ошибках
	 *  [array miniature] Миниатюра-логотип
	 *  [bool log] Флаг логирования ошибки
	 *  [string uri] URI
	 *  [string title] Название
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 *  [string _a] Ссылка на просмотр страницы в пользовательской части
	 * @param bool $notempty Флаг того, что статические страницы существуют, несмотря на настройки фильтра
	 * @param int $cnt Суммарное количество страниц ошибок (всего)
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_title] Ссылка на сортировку списка по названию
	 *  [string sort_email] Ссылка на сортировку списка по e-mail
	 *  [string sort_http_code] Ссылка на сортировку списка по HTTP коду
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [string form_items] Ссылка для параметра action формы, внтури которой происходит отображение перечня $items
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$notempty,$cnt,$pp,$query,$page,$links)
	{

	}

	/** Страница создается/редактирования страницы ошибки
	 * @param int $id ID редактируемой страницы, если равно 0, значит страница создается
	 * @param array $values Значения полей формы:
	 *  [string|array title] Название
	 *  [string|array uri] URI
	 *  [string|array text] Текст
	 *  [string|array document_title] Document title
	 *  [string|array meta_descr] Meta description
	 * Только при включенной мультиязычности:
	 *  [string log_language] Язык логирования
	 *  [bool single-lang] Флаг одной языковой версии (сквозной для всех языков)
	 *  [array language] Перечень языковых версий
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param string $draft Флаг наличия черновика
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 *  [string|null delete-draft] Ссылка на удаление черновика
	 *  [string draft] Ссылка на сохранение черновиков (для фоновых запросов)
	 * @param int $maxupload Максимально доспустимый размер файла для загрузки
	 * @return string */
	public static function CreateEdit($id,$values,$Editor,$Uploader,$errors,$back,$draft,$links,$maxupload)
	{

	}

	/** Страница удаления страницы ошибки
	 * @param array $error Данные удаляемой страницы ошибки
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($error,$back)
	{

	}

	/** Страница правки форматов писем
	 * @param array $controls Перечень контролов
	 * @param callback $Controls2Html Генератор html из $controls
	 * @param bool $saved Флаг успешного сохранения
	 * @param array $errors Ошибки заполнения формы
	 * @return string*/
	public static function Letters($controls,$Controls2Html,$saved,$errors)
	{

	}
}

return Admin::class;