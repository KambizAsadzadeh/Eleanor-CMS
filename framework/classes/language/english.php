<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes\Language;
use \Eleanor;

/** Поддержка английского языка */
class English extends Eleanor\BaseClass
{
	const
		ALPHABET='abcdefghijklmnopqrstuvwxyz';#Латинский технический алфавит

	/** Образование множественной формы слова
	 * @param int $n Число
	 * @param array $forms Формы слова. Пример ['один','два и больше'] */
	public static function Plural($n,array$forms)
	{
		return$n==1 ? $forms[0] : $forms[1];
	}

	/** Человеческое представление даты
	 * @param int|string|null $d Дата в обычном машинном формате, либо timestamp, false = time()
	 * @param string $t Тип вывода: t - время, d - дата, dt - дата и время,my - месяц и год, fd - полная дата,
	 * fdt - полная дата и время
	 * @param array $a Экстра опции. Ключ advanced включает вывод значений "Today", "Tomorrow", "Yesterday"
	 * @return string */
	public static function Date($d=null,$t='',$a=[])
	{
		if(!$d)
			$d=time();
		elseif(is_array($d))
		{
			$d+=array_combine(['H','i','s','n','j','Y'],explode(',',date('H,i,s,n,j,Y')));
			$d=mktime($d['H'],$d['i'],$d['s'],$d['n'],$d['j'],$d['Y']);
		}
		elseif(!is_int($d))
			$d=strtotime($d);

		if(!$d)
			return'';

		switch($t)
		{
			case't':#time
				return date('H:i:s',$d);
			case'd':#date
				return date('Y-m-d',$d);
			case'dt':#datetime
			default:
				return date('Y-m-d H:i:s',$d);

			case'my':#Month year
				return date('F Y',$d);
			case'fd':#full date
				$a+=['advanced'=>true];
				return static::DateText($d,$a['advanced']);
			case'fdt':#full datetime
				$a+=['advanced'=>true];
				return static::DateText($d,$a['advanced']).date(' H:i',$d);
		}
	}

	/** Человеческое представление даты
	 * @param int $t Дата в оформате timestamp
	 * @param bool $adv Флаг включения значений "Today", "Tomorrow", "Yesterday"
	 * @return string */
	public static function DateText($t,$adv)
	{
		$day=explode(',',date('Y,n,j,t',$t));
		$tod=explode(',',date('Y,n,j,t'));

		if($adv)
		{
			if($day[2]==$tod[2] and $day[1]==$tod[1] and $day[0]==$tod[0])
				return'Today';
			if($day[2]+1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]+1==$tod[1] and $tod[0]==$day[0] and $tod[2]==1 and $day[3]==$day[2] or $day[0]+1==$tod[0] and $tod[2]==1 and $tod[1]==1 and $day[3]==$day[2])
				return'Yesterday';
			if($day[2]-1==$tod[2] and $tod[0]==$day[0] and $tod[1]==$day[1] or $day[1]-1==$tod[1] and $tod[0]==$day[0] and $tod[2]==$tod[3] and $day[2]==1 or $day[0]-1==$tod[0] and $tod[2]==$tod[3] and $tod[1]==12 and $day[2]==1)
				return'Tomorrow';
		}

		return date('m F Y',$t);
	}
}