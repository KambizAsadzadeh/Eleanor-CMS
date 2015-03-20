<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes\Cache;
use Eleanor;

/** Кэшмашина MemCached */
class MemCached implements Eleanor\Interfaces\Cache
{
	private
		/** @var string Уникализация кэш машины */
		$u,

		/** @var array Ключи находящихся в кэше */
		$names=[''=>true];

	/** @var \Memcached Объект */
	public $M=false;#Объект MemCached-a

	/** @param string $u Уникализации кэша (на одной кэш машине может быть запущено несколько копий Eleanor CMS)
	 * @throws Eleanor\Classes\EE */
	public function __construct($u='')
	{
		$this->u=$u;

		#Поскольку данная кеш-машина весьма специфична, рекомендую прописать значения самостоятельно.
		$this->M=new \Memcached;

		if(!$this->M)
			throw new Eleanor\Classes\EE('MemCached failure',Eleanor\Classes\EE::ENV);

		$this->M->addServer('localhost',11211);

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
		$r=$this->M->set($this->u.$k,$v,$ttl);

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

		$r=$this->M->get($this->u.$k);

		if($r===false)
			unset($this->names[$k]);

		return$r;
	}

	/** Удаление записи из кэша
	 * @param string $k Ключ
	 * @return bool */
	public function Delete($k)
	{
		unset($this->names[$k]);
		return$this->M->delete($this->u.$k);
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