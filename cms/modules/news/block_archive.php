<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;
global$Eleanor;

#Настройки
$uri=array_keys($Eleanor->modules['uris'],'news');
$uri=reset($uri);
#$uri=['russian'=>'новости','ukrainian'=>'новини','english'=>'news',''=>'news'];#URI модуля. Может быть строкой

$limit=10;#Количество месяцев за которые брать архив по месяцам
#/Настройки

if(is_array($uri))
	$uri=FilterLangValues($uri,Language::$main);

$config=include __DIR__.'/config.php';

#Months
$months=Eleanor::$Cache->Get($config['n'].'_archive-months');
if($months===false)
{
	$months=[];
	$R=Eleanor::$Db->Query('SELECT EXTRACT(YEAR_MONTH FROM IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`)) `ym`, COUNT(`id`) `cnt` FROM `'
		.$config['t'].'` WHERE `status`=1 GROUP BY `ym` ORDER BY `ym` DESC LIMIT '.$limit);
	while($a=$R->fetch_assoc())
	{
		$a['ym']=substr_replace($a['ym'],'-',4,0);
		$months[$a['ym']]=[
			'cnt'=>$a['cnt'],
			'a'=>Url::$base.Url::Make([$uri,$a['ym']]),
		];
	}
	Eleanor::$Cache->Put($config['n'].'_archive-months',$months,3600);
}

#Days
if($cdate=GetCookie($config['n'].'-archive') and preg_match('#^(\d{4})\D(\d{1,2})$#',$cdate,$ma)>0)
{
	list(,$y,$m)=$ma;
	$y=(int)$y;
	$m=(int)$m;
}
else
{
	$y=idate('Y');
	$m=idate('n');
}

include_once __DIR__.'/block_archive_funcs.php';
$days=ArchiveDays($y,$m,$config,$uri);

try
{
	return(string)Eleanor::$Template->BlockArchive($days,$uri,$months);
}
catch(\Eleanor\Classes\EE$E)
{
	return'BlockArchive is missed';
}