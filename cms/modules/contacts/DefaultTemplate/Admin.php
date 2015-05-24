<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\ContactsTemplate;

/** Шаблон по умолчанию для админки модуля обратной связи. Рекомендуется скопировать этот файл в
 * templates/[шаблон админки]/Classes/Errors.php и там уже править. Если такой файл уже существует - правьте его.*/
class Contacts
{
	/** Основная страница правки обратной связи
	 * @param array $values Значения полей формы:
	 *  [string|array info] Информация для страницы обратной связи
	 *  [array recipient] Возможные получатели письма
	 *  [string|array subject] Формат темы получаемого письма
	 *  [string|array text] Формат текста письма
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Classes\StringCallback $Uploader Загрузчик файлов
	 * @param array $errors Ошибки формы
	 * @param bool $saved Флаг успешного сохранения
	 * @return string */
	public static function Contacts($values,$Editor,$Uploader,$errors,$saved)
	{

	}
}

return Contacts::class;