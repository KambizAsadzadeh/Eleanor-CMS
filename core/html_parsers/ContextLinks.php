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
class HtmlParserContextLinks
{
		$cl=array();
	{
		if(self::$cl===false)
		{
			$t=time();
			self::$cl=array();
			$Eleanor=Eleanor::getInstance();
			while($a=$R->fetch_assoc())
			{
					continue;

				if($a['eval_url'])
				{
					ob_start();
					$f=create_function('$Eleanor',$a['eval_url']);
					if($f===false)
					{
						ob_end_clean();
						continue;
					}
					ob_end_clean();
					$a['url']=$f($Eleanor);
				}
				unset($a['eval_url'],$a['date_from'],$a['date_till']);
				if($newlast)
					$last=min($last,$newlast-$t);
				if(!$a['regexp'])
				{
					$a['from']=preg_quote($a['from'],'#');
					$a['from']=str_replace(',','|',$a['from']);
					$a['from']='#(^|[\b"\s])('.str_replace(array(' |','| '),'|',$a['from']).')([\b"\s\.,]|$)#i';
				}
				self::$cl[]=$a;
			Eleanor::$Cache->Put('cl_'.Language::$main,self::$cl,$last);

		if(self::$cl)
		{
			$bl=strlen('<!-- CONTEXT LINKS -->');
			$el=strlen('<!-- /CONTEXT LINKS -->');
			$cnt=count(self::$cl)-1;
			while(false!==$bp=strpos($s,'<!-- CONTEXT LINKS -->',$cp) and false!==$ep=strpos($s,'<!-- /CONTEXT LINKS -->',$bp))
			{
				$r=substr($s,$bp+$bl,$ep-$bp-$bl);

				$op=array();
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
							elseif(!$ct and in_array($t,array('a','textarea','script')))
								$op[]=$t;
						}
						$r.=substr($w,0,$we+1);
						$w=substr($w,$we+1);
					}
					if(count(array_intersect(array('a','textarea','script'),$op))==0)
					{
						foreach(self::$cl as $k=>&$v)
						{
							foreach($bounds as &$b)
							{
								$wrep=preg_replace($v['from'],'\1<a href="'.$v['url'].'"'.$v['params'].'>'.($v['to'] ? $v['to'] : '\2').'</a>\3',substr($w,$b[0]+$offset,$b[1]));
								$w=substr_replace($w,$wrep,$b[0]+$offset,$b[1]);
								$offset+=strlen($wrep)-$b[1];
							}
							if($k<$cnt)
							{
								$bounds=array();
								$bw=0;
								$ew=strpos($w,'<');
								while($ew!==false)
								{
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
									$bounds[]=array($bw,strlen($w)-$bw);
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
}