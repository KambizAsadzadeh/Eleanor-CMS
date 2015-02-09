<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;

/** Преобразование опасного HTML-а (полученного из редакторов) в безопасный */
class SafeHtml extends \Eleanor\BaseClass
{
	public static
		/** @var string Автоматическое прописывание альтов к картинкам*/
		$alt,

		/** @var bool Автоматическое прописывание rel="nofollow" ко всем внешним ссылкам */
		$nofollow=true,

		/** @var bool Включение проверки корректности ссылок. Полезно в случае сохранения текста с переменными */
		$checkup=true,

		/** @var bool Запрещенные теги. Они будут вырезаны после парсинга */
		$disabled=['applet','meta','link','html','body','style','head','script','iframe','frame','frameset','base',
			'!doctype'],

		/** @var bool Разрешенные теги. Все остальные будут вырезаны. Если путо - все теги разрешены */
		$enabled=[];

	/** Преобразователь опасного HTML в безопасный (очищение HTML от XSS атак)
	 * @param string $html Строка опасного HTML
	 * @return string */
	public static function Make($html)
	{
		#Сначала идут комментарии, а только потом CDATA, потому что мы заменяем текст на комментарии!
		$exclude=[
			#Не убирать пробелы! Возможны XSS типа <!--[if gte IE 4]>\br<SCRIPT>alert('XSS');</SCRIPT>\br<![endif]-->
			['<!-- ',' -->'],
			['<![CDATA[',']]>'],
		];
		$n=0;
		$np=[];

		foreach($exclude as $excl)
		{
			$oldstart=-1;
			$start=0;

			while(false!==$start=strpos($html,$excl[0],$start) and false!==$finish=strpos($html,$excl[1],$start))
			{
				if($start==$oldstart)
				{
					++$start;
					continue;
				}

				$len=strlen($excl[1]);
				$finish-=$start-$len;
				$repl='<!-- '.++$n.' -->';
				$tost=substr($html,$start,$finish);
				$html=substr_replace($html,$repl,$start,$finish);
				$np[]=['r'=>$repl,'t'=>$tost];
				$oldstart=$start;
			}
		}

		#Убираем чередование тегов <<< и >>>
		$html=preg_replace(['#<+#','#>+#','#^([^<]*)>#m','#<([^>]*)$#m'],['<','>','\1&gt;','&lt;\1'],$html);

		#Список одиночных тегов
		$single=['input','hr','br','img','image','param','area','embed','col','source','wbr'];

		#Теги, закрытием которых обычно пренебрегают.
		$clforget=['p','li','colgroup'];

		#Строчные теги. Они могут находится друг в друге неограниченное число раз.
		$inline=['a','abbr','address','span','small','i','b','s','em','strong','q','big','small','sup','sub','var','tt',
			'cite','code','input','select','br','img'];

		#Ограничения вложенности тегов. Теги, не указанные в массиве справа, вложенные в тег слева, будут игнорироваться
		$children=[
			'button'=>&$inline,
			'caption'=>&$inline,
			'th'=>&$inline,
			'h1'=>&$inline,
			'h2'=>&$inline,
			'h3'=>&$inline,
			'h4'=>&$inline,
			'h5'=>&$inline,
			'h6'=>&$inline,
			'nav'=>&$inline,
			'progress'=>&$inline,
			'rp'=>&$inline,
			'rt'=>&$inline,
			'meter'=>&$inline,
			'colgroup'=>['col'],
			'table'=>['caption','col','colgroup','tbody','thead','tfoot','tr'],
			'thead'=>['tr'],
			'tfoot'=>['tr'],
			'tbody'=>['tr'],
			'tr'=>['td','th'],
			'output'=>[],
			'textarea'=>[],
			'keygen'=>[],
			'time'=>[],
			'ul'=>['li'],
			'ol'=>['li'],
			'option'=>[],
			'object'=>['param'],
			'select'=>['option','optgroup'],
			'optgroup'=>['option'],
			'datalist'=>['option'],
			'map'=>['area'],
			'hgroup'=>['h1','h2','h3','h4','h5','h6'],
			'video'=>['sorce'],
			'audio'=>['sorce'],
		];

		#Теги которые могут находится только внутри остальных тегов ограниченное число раз. 0 - без ограничений.
		$parents=[
			'th'=>['tr'=>0],
			'td'=>['tr'=>0],
			'tr'=>['table'=>0,'thead'=>0,'tfoot'=>0,'tbody'=>0],
			'thead'=>['table'=>1],
			'tfoot'=>['table'=>1],
			'tbody'=>['table'=>1],
			'caption'=>['table'=>0],
			'colgroup'=>['table'=>0],
			'col'=>['table'=>0,'colgroup'=>0],
			'li'=>['ul'=>0,'ol'=>0],
			'legend'=>['fieldset'=>1],
			'param'=>['object'=>0],
			'option'=>['select'=>0,'optgroup'=>0,'datalist'=>0],
			'optgroup'=>['select'=>0],
			'figcaption'=>['figure'=>1],
			'rp'=>['ruby'=>0],
			'rt'=>['ruby'=>0],
			'summary'=>['details'=>1],
			'source'=>['video'=>0,'audio'=>0],
		];

		$start=$n=0;
		$opened=$allowed=$used=[];
		$ent=ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED;

		while(isset($html[$start]) and false!==$start=strpos($html,'<',$start))
		{
			$fulltag='';
			$finish=$start;

			while(false!==$finish=strpos($html,'>',$finish))
			{
				$fulltag=substr($html,$start+1,$finish-$start-1);#1 это < и >

				#Нужно учесть, что бывают ситуации типа <b data-h="<i></i>"> - вполне валидная конструкция
				if(false===$quotpos=strpbrk($fulltag,'"\''))
					break;

				$quot=substr($quotpos, 0, 1);#" или '
				if(substr_count($fulltag,$quot)%2==0)
					break;

				#Если число кавычек нечётное - считаем, что > находится внутри них
				$finish++;
			}

			#Если тег без закрывающей < - удаляем.
			if($finish===false)
			{
				$html=substr($html,0,$start);
				break;
			}
			#Комментарии мы не трогаем вообще
			elseif(strpos($fulltag,'!-- ')!==0)
			{
				do
				{
					$tag=ltrim($fulltag);
					$tag=rtrim($tag,' /	');

					if(!$tag)
					{
						$rep='';
						break;
					}

					if($closed=$tag[0]=='/')
						$tag=substr($tag,1);

					$tag=str_replace(["\r","\n"],' ',$tag);

					if(preg_match('#^([a-z0-9]+)#i',$tag,$m)==0)
					{
						$rep='';
						break;
					}

					$params=$m[1]==$tag || $closed ? '' : substr($tag,strlen($m[1])+1);
					$tag=strtolower($m[1]);

					if(in_array($tag,static::$disabled) or
						static::$enabled and !in_array($tag,static::$enabled) or
						$closed and !in_array($tag,$opened))
					{
						$rep='';
						break;
					}

					if($closed)
					{
						#Если передыдущий открытый тег оказался таким как этот - благополучно все закрываем.
						if($opened[$n]==$tag)
						{
							unset($opened[$n],$used[$n],$allowed[$n--]);
							$rep='</'.$tag.'>';
							break;
						}

						$rep='';

						#Если мы закрываем тег, который открыли ранее... - Закроем все предыдущеие теги.
						for($i=$n-1;$i>0;--$i)
							if($opened[$i]==$tag)
							{
								for($j=$n;$j>=$i;--$j)
								{
									$rep.='</'.$opened[$j].'>';
									unset($opened[$j],$allowed[$j]);
								}
								$n=$i-1;
								break;
							}

						break;
					}

					if(isset($parents[$tag]) and !isset($opened[$n],$parents[$tag][$opened[$n]]) or
						isset($opened[$n],$children[$opened[$n]]) and !in_array($tag,$children[$opened[$n]]) or
						isset($used[$n][$tag]) and !$used[$n][$tag] or
						isset($allowed[$n]) and !in_array($tag,$allowed[$n]))
					{
						$rep='';
						break;
					}

					if($params)
					{
						$params=Strings::ParseParams($params);
						$params=array_change_key_case($params,CASE_LOWER);
						$sparams='';

						switch($tag)
						{
							case'object':
								unset($params['data']);
							break;
							case'a':
								if(isset($params['href']) and preg_match('#^[a-z]{3,7}://#i',$params['href'])>0)
								{
									#Ставим первым параметром ссылки href для удобства замены прямых ссылок
									$params=['href'=>$params['href']]+$params;

									if(static::$nofollow)
										$params['rel']='nofollow';
								}
							break;
							case'img':
							case'image':
								if(empty($params['alt']))
									$params['title']=$params['alt']=static::$alt;

								if(!isset($params['src']))
								{
									$rep='';
									break 2;
								}
						}

						foreach($params as $k=>$v)
						{
							#Вырежем все события
							if(preg_match('#^(on|javascript:)#i',$k)>0 or
								preg_match('#^[a-z0-9]+#',$k)==0)
								continue;

							if($k=='style')
							{
								while(preg_match('#/\*.*?\*/#s',$v)>0)
									$v=preg_replace('#/\*.*?\*/#s','',$v);
								/*@import, url и expression не разрешены в тегах style!
								Нельзя, чтобы контент из подписи отображался где-то вне своего места.*/
								$v=str_ireplace(['expression','url','@import','position'],'',$v);
							}

							if(static::$checkup and ($k=='href' or $k=='src') and $v!='#')
								if(filter_var($v,FILTER_VALIDATE_URL) or strpos($v,'://')===false and filter_var('http://eleanor-cms.ru/'.$v,FILTER_VALIDATE_URL))
									$v=htmlspecialchars($v,$ent,\Eleanor\CHARSET,false);
								else
								{
									$rep='';
									break 2;
								}

							$v=str_replace(['"','&39;','&lt;','&gt;'],['&quot;','\'','<','>'],$v);

							$sparams.=' '.$k.'="'.$v.'"';
						}

						$params=$sparams;
					}
					elseif(in_array($tag,['a']))#Теги, невозможные без параметров
					{
						$rep='';
						break;
					}
					if(in_array($tag,$single))
					{
						$rep='<'.$tag.$params.' />';
						break;
					}

					if(in_array($tag,$clforget) and isset($opened[$n]) and $opened[$n]==$tag)
					{
						$rep='</'.$tag.'><'.$tag.$params.'>';
						break;
					}

					if($n>0)
					{
						#Тег caption может быть только сразу после тега <table>
						if($opened[$n]=='table' and $tag!='caption')
							$used[$n]['caption']=0;

						#Тег figcaption должен быть первым или последним в теге figure
						if($opened[$n]=='figure')
							if($tag=='figcaption' and isset($used[$n]['_figdone']))
								$allowed[$n]=[];
							else
								$used[$n]['_figdone']=true;

						#Тег summary должен идти первым внутри details.
						if($opened[$n]=='details' and $tag!='summary')
							$used[$n]['summary']=0;
					}

					$ch=isset($children[$tag]);

					if(in_array($tag,$inline) and !$ch)
					{
						$allowed[$n+1]=isset($allowed[$n]) ? array_intersect($inline,$allowed[$n]) : $inline;
					}
					elseif($ch)
						$allowed[$n+1]=$children[$tag];

					if(isset($used[$n][$tag]))
					{
						if($used[$n][$tag]>0)
							--$used[$n][$tag];
					}
					elseif(isset($parents[$tag]))
						foreach($parents[$tag] as $k=>&$v)
							if($k==$opened[$n] and $v>0)
								$used[$n][$tag]=$v-1;

					$opened[++$n]=$tag;
					$rep='<'.$tag.$params.'>';
				}
				while(false);

				$replen=strlen($rep);
				$ftlen=strlen($fulltag)+2;#Это < и >
				$html=substr_replace($html,$rep,$start,$ftlen);
			}
			else
				$replen=$ftlen=0;

			$start=$finish-$ftlen+$replen+1;#1 это >
		}

		for(;$n>0;--$n)
			$html.='</'.$opened[$n].'>';

		/* Следующий участок кода призван убрать запрещенные текстовые вставки. Например <table>текст<tr> или
		</td>текст</tr>. Стоит отметить, что на этом этапе все теги, в которые обрамлен запрещенный текст должны
		быть "пришиблены" кодом выше. */
		$html=preg_replace('#(<(?:table|tr|thead|tbody|tfoot|select|/tfoot|/thead|/tbody|/tr|/td|/option|ul|ol|/li|colgroup|/colgroup|video|source)[^>]*>)[^<]+#','\1',$html);

		#Тег figcaption должен быть первым или последним в теге figure
		$html=preg_replace('#<figure[^>]*>[^<]+<figcaption>#','<figure><figcaption>',$html);
		$html=preg_replace('#</figcaption>[^<]+</figure>#','</figcaption></figure>',$html);

		#Убираем любые пустые конструкции типа <b>   </b>
		$html=preg_replace('#<(b|i|u|s|a|q|li|ul|ol|em|tt|big|sub|sup|var|cite|code|span|small|spansmall|strong|noindex|legend|blockquote|select|table)[^>]*>\s*</\1>#i','',$html);

		$op=[];
		$w=strtok($html,'<');
		$html='';

		while($w!==false)
		{
			if(false!==$we=strpos($w,'>'))
			{
				$html.='<';
				$ct=$w[0]=='/';

				if(preg_match('#^'.($ct ? '/' : '').'([a-z0-9]+)#',$w,$t)>0 and substr($w,$we-1,1)!='/')
				{
					$t=$t[1];
					if($ct and end($op)==$t)
						array_pop($op);
					elseif(!$ct and in_array($t,['a','textarea','script']))
						$op[]=$t;
				}

				$html.=substr($w,0,$we+1);
				$w=substr($w,$we+1);
			}

			if(count(array_intersect(['a','textarea','script'],$op))==0)
				$w=static::PlainTextHandler($w);

			$html.=$w;
			$w=strtok('<');
		}

		if($np)
			foreach($np as &$v)
				$html=str_replace($v['r'],$v['t'],$html);

		#Удаление абсолютных ссылок
		$html=preg_replace('#(href|src|action)="https?://('
				.preg_quote(\Eleanor\DOMAIN).'|'.preg_quote(\Eleanor\PUNYCODE).')'.preg_quote(\Eleanor\SITEDIR).'#i','\1="',$html);

		return$html;
	}

	/** Обработчик текста вне ссылок, скриптов и textarea. Полезен для наследования
	 * @param string $text Текст
	 * @return string */
	protected static function PlainTextHandler($text) { return$text; }
}