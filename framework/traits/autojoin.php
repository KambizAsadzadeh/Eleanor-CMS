<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Traits;
use Eleanor;

/** Массив, который сам себя превращает в сторку */
trait AutoJoin
{
	/** @var string "клей" для частей */
	public$glue='';

	/** @var array Хранилище */
	protected$storage=[];

	/** Реализация isset()
	 * @param mixed $key
	 * @return bool */
	public function OffsetExists($key)
	{
		return isset($this->storage[$key]);
	}

	/** Реализация получения ключа массива
	 * @param mixed $key
	 * @return mixed */
	public function OffsetGet($key)
	{
		return isset($this->storage[$key]) ? $this->storage[$key] : null;
	}

	/** Реализация присвоения значения ключу массиву
	 * @param mixed $key
	 * @param mixed $value */
	public function OffsetSet($key,$value)
	{
		if(count($this->storage)==0)
			$this->storage=is_object($key) ? new \SplObjectStorage : [];

		$this->storage[$key]=$value;
	}

	/** Реализация unset() */
	public function OffsetUnset($key)
	{
		unset($this->storage[$key]);
	}

	/** Реализация count()
	 * @return int*/
	public function count()
	{
		return count($this->storage);
	}

	/** Реализация current()
	 * @return mixed */
	public function current()
	{
		return current($this->storage);
	}

	/** Реализация key()
	 * @return mixed */
	public function key()
	{
		return key($this->storage);
	}

	/** Реализация next() */
	public function next()
	{
		next($this->storage);
	}

	/** Реализация rewind() */
	public function rewind()
	{
		rewind($this->storage);
	}

	/** Проверка корректности текущего положения
	 * @return bool */
	public function valid()
	{
		return key($this->storage)!==null;
	}

	/** Реализация foreach(...) */
	public function getIterator()
	{
		foreach($this->storage as $k=>$v)
			yield$k=>$v;
	}

	/** Преобразование в строку.
	 * @return string */
	public function __toString()
	{
		return join($this->glue,$this->storage);
	}
}