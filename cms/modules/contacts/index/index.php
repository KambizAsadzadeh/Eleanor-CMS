<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

use Eleanor\Classes\BBCode, Eleanor\Classes\Email, Eleanor\Classes\Files;

defined('CMS\STARTED')||die;
global$Eleanor,$title;

/** @var array $config */

$isu=Eleanor::$Login->IsUser();
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'index-*.php',$config['n']);
$data=include $config['data'];
$maxupload=5*1024*1024;//Eleanor::$Permissions->MaxUpload();
$errors=[];
$values=[
	'subject'=>'',
	'message'=>'',
	'recipient'=>0,
	'session'=>'',
	'from'=>$isu ? null : '',
];
$Eleanor->module['origurl']=rtrim($Eleanor->Url->prefix,'/');
$Captcha=Captcha();

if($_SERVER['REQUEST_METHOD']=='POST')
{
	$Saver=new Saver(null,false,false);

	$values=[
		'subject'=>isset($_POST['subject']) ? trim((string)Eleanor::$POST['subject']) : '',
		'message'=>isset($_POST['message']) ? $Saver->Save((string)$_POST['message']) : '',
		'recipient'=>isset($_POST['recipient']) ? (int)$_POST['recipient'] : 0,
		'session'=>isset($_POST['session']) ? (string)$_POST['session'] : false,
		'from'=>isset($_POST['from']) && !$isu ? (string)$_POST['from'] : '',
	];

	do
	{
		#Защита от F5
		if($values['session'])
			\Eleanor\StartSession($values['session']);

		if(empty($_SESSION['can']) or !$data['recipient'])
			break;

		if($values['from'])
		{
			if(!filter_var($values['from'],FILTER_VALIDATE_EMAIL))
				$errors[]='WRONG_EMAIL';
		}
		else
			$values['from']=$isu ? Eleanor::$Login->Get('email') : false;

		$recipient=array_keys($data['recipient']);
		if(!isset($recipient[ $values['recipient'] ]))
			$errors[]='WRONG_RECIPIENT';

		if(!$values['subject'])
			$errors[]='EMPTY_SUBJECT';

		if(!isset($values['message'][7]))
			$errors[]='SHORT_MESSAGE';

		if($Captcha and !$Captcha->Check())
			$errors[]='WRONG_CAPTCHA';

		$files=[];
		$size=0;

		if($maxupload and isset($_FILES['file']) and is_array($_FILES['file']['name']))
			foreach($_FILES['file']['name'] as $k=>$name)
			{
				$size+=$_FILES['file']['size'][$k];
				$files[ $name ]=file_get_contents($_FILES['file']['tmp_name'][$k]);
			}

		if($size>$maxupload)
			$errors['ATTACH_TOO_BIG']=sprintf($lang['FILE_TOO_BIG'],Files::BytesToSize($maxupload),Files::BytesToSize($size));

		if($errors)
			break;

		if(is_int($recipient[ $values['recipient'] ]))
		{
			$table=P.'users_site';
			$R=Eleanor::$Db->Query("SELECT `email` FROM `{$table}` WHERE `id`={$recipient[ $values['recipient'] ]} LIMIT 1");
			if(!list($email)=$R->fetch_row())
			{
				$errors[]='WRONG_RECIPIENT';
				break;
			}
		}
		else
			$email=$recipient[ $values['recipient'] ];

		$user=$isu ? Eleanor::$Login->Get(['id','name']) : false;
		$subject=is_array($data['subject']) ? FilterLangValues($data['subject']) : $data['subject'];
		$text=is_array($data['text']) ? FilterLangValues($data['text']) : $data['text'];

		$repl=[
			'subject'=>$values['subject'],
			'message'=>$values['message'],
			'name'=>$isu ? htmlspecialchars($user['name'],ENT,\Eleanor\CHARSET) : '',
			'userlink'=>$isu ? \Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.UserLink($user['name'],$user['id']) : '',
			'ip'=>Eleanor::$ip,
			'site'=>Eleanor::$vars['site_name'],
			'link'=>\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR,
		];


		Email::Simple($email,
			BBCode::ExecLogic($subject,$repl),
			BBCode::ExecLogic($text,$repl),
			['files'=>$files,'from'=>$values['from']]);

		$_SESSION['can']=false;

		$links=[
			'send'=>$Eleanor->module['origurl'],
		];
		$title[]=$lang['successfully-send'];
		$c=Eleanor::$Template->Sent($links);
		Response($c);

		return 1;
	}while(false);
}

if($errors)
{
	include_once DIR.'crud.php';

	if(!is_array($errors))
		$errors=[];

	$postdata=[
		'subject'=>'string',
		'message'=>'string',
		'recipient'=>'int',
		'session'=>'string',
	];

	if(!$isu)
		$postdata['from']='string';

	PostValues($values,$postdata);
}

if(is_array($data['document_title']))
	$data['document_title']=FilterLangValues($data['document_title']);

if($data['document_title'])
	$title=$data['document_title'];
else
	$title[]=$lang['contacts'];

$info=is_array($data['info']) ? FilterLangValues($data['info']) : $data['info'];
$recipient=$data['recipient'] ? array_values($data['recipient']) : null;

foreach($recipient as &$v)
	if(is_array($v))
		$v=FilterLangValues($v);
unset($v);

\Eleanor\StartSession($values['session']);
$_SESSION['can']=true;
$values['session']=session_id();

$Editor=function(...$args)use($Eleanor){
	$Editor=new Editor(null,false,false);
	return $Editor->Area(...$args);
};

$Eleanor->module['description']=is_array($data['meta_descr']) ? FilterLangValues($data['meta_descr']) : $data['meta_descr'];

$c=Eleanor::$Template->Contacts($info,$maxupload,$recipient,$values,$errors,$Editor,$Captcha->GetCode());
Response($c);