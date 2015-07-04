<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Interfaces;

/** Обработка действий модуля аккаунт */
interface Account#Интерфейс для создания медов авторизации
{
	/** Получение ссылок на обработчик из меню
	 * @return array*/
	public static function Links();

	/** Получение всех URI обработчика
	 * @return array*/
	public static function Uris();

	/** Пока контента с обработкой запроса
	 * @param bool $master Возможность обработки входящего запроса с возможным перенаправлением
	 * @return array*/
	public static function Content($master=true);
}
