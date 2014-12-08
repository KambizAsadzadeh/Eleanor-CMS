<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Получение параметров из $POST запроса
 * @param array $values Переменная, куда помещать значения
 * @param array $data Названия => типы переменных: safe, string, int, array */
function PostValues(array&$values,array$data)
{
	foreach($data as $k=>$v)
		if($v and isset($_POST[$k]))
			switch($v)
			{
				case'string':
					$values[$k]=(string)$_POST[$k];
				break;
				case'int':
					$values[$k]=(int)$_POST[$k];
				break;
				case'array':
					if(isset($values[$k]))
						$values[$k]=(array)$_POST[$k]+$values[$k];
					else
						$values[$k]=(array)$_POST[$k];
				break;
				case'safe':
					$values[$k]=(string)Eleanor::$POST[$k];
				break;
				default:
					$values[$k]=Eleanor::$POST[$k];
			}
}

/** Получение значения параметра статуса
 * @param array $values Переменная, куда помещать значения
 * @param string $name Имя параметра
 * @param array $possible Возможные значения */
function IntValue(array&$values,$name,array$possible=[-1,0,1])
{
	if(isset($_POST[$name]))
	{
		$value=(int)$_POST[$name];

		if(in_array($value,$possible,true))
			$values[$name]=$value;
	}
}