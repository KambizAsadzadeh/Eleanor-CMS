<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Объект для передачи капч шаблонизатору */
class CaptchaCallback extends Eleanor\BaseClass
{
	protected
		/** @var Callable Генератор HTML капчи для вставки на страницу */
		$body,

		/** @var Callable|null Генератор общего Head капчи (html для помещения в <head>) */
		$head,

		/** Флаг полученного Head */
		$gothead=false;

	/** Конструктор
	 * @param Callable $body Генератор HTML капчи для вставки на страницу
	 * @param Callable|null $head Генератор общего Head капчи (html для помещения в <head>)
	 * @throws EE */
	public function __construct($body,$head=null)
	{
		if(!is_callable($body))
			throw new EE('Callback is not callable',EE::DEV,['input'=>$body]);

		$this->body=$body;
		$this->head=is_callable($head) ? $head : null;
	}

	/** Нельзя бросить исключение из метода __toString(). Попытка это сделать закончится фатальной ошибкой.
	 * @return mixed */
	public function __toString()
	{
		return($this->gothead || !$this->head ? '' :call_user_func($this->head))
			.call_user_func($this->body);
	}

	/** Вызов объекта, как функцию */
	public function __invoke(...$args)
	{
		return call_user_func_array($this->body,$args);
	}

	/** Получение общего $head
	 * @return Head */
	public function GetHead()
	{
		$this->gothead=true;
		return $this->head ? call_user_func($this->head) : new Head;
	}
}