<?php/**	Eleanor CMS © 2014	http://eleanor-cms.ru	info@eleanor-cms.ru*/namespace CMS;defined('CMS\STARTED')||die;/** Формирование массива модулей для верхнего меню * @return array */$modules=$titles=$items=[];$table=P.'modules';$service=Eleanor::$service;$R=Eleanor::$Db->Query("SELECT `id`, `uris`, `title_l` `title`,`descr_l` `descr`, `protected`, `miniature_type`, `miniature` FROM `{$table}` WHERE `services`='' OR `services` LIKE '%,{$service},%' AND `status`=1");while($module=$R->fetch_assoc()){	$image=false;	if($module['miniature'] and $module['miniature']) switch($module['miniature_type'])	{		case'gallery':			if(strpos($module['miniature'],'*')!==false)			{				$files=glob(Template::$path['static'].'images/modules/'.$module['miniature']);				if($files)					foreach($files as $v)					{						$bn=basename($v);						$ext=strrchr($bn,'.');						$compose=preg_match('#\-(\d+x\d+)\\'.$ext.'$#',$bn,$m)>0;						if($compose)						{							if(!is_array($image))								$image=[];							$image[ $m[1] ]=[								'path'=>$v,								'http'=>Template::$http['static'].'images/modules/'.$bn,							];						}						else							$image=[								'path'=>$v,								'http'=>Template::$http['static'].'images/modules/'.$bn,							];					}			}			elseif(is_file($f=Template::$path['static'].'images/modules/'.$module['miniature']))				$image=[					'type'=>'gallery',					'path'=>$f,					'http'=>Template::$http['static'].'images/modules/'.$module['miniature'],					'src'=>$module['miniature'],				];			break;		case'upload':			if(is_file($f=Template::$path['uploads'].'modules/'.$module['miniature']))				$image=[					'type'=>'upload',					'path'=>$f,					'http'=>Template::$http['uploads'].'modules/'.$module['miniature'],					'src'=>$module['miniature'],				];			break;		case'link':			$image=[				'type'=>'link',				'http'=>$module['miniature'],			];	}	$module['miniature']=$image;	$module['title']=$module['title'] ? FilterLangValues($mt=json_decode($module['title'],true)) : '';	$module['descr']=$module['descr'] ? FilterLangValues(json_decode($module['descr'],true)) : '';	$uris=$module['uris'] ? json_decode($module['uris'],true) : false;	if($uris)	{		foreach($uris as &$section)			if(isset($section[ Language::$main ]))				$section=reset($section[ Language::$main ]);			elseif(isset($section['']))				$section=reset($section['']);			else				$section=null;		$module['_a']=DynUrl::$base.DynUrl::Query(['section'=>'modules','module'=>reset($uris)]);		$titles[ $module['id'] ]=$module['title'];		$items[ $module['id'] ]=array_slice($module,1);	}}asort($titles,SORT_STRING);foreach($titles as $k=>$item)	$modules[]=$items[$k];return$modules;