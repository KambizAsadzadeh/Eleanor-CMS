<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Реализация языковой поддержки */
class Language extends Eleanor\BaseClass implements \ArrayAccess
{
	/** @var string Системный язык */
	public static $main='russian';

	public
		/** @var string Каталог по умолчанию, откуда будут загружаться недостающие языковые файлы. Обязательный с / */
		$source,

		/** @var string Очередь файлов для загрузки имя Файла => путь к файлу */
		$queue=[];

	protected
		/** @var string Имя языка */
		$name,

		/** @var bool Флаг включения поддержки нескольких файлов: $L['file']['param'] */
		$multiple=false,

		/** @var array Дамп всех языков (загруженная копия в памяти) */
		$dump=[],

		/** @var array Перечень всех загруженных файлов file => array of paths */
		$loaded=[];

	/** Конструктор языкового объекта
	 * @param bool|string|array $init string, string - путь к файлу с языковыми переменными; bool - создание пустого
	 * объекта: true - multiple ( $L['file']['param'] ), false - single ( $L['param'] )
	 * @param string $file Если $init - string, переменная определяет file (из примера выше) для multiple объекта */
	public function __construct($init=false,$file='')
	{
		$this->name=static::$main;
		$this->source=__DIR__.'/translation/';

		if($init===true)
			$this->multiple=true;
		elseif($init)
		{
			if($file)
				$this->multiple=true;

			$this->Load($init,$file);
		}
	}

	/** Возвращение текущего языка объекта */
	public function __toString()
	{
		return$this->name;
	}

	/** Универсальный вызов методов классов поддержки языков - у которых имя класса совпадает с названием языка
	 * Russian, English...
	 * @param string $n Название метода
	 * @param array $p Параметры метода
	 * @return mixed */
	public function __call($n,$p)
	{
		$class=__NAMESPACE__.'\\Language\\'.$this->name;
		if(method_exists($class,$n))
			return call_user_func_array([$class,$n],$p);

		$c=[$class,$n];
		if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
			return$s;

		return parent::__call($n,$p);
	}

	/** Загрузка языкового файла. Структура языкового файла должна быть такой:
	 * <?php
	 * return [
	 *     'param1'=>'value1',
	 *     ...
	 * ];
	 * @param string|array $path Путь к файлу, в котором вместо * будет подставлено название языка.
	 * @param string|false $file Название файла (секции) в случае multiple (пример $L['file']['param']), в случае false
	 * языковые параметры будут просто возвращены без сохранения локальной копии в dump-е.
	 * @return array */
	public function Load($path,$file='')
	{
		if(is_array($path))
		{
			$params=[];
			foreach($path as $v)
				$params+=$this->Load($v,$file);

			return$params;
		}

		$langfile=str_replace('*',$this->name,$path);
		if(!is_file($langfile))
		{
			$deffile=str_replace('*','default',$path);
			if(is_file($deffile))
				$langfile=$deffile;
		}

		if(!is_file($langfile))
			throw new EE('Missing file '.(strpos($langfile,\Eleanor\SITEDIR)===0 ? substr($langfile,strlen(\Eleanor\SITEDIR)) : $file),
				EE::ENV,
				static::_BT(array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2),1))
			);

		$params=\Eleanor\AwareInclude( $langfile );
		$this->loaded[$file][]=$path;

		if($file===false)
			return$params;

		if($this->multiple)
			return$this->dump[$file]=isset($this->dump[$file]) ? $params+$this->dump[$file] : $params;
		else
			return$this->dump=$params+$this->dump;
	}

	/** Изменение языка. Все языковые файлы будут перезагружены.
	 * @param string $newlang Имя нового языка */
	public function Change($newlang)
	{
		foreach($this->loaded as $file=>$paths)
			foreach($paths as $path)
			{
				$path=str_replace('*',$newlang,$path);

				if(is_file($path))
				{
					$params=\Eleanor\AwareInclude($path);

					if($this->multiple)
						$this->dump[$file]=$params+$this->dump[$file];
					else
						$this->dump=$params+$this->dump;
				}
			}

		$this->name=$newlang;
	}

	/** Установка языковой переменной
	 * @param string $k Имя переменной
	 * @param mixed $v Языковое значение */
	public function offsetSet($k,$v)
	{
		$this->dump[$k]=$v;
	}

	/** Проверка существования языковой переменной
	 * @param string $k Имя переменной
	 * @return bool */
	public function offsetExists($k)
	{
		return isset($this->dump[$k]);
	}

	/** Удаление языковой переменной
	 * @param string $k Имя переменной */
	public function offsetUnset($k)
	{
		unset($this->dump[$k]);
	}

	/** Получение языковой переменной
	 * @param string $k Имя переменной
	 * @throws EE
	 * @return mixed */
	public function offsetGet($k)
	{
		if($this->multiple)
		{
			if(!isset($this->dump[$k]) and
				!$this->Load(isset($this->queue[$k]) ? $this->queue[$k] : $this->source.$k.'-*.php',$k))
				return parent::__get(debug_backtrace(),$k);

			return$this->dump[$k];
		}

		if(!isset($this->dump[$k]))
		{
			while($l=array_pop($this->queue))
				if($this->Load($this->source.$l) and isset($this->dump[$k]))
					return$this->dump[$k];

			throw new EE('Unable to get translation key "'.$k.'".',EE::DEV);
		}

		return$this->dump[$k];
	}
}