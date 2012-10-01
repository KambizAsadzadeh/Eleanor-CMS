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
#����� ��������
if(!isset($cache))
	$cache=3600;#����� �������� ���� � ��������
#����� ��������

global$Eleanor;
$uri=array_keys($Eleanor->modules['sections'],'news');
$uri=reset($uri);
$conf=include dirname(__file__).'/config.php';

$narr=Eleanor::$Cache->Get($conf['n'].'_nv_'.Eleanor::$Language);
if($narr===false)
{
	$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`title`,`cats`,`voting` FROM `'.$conf['t'].'` INNER JOIN `'.$conf['tl'].'` USING(`id`) WHERE `voting`!=0 AND `status`=1 AND `language`IN(\'\',\''.Eleanor::$Language.'\') ORDER BY `voting` DESC LIMIT 1');
	if($narr=$R->fetch_assoc())
	{
		$narr['cats']=$narr['cats'] ? explode(',',trim($narr['cats'],',')) : array();
		$narr['cats']=reset($narr['cats']);
	}

	Eleanor::$Cache->Put($conf['n'].'_nv_'.Eleanor::$Language,$narr ? $narr : 0,$cache);
}

if($narr)
{	$mid=array_keys($Eleanor->modules['ids'],$uri);
	$mid=reset($mid);
	if($mid==$Eleanor->module['id'])
		$C=$Eleanor->Categories;
	else
	{		$C=new Categories;
		$C->Init($conf['c']);
	}
	if(isset($Eleanor->Voting))
		$V=$Eleanor->Voting;
	else
	{		$V=new Voting($narr['voting']);
		$V->mid=$mid;
	}
	$c=$V->Show(array('module'=>$uri,'event'=>'voting','id'=>$narr['id']));
	if(isset($Eleanor->module['etag']))
		$Eleanor->module['etag'].=$V->status.$narr['voting'];
	if($c)
	{		if($narr['cats'] and $Eleanor->Url->furl)			$cat=$C->GetUri($narr['cats']);
		else
			$cat=false;
		$un=array('u'=>array($narr['uri'],'nid'=>$narr['id']));
		echo'<a href="'.$Eleanor->Url->Construct(array('module'=>$uri)+($cat ? $cat+$un : $un),false).'" style="font-weight:bold">'.$narr['title'].'</a><br />'.$c;
	}}