<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Класс организации запуска задач
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Расписание задач */
class Tasks extends \Eleanor\BaseClass
{
	/** Пересчет точной даты и времени следующего запуска крона */
	public static function UpdateNextRun()
	{
		$R=Eleanor::$Db->Query('SELECT UNIX_TIMESTAMP(`nextrun`) FROM `'
			.P.'tasks` WHERE `status`=1 AND `locked`=0 ORDER BY `free` ASC, `nextrun` ASC');
		list($next)=$R->fetch_row();

		if(!$next)
			$next=strtotime('+1day');

		Eleanor::$Cache->Put('nextrun',$next,0,true);
	}

	/** Выбор первого ближайшего числа из массива: большего или равного заданному
	 * @param array $ints Отсортированный массив чисел
	 * @param int $int Заданное число
	 * @param bool $equal Флаг возможности возврата $int
	 * @return int|null */
	public static function NearGInt($ints,$int,$equal=true)
	{
		foreach($ints as $v)
			if($equal and $v>=$int)
				return$v;
			elseif($v>$int)
				return$v;

		return null;
	}

	/** Раскрытие диапазонов, описанных в строке с сокращенной последовательностью чисел. Например 1,2,5-10 будет
	 * преобразовано в 1,2,5,6,7,8,9,10 . Существует возможность указания шага раскрытий при помощи двоеточия:
	 * 1,2,5-10:2,15 будет преобразовано в 1,2,5,7,9,10,15
	 * @param string $str Строка
	 * @return string */
	public static function FillInt($str)
	{
		$str=preg_replace('#[^0-9,:\-*]+#','',$str);
		$str=preg_replace_callback('/([0-9]+)\-([0-9]+)(?::([0-9]+))?/',
			function($abc)
			{
				$a=(int)$abc[1];
				$b=(int)$abc[2];
				$c=isset($abc[3]) ? (int)$abc[3] : 1;

				if($c<1)
					$c=1;

				if($a>=$b)
					return$a.','.$b;

				$result='';

				for(;$a<$b;$a+=$c)
					$result.=$a.',';

				return$result.$b;
			},
			$str);

		return$str;
	}

	/** Вычисление времени ближайшего запуска, исходя из входящих пожалений
	 * @param array $t Пожелания времени, детали смотрите в начале тела метода
	 * @param int|null $do Date offset. Смещение в секундах по часовому поясу
	 * @return int|null */
	public static function CalcNextRun(array$t=[],$do=null)
	{
		$t+=[
			'month'=>'*',
			'day'=>'*',
			'hour'=>'*',
			'minute'=>'*',
			'second'=>'*',
		];

		foreach($t as &$v)
			if($v==='')
				$v='*';
			elseif($v[0]=='+')
				$v=(int)ltrim($v,'+');
			elseif($v!=='*')
			{
				$v=explode(',',static::FillInt($v));
				sort($v,SORT_NUMERIC);
			}
		unset($v);

		if($do===null)
			$do=date_offset_get(date_create());

		#Довески
		$extra=['year'=>0,'month'=>0,'day'=>0,'hour'=>0,'minute'=>0];

		$time=time();
		list($y,$m,$d,$h,$i,$s)=explode('-',gmdate('Y-n-j-G-i-s',$time+$do));
		$i=(int)$i;

		if(is_int($t['second']))
		{
			$s+=$t['second'];

			if($s>59)
			{
				$extra['minute']+=floor($s/60);
				$s%=60;
			}
		}
		elseif($t['second']!=='*' and null===$s=static::NearGInt($t['second'],$s))
		{
			$s=min(59,reset($t['second']));
			$extra['minute']++;
		}

		if(is_int($t['minute']))
		{
			$i+=$extra['minute']+$t['minute'];
			$extra['minute']=0;

			if($i>59)
			{
				$extra['hour']+=floor($i/60);
				$i%=60;
			}
		}
		elseif($t['minute']!=='*')
		{
			$i+=$extra['minute'];

			if(null===$tmp=static::NearGInt($t['minute'],$i))
			{
				$i=reset($t['minute']);
				$extra['hour']++;
				$extra['minute']=0;
			}
			else
			{
				if($tmp>=$i)
					$extra['minute']=0;

				$i=$tmp;
			}
		}

		if(is_int($t['hour']))
		{
			$h+=$extra['hour']+$t['hour'];
			$extra['hour']=0;

			if($h>23)
			{
				$extra['day']+=floor($h/60);
				$h%=60;
			}
		}
		elseif($t['hour']!=='*')
		{
			$h+=$extra['hour'];

			if(null===$tmp=static::NearGInt($t['hour'],$h))
			{
				$h=reset($t['hour']);
				$extra['day']++;
				$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$h)
					$extra['hour']=0;

				$h=$tmp;
			}
		}

		if(is_int($t['day']))
		{
			$d+=$extra['day']+$t['day'];
			$extra['day']=0;

			if($d>31)
			{
				$extra['month']+=floor($d/31);
				$d%=31;
			}
		}
		elseif($t['day']!=='*')
		{
			$d+=$extra['day'];

			#ToDo! Сделать поддержку дней недели + админку к дням недели
			if(null===$tmp=static::NearGInt($t['day'],$d))
			{
				$d=reset($t['day']);
				$extra['month']++;
				$extra['day']=$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$d)
					$extra['day']=0;

				$d=$tmp;
			}
		}

		if(is_int($t['month']))
		{
			$m+=$extra['month']+$t['month'];
			$extra['month']=0;

			if($m>12)
			{
				$extra['year']+=floor($m/12);
				$m%=12;
			}
		}
		elseif($t['month']!=='*')
		{
			$m+=$extra['month'];

			if(null===$tmp=static::NearGInt($t['month'],$m))
			{
				$m=reset($t['month']);
				$extra['year']++;
				$extra['month']=$extra['day']=$extra['hour']=$extra['minute']=0;
			}
			else
			{
				if($tmp>=$m)
					$extra['month']=0;

				$m=$tmp;
			}
		}

		$y+=$extra['year'];
		unset($extra['year']);

		#Проверка корректности дней
		$days=idate('t',mktime(0,0,0,$m,1,$y));

		if($days<$d)
		{
			$d-=$days;
			$m++;

			if($m>12)
			{
				$y++;
				$m=1;
			}
		}

		$ret=gmmktime($h,$i,$s,$m,$d,$y);

		#Предотвращения повторного запуская сейчас же
		if($ret==$time and $t['second']!=='*')
		{
			if(null===$s=static::NearGInt($t['second'],$s))
			{
				$s=min(59,reset($t['second']));
				$extra['minute']++;
			}

			$ret=gmmktime($h,$i,$s,$m,$d,$y);
		}

		if(0!=$s=array_sum($extra))
		{
			$s='+';

			foreach($extra as $k=>$v)
				if($v>0)
					$s.=$v.$k;

			$ret=strtotime($s,$ret);
		}

		#Смещение по времени от пользователя
		$ret-=$do;

		return$ret;
	}
}