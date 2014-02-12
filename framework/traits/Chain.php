<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Паттерн, упрощающий связывание зависимых друг от друга объектов. Представим, что у нас есть группа классов, объекты которых взаимодействуют
	между собой, причем выполняются 2 условия: создается по одному объекту для каждого класса, неизвестно, с какого класса может начаться создание
	цепочки. Данный паттерн позволяет начать создание цепочки с любого класса и перемещаться внутри цепочки, как внутри одного объекта:
	$One->Two->Three->Two и т.п. При этом One, Two, Three - это одноименные (!) объекты классов. Внутри цепочки все объекты одинаковы (передаются
	по ссылке). После завершения работы с цепочкой, рекомендуется взывать $One->Free() - это уничтожит все остальные объекты. Для удаления объекта
	$One, воспользуйтесь стандартным способом unset($One). Если необходимо уничтожить только один объект в цепочке, необходимо вызвать метод Free()
	у него. Следующий метод уничтожит все Two $One->Two->Free();
*/
namespace Eleanor\Traits;
trait Chain
{
	public
		$Base,#Объект базового класса, с которого все запустилось
		$good=true,#Флаг того, что объект пригоден для работы
		$held,#Массив "удерживаемых" объектов зависимых объектов
		$name;#Название текущего класса

	/**
	 * Cоздание объекта
	 * @param string $name Имя класса
	 * @param Obj $Base Объект базового класса
	 * return ОБЪЕКТ
	 */
	abstract protected function Create($name,$Base);

	/**
	 * Удаление конкретного объекта
	 */
	final public function Free()
	{
		if($this->good)
		{
			$this->good=false;
			$isb=isset($this->Base);
			foreach($this->held as $k=>&$v)
			{
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

	/**
	 * Служебный метод
	 */
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