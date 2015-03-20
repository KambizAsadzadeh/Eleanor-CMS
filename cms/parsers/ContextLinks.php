<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Parsers;
defined('CMS\STARTED')||die;
use CMS, CMS\Eleanor, CMS\Language;

/** Преобразование текста в текст с контекстными ссылками */
class ContextLinks extends \Eleanor\BaseClass implements CMS\Interfaces\HtmlParser
{
	/** Непосредственная обработка контекстных слов в тексте в ссылки
	 * @param string $s Обрабатываемый текст
	 * @return string */
	public static function Parse($s)
	{
		$links=Eleanor::$Cache->Get('context_links_'.Language::$main);
		if($links===false)
		{
			$last=86400;
			$t=time();
			$links=[];
			$table=CMS\P.'context_links';
			$lang=Language::$main;

			$R=Eleanor::$Db->Query("SELECT `from`,`regexp`,`to`,`url`,`params`,`date_from`,`date_till`
FROM `{$table}` INNER JOIN `{$table}_l` USING(`id`) WHERE `language` IN ('','{$lang}') AND `status`=1");
			while($a=$R->fetch_assoc())
			{
				$newlast=false;
				if((int)$a['date_from']>0 and strtotime($a['date_from'])>$t or
					(int)$a['date_till']>0 and $t>$newlast=strtotime($a['date_till']))
					continue;

				unset($a['date_from'],$a['date_till']);

				if($newlast)
					$last=min($last,$newlast-$t);

				if(!$a['regexp'])
				{
					$a['from']=preg_quote($a['from'],'#');
					$a['from']=str_replace(',','|',$a['from']);
					$a['from']='#(^|[\b"\s])('.str_replace([' |','| '],'|',$a['from']).')([\b"\s\.,]|$)#i';
				}

				$links[]=$a;
			}
			Eleanor::$Cache->Put('context_links_'.Language::$main,$links,$last);
		}

		if($links)
		{
			$cp=0;
			$bl=strlen('<!-- CONTEXT LINKS -->');
			$el=strlen('<!-- /CONTEXT LINKS -->');
			$cnt=count($links)-1;

			while(false!==$bp=strpos($s,'<!-- CONTEXT LINKS -->',$cp) and
				false!==$ep=strpos($s,'<!-- /CONTEXT LINKS -->',$bp))
			{
				$r=substr($s,$bp+$bl,$ep-$bp-$bl);
				$op=[];
				$w=strtok($r,'<');
				$r='';

				while($w!==false)
				{
					if(false!==$we=strpos($w,'>'))
					{
						$r.='<';
						$ct=$w[0]=='/';

						if(preg_match('#^'.($ct ? '/' : '').'([a-z0-9]+)#',$w,$t)>0 and substr($w,$we-1,1)!='/')
						{
							$t=$t[1];

							if($ct and end($op)==$t)
								array_pop($op);
							elseif(!$ct and in_array($t,['a','textarea','script']))
								$op[]=$t;
						}

						$r.=substr($w,0,$we+1);
						$w=substr($w,$we+1);
					}

					if(count(array_intersect(['a','textarea','script'],$op))==0)
					{
						$bounds=[[0,strlen($w)]];
						foreach($links as $k=>$v)
						{
							$offset=0;

							foreach($bounds as $b)
							{
								$wrep=preg_replace($v['from'],
									'\1<a href="'.$v['url'].'"'.$v['params'].'>'.($v['to'] ? $v['to'] : '\2').'</a>\3',
									substr($w,$b[0]+$offset,$b[1]));
								$w=substr_replace($w,$wrep,$b[0]+$offset,$b[1]);
								$offset+=strlen($wrep)-$b[1];
							}

							if($k<$cnt)
							{
								$bounds=[];
								$bw=0;
								$ew=strpos($w,'<');

								while($ew!==false)
								{
									if($ew!=$bw)
										$bounds[]=[$bw,$ew-$bw];

									$bw=strpos($w,'>',$ew);

									if($bw===false)
										break;

									$bw=strpos($w,'>',$bw+1);

									if($bw===false)
										break;

									$bw++;
									$ew=strpos($w,'<',$bw);
								}

								if($ew===false)
									$bounds[]=[$bw,strlen($w)-$bw];
							}
						}
					}

					$r.=$w;
					$w=strtok('<');
				}

				$s=substr_replace($s,$r,$bp,$ep-$bp+$el);
				$cp=$bp;
			}
		}

		return$s;
	}
}

return ContextLinks::class;