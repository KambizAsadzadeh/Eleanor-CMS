<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

defined('Eleanor\Classes\ENT')||define('Eleanor\Classes\ENT',ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED);

/** Преобразователь BB кодов в HTML и обратно */
class BBCode extends Eleanor\BaseClass
{
	public static
		/** @static Включение проверки корректности ссылок. Полезно в случае сохранения текста с переменными */
		$checkup=true,

		/** @static BB теги, подлежащие замене на одноименные HTML теги */
		$tags=['b','p','i','s','a','q','li','ul','ol','em','tt','big','sub','sup','var','abbr','cite','code',
			'spansmall','strong','noindex','legend','blockquote','span','small','address','option','optgroup','select',
			'table','tr','td','th','thead','tfoot','tbody','caption','col','colgroup','legend','fieldset','object',
			'param','article','aside','details','details','figcaption','figure','footer','header','hgroup','mark','nav',
			'wbr','source','video','time','summary','section','ruby','rp','rt','progress','output','h1','h2','h3','h4',
			'h5','h6','option','textarea','select','optgroup'];

	/** Преобразование текста из BB в HTML
	 * @param string $bb Текст, размеченный BB кодами
	 * @return string */
	public static function BB2HTML($bb)
	{
		#Списки
		$List=function($text)
		{
			$text=trim($text);

			if(preg_match('#^\[(ul|ol)([^\]]*)\](.+)$#is',$text,$m)==0)
				return'';

			$tag=strtolower($m[1]);
			$params=Strings::ParseParams($m[2]);
			$text=trim($m[3]);
			$attribs=[];

			foreach($params as $k=>$v)
				switch(strtolower($k))
				{
					case'id':
					case'class':
					case'style':
					case'title':
						$attribs[$k]=$k.'="'.htmlspecialchars($v,ENT,Eleanor\CHARSET,false).'"';
				}

			if(strpos($text,'[*]')==0)
			{
				$text=str_replace('[*]','</li><li>',$text);
				$text=preg_replace('#^</li>#','',$text);
				$text=preg_replace("#(\r)?(\n)?</li>#",'</li>',$text.'</li>');
			}
			else
				$text='<li>'.$text.'</li>';

			return'<'.$tag.($attribs ? ' '.join(' ',$attribs) : '').'>'.$text.'</'.$tag.'>';
		};
		$bb=static::ParseContainer($bb,'[ul','[/ul]',$List,true);
		$bb=static::ParseContainer($bb,'[ol','[/ol]',$List,true);
		#/Списки

		$Image=function($src,$params)
		{
			$params=Strings::ParseParams($params,'src');
			$attribs=[];

			foreach($params as $k=>$v)
			{
				$v=htmlspecialchars($v,ENT,Eleanor\CHARSET,false);

				switch(strtolower($k))
				{
					default:
						if(strpos($k,'data-')!==0)
							continue;
					case'alt':
					case'title':
						$attribs['alt']='alt="'.$v.'" title="'.$v.'"';
					break;
					case'id':
					case'class':
						$attribs[$k]=$k.'="'.$v.'"';
					break;
					case'src':
						$src=htmlspecialchars($src,ENT,Eleanor\CHARSET,false);
						$attribs['alt']='alt="'.$src.'" title="'.$src.'"';
						$src=$v;
				}
			}

			return'<img src="'.$src.'"'.($attribs ? ' '.join(' ',$attribs) : '').' />';
		};

		$Url=function($text,$params)
		{
			if(is_string($params))#На случай, если мы обратимся из функции DoEmail
				$params=Strings::ParseParams($params,'href');

			if(!isset($params['href']))
			{
				$params['href']=$text;

				if(strlen($text)>55)
					$text=substr($text,0,35).'...'.substr($text,-15);
			}

			$sitepref=Eleanor\PROTOCOL.Eleanor\DOMAIN.Eleanor\SITEDIR;

			if(static::$checkup and stripos($params['href'],$sitepref)===0)
				$params['href']=substr($params['href'],strlen($sitepref));

			$attribs=['target'=>'target="_blank"'];

			foreach($params as $k=>$v)
				switch(strtolower($k))
				{
					default:
						if(strpos($k,'data-')!==0)
							continue;
					case'id':
					case'class':
					case'title':
					case'target':
					case'href':
					case'rel':
						$attribs[$k]=$k.'="'.htmlspecialchars($v,ENT,Eleanor\CHARSET,false).'"';
					break;
					case'self':
						unset($attribs['target']);
				}

			return'<a'.($attribs ? ' '.join(' ',$attribs) : '').'>'.$text.'</a>';
		};

		foreach(['img'=>$Image,'url'=>$Url] as $tag=>$handler)
		{
			$ostp=-1;#Old STart Pos
			$stp=0;#STart Pos

			while(false!==$stp=stripos($bb,'['.$tag,$stp))
			{
				if($stp==$ostp)
				{
					++$stp;
					continue;
				}

				$taglen=strlen($tag);

				#Возможно, мы нашли какой-то другой тег, который начинается с текущего [i != [img
				if(trim($bb{$stp+$taglen+1},'=] ')!='')
				{
					++$stp;
					continue;
				}

				$len=false;#Длина всего открывающего тега вместе с его параметрами \] не считается закрытие тега

				do
				{
					$len=strpos($bb,']',$len ? $len+1 : $stp);

					if($len===false)
					{
						++$stp;
						continue 2;
					}
				}while($bb{$len-1}=='\\');

				$params=substr($bb,$stp+$taglen+1,$len-$stp-4);#1 это пробел или = после имени тега, 4 - подумай сам :)
				$params=str_replace('\\]',']',$params);

				if(false===$finp=stripos($bb,'[/'.$tag.']',$len+1))#FINish Pos
				{
					++$stp;
					continue;
				}

				$result=$handler(trim(substr($bb,$len+1,$finp-$len-1)),trim($params));
				$len=$finp-$stp+$taglen+3;#[/]
				$bb=substr_replace($bb,$result,$stp,$len);
				$ostp=$stp++;
			}
		}

		#Обработка некоторых устойчивых тегов
		$bb=str_replace(['[c]','[tm]','[r]','[s]','[/s]','[u]','[/u]','[hr]',"\t"],
			['&copy;','&trade;','&reg;','<span style="text-decoration:line-through">','</span>',
				'<span style="text-decoration:underline">','</span>','<hr />','&nbsp;&nbsp;&nbsp;&nbsp;'],
			$bb);

		#Обработка ссылок на e-mail
		$bb=preg_replace_callback('#\[email([^\]]*?)\](.+?)\[/email\]#i',function($m)use($Url){
			$params=Strings::ParseParams($m[1],'href');
			$text=trim($m[2]);

			if(!isset($params['href']))
				$params['href']=$text;

			if(static::$checkup and !filter_var($params['href'],FILTER_VALIDATE_EMAIL))
				return'';

			if(strpos($params['href'],'mailto:')!==0)
				$params['href']='mailto:'.preg_replace('#^mailto:#i','',$params['href']);

			return$Url($text,$params);
		},$bb);

		#Обработка тегов выравнивания текста: left|right|center|justify
		$bb=preg_replace('#\[(left|right|center|justify)\](.+?)\[/\1\]#is','<p style="text-align:\1"\>\2</p>',$bb);

		#Размер
		$bb=preg_replace('#\[size=(xx?-small|small|medium|large|xx?-large)[^\]]*\](.+?)\[/size\]#is',
			'<span style="font-size:\1">\2</span>',$bb);
		$bb=preg_replace_callback(
			'#\[size=(\d{1,})(px|pt|em)?[^\]]*\](.+?)\[/size\]#is',
			function($m){
				$pt=$m[2] ? $m[2] : 'pt';
				$size=(int)$m[1];

				#Безопасность от сверхогромного и сверхмалого шрифта
				if($size<1)
					return$m[3];

				switch($pt)
				{
					case'pt':
						if($size>25)
							return$m[3];
					break;
					case'px':
						if($size>34)
							return$m[3];
					break;
					case'em':
						if($size>4)
							return$m[3];
				}

				return'<span style="font-size:'.$size.$pt.'">'.$m[3].'</span>';
			},
			$bb
		);

		#Цвет
		$bb=preg_replace('%\[background=([a-z0-9\-#]+)[^\]]*\](.+?)\[/background\]%is',
			'<span style="background-color:\1">\2</span>',
			$bb);
		$bb=preg_replace('%\[color=([a-z0-9\-#]+)[^\]]*\](.+?)\[/color\]%is',
			'<span style="color:\1">\2</span>',
			$bb);

		#Шрифт
		$bb=preg_replace('%\[font=([a-z0-9\-;\s]+)[^\]]*\](.+?)\[/font\]%is',
			'<span style="font-family:\1">\2</span>',
			$bb);

		#Замена ошибочных символов
		$bb=preg_replace('/&amp;#(\d+?);/i','&#\1;',$bb);
		$bb=preg_replace('/&#(\d+?)([^\d;])/','&#\1;\2',$bb);

		#Замена одиночных тегов
		$bb=preg_replace_callback('#\[input ([^\]]*?)\]#is',function($m){
			$params=Strings::ParseParams($m[1],'href');
			$attribs=['type'=>'text'];

			foreach($params as $k=>$v)
				switch(strtolower($k))
				{
					default:
						if(strpos($k,'data-')!==0)
							continue;
					case'id':
					case'class':
					case'title':
					case'type':
					case'value':
					case'min':
						$attribs[$k]=$k.'="'.htmlspecialchars($v,ENT,Eleanor\CHARSET,false).'"';
				}

			return'<input'.($attribs ? ' '.join(' ',$attribs) : '').' />';
		},$bb);

		$tags=join('|',static::$tags);
		$Handler=function($m)
		{
			$tag=$m[1];
			$params=Strings::ParseParams($m[2],'href');
			$attribs=[];

			foreach($params as $k=>$v)
				switch(strtolower($k))
				{
					default:
						if(strpos($k,'data-')!==0)
							continue;
					case'id':
					case'class':
					case'title':
						$attribs[$k]=$k.'="'.htmlspecialchars($v,ENT,Eleanor\CHARSET,false).'"';
				}

			return'<'.$tag.($attribs ? ' '.join(' ',$attribs) : '').'>'.trim($m[3]).'</'.$tag.'>';
		};
		while(preg_match('#\[('.$tags.')(\s+[^\]]+)?\].*?\[/\1\]#is',$bb))
			$bb=preg_replace_callback('#\[('.$tags.')(\s+[^\]]+)?\](.*?)\[/\1\]#is',$Handler,$bb);

		return nl2br($bb);
	}

