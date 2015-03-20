<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\OutPut;
define('CMS\STARTED',microtime(true));

require __DIR__.'/cms/core.php';
include DIR.'functions.php';

$Eleanor=new Eleanor(true);

LoadOptions(['site','files']);
SetService('download');

/** Страница с ошибкой
 * @param int $code Код ошибки
 * @param int $r Код редиректа на страницу ошибки */
function ExitPage($code=403,$r=301)
{
	$Url=new Url(false);

	if(Eleanor::$vars['multilang'])
		$Url->prefix=Url::Encode(Eleanor::$langs[ Language::$main ]['uri']).'/';

	$errors=array_keys(GetModules('index')['uri2section'],'errors');

	GoAway($Url([reset($errors),$code]),$r);
}

if(Eleanor::$vars['multilang'])
{
	if(isset($_REQUEST['language']) and is_string($_REQUEST['language']) and $_REQUEST['language']!=Language::$main)
		Eleanor::$Language->Change($_REQUEST['language']);

	foreach(Eleanor::$lvars as $lk=>$lv)
		Eleanor::$vars[$lk]=FilterLangValues($lv);
}
else
	Eleanor::$lvars=[];

if(Eleanor::$vars['site_closed'] and (!isset(Eleanor::$Permissions) or !Eleanor::$Permissions->ClosedSiteAccess()))
{
	/** @var Logins\Admin $class */
	$class='\CMS\Logins\\'.Eleanor::$services['admin']['login'];

	if(!$class::IsUser())
		return ExitPage();
}

unset(Eleanor::$vars['site_close_mes']);

if(Eleanor::$ban or isset(Eleanor::$Permissions) and Eleanor::$Permissions->IsBanned())
	ExitPage();
elseif(isset($_GET['direct']) and key($_GET)=='direct'
	and is_file($f=DIR.'direct/'.preg_replace('#[^a-z0-9\-_]+#i','',(string)$_GET['direct']).'.php'))
	include$f;
elseif(isset($_REQUEST['module']) and is_string($_REQUEST['module']))
{
	$uri=$_REQUEST['module'];
	$Eleanor->modules=GetModules();

	if(!isset($Eleanor->modules['uri2id'][$uri]))
		return ExitPage();

	$id=$Eleanor->modules['uri2id'][$uri];
	$module=$Eleanor->modules['id2module'][$id];
	$path=DIR.$module['path'];
	$Eleanor->module=[
		'uri'=>$uri,
		'section'=>isset($Eleanor->modules['uris'][$uri]) ? $Eleanor->modules['uris'][$uri] : '',
		'path'=>$path,
		'id'=>$id,
		'uris'=>$module['uris'],
	];

	$path.=$module['file'] ? $module['file'] : 'index.php';

	if(is_file($path))
		\Eleanor\AwareInclude($path);
	else
		return ExitPage();
}
elseif(isset($_GET['captcha']))
	\Eleanor\Classes\Captcha::GetImage();
elseif(isset($_GET['download']))
{
	if(Eleanor::$vars['download_antileech'] and !Eleanor::$ourquery)
		return ExitPage();

	if(Eleanor::$vars['download_no_session'])
	{
		$sip=Eleanor::$Db->Escape(Eleanor::$ip);
		$R=Eleanor::$Db->Query('SELECT `expire` FROM `'.P.'sessions` WHERE `expire`>\''.date('Y-m-d H:i:s').'\' AND (`ip_guest`='.$sip.' OR `ip_user`='.$sip.') LIMIT 1');
		if($R->num_rows==0)
			return ExitPage();
	}

	$file=Url::Decode($_GET['download']);
	$file=\Eleanor\Classes\Files::Windows($file);
	$file=Template::$path['uploads'].trim($file,'/\\');
	$file=realpath($file);

	if(strpos($file,realpath(Template::$path['uploads']))!==0)
		return ExitPage(404);

	if(!is_file($file))
		return ExitPage(404);

	$etag=md5($file);
	$mtime=filemtime($file);

	if(!Output::TryReturnCache($etag,$mtime))
		Output::Stream([ 'file'=>$file, 'etag'=>$etag, 'last-modified'=>$mtime ]);
}
else
	return ExitPage();

SetSession();