<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Interfaces;
use CMS\Controls_Manager, CMS\Controls;

/** Элемент формы, как единое целое для взаимодействия с пользователем */
interface Control
{
	/** Получение настроек контрола
	 * @param Controls_Manager $CM
	 * @return array */
	public static function Settings($CM);

	/** Вывод контрола пользователю в форму
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @return mixed */
	public static function Control($control,$Controls);

	/** Сохранение контрола (получение данных их контрола после сабмита пользователем формы)
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @return mixed */
	public static function Save($control,$Controls);

	/** Вывод результата (представление в доступной форме данных, которые заданы пользователем в контроле)
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @param array $controls Остальные контролы, выводимые группой */
	public static function Result($control,$Controls,$controls);
}