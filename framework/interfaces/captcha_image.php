<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Interfaces;

interface Captcha_Image extends Captcha
{
	/**
	 * @param string $imgurl Ссылка для загрузки картинки, к ссылке будут добавлены параметры k1=v2&amp;k2=v2
	 */
	public function __construct($imgurl);

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