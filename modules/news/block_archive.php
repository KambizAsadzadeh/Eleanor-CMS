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
#���������
$mname=array_keys($GLOBALS['Eleanor']->modules['sections'],'news');
$mname=reset($mname);
#$mname=array('russian'=>'�������','ukrainian'=>'������','english'=>'news',''=>'news');#URL ������. ����� ���� �������

$limit=10;#���������� ������� �� ������� ����� ����� �� �������
#����� ��������

if(is_array($mname))
	$mname=Eleanor::FilterLangValues($mname,Language::$main);
$conf=include dirname(__file__).'/config.php';

#Months
$months=Eleanor::$Cache->Get($conf['n'].'_archivemonths');
if($months===false)
{
	$months=array();
	$R=Eleanor::$Db->Query('SELECT EXTRACT(YEAR_MONTH FROM IF(`pinned`=\'0000-00-00 00:00:00\',`date`,`pinned`)) `ym`, COUNT(`id`) `cnt` FROM `'.$conf['t'].'` WHERE `status`=1 GROUP BY `ym` ORDER BY `ym` DESC LIMIT '.$limit);
	while($a=$R->fetch_assoc())
	{		$a['ym']=substr_replace($a['ym'],'-',4,0);
		$months[$a['ym']]=array(
			'cnt'=>$a['cnt'],
			'a'=>$GLOBALS['Eleanor']->Url->Construct(array('module'=>$mname,'do'=>$a['ym']),false,''),
		);
	}
	Eleanor::$Cache->Put($conf['n'].'_archivemonths',$months,3600);
}

#Days
if($cdate=Eleanor::GetCookie($conf['n'].'-archive') and preg_match('#^(\d{4})\D(\d{1,2})$#',$cdate,$ma)>0)
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

include_once dirname(__file__).'/block_archive_funcs.php';
$days=ArchiveDays($y,$m,$conf,$mname);
$lang=Eleanor::$Language->Load(dirname(__file__).'/lang_blocks-*.php',false);

try
{
	return Eleanor::$Template->BlockArchive($days,$lang,$mname,false,$months);
}
catch(EE$E)
{	return'BlockArchive is missed';}