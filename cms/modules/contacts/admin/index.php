<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
/** @var array $config */

$saved=false;
$errors=[];
$values=is_file($config['data']) ? (array)\Eleanor\AwareInclude($config['data']) : [];
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$config['n']);
$values+=[
	'info'=>[],
	'recipient'=>[],
	'subject'=>[],
	'text'=>[],
];

if($_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery)
{

	if($errors)
		goto Form;

	$saved=file_put_contents($config['data'],'<?php return '.var_export($data,true).';');
}

Form:

$Editor=function()use($Eleanor){
	return call_user_func_array([$Eleanor->Editor,'Area'],func_get_args());
};

$title[]=$Eleanor->module['title'];
$s=Eleanor::$Template->Contacts($values,$Editor,$errors,$saved);
Response($s);