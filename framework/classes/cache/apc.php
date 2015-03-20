<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes\Cache;
use Eleanor;

/** Кэшмашина APC */
class Apc implements Eleanor\Interfaces\Cache
{
	private
		/** @var string Уникализация кэш машины */
		$u,

		/** @var array Ключи находящихся в кэше */
		$names=[''=>true];

	/** @param string $u Уникализации кэша (на одной кэш машине может быть запущено несколько копий Eleanor CMS) */
	public function __construct($u='')
	{
		$this->u=$u;
		$this->names=$this->Get('');

		if(!$this->names or !is_array($this->names))
			$this->names=[];
	}

	public function __destruct()
	{
		$this->Put('',$this->names);
	}

	/** Запись значения
	 * @param string $k Ключ. Рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $v Значение
	 * @param int $ttl Время жизни этой записи кэша в секундах
	 * @return true */
	public function Put($k,$v,$ttl=0)
	{
		$r=apc_store($this->u.$k,$v,$ttl);

		if($r)
			$this->names[$k]=$ttl+time();

		return$r;
	}

	/** Получение записи из кэша
	 * @param string $k Ключ
	 * @return mixed */
	public function Get($k)
	{
		if(!isset($this->names[$k]))
			return false;

		$r=apc_fetch($this->u.$k,$s);

		if(!$s)
			unset($this->names[$k]);

		return$r;
	}

	/** Удаление записи из кэша
	 * @param string $k Ключ
	 * @return bool */
	public function Delete($k)
	{
		unset($this->names[$k]);
		return apc_delete($this->u.$k);
	}

	/** Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш
	 * @param string $tag Тег */
	public function DeleteByTag($tag)
	{
		foreach($this->names as $k=>$v)
			if($tag=='' or strpos($k,$tag)!==false)
				$this->Delete($k);
	}
}