<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
/** @var array $config */

$saved=false;
$errors=[];
$values=is_file($config['data']) ? (array)\Eleanor\AwareInclude($config['data']) : [];
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$config['n']);
$tt=Eleanor::$vars['multilang'] ? [] : '';#TextType
$values+=[
	'info'=>$tt,
	'recipient'=>[],
	'subject'=>$tt,
	'text'=>$tt,
	'document_title'=>$tt,
	'meta_descr'=>$tt,
];

if(AJAX and isset($_REQUEST['do']) and $_REQUEST['do']=='author-autocomplete')
{
	$q=isset($_REQUEST['query']) ? (string)$_REQUEST['query'] : '';
	$out=[];

	if($q!='')
	{
		$q=Eleanor::$UsersDb->Escape($q,false);
		$table=USERS_TABLE;
		$R=Eleanor::$UsersDb->Query("SELECT `id`, `name` FROM `{$table}` WHERE `name` LIKE '%{$q}%' ORDER BY `name` ASC LIMIT 14");
		while($a=$R->fetch_assoc())
		{
			$a['_a']=UserLink($a['id'],$a['name'],'admin');
			$out[]=$a;
		}
	}

	OutPut::SendHeaders('application/json');
	OutPut::Gzip(json_encode($out,JSON^JSON_PRETTY_PRINT));
	return 1;
}

if($_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery)
{
	$Saver=new Saver;
	$data=[];

	if(Eleanor::$vars['multilang'])
	{
		foreach(Eleanor::$langs as $lng=>$_)
		{
			Saver::$checkup=true;
			$data['info'][$lng]=isset($_POST['info'][$lng]) && is_array($_POST['info']) ? $Saver->Save((string)$_POST['info'][$lng]) : '';

			Saver::$checkup=false;
			$data['subject'][$lng]=isset($_POST['subject'][$lng]) && is_array($_POST['subject']) ? (string)$_POST['subject'][$lng] : '';
			$data['text'][$lng]=isset($_POST['text'][$lng]) && is_array($_POST['text']) ? $Saver->Save((string)$_POST['text'][$lng]) : '';
			$data['document_title'][$lng]=isset($_POST['document_title'][$lng]) && is_array($_POST['document_title']) ? (string)$_POST['document_title'][$lng] : '';
			$data['meta_descr'][$lng]=isset($_POST['meta_descr'][$lng]) && is_array($_POST['meta_descr']) ? (string)$_POST['meta_descr'][$lng] : '';

			if($data['subject'][$lng]==='')
				$errors['EMPTY_SUBJECT'][]=$lng;

			if($data['text'][$lng]==='')
				$errors['EMPTY_TEXT'][]=$lng;
		}

		foreach($data as &$v)
			if(join('',$v)=='')
				$v='';

		unset($v);
	}
	else
	{
		Saver::$checkup=true;
		$data['info']=isset($_POST['info']) && is_array($_POST['info']) ? $Saver->Save((string)$_POST['info']) : '';

		Saver::$checkup=false;
		$data['subject']=isset($_POST['subject']) && is_array($_POST['subject']) ? (string)$_POST['subject'] : '';
		$data['text']=isset($_POST['text']) && is_array($_POST['text']) ? $Saver->Save((string)$_POST['text']) : '';
		$data['document_title']=isset($_POST['document_title']) && is_array($_POST['document_title']) ? (string)$_POST['document_title'] : '';
		$data['meta_descr']=isset($_POST['meta_descr']) && is_array($_POST['meta_descr']) ? (string)$_POST['meta_descr'] : '';

		if($data['subject']==='')
			$errors['EMPTY_SUBJECT']='';

		if($data['text']==='')
			$errors['EMPTY_TEXT']='';
	}

	$data['recipient']=[];

	if(isset($_POST['recipient']) and is_array($_POST['recipient']))
	{
		foreach($_POST['recipient'] as $k=>$v)
			if(is_array($v) and isset($v['email'],$v['title']))
			{
				if(isset($v['email_id']) and ctype_digit($v['email_id']))
					$v['email']=(int)$v['email_id'];
				elseif(!filter_var($v['email'],FILTER_VALIDATE_EMAIL))
				{
					$errors[0]='INCORRECT_EMAIL';
					continue;
				}

				$v['title']=GlobalsWrapper::Filter($v['title']);

				if(Eleanor::$vars['multilang'] xor is_array($v['title']))
					$v['title']=Eleanor::$vars['multilang'] ? [''=>$v['title']] : FilterLangValues($v['title']);

				$data['recipient'][ $v['email'] ]=$v['title'];
			}
	}

	foreach(['EMPTY_SUBJECT','EMPTY_TEXT'] as $k)
		if(isset($errors[$k]))
			$errors[$k]=$lang[$k]( $errors[$k]==='' ? [] : $errors[$k] );

	if($errors)
		goto Form;

	$saved=file_put_contents($config['data'],'<?php return '.var_export($data,true).';');
}

Form:

if($errors or $saved)
{
	include_once DIR.'crud.php';

	if(!is_array($errors))
		$errors=[];

	$tt=Eleanor::$vars['multilang'] ? 'array' : 'string';#TextType

	$data=[
		'info'=>$tt,
		'subject'=>$tt,
		'text'=>$tt,
		'document_title'=>$tt,
		'meta_descr'=>$tt,
	];
	PostValues($values,$data);

	$recipient=[];

	if(isset($_POST['recipient']) and is_array($_POST['recipient']))
	{
		foreach($_POST['recipient'] as $k=>$v)
			if(is_array($v) and isset($v['email'],$v['title']))
			{
				if(isset($v['email_id']) and ctype_digit($v['email_id']))
					$v['email']=$v['email_id'];

				$recipient[ $v['email'] ]=GlobalsWrapper::Filter($v['title']);
			}

		$values['recipient']=$recipient;
	}
}

$table=USERS_TABLE;
$recipient=[];
$def=Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : '';

foreach($values['recipient'] as $k=>&$v)
{
	if(is_int($k))
		$recipient[]=$k;

	if(Eleanor::$vars['multilang'] xor is_array($v))
		$v=Eleanor::$vars['multilang'] ? [Language::$main=>$v]+$def : FilterLangValues($v);
	elseif(Eleanor::$vars['multilang'])
		$v+=$def;
}

unset($v);

if($recipient)
{
	$R=Eleanor::$UsersDb->Query("SELECT `id`,`name` FROM `{$table}` WHERE `id`".Eleanor::$Db->In($recipient));
	$recipient=[];
	if($a=$R->fetch_assoc())
	{
		$a['_a']=UserLink($a['id'],$a['name'],'admin');
		$recipient[ $a['id'] ]=array_slice($a,1);
	}
}

$Editor=function($name)use($Eleanor){
	$Editor=strpos($name,'info')===0 ? new Editor : new Editor(null,false,false);
	return call_user_func_array([$Editor,'Area'],func_get_args());
};

$title[]=$Eleanor->module['title'];
$s=Eleanor::$Template->Contacts($values,$recipient,$Editor,$Eleanor->Uploader->Show(),$errors,$saved);
Response($s);