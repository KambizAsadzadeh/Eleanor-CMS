<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Traits;

/** Паттерн, упрощающий связывание зависимых друг от друга объектов. Представим, что у нас есть группа классов, объекты
 * которых взаимодействуют связываясь между собой, причем выполняются 2 условия:
 * 1. Достаточно создать только по одному объекту от каждого класса для каждой связки объектов.
 * 2. Не известно, с какого класса начинается создание.
 * Данный паттерн позволяет эффективно создавать цепочки объектов, например:
 * $One->Two->Three->Two и т.п. При этом One, Two, Three - это одноименные (!) объекты классов. Внутри цепочки все
 * объекты одинаковы (передаются по ссылке).
 * После завершения работы с цепочкой, рекомендуется взывать $One->Free() - это уничтожит все остальные объекты.
 * Для удаления объекта $One, воспользуйтесь стандартным способом unset($One).
 * Если необходимо уничтожить только один объект в цепочке, необходимо вызвать метод Free() у него: уничтожение всех
 * объектов Two $One->Two->Free(); */
trait Chain
{
	public
		/** @var Object Объект базового класса, с которого все запустилось */
		$Base,

		/** @var bool Флаг пригодности объекта для работы*/
		$good=true,

		/** @var array Удерживаемые зависимые объекты */
		$held,

		/** @var string Название текущего объекта */
		$name;

	/** Cоздание объекта
	 * @param string $name Имя класса
	 * @param Object $Base Объект базового класса
	 * @return Object */
	abstract protected function Create($name,$Base);

	/** Удаление конкретного объекта */
	final public function Free()
	{
		if($this->good)
		{
			$this->good=false;
			$isb=isset($this->Base);

			foreach($this->held as $k=>&$v)
			{/** @var Chain $v */
				if(!$isb or $this->Base!==$v and (!isset($this->Base->held[$k]) or $this->Base->held[$k]!==$v))
					$v->Free();

				unset($this->held[$k]);
			}

			if($isb)
			{
				foreach($this->Base->held as &$v)
					unset($v->held[ $this->name ]);

				unset($this->Base->held[ $this->name ],$this->Base);
			}
		}
	}

	/** Служебный метод */
	public function __get($n)
	{
		if(isset($this->held[$n]))
			return$this->held[$n];

		if(isset($this->Base))
			$O=$this->Base->__get($n);
		elseif($n==$this->name)
			return$this;
		else
		{
			$O=$this->Create($n,$this);
			$O->Base=$this;
			$O->name=$n;
		}

		$this->held[$n]=$O;

		return$O;
	}
}