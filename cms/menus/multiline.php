<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$parent=isset($parent) ? (int)$parent : false;
$exclude=isset($exclude) ? (int)$exclude : 0;
$name=isset($name) ? (string)$name : 'menu_multiline_'.Language::$main.$parent;
$menu=Eleanor::$Cache->Get($name);

if($menu===false)
{
	if(!isset($builder) or $builder!==false and !is_callable($builder))
		$builder=function($a,$first='<ul>')use(&$builder){
			$parents=reset($a);
			$l=strlen($parents['parents']);
			$c=$first;
			$n=-1;
			$onp=false;

			foreach($a as &$v)
			{
				++$n;
				$nl=strlen($v['parents']);
				if($nl!=$l)
				{
					if($l>$nl)
						break;
					elseif(!$onp)
					{
						$c.=$builder(array_slice($a,$n));
						$onp=true;
					}
					continue;
				}

				if($n>0)
					$c.='</li>';

				$c.='<li><a href="'.$v['url'].'"'.$v['params'].'>'.$v['title'].'</a>';
				$onp=false;
			}

			return$c.'</li></ul>';
		};

	$p='';

	if($parent)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'menu` WHERE `id`='.$parent.' AND `status`=1 LIMIT 1');
		if(!list($p)=$R->fetch_row())
			return'';
		$p.=$parent.',';
	}

	$maxlen=0;
	$menu=$to1sort=$to2sort=$db=$excl=[];
	$R=Eleanor::$Db->Query('SELECT `id`,`title`,`url`,`params`,`parents`,`pos`,`status` FROM `'.P.'menu` LEFT JOIN `'.P
		.'menu_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'
		.($p ? ' AND `parents` LIKE \''.$p.'%\'' : '').' ORDER BY `parents` ASC, `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		foreach($excl as $v)
			if(strpos($a['parents'],$v)===0)
				continue;

		if($a['id']==$exclude or !$a['status'])
		{
			$excl[]=$a['parents'].$a['id'].',';
			continue;
		}

		$a['parents']=str_replace($p,'',$a['parents']);

		if($a['parents'])
		{
			$cnt=substr_count($a['parents'],',');
			$to1sort[$a['id']]=$cnt;
			$maxlen=max($cnt,$maxlen);
		}

		$db[$a['id']]=$a;
		$to2sort[$a['id']]=$a['pos'];
	}

	asort($to1sort,SORT_NUMERIC);

	foreach($to1sort as $k=>&$v)
		if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0 and $p[1]!=$parent)
			if(isset($to2sort[$p[1]]))
				$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
			else
				unset($to1sort[$k],$db[$k],$to2sort[$k]);

	foreach($to2sort as $k=>&$v)
		$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

	natsort($to2sort);
	foreach($to2sort as $k=>&$v)
		$menu[(int)$db[$k]['id']]=$db[$k];

	if($builder)
		$menu=$builder($menu,'');

	Eleanor::$Cache->Put($name,$menu);
}

return$menu;