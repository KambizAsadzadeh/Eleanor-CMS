<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Объект для Head */
class Head extends Eleanor\BaseClass implements \ArrayAccess, \Countable, \Iterator, \IteratorAggregate
{
	use \Eleanor\Traits\AutoJoin;

	/** @var array Хранилище */
	protected $storage=[
		'script'=>[],#Скрипты
		'other'=>[],#Другое
	];

	/** Создание объекта
	 * @throws EE */
	public function __construct(array$head=[])
	{
		foreach($head as $k=>$v)
			if(isset($this->storage[$k]))
				$this->storage[$k]=(array)$v;
			else
				throw new EE('Head class doesn\'t support key '.$k,EE::DEV);
	}

	/** Реализация присвоения значения ключу массиву
	 * @param string $key
	 * @param string $value
	 * @throws EE */
	public function OffsetSet($key,$value)
	{
		if(!isset($this->storage[$key]))
			throw new EE('Head class doesn\'t support key '.$key,EE::DEV);

		$this->storage[$key]=(array)$value;
	}

	/** Преобразование в строку.
	 * @return string */
	public function __toString()
	{
		$content=[];

		foreach($this->storage as $k=>$v)
			switch($k)
			{
				case'script':
					$content[]='<script src="'.join('"></script><script src="',$v).'"></script>';
				break;
				case'other':
					$content[]=join($this->glue,$v);
			}

		return join($this->glue,$content);
	}

	/** Добавление скрипта
	 * @param string $src Путь к скрипту */
	public function AddScript($src)
	{
		$this->storage['script'][]=(string)$src;
	}

	/** Добавление свободного участка head
	 * @param string $data HTML */
	public function AddOther($data)
	{
		$this->storage['other'][]=(string)$data;
	}
}