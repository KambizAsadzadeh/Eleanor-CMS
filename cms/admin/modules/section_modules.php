<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\EE;
defined('CMS\STARTED')||die;

global$Eleanor,$title;

$uri=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
$modules=GetModules();

if($uri and isset($modules['uri2id'][$uri]))
{
	$id=(int)$modules['uri2id'][$uri];
	$module=$modules['id2module'][$id];

	$path=DIR.$module['path'];
	$Eleanor->module=[
		'title'=>$module['title'],
		'uri'=>$uri,
		'section'=>isset($Eleanor->modules['uri2section'][$uri]) ? $Eleanor->modules['uri2section'][$uri] : '',
		'path'=>$path,
		'id'=>$id,
		'uris'=>$module['uris'],
		'miniature'=>$module['miniature'],
		'miniature_type'=>$module['miniature_type'],
	];

	$path.=$module['file'] ? $module['file'] : 'index.php';

	if(is_file($path))
	{
		$title[]=$Eleanor->module['title'];
		$Eleanor->DynUrl->prefix.='module='.urlencode($uri).'&amp;';

		return\Eleanor\AwareInclude($path);
	}

	throw new EE('Unable to load module',EE::DEV);
}
include __DIR__.'/modules.php';