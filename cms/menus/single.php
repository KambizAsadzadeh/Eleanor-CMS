<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$parent=isset($parent) ? (int)$parent : 0;
$menu=Eleanor::$Cache->Get('menu_single'.Language::$main.$parent);

if($menu===false)
{
	$p='';
	$menu=[];

	if($parent)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'menu` WHERE `id`='.$parent.' LIMIT 1');
		if(!list($p)=$R->fetch_row())
			return'';
		$p.=$parent.',';
	}

	$R=Eleanor::$Db->Query('SELECT `title`,`url`,`params` FROM `'.P.'menu` INNER JOIN `'.P
		.'menu_l` USING(`id`) WHERE `language` IN(\'\',\''.Language::$main
		.'\') AND `in_map`=1 AND `status`=1 AND `parents`=\''.$p.'\' ORDER BY `parents` ASC, `pos` ASC');
	while($a=$R->fetch_assoc())
		$menu[]='<a href="'.$a['url'].'"'.$a['params'].'>'.$a['title'].'</a>';

	Eleanor::$Cache->Put('menu_single'.Language::$main.$parent,$menu);
}
return$menu;