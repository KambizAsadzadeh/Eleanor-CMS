<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
$ids=isset($_POST['ids']) ? (array)$_POST['ids'] : array();
if(!$ids)
	return Error();
$res=array();
$R=Eleanor::$Db->Query('SELECT `id`,`options`,`data` FROM `'.P.'tasks` WHERE `id`'.Eleanor::$Db->In($ids).' AND `name`=\'recovernames\'');
while($a=$R->fetch_assoc())
{
	$a['options']=unserialize($a['options']);
	$a['data']=unserialize($a['data']);
	$res[$a['id']]=array(
		'done'=>$a['data']['done'],
		'percent'=>round($a['data']['total']/$a['options']['total']*100),
		'val'=>$a['data']['total'],
		'total'=>$a['options']['total'],
	);
}
Result($res ? $res : false);