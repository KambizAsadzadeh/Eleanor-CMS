<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Класс работы с датами
*/
namespace Eleanor\Classes;
use Eleanor;

class Dates extends Eleanor\BaseClass
{
	/**
	 * Генерация "календаря" в виде [week][day], где week это от 1 до 5 или 6, а day от 1 до 7.
	 * @param int $y Год
	 * @param int $m Месяц
	 * @param bool $pn Флаг дописывания в начало и конец календаря числа следующих месяцев, FALSE - вставляются нули
	 * @return array
	 */
	public static function BuildCalendar($y,$m,$pn=true)
	{
		$mt=mktime(0,0,0,$m,1,$y);
		$t=idate('w',$mt);

		if($t==0)
			$t=7;

		$p=$t>1 ? idate('t',$mt-172800)-$t+2 : 1;
		$c=[];
		$t=idate('t',$mt);

		for($week=0;$week<6;$week++)
			for($day=0;$day<7;$day++)
			{
				$d=($week==0 and $p>20 or $week>=4 and $p<10) && !$pn ? 0 : $p;

				if($day==0 and $week>=4 and $d<10)
					break;

				$c[$week][] = $d;

				if($week==0 and $p==idate('t',$mt-172800) or $week>0 and $p==$t)
					$p=0;

				++$p;
			}

		return$c;
	}
}