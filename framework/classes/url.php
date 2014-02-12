<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Генерации ссылок
*/
namespace Eleanor\Classes;
use Eleanor;

class Url extends Eleanor\BaseClass
{
	public static
		/** @static Адрес текущего адреса в браузере */
		$curpage;

	/**
	 * Конструктор, самый обыкновенный, ничем не приметный конструктор.
	 * @param string|bool $qs Строка запроса для дальнейшего разбора, false - берется запрос из браузера
	 * @return array ( статическая часть URL, часть динамического запроса (только для string $qs) )
	 */
	public static function ParseRequest($qs=false)
	{
		if($qs===false)
		{
			$qs=isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING'];
			$direct=false;
		}
		else
			$direct=true;

		$result=['',[]];

		if(strpos($qs,'!')===0 and false!==$ap=strpos($qs.'&','!&'))
		{
			$qs=substr($qs,0,$ap);
			$qs=substr($qs,1);
			$result[0]=static::Decode($qs);
		}
		elseif($direct)
		{
			if(false!==$p=strpos($qs,'?'))
			{
				$mixedget=substr($qs,$p+1);
				if($mixedget)
					parse_str($mixedget,$result[1]);

				if($p>0)
				{
					$qs=substr($qs,0,$p);
					$result[0]=static::Decode($qs);
				}
			}
			else
				$result[0]=static::Decode($qs);
		}

		return$result;
	}

	/**
	 * Кодирование строк для использования кириличных и других символов, не относящихся к латиннице, в ссылках
	 * @param string $s Входящая строка
	 * @return string
	 */
	public static function Encode($s)
	{
		return urlencode(Eleanor\UTF8 ? $s : mb_convert_encoding((string)$s,'utf-8'));
	}

	/**
	 * Декодирование строк, обратное действие методу Encode
	 * @param string $s Входящая строка
	 * @return string
	 */
	public static function Decode($s)
	{
		$s=urldecode($s);

		if(Eleanor\UTF8)
			return$s;

		return preg_match('/^.{1}/us',$s)==1 ? mb_convert_encoding($s,Eleanor\CHARSET,'utf-8') : $s;
	}

	/**
	 * Генерация ссылок
	 * @param array $static Статическая часть ссылки
	 * @param string $ending Окончание ссылки
	 * @param array $query request часть ссылки
	 * @return string
	 */
	public static function Make(array$static=[],$ending='',array$query=[])
	{
		$result=[];

		foreach($static as $v)
			if($v or (string)$v=='0')
				$result[]=static::Encode($v);

		return join('/',$result).$ending.($query ? '?'.static::Query($query) : '');
	}

	/**
	 * Генерация сложных динамических URLов, состоящих из многомерных массивов
	 * @param array $a Многомерный массив параметров, которых должен быть преобразован в URL
	 * @param string $d Разделитель параметров, получаемого URLа
	 * @return string
	 */
	public static function Query(array$a,$d='&amp;')
	{
		$r=[];

		foreach($a as $k=>&$v)
		{
			$k=urlencode($k);

			if(is_array($v))
				static::QueryPart($v,$k.'[',$r);
			elseif($v or (string)$v=='0')
				$r[]=$k.'='.(is_string($v) ? urlencode($v) : (int)$v);
		}

		return join($d,$r);
	}

	/**
	 * Генерация многомерных параметров для метода Query.
	 * @param array $a Параметры
	 * @param string $p Префикс для каждого параметра
	 * @param array &$r Ссылка на массив для помещения результатов
	 */
	protected static function QueryPart(array$a,$p,&$r)
	{
		$i=0;

		foreach($a as $k=>&$v)
			if(is_array($v))
				static::QueryPart($v,$p.$k.'][',$r);
			elseif($v or (string)$v=='0')
				$r[]=$p.(($k===$i++) ? '' : urlencode($k)).']='.(is_string($v) ? urlencode($v) : (int)$v);
	}
}

Url::$curpage=isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING'];
Url::$curpage.='&';
if(strpos(Url::$curpage,'!')===0 and strpos(Url::$curpage,'!&')!==false)
{
	Url::$curpage=str_replace('!&','?',ltrim(Url::$curpage,'!'));
	Url::$curpage=rtrim(Url::$curpage,'?&');
	Url::$curpage=Url::Decode(Url::$curpage);
}
else
	Url::$curpage=substr($_SERVER['REQUEST_URI'],strlen(Eleanor\SITEDIR));