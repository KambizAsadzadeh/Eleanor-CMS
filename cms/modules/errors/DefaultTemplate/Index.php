<?php
#ToDo! Исправить файл
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Шаблон по умолчанию для пользователей системного модуля страниц ошибок
	Рекомендуется скопировать этот файл в templates/[шаблон пользовательской части]/Classes/[имя этого файла] и там уже начинать править.
	В случае если такой файл уже существует - правьте его.
*/
class TPLUserErrors
{
	/*
		Вывод страницы ошибки
		$a - параметры ошибки, массив с ключами:
			id - идентификатор ошибки в БД
			http_code - HTTP код ошибки
			image - логотип ошибки
			mail - e-mail, куда необходимо присылать сообщение от пользователей
			title - название страницы ошибки
			text - текст с пояснением ошибки
			document_title - заголовок окна
			meta_descr - meta description
		$sent - флаг отправленности сообщения
		$values - массив значений полей, ключи
			text - текст сообщения
			name - имя для гостя
		$errors - массив ошибок
		$back - URI возврата
		$captcha - captcha при отправке письма
	*/
	public static function ShowError($a,$sent,$values,$errors,$back,$captcha)
	{

	}
}