	/** Преобразование HTML разметки в текст, текст размеченный BB кодами
	 * @param string $html Текст с HTML разметкой
	 * @return string */
	public static function HTML2BB($html)
	{
		#Списки
		$List=function($text)
		{
			$text=trim($text);

			if(preg_match('#^<(ul|ol)([^>]*)>(.+)$#is',$text,$m)==0)
				return'';

			$tag=strtolower($m[1]);
			$params=Strings::ParseParams($m[2]);
			$text=str_replace(['<li>','</li>'],["\n[*]",''],trim($m[3]));
			$attribs='';

			foreach($params as $k=>$v)
			{
				$q='';

				if(strpos($v,'"')!==false)
					$q='\'';
				elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
					$q='"';

				$attribs.=' '.$k.'='.$q.$v.$q;
			}

			return'['.$tag.$attribs.']'.$text."\n[/".$tag.']';
		};

		$html=static::ParseContainer($html,'<ul','</ul>',$List,true);
		$html=static::ParseContainer($html,'<ol','</ol>',$List,true);
		#/Списки

		$html=preg_replace_callback('#<a([^>]+?)>(.+?)</a>#',function($m){
			$text=trim($m[2]);
			$params=$m[1] ? Strings::ParseParams($m[1]) : [];
			$tag='url';
			$attribs=['self'=>'self'];
			$sitepref=Eleanor\PROTOCOL.Eleanor\DOMAIN.Eleanor\SITEDIR;

			if(isset($params['href']) and stripos($text,$sitepref)===0 and $params['href']==substr($text,strlen($sitepref)))
				unset($params['href']);

			$ta='';
			foreach($params as $k=>$v)
			{
				$q='';

				if(strpos($v,'"')!==false)
					$q='\'';
				elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
					$q='"';

				switch(strtolower($k))
				{
					case'target':
						unset($attribs['self']);

						if($v!='_blank')
							$attribs[$k]='target='.$v;
					break;
					case'href':
						if(strpos($v,'mailto:')===0)
						{
							$tag='email';
							$v=preg_replace('#^mailto:#','',$v);
						}

						if($v==$text)
							continue;

						$ta.='='.$q.$v.$q;
					break;
					default:
						if(strpos($k,'data-')!==0)
							continue;
					case'id':
					case'class':
					case'title':
					case'rel':
						$attribs[$k]=$k.'='.$q.$v.$q;
				}
			}

			#Для того, чтобы параметр self был всегда последним
			if(isset($attribs['self']))
			{
				unset($attribs['self']);
				$attribs[]='self';
			}

			return'['.$tag.$ta.($attribs ? ' '.join(' ',$attribs) : '').']'.$text.'[/'.$tag.']';
		},$html);

		$html=preg_replace('#<(p|div) style="text-align:\s*(left|right|center|justify);?">(.+?)</\1>#si','[\2]\3[/\2]',
			$html);
		$html=preg_replace('#<span style="text\-decoration:\s*line-through;?">(.+?)</span>#si','[s]\1[/s]',$html);
		$html=preg_replace('#<span style="text\-decoration:\s*underline;?">(.+?)</span>#si','[u]\1[/u]',$html);
		$html=preg_replace("#(<br>|<br />)[\r\n]*#i","\n",$html);
		$html=preg_replace('#<hr ?/?>*#i','[hr]',$html);
		$html=preg_replace(
			'#<span style="font-size:\s*(\d{1,}|xx?-small|small|medium|large|xx?-large)(px|pt|em)?;?">(.+?)</span>#s',
			'[size=\1\2]\3[/size]',$html);
		$html=preg_replace('#<span style="font-family:\s*(.+?)">(.+?)</span>#s','[font=\1]\2[/font]',$html);
		$html=preg_replace('#<span style="color:\s*([^"]+?)">(.+?)</span>#s','[color=\1]\2[/color]',$html);
		$html=preg_replace('#<span style="background-color:\s*(.+?)">(.+?)</span>#s','[background=\1]\2[/background]',
			$html);

		$html=preg_replace_callback('#<img([^>]+?)>#',function($m){
			$params=Strings::ParseParams($m[1]);
			$attribs=[];

			foreach($params as $k=>$v)
			{
				$q='';
				if(strpos($v,'"')!==false)
					$q='\'';
				elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
					$q='"';

				switch(strtolower($k))
				{
					case'border':
						$v=(int)$v;
						if($v>5)
							$v=5;
						if($v>0)
							$attribs['border']='border='.$v;
						break;
					case'alt':
					case'title':
						if($v!='')
							$attribs['alt']='alt='.$q.$v.$q;
						break;
					case'src':
						break;
					default:
						$attribs[$k]=$k.'='.$q.$v.$q;
				}
			}
			return'[img'.($attribs ? ' '.join(' ',$attribs) : '').']'.$params['src'].'[/img]';
		},$html);

		$html=preg_replace('#<input(\s+[^>]+?)?>#is','[input\1]',$html);

		$bbs=join('|',static::$tags);
		$Handler=function($m)
		{
			$params=Strings::ParseParams($m[2]);
			$attribs=[];

			foreach($params as $k=>$v)
			{
				$q='';
				if(strpos($v,'"')!==false)
					$q='\'';
				elseif(strpos($v,'\'')!==false or preg_match('#\s#',$v)>0)
					$q='"';

				$attribs[$k]=$k.'='.$q.$v.$q;
			}

			return'['.$m[1].($attribs ? ' '.join(' ',$attribs) : '').']'.$m[3].'[/'.$m[1].']';
		};
		while(preg_match('#<('.$bbs.')(\s+[^>]*)?>.*?</\1>#is',$html))
			$html=preg_replace_callback('#<('.$bbs.')(\s+[^>]*)?>(.*?)</\1>#is',$Handler,$html);

		return str_replace(['&copy;','&trade;','&reg;','&nbsp;&nbsp;&nbsp;&nbsp;'],['[c]','[tm]','[r]',"\t"],$html);
	}

