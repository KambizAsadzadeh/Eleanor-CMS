<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Набор текстовых функций (работа со строками) */
class Strings extends Eleanor\BaseClass
{
	/** Преобразование текстовой строки параметров в массив. Корректно обрабатывает даже некорректные данные.
	 * Метод корректно работает с UTF-8: lобавлять параметры mb_ в substr не нужно.
	 * @param string $s Строка параметров, формата param1="value1" param2=   value2 param3=  "value3"
	 * @param string|int $first Имя первого параметра, в случае если $s начинается с "="
	 * (в BB кодах такое возможно [url=http://eleanor-cms.ru ]CMS[/url] )
	 * @return array */
	public static function ParseParams($s,$first=0)
	{
		$a=[];
		$s=trim($s);
		$l=strlen($s);

		$cur=0;
		$finp=false;
		$param='';

		while($cur<$l)
		{
			if($cur==0 and substr($s,$cur,1)=='=')
			{
				$param=$first;
				$finp=true;
				$cur++;
			}

			if($finp)
			{
				$finp=false;

				switch($q=substr($s,$cur,1))
				{
					case'"':
					case'\'':
						if(preg_match('#'.$q.'([^'.$q.']*)'.$q.'#',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
							$a[$param]=$m[1][0];
						else
						{
							$a[$param]=substr($s,$cur+1);
							break 2;
						}

						$cur=$m[0][1]+strlen($m[0][0]);
					break;
					default:
						if(preg_match('#[^\s"\']+#',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
							$a[$param]=$m[0][0];
						else
						{
							$a[$param]=true;#Обрубаем "висячие" параметры.
							break 2;
						}

						$cur=$m[0][1]+strlen($m[0][0]);
				}
			}
			elseif(preg_match('#([a-z0-9]+)(\s*=\s*)?#i',$s,$m,PREG_OFFSET_CAPTURE,$cur)>0)
			{
				$param=$m[1][0];

				if(isset($m[2]))
					$finp=true;
				else
					$a[$param]=true;#Обрубаем "висячие" параметры.

				$cur=$m[0][1]+strlen($m[0][0]);
			}
			else
				break;
		}
		return$a;
	}

	/** Корректная обрезка строки до N символов. Метод не ломает html мнемоники.
	 * @param string $s Строка, которую необходимо обрезать
	 * @param int $n Число символов, до которых нужно обрезать строку, считая слева направо
	 * @param string $e Замена обрезанных символов
	 * @return string */
	public static function CutStr($s,$n=30,$e='...')
	{
		if(mb_strlen($s)>$n)
		{
			$s=mb_substr($s,0,$n);
			$s=trim(preg_replace('#[&<][^;>]*$#','',$s),';., ').$e;
		}

		return$s;
	}

	/** Версия функции ucfirst, которая корректно с utf-8
	 * @param string $s Воходящая строка
	 * @return string */
	public static function UcFirst($s)
	{
		return $s ? mb_strtoupper(mb_substr($s,0,1)).mb_substr($s,1) : '';
	}

	/** Выделение слов в тексте определенным цветов. Метод корректно минут все теги.
	 * @param string|array $w Слово для выделения
	 * @param string $s Текст в котором слово необходимо выделить
	 * @param string $params Цвет текста в выделении
	 * @return string */
	public static function MarkWords($w,$s,$params='style="background-color:#FFFF00;color:#FF0000;"')
	{
		if(!$s or !$w)
			return $s;

		$w=(array)$w;
		$params=preg_quote($params,'#');

		foreach($w as $k=>&$v)
		{
			$v=preg_quote(str_replace(['<','>'],'',trim($v)),'#');
			if($v=='')
				unset($w[$k]);
		}

		return preg_replace_callback(
			'#(?<=>|^)([^<]+)#',
			function($s)use($w,$params)
			{
				return preg_replace('#(?:\b)('.join('|',$w).')(?:\b)#i','<span '.$params.'>\1</span>',$s[1]);
			},
			$s
		);
	}

	/** Ord для строк UTF-8
	 * @param string $string Строка
	 * @param int $offset Позиция символа
	 * @return int */
	function OrdUtf8($string, &$offset)
	{
		$code=ord(substr($string, $offset,1));

		if($code>=128)#otherwise 0xxxxxxx
		{
			if($code<224)#110xxxxx
				$bytesnum=2;
			elseif($code<240)#1110xxxx
				$bytesnum=3;
			elseif($code<248)#11110xxx
				$bytesnum=4;
			else
				$bytesnum=1;

			$codetemp=$code-192-($bytesnum>2 ? 32 : 0)-($bytesnum>3 ? 16 : 0);
			for($i=2;$i<=$bytesnum;$i++)
			{
				$offset++;
				$code2=ord(substr($string,$offset,1))-128;#10xxxxxx
				$codetemp=$codetemp*64+$code2;
			}

			$code=$codetemp;
		}

		$offset++;
		if($offset>=strlen($string))
			$offset=-1;

		return$code;
	}
}