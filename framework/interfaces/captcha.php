<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Interfaces;

interface Captcha
{
	/**
	 * Получение HTML кода капчи
	 * @return object|string
	 */
	public function GetCode();

	/**
	 * Проверка пользователя: прошел он капчу, или нет
	 * @return bool
	 */
	public function Check();
}