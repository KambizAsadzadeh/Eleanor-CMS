<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Капча c подгрузки контента (с отдельной генерацией картинки, например)
*/
namespace Eleanor\Interfaces;

interface Captcha_Image
{
	/** @param string $src Ссылка для загрузки картинки, к ссылке будут добавлены параметры k1=v2&amp;k2=v2 */
	public function __construct($src);

	/** Получение HTML кода капчи
	 * @param string $name Имя капчи, используется в случае, когда на странице выводится более одной капчи
	 * @return object|string */
	public function GetCode($name='captcha');

	/** Проверка пользователя: прошел он капчу, или нет
	 * @param string $name Имя капчи, используется в случае, когда на странице выводится более одной капчи
	 * @return bool */
	public static function Check($name='captcha');

	/** Разрушение капчи. После проверки (вне зависимости от того, успешно прошла она или нет), капчу нужно разрушить
	 * для исключения перебора возможных значений
	 * @param string $name Имя капчи, используется в случае, когда на странице выводится более одной капчи */
	public static function Destroy($name='captcha');

	/** Генерация и выдача картинки. Здесь не может быть конфигурирования капчи, все конфигурирование при создании! */
	public static function GetImage();
}