<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\BBCode, \Eleanor\Classes\Output, Eleanor\Classes\Strings, Eleanor\Classes\Email;

defined('CMS\STARTED')||die;

/** @var $config array */

global$Eleanor,$title;

if(!isset($Eleanor->module['etag']))
	$Eleanor->module['etag']='';

$id=0;

if(isset($Eleanor->module['code']))
	$id=(string)$Eleanor->module['code'];
elseif(isset($_GET['id']))
	$id=(int)$_GET['id'];
elseif($Eleanor->Url->parts)
	$id=reset($Eleanor->Url->parts);

$id=is_int($id) ? '`id`='.$id : '`uri`='.Eleanor::$Db->Escape($id);
$l=Language::$main;
$R=Eleanor::$Db->Query("SELECT `id`, `http_code`, `miniature_type`, `miniature`, `email`, `log`, `log_language`, `language`, `title`, `text`, `document_title`, `meta_descr`, `last_mod`
FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`) WHERE `language` IN ('','{$l}') AND {$id} LIMIT 1");
if(!$error=$R->fetch_assoc())
	return GoAway('');

$image=false;

if($error['miniature'] and $error['miniature']) switch($error['miniature_type'])
{
	case'gallery':
		if(is_file($f=$config['gallery-path'].$error['miniature']))
			$image=[
				'type'=>'gallery',
				'path'=>$f,
				'http'=>$config['gallery-http'].$error['miniature'],
				'src'=>$error['miniature'],
			];
		break;
	case'upload':
		if(is_file($f=$config['uploads-path'].$error['miniature']))
			$image=[
				'type'=>'upload',
				'path'=>$f,
				'http'=>$config['uploads-http'].$error['miniature'],
				'src'=>$error['miniature'],
			];
		break;
	case'link':
		$image=[
			'type'=>'link',
			'http'=>$error['miniature'],
		];
}

$error['miniature']=$image;

$isu=Eleanor::$Login->IsUser();
$uid=$isu ? (int)Eleanor::$Login->Get('id') : 0;
$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
$errors=[];
$sent=false;
$Captcha=Captcha();
$values=['text'=>''];

if($isu)
	$values['name']='';

$logerror=$error;

if(Eleanor::$vars['multilang'] and $error['log_language'] and $error['language'] and $error['language']!=$error['log_language'])
{
	$R=Eleanor::$Db->Query("SELECT `language`, `title` FROM `{$config['tl']}` WHERE `id`={$error['id']} AND `language`='{$error['log_language']}'");
	if($R->num_rows>0)
		$logerror=$R->fetch_assoc();
}

if($error['email'] and $_SERVER['REQUEST_METHOD']=='POST')
{
	if(isset($_POST['text']))
	{
		$Eleanor->Saver->ownbb=false;
		$values['text']=$Eleanor->Saver->Save('text');
	}

	if($values['text']=='')
		$errors[]='EMPTY_TEXT';

	if($isu)
	{
		$user=Eleanor::$Login->Get(['id','full_name','name']);
		$name=$user['name'];
	}
	else
	{
		$values['name']=isset($_POST['name']) ? (string)$_POST['name'] : '';

		if($values['name']!=='')
			$name=$values['name'];
		else
			$errors[]='EMPTY_NAME';
	}

	if($Captcha)
	{
		if(!$Captcha->Check())
			$errors[]='WRONG_CAPTCHA';

		if(method_exists($Captcha,'Destroy'))
			$Captcha->Destroy();
	}

	if(!$errors)
	{
		$l=include$Eleanor->module['path'].'letters-'.($logerror['language'] ? $logerror['language'] : Language::$main).'.php';
		$repl=[
			'site'=>Eleanor::$vars['site_name'],
			'name'=>GlobalsWrapper::Filter($name),
			'fullname'=>$isu ? $user['full_name'] : '',
			'userlink'=>$isu ? \Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.UserLink($user['name'],$user['id']) : '',
			'text'=>$values['text'],
			'link'=>\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR,
			'linkerror'=>\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.Url::$current,
			'from'=>$back,
		];

		Email::Simple(
			$error['email'],
			BBCode::ExecLogic($l['error_t'],$repl),
			BBCode::ExecLogic($l['error'],$repl)
		);

		$sent=true;
	}
}

if($error['log'] and $back and strpos($back,\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR)===0 and !$errors and !$sent)
{
	$E=new EE_Request($logerror['title'],EE_Request::USER,['code'=>$error['http_code'],'back'=>$back]);
	$E->Log();
}

if($error['document_title'])
	$title=$error['document_title'];
else
	$title=[$error['title']];

$Eleanor->module['description']=$error['meta_descr'] ? $error['meta_descr'] : Strings::CutStr(strip_tags(str_replace("\n",' ',$error['text'])),250);

if($error['email'])
	$Eleanor->Editor->ownbb=false;

$etag=md5($Eleanor->module['etag'].$uid);

if(!Output::TryReturnCache($etag))
{
	$Editor=function(...$args)use($Eleanor){
		return$Eleanor->Editor->Area(...$args);
	};

	$out=Eleanor::$Template->ShowError($error,$sent,$values,$Editor,$errors,$back,$Captcha ? $Captcha->GetCode() : '');
	$out=(string)Eleanor::$Template->index([ 'content'=>$out ]);

	OutPut::SendHeaders('html',!$sent && $error['http_code'] ? $error['http_code'] : 200,[
		'max-age'=>0,
		'etag'=>$etag,
		'modified'=>$error['last_mod'],
	]);

	header('Retry-After: 0');
	Output::Gzip($out);
}