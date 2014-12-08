<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;
#ToDo! Создать блок
global$Eleanor;
if(empty($Eleanor->module['pub_tags']) or empty($Eleanor->module['pub_pid']))
	return'';

$config=include __DIR__.'/config.php';
$near=$reads=[];

$R=Eleanor::$Db->Query('SELECT `p`.`id`, `p`.`reads` FROM `'.$config['rt'].'` `rt` INNER JOIN `'.$config['t']
	.'` `p` ON `rt`.`id`=`p`.`id` WHERE `rt`.`tag`'.Eleanor::$Db->In($Eleanor->module['pub_tags']));
while($a=$R->fetch_row())
	if($Eleanor->module['pub_id']!=$a[0])
	{
		$near[ $a[0] ]=isset($near[$a[0]]) ? $near[$a[0]]+1 : 1;
		$reads[ $a[0] ]=$a[1];
	}

if(!$near)
	return'';

foreach($near as $k=>&$v)
	$v.='-'.$reads[$k];

unset($v,$reads);
natsort($near);

$near=array_keys(array_reverse($near,true));
if(isset($near[5]))
	$near=array_slice($near,0,5);

$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`lcats`,`title` FROM `'.$config['tl'].'` WHERE `id`'.Eleanor::$Db->In($near)
	.' AND `language`IN(\'\',\''.Language::$main.'\') AND `lstatus`=1');
if($R->num_rows>0)
{
	echo'<ul>';
	while($a=$R->fetch_assoc())
	{
		$a['lcats']=$a['lcats'] ? (int)ltrim($a['lcats'],',') : false;

		$url='#';
		#ToDo! URL
		/*$u=array('u'=>array($a['uri'],'id'=>$a['id']));
		if($a['lcats'] and $Eleanor->Url->furl)
		{
			$cu=$Eleanor->Categories->GetUri($a['lcats']);
			if($cu)
				$u=$cu+$u;
		}*/

		echo'<li><a href="'.$url.'">'.$a['title'].'</a></li>';
	}
	echo'</ul>';
}