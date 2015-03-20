<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Набор методов для оценки пользователями материалов (вычисление среднего значения) */
class Rating extends \Eleanor\BaseClass
{
	/** Вычисление новой средней оценки при добавлении оценки
	 * @param int $total Количество проголосовавших
	 * @param float $average Текущая средняя оценка
	 * @param int $mark Добавляемая оценка
	 * @return float Новая средня оценка */
	public static function AddMark($total,$average,$mark)
	{
		return round((ceil($average*$total)+$mark)/++$total,2);
	}

	/** Вычисление средней оценки при удалении оценки
	 * @param int $total Количество проголосовавших
	 * @param float $average Текущая средняя оценка
	 * @param int $mark Удаляемая оценка
	 * @return float Новая средня оценка */
	public static function SubMark($total,$average,$mark)
	{
		return round((ceil($average*$total)-$mark)/--$total,2);
	}

	/** Вычисление новой средней оценки при изменении оценки
	 * @param int $total Количество проголосовавших
	 * @param float $average Текущая средняя оценка
	 * @param int $oldmark Старая оценка
	 * @param int $newmark Новая оценка
	 * @return float Новая средня оценка */
	public static function ChangeMark($total,$average,$oldmark,$newmark)
	{
		return round((ceil($average*$total)-$oldmark+$newmark)/$total,2);
	}
}