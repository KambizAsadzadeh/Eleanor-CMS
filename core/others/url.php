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
class Url extends BaseClass
{
		$s_prefix,#������� ���� ����� � �������
		$d_prefix='?',#������� ���� ����� � ��������
		$delimiter='/',#������, ��� ������������������� �������� ��� ���������� ���������� � �������
		$defis='_',#������ ��� ��������� ���������� �� �������� � �������
		$ending='.html',#��������� ���� ����� �������������� ������ � �������
		$string,#������ ����, ������� �� ������
		$is_static=false,
		$file,#���� ��� ������������ ������
		$furl=false;#��� - �������� ���������������� ���

/*
	#ToDo!
	public function __invoke(array$p=array(),$pr=true,$e=true)
	{
*/
	public function Construct(array$p=array(),$pr=true,$e=true)
	{
		{
		}
		else
			$suf=false;

		$r=$or=array();#resul & results object
		{
				$e=$this->ending;
			elseif($e===false)
				$e=$this->delimiter;
			foreach($p as $pk=>&$pv)
				if(is_array($pv))
				{
					{
						if(is_int($k))
						{
							{
								$add=false;
								if($v===true)
								{
									$or[$pk]=function($v)
									{
										if($v or (string)$v=='0')
											return Url::Encode($v);#ToDo! PHP 5.4 Url=>static
									};
								else
									$r[]=static::Encode($v);
							}
						}
						elseif($add)
						{
							{
								$THIS=$this;#ToDo! PHP 5.4 ������ ���� ������� (������ ����) use ($THIS)
								$or[$pk]=function($v) use ($k,$THIS)
								{
									if($v or (string)$v=='0')
										return Url::Encode($k).$THIS->defis.Url::Encode($v);#ToDo! PHP 5.4 Url=>static
								};
							elseif($v or (string)$v=='0')
								$r[]=static::Encode($k).$this->defis.static::Encode($v);
						else
						{
							continue;
					}
				}
				elseif($pv===true)
				{
					$or[$pk]=function($v)
					{
						if($v or (string)$v=='0')
							return Url::Encode($v);#ToDo! PHP 5.4 Url=>static
					};
				elseif($pv or (string)$pv=='0')
					$r[]=static::Encode($pv);

			if($pr===true)
				$pr=$this->s_prefix;
			if($or)
				return new UrlFunc($r,$or,$this->delimiter,$pr,$suf ? '?'.$suf : '',$e);
			$r=$r ? $pr.join($this->delimiter,$r).$e : $pr;

			if($suf)
				$r.='?'.$suf;
		}
		else
		{
				if(is_array($pv))
				{
					foreach($pv as $k=>&$v)
						if(is_string($k) and ($v or (string)$v=='0'))
						{
							if($v===true)
							{
								$r[$pk]=null;
								$or[$pk]=function($v) use ($k)
								{
									if($v or (string)$v=='0')
										return urlencode($k).'='.urlencode($v);
								};
							}
							else
								$r[]=urlencode($k).'='.urlencode($v);
						}
				}
				elseif($pv===true)
				{
					$r[$pk]=null;
					$or[$pk]=function($v) use ($pk)
					{
							return urlencode($pk).'='.urlencode($v);
					};
				}
				elseif($pv or (string)$pv=='0')
					$r[]=urlencode($pk).'='.urlencode($pv);

			if($suf)
				$r[]=$suf;
			if($pr===true)
				$pr=$this->file.$this->d_prefix;
			if($or)
				return new UrlFunc($r,$or,'&amp;',$pr);

			if($e===true)
				$e='';

			//die(var_dump($p,$r,$this->d_prefix));
			$r=$r ? $pr.join('&amp;',$r).($e===false ? '&amp;' : $e) : ($e===false ? $pr : preg_replace('#(&amp;|&|\?)$#','',$pr).$e);
		}
		return$r;
	}

	public function Parse(array$params=array(),$pd=true)#Parse defis
	{
		{

			$input=ltrim($input,$this->delimiter);
			/*if(strpos($input,$this->delimiter)===0)
				$input=substr($input,strlen($this->delimiter));*/

			$a=$input=='' ? array() : explode($this->delimiter,$input);
			/*$a=array();
			if(strpos($input,$this->defis)!==false and strlen($this->defis)>strlen($this->delimiter))
			{
				$delim=count_chars($this->delimiter,1);
				$defis=count_chars($this->defis,1);
				if(count(array_diff_key($delim,$defis))>0)
					$a=preg_split('#(?<=[a-z0-9'.constant(Language::$main.'::ALPHABET').'])'.preg_quote($this->delimiter,'#').'(?=[a-z0-9'.constant(Language::$main.'::ALPHABET').'])#',$input);
			}
			if(!$a and $input)
				$a=explode($this->delimiter,$input);*/

			$r=array();
			$n=-1;
			foreach($a as &$v)
				if($pd and strpos($v,$this->defis)!==false)
				{
					$ek=explode($this->defis,$v,2);
					$r[$ek[0]]=$ek[1];
				}
				elseif(isset($params[++$n]))
					$r[$params[$n]]=$v;
				else
					$r[''][]=$v;
			$this->string='';
		else
			parse_str($this->string,$r);
		return$r;
	}

	/*
		������� ���������� "���������" ������. �.�. ".html", "/". �������� ������ ��� ������� (�� �������� ��������)
		��������! ������������� ������ ������������ ��������� � �����, ���� ��������� �� ����� - ������� ����� �������� �����������.
	*/
	public function GetEnding($es=array(),$cut=true)
	{
		{
			foreach((array)$es as $v)
				$ends.=preg_quote($v,'#').'|';
			$e=preg_match('#('.rtrim($ends,'|').')$#',$this->string,$m)>0 ? $m[1] : '';
		}
		else
		{
		}
		if($e and $cut)
			$this->string=substr($this->string,0,-strlen($e));
		return$e;
	/*
		���������� �� ������� ������� ��������. ���, ��� ���� ����� ����� - ��� ��������� ������.
	*/
	public function ParseToValue($p,$cut=true,$pd=true)#parse defis
	{
			return isset($_GET[$p]) ? $_GET[$p] : false;
		$str=strtok($this->string,$this->delimiter);
		$value=false;
		$a=array();
		$ending=preg_quote($this->ending,'#');
		while($str!==false)
		{
			{
				break;
			else
			{
				if($temp[0]==$p)
				{
					break;
				elseif($cut)
					$a[$temp[0]]=preg_replace('#'.$ending.'$#i','',$temp[1]);
			$str=strtok($this->delimiter);
		if($a)
			$_GET+=$a;
		if($cut)
			$this->string=strtok('');
		if($value)
			$value=preg_replace('#'.$ending.'$#i','',$value);
		return$value;
	}

	/*
		������� ������� �� ������ �� ������� ������������� - �������� ��� ������������ ������� � ������� �� $rep
	*/
	public function Filter($s,$l=false,$rep=false)
	{
			$l=Language::$main;
		if(Eleanor::$vars['trans_uri'] and method_exists($l,'Translit'))
			$s=$l::Translit($s);
		if($rep===false)#ToDo! parent::framework
			$rep=Eleanor::$vars['url_rep_space'];

		$s=preg_replace(array('`('.preg_quote($this->defis,'#').'|'.preg_quote($this->delimiter,'#').'|[\\\\=\s#,"\'\\/:*\?&\+<>%\|])+`','#('.preg_quote($this->ending,'#').')+$#'),$rep,$s);
		$rep=preg_quote($rep,'#');
		return preg_replace('#^('.$rep.')+|('.$rep.')+$#','',$s);
	}

	public function Prefix($e=true)
	{
			return$e===false ? $this->s_prefix : preg_replace('#'.preg_quote($this->delimiter,'#').'$#','',$this->s_prefix).($e===true ? '' : $e);

		$p=$this->file.$this->d_prefix;
		return$e===false ? $p : preg_replace('#(&amp;|&|\?)$#','',$p).($e===true ? '' : $e);
	}

	public function GetDel()
	{

	public function SetPrefix($p,$a=false)
	{
		{
			$this->furl=true;
			$this->d_prefix=($a ? $this->d_prefix : '?').$this->Construct($p,false,false);
			$this->furl=$f;
		}
		elseif($this->furl)
		{
			$this->s_prefix=$a ? $this->s_prefix.$p : $p;
		}
		else
		{
			if(!$p and !$a)
				$p='?';
			else
			{
				$p=preg_replace('#(&amp;)+$#','',$p);
				if(false!==$qp=strpos($p,'?'))
					$p=substr($p,$qp);
				$p.='&amp;';
			}
			$this->d_prefix=$a ? $this->d_prefix.$p : $p;
		}
	}

	public function __construct($qs=false)
	{
		if($qs===false)
			$qs=$_SERVER['QUERY_STRING'];
		if($qs)
		{
			{
				$cp=$ap===false ? false : strpos($qs,'=',$ap);
			}
			else
			{
				$cp=strpos($qs,'=');
				$ap=strpos($qs,'&');
			}
			$this->is_static=$cp===false || $ap!==false && $cp>$ap;
			if($this->is_static and $ap!==false)
				$qs=substr($qs,0,$ap);
			$this->string=static::Decode($qs);
		}
		$this->file=Eleanor::$filename;
	}

	public static function Encode($s)
	{
		return urlencode(CHARSET=='utf-8' ? $s : mb_convert_encoding((string)$s,'utf-8'));
	}

	public static function Decode($s)
	{
		return preg_match('/^.{1}/us',$s)==1 ? mb_convert_encoding($s,CHARSET,'utf-8') : $s;
	}

	protected static function QueryPart($a,$p,&$r)
	{
		$i=0;
		foreach($a as $k=>&$v)
			if(is_array($v))
				static::QueryPart($v,$p.$k.'][',$r);
			elseif($v or (string)$v=='0')
				$r[]=$p.(($k===$i++) ? '' : urlencode($k)).']='.(is_string($v) ? urlencode($v) : (int)$v);
	}

	public static function Query($a,array$o=array())
	{
		$o+=array(
			'delim'=>'&amp;',
		);
		$r=array();
		foreach($a as $k=>&$v)
		{
			if(is_array($v))
				static::QueryPart($v,$k.'[',$r);
			elseif($v or (string)$v=='0')
				$r[]=$k.'='.(is_string($v) ? urlencode($v) : (int)$v);
		}
		return join($o['delim'],$r);
	}
}