	/** Обработка контейнера в тексте. Например есть текст: '[quote]Первая цитата[quote]Цитата в цитате[/quote][/quote]'
	 * Если мы будем пытаться обработать цитату при помощи регулярки '#\[quote([^\]]*)\](.*)\[/quote\]#U' то получим:
	 *    |------------Первая цитатая------------------|
	 *    |                     |-----Вторая цитата----|-------|
	 * '[quote]Первая цитатая [quote]Цитата в цитате[/quote][/quote]';
	 * Это метод метод позволяет добиться корректной обработки цитаты:
	 *    |------------Вторая цитатая--------------------------|
	 *    |                     |-----Первая цитата----|       |
	 * '[quote]Первая цитатая [quote]Цитата в цитате[/quote][/quote]';
	 *
	 * @param string $s Входящий текст с контейнером
	 * @param string $beg Начало контейнера
	 * @param string $end Конец контейнера
	 * @param callable $cb Обработчик, которому передается строка, $beg и содержимое контейнера и не содержащая $end
	 * @return string */
	public static function ParseContainer($s,$beg,$end,$cb)
	{
		$bl=strlen($beg);
		$el=strlen($end);
		while(false!==$bp=strpos($s,$beg) and false!==$ep=strpos($s,$end,$bp+1+$bl))
		{
			$brp=strrpos(substr($s,0,$ep-1),$beg);
			if($brp>$bp)
				$bp=$brp;
			$ns=substr($s,$bp,$ep-$bp);
			$ns=call_user_func($cb,$ns);
			$s=substr_replace($s,$ns,$bp,$ep-$bp+$el);
		}
		return$s;
	}

