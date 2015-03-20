<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor,$title;

$modules=require DIR.'admin/modules.php';
$module=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;

if($module and isset($modules[$module]))
{
	$Eleanor->module=[
		'uri'=>$module,
		'title'=>$modules[$module]['title'],
		'descr'=>$modules[$module]['descr'],
		'image'=>$modules[$module]['image'] ? $modules[$module]['image'] : 'default-*.png',
	];
	$Eleanor->DynUrl->prefix.='module='.urlencode($module).'&amp;';
	$title[]=$modules[$module]['title'];

	return \Eleanor\AwareInclude(DIR.'admin/modules/'.$module.'.php');
}

$Eleanor->module=[
	'title'=>Eleanor::$Language['main']['management'],
	'image'=>'modules-*.png',
];

Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Management.php';
$items=$titles=[];
/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;

foreach($modules as $name=>$t)
	$titles[$name]=$t['title'];

asort($titles,SORT_STRING);

foreach($titles as $name=>$q)
{
	$a=$modules[$name];

	if(!empty($a['hidden']))
		continue;

	$a['_a']=$Url(['module'=>$name]);

	$items[]=$a;
}

Response(Eleanor::$Template->ManageCover($items));