<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class Strings extends BaseClass
{	/**
	 * �������� ������������� e-mail
	 *
	 * @param string $s ����������� e-mail
	 * @param bool $ep ���� ������������� ������� ��������, ��� �����������
	 */
	public static function CheckEmail($s,$ep=true)
	{
		$ab=constant(Language::$main.'::ALPHABET');
		$s=(array)$s;
		foreach($s as &$v)
			if((!$ep or $v) and preg_match('#^[_\-\.\wa-z'.$ab.'0-9]+\@([\wa-z'.$ab.'0-9](?:[\.\-\wa-z'.$ab.'0-9][\wa-z'.$ab.'0-9])*)+\.[\wa-z'.$ab.'\-]{2,}$#i',$v)==0)
				return false;
		return true;
	}

	/**
	 * �������� ������������ ������ ������
	 *
	 * @param string $s ���������� ������
	 */
	public static function CheckUrl($s)
	{
		$s=trim($s);
		if(strpos($s,'mailto:')===0)
			return self::CheckEmail(substr($s,7));
		$ab=constant(Language::$main.'::ALPHABET');
		return preg_match('~^([a-z]{3,10}://[\wa-z'.$ab.'0-9/\._\-:]+\.[\wa-z'.$ab.'\-]{2,}/)?(?:[^\s{}]*)?$~i',$s)>0;
	}

	/**
	 * �������������� ��������� ������ ���������� � ������. ��������� ������������ ���� ������������ ������.
	 * ����� ��������� �������� � UTF-8: l�������� ��������� mb_ � substr �� �����.
	 *
	 * @param string $s ������ ����������, ������� param1="value1" param2=   value2 param3=  "value3"
	 * @param string $first ��� ������� ���������, � ������ ���� $s ���������� � "=" (� BB ����� ����� �������� [url=http://eleanor-cms.ru]CMS[/url]
	 */
	public static function ParseParams($s,$first=0)
	{
		$a=array();
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
							$a[$param]=true;#�������� "�������" ���������.
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
					$a[$param]=true;#�������� "�������" ���������.
				$cur=$m[0][1]+strlen($m[0][0]);
			}
			else
				break;
		}
		return$a;
	}

	/**
	 * ���������� ������� ������ �� N ��������. ����� �� ������ html ���������.
	 *
	 * @param string $s ������, ������� ���������� ��������
	 * @param int $n ����� ��������, �� ������� ����� �������� ������, ������ ����� �������
	 * @param string $e ������ ���������� ��������
	 */
	public static function CutStr($s,$n=30,$e='...')
	{
		if(mb_strlen($s)>$n)
		{
			$s=mb_substr($s,0,$n);
			$s=preg_replace('#[&<][^;>]*$#','',$s).$e;
		}
		return$s;
	}

	/**
	 * ������ ucfirst �������, ��������� ���������� � utf-8
	 *
	 * @param string $s ��������� ������
	 */
	public static function UcFirst($s)
	{
		if(!$s)
			return$s;
		return mb_strtoupper(mb_substr($s,0,1)).mb_substr($s,1);
	}

	/**
	 * ������������ ������ ��� ������������ ����������� �������������� � ������ ��� ������ explode.
	 * ������� ����� ������������, ������� ��������� � ������ � � ����� ������.
	 *
	 * @param string $s �������� ������
	 * @param string $d ����������� ��� ������������ explode
	 */
	public static function CleanForExplode($s,$d=',')
	{
		$dq=preg_quote($d,'/');
		$s=preg_replace('/(?:'.$dq.'){2,}/',$d,$s);
		return preg_replace(array('/(?:'.$dq.')$/','/^(?:'.$dq.')/'),'',$s);
	}

	/**
	 * ��������� ���� � ������ ������������ ������. ����� ��������� ����� ��� ����.
	 *
	 * @param string|array $w ����� ��� ���������
	 * @param string $s ����� � ������� ����� ���������� ��������
	 * @param string $c ���� ������ � ���������
	 * @param string $bc ���� ������ ���������
	 */
	public static function MarkWords($w,$s,$c='#FFFF00',$bc='#FF0000')
	{
		if(!$s or !$w)
			return $s;
		$w=(array)$w;
		foreach($w as $k=>&$v)
		{
			$v=preg_quote(str_replace(array('<','>'),'',trim($v)),'#');
			if($v=='')
				unset($w[$k]);
		}

		$F=function($s)use($w,$c,$b)
		{
			$s=stripslashes($s);
			foreach($w as &$v)
				$v=preg_quote($v,'#');
			return preg_replace('#(?:\b)('.join('|',$w).')(?:\b)#i','<span style="background-color: '.$c.'; color: '.$b.';">\1</span>',$s);
		};
		return preg_replace('#(?<=>|^)([^<]+)#e','$F(\'\1\')',$s);
	}
}