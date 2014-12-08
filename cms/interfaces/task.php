<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Interfaces;

/** Задачи, запускаемые в системе по крону */
interface Task
{
	/** @const Флаг блокирования повторного запуска задачи до завершения текущего запуска */
	const BLOKING=true;

	/** Запуск задачи
	 * @param mixed $data Данные, возвращенные прошлый раз методом GetNextRunInfo
	 * @return bool Нужно вернуть true, если задание выполнено успешно, и false - если нет */
	public function Run($data);

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром
	 * @return mixed Данные доложны быть сериализуемыми */
	public function GetNextRunInfo();
}