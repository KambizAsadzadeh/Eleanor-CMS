<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Interfaces;

/** Капча c подгрузки контента (с отдельной генерацией картинки, например) */
interface Captcha_Image
{
	/** @param string $src Ссылка для загрузки картинки, к ссылке будут добавлены параметры k1=v2&amp;k2=v2 */
	public function __construct($src);

	/** Получение HTML кода капчи
	 * @return \Eleanor\Classes\CaptchaCallback */
	public function GetCode();

	/** Проверка пользователя: прошел он капчу, или нет
	 * @return bool */
	public static function Check();

	/** Генерация и выдача картинки. Здесь не может быть конфигурирования капчи, все конфигурирование при создании! */
	public static function GetImage();
}