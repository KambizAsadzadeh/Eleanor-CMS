<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

/** Класс обеспечения интеграции системы со сторонними скриптами */
class Integration
{
	/** Создание пользователя. Ожидается возврат ID, который будет записан в таблице пользователей в поле integration
	 * @param array $data Очищенные и приведенные к системному виду данные добавляемого пользователя
	 * @param array $raw "Сырые" данные (обычно в том виде, в котором они переданы методу UserManager::Add
	 * @return string */
	public static function Create($data,$raw)
	{

	}

	/** Обновление пользователей
	 * @param array $data Очищенные и приведенные к системному виду данные обновляемого пользователя
	 * @param array $raw "Сырые" данные (обычно в том виде, в котором они переданы методу UserManager::Update
	 * @param array|int $ids Идентификатор(ы) обновляемого пользователя
	 */
	public static function Update($data,$raw,$ids)
	{

	}

	/** Удаление пользователей
	 * @param array|int $ids Идентификатор удаляемого пользователя */
	public static function Delete($ids)
	{

	}
}