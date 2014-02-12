<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Паттерн создания цепочек последовательной генерации строки. Используется преимущественно для шаблонов. У объекта от
	класса, наследующего данный шаблон сможет, появляется возможность генерировать строку последовательно добавляя в её
	конец результаты от вызова методов. Например $Obj->Part1([params])->Part2([params])->Part3([params])... Результат
	работы станет доступен после преобразования оъекта в строку. Дважды преобразовать объект в строку нельзя. Тоесть
	можно, но после каждого преобразования в строку, контейнер оъекта "очищается". 	Дополнительная возможность: возможно
	получить каждую отдельную часть строки, не затрагивая контейнера объекта. Для этого нужно вызвать объект, как
	функцию, первым параметром передав название части, а последующими - параметры. Например: $part=$Obj('Part1'[,params]);
*/
namespace Eleanor\Abstracts;
use Eleanor;

abstract class AppendString extends Eleanor\BaseClass
{
	public
		/** @property string $s Аккомулятор результатов */
		$s='',
		/** @property bool $cloned Флаг выполненной клонированости.
		 * Смысл состоит в том, что каждый fluent interface - отдельный независимый объект. */
		$cloned=false;

	/**
	 * Терминатор Fluent Interface, выдача результата
	 */
	public function __toString()
	{
		$s=$this->s;
		$this->s='';
		return$s;
	}

	/**
	 * Единичное выполнение какого-нибудь шаблона, без изменения текущего буфера
	 * @param \string Название шаблона
	 * @params Переменные шаблона
	 * @return mixed
	 */
	public function __invoke()
	{
		$n=func_num_args();
		if($n>0)
		{
			$a=func_get_args();
			return$this->_($a[0],array_slice($a,1));
		}

		#Redundance for PhpStorm: удалить следующую строку
		return null;
	}

	public function __clone()
	{
		$this->cloned=true;
	}

	/**
	 * Реализация fluent interface шаблона
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 * @return mixed
	 */
	public function __call($n,$p)
	{
		if(!$this->cloned)
		{
			$O=clone$this;
			return$O->__call($n,$p);
		}

		$r=$this->_($n,$p);
		if($r===null or is_scalar($r) or is_object($r) and $r instanceof self)
		{
			$this->s.=$r;
			return$this;
		}
		return$r;
	}

	/**
	 * Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 */
	abstract public function _($n,array$p);
}