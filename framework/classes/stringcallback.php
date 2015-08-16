<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Специальная строка с возможностью обратного вызова */
class StringCallback extends Eleanor\BaseClass
{
	/** @var Callable Генератор строки */
	protected $Callback;

	/** Конструктор
	 * @param Callable $Callback Генератор строки
	 * @throws EE */
	public function __construct($Callback)
	{
		if(!is_callable($Callback))
			throw new EE('Callback is not callable',EE::DEV,['input'=>$Callback]);

		$this->Callback=$Callback;
	}

	/** Нельзя бросить исключение из метода __toString(). Попытка это сделать закончится фатальной ошибкой.
	 * @return mixed */
	public function __toString()
	{
		return call_user_func($this->Callback);
	}

	/** Вызов объекта, как функцию */
	public function __invoke(...$args)
	{
		return call_user_func_array($this->Callback,$args);
	}
}