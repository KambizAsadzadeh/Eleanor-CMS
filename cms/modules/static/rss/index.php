<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

defined('CMS\STARTED')||die;

/** @var array $config */
include_once __DIR__.'/../api.php';
$Api=new ApiStatic($config);

$pp_=50;#Количество статических страниц на страницу по умолчанию :)

if(!empty($_GET['pp']))
{
	$pp=(int)$_GET['pp'];
	if($pp>$pp_)
		$pp=$pp_;
	elseif($pp<1)
		$pp=1;
}
else
	$pp=$pp_;

$page=empty($_GET['page']) ? 1 : (int)$_GET['page'];

if($page<=0)
	$page=1;

$offset=$pp*($page-1);
$items=$Api->GetSubstance();
$items=array_slice($items,$offset,$pp);
$ids=$parents=[];

foreach($items as $k=>$v)
{
	$ids[]=$k;

	if($v['parents'])
		$parents=array_merge($parents,explode(',',rtrim($v['parents'],',')));
}

if($parents)
{
	$parents=array_unique($parents);
	$R=Eleanor::$Db->Query('SELECT `id`, `title` FROM `'.$config['t'].'` INNER JOIN `'.$config['tl']
		.'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($parents).' AND `status`=1');
	$parents=[];

	while($item=$R->fetch_assoc())
		$parents[$item['id']]=$item['title'];
}

$modified=0;
$items=[];
global$Eleanor;

if($ids)
{
	$R=Eleanor::$Db->Query('SELECT `id`, `parents`, `title`, `text`, UNIX_TIMESTAMP(`last_mod`) `modified` FROM `'
		.$config['t'].'` INNER JOIN `'.$config['tl'].'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids).' AND `status`=1');
	while($item=$R->fetch_assoc())
	{
		if($item['parents'])
		{
			$c=[];

			foreach(explode(',',rtrim($item['parents'],',')) as $p)
				if(isset($parents[$p]))
					$c[]=$parents[$p];

			$c=join('/',$c);
		}
		else
			$c='&#47;';

		if($item['modified']>$modified)
			$modified=$item['modified'];

		$u=$Api->GetUrl($item['id'],$Eleanor->Url);
		$items[ $item['id'] ]=[
			'title'=>$item['title'],#Заголовок сообщения
			'link'=>$u,#URL сообщения
			'description'=>OwnBB::Parse($item['text']),#Краткий обзор сообщения
			'guid'=>$u,#Строка, уникальным образом идентифицирующая сообщение.
			'category'=>$c,#Включает сообщение в одну или более категорий. См. ниже.
		];
	}
}

Rss($items,['lastBuildDate'=>$modified]);