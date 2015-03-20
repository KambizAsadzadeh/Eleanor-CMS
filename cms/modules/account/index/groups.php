<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'user-*.php',$Eleanor->module['config']['n']);
Eleanor::$Template->queue[]=$Eleanor->module['config']['usertpl'];

$groups=$tosort=$values=array();
$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`style`,`descr_l` `descr` FROM `'.P.'groups`');
while($a=$R->fetch_assoc())
{
	$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
	$a['descr']=$a['descr'] ? Eleanor::FilterLangValues((array)unserialize($a['descr'])) : '';
	$tosort[]=$a['title'];
	$values[]=$a;
}
natsort($tosort);
foreach($tosort as $k=>&$v)
	$groups[$values[$k]['id']]=array_slice($values[$k],1);
$title[]=$lang['groups'];
$c=Eleanor::$Template->AcGroups($groups);
Start();
echo$c;