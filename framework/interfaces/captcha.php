<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Interfaces;

/** Самостоятельная капча без подгрузки контента */
interface Captcha
{
	/** Получение HTML кода капчи
	 * @return \Eleanor\Classes\CaptchaCallback */
	public static function GetCode();

	/** Проверка пользователя: прошел он капчу, или нет
	 * @return bool */
	public static function Check();
}