	/** Интерпретация bb логики в тексте. Несколько примеров
	 * Вывод переменной: {var}
	 * Условия: [var]Переменная var равна {var}[/var]
	 * Условия с else: [var]Переменная var равна {var}[-var]Переменная var пуста[/var]
	 * Подбора корректной формы слова, в зависимости от рядом стоящего числа: Вам {var} [var=plural]год|года|лет[var]
	 * captcha.php* Сравненеие: [var>2]Переменная var больше 2[/var]
	 * @param string $text Текст, с bb переменными
	 * @param array $bbs Массив var=>значение
	 * @return string */
	public static function ExecLogic($text,array$bbs)
	{
		foreach($bbs as $k=>$v)
		{
			$fp=0;

			while(false!==$fp=strpos($text,'['.$k,$fp))
			{
				$kl=strlen($k);

				if(trim($text[$fp+$kl+1],'=] ')!='')
				{
					++$fp;
					continue;
				}

				$fclb=false;#First Close Bracket post  ( [var... ] <- this right bracket )
				do
				{
					$fclb=strpos($text,']',$fclb ? $fclb+1 : $fp);

					if($fclb===false)
					{
						++$fp;
						continue 2;
					}

				}while($text[$fclb-1]=='\\');

				$ps=substr($text,$fp+$kl+1,$fclb-$fp-$kl-1);
				$ps=str_replace('\\]',']',trim($ps));

				$fclb++;#1 - это ]
				$lp=strpos($text,'[/'.$k.']',$fp);

				if($lp===false)
				{
					$len=$fclb-$fp;
					$cont=false;
				}
				else
				{
					$len=$lp-$fp+$kl+3;#3 - это [/] закрывающего тега
					$cont=substr($text,$fclb,$lp-$fclb);
				}

				switch($ps)
				{
					case'=plural':
						$cont=call_user_func(
							[ __NAMESPACE__.'\\Language\\'.Language::$main,'Plural'],$v,explode('|',$cont)
						);
					break;
					default:
						if(isset($ps[1]))
							switch($ps[0])
							{
								case'=':
									$v=$v==substr($ps,1);
									break;
								case'>':
									$v=$ps[1]=='=' ? $v>=(int)substr($ps,2) : $v>(int)substr($ps,1);
									break;
								case'<':
									$v=$ps[1]=='=' ? $v<=(int)substr($ps,2) : $v<(int)substr($ps,1);
									break;
							}

						$cont=explode('[-'.$k.']',$cont,2)+[1=>''];
						$cont=$v ? $cont[0] : $cont[1];
				}

				$text=substr_replace($text,$cont,$fp,$len);
			}

			if(is_scalar($v))
				$text=str_replace('{'.$k.'}',$v,$text);
		}

		return$text;
	}
}