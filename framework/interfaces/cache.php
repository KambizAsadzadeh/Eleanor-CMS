<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Interfaces;

/** Кэшмашина */
interface Cache
{
	/** Запись значения
	 * @param string $k Ключ. Рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $v Значение
	 * @param int $ttl Время жизни этой записи кэша в секундах */
	public function Put($k,$v,$ttl=0);

	/** Получение записи из кэша
	 * @param string $k Ключ */
	public function Get($k);

	/** Удаление записи из кэша
	 * @param string $k Ключ */
	public function Delete($k);

	/** Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш
	 * @param string $tag Тег */
	public function DeleteByTag($tag);
} 