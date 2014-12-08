<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;
global$Eleanor;

$n=isset($Eleanor->module['section']) ? $Eleanor->module['section'] : 'index';
$cache=Eleanor::$Cache->Get('categories_'.$n.'_'.Language::$main);
$Url=new Url(false);
$Url->prefix=Url::Encode(isset($Eleanor->module['uri']) ? $Eleanor->module['uri'] : 'news').'/';

if($cache===false and isset($GLOBALS['Eleanor']->Categories))
{
	$Fbc=function($a,$c='<ul>') use (&$Fbc,$Url)
	{global$Eleanor;
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$n=-1;
		$nonp=true;

		foreach($a as &$v)
		{
			++$n;
			$nl=strlen($v['parents']);

			if($nl!=$l)
			{
				if($l>$nl)
					break;

				elseif($nonp)
				{
					$c.=$Fbc(array_slice($a,$n));
					$nonp=false;
				}

				continue;
			}

			if($n>0)
				$c.='</li>';

			$uris=$Eleanor->Categories->GetUri($v['id']);
			$c.='<li><a href="'.($uris ? $Url($uris,'/') : rtrim($Url->prefix,'/').'?category='.$v['id']).'">'
				.$v['title'].'</a>';
			$nonp=true;
		}

		return$c.'</li></ul>';
	};
	$mn=isset($Eleanor->module['uri']) ? $Eleanor->module['uri'] : '';

	$cache=$Fbc($Eleanor->Categories->dump,'');
	Eleanor::$Cache->Put('categories_'.$n.'_'.Language::$main,$cache);
}

try
{
	return$cache ? Eleanor::$Template->BlockCategories($cache,null) : false;
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockCategories does not exists.';
}