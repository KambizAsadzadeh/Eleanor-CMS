<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes\Cache;
use Eleanor;

/** Кэшмашина MemCache */
class MemCache implements Eleanor\Interfaces\Cache
{
	private
		/** @var string Уникализация кэш машины */
		$u,

		/** @var array Ключи находящихся в кэше */
		$names=[''=>true];

	/** @var \Memcache Объект */
	public $Driver=false;#Объект MemCache-a

	/** @param string $u Уникализации кэша (на одной кэш машине может быть запущено несколько копий Eleanor CMS)
	 * @throws Eleanor\Classes\EE */
	public function __construct($u='')
	{
		$this->u=$u;
		$this->Driver=new \Memcache;

		$connected=$this->Driver->connect('localhost', 11211);

		if(!$connected)
		{
			$this->Driver->close();
			throw new Eleanor\Classes\EE('MemCache failure',Eleanor\Classes\EE::ENV,
				['hint'=>'try to delete the file framework/classes/cache/memcache.php']);
		}

		$this->Driver->setCompressThreshold(20000,0.2);

		$this->names=$this->Get('');

		if(!$this->names or !is_array($this->names))
			$this->names=[];
	}

	public function __destruct()
	{
		$this->Put('',$this->names);
		$this->Driver->close();
	}

	/** Запись значения
	 * @param string $k Ключ. Рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $v Значение
	 * @param int $ttl Время жизни этой записи кэша в секундах
	 * @return true */
	public function Put($k,$v,$ttl=0)
	{
		$r=$this->Driver->set($this->u.$k,$v,is_bool($v) || is_int($v) || is_float($v) ? 0 : MEMCACHE_COMPRESSED,$ttl);

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

		$r=$this->Driver->get($this->u.$k);

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
		return $this->Driver->delete($this->u.$k);
	}

	/** Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш
	 * @param string $tag Тег */
	public function DeleteByTag($tag)
	{
		if($tag)
		{
			foreach($this->names as $k=>$v)
				if($tag=='' or strpos($k,$tag)!==false)
					$this->Delete($k);
		}
		else
			$this->Driver->flush();
	}
}