<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\ContactsTemplate;

/** Шаблон по умолчанию для публичной части модуля обратной связи. Рекомендуется скопировать этот файл в
 * templates/[шаблон публичной части]/Classes/Contacts.php и там уже править. Если такой файл уже существует - правьте его.*/

class Contacts
{
	/** Основная страница обратной связи
	 * @param string $info Текстовая информация для связи
	 * @param int|bool $canupload Максимальный размер загружаемых файлов в байтах, false - загружать файлы нельзя
	 * @param array $recipient Получатели
	 * @param array $values Значения полей формы:
	 *  [string subject] Тема письма
	 *  [string message] Текст письма
	 *  [int recipient] ID получателя
	 *  [string session] ID сессии (hidden поле)
	 *  [string|null from] e-mail отправителя, ключ присутствует только если пользователь не залогинен (гость на сайте)
	 * @param array $errors Ошибки формы
	 * @param callback $Editor Генератор Editor-a, параметры аналогичны Editor->Area
	 * @param \Eleanor\Interfaces\Captcha | \Eleanor\Interfaces\Captcha_Image | null $captcha Капча
	 * @return string */
	public static function Contacts($info,$maxupload,$recipient,$values,$errors,$Editor,$captcha)
	{

	}

	/** Страница с информацией о том, что сообщение успешно отправлено
	 * @param array $links Ссылки
	 *  [string send] Ссылка на "отправить еще сообщение"
	 * @return string */
	public static function Sent($links)
	{

	}
}

return Contacts::class;