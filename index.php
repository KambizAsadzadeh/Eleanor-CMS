<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, \Eleanor\Classes\OutPut;

define('CMS\STARTED',microtime(true));

require __DIR__.'/cms/core.php';

#А мы-то собственно, установлены или нет? Удалить эту и 4 следующие строки после установки системы
if(filesize(__DIR__.'/cms/config.php')==0)
{
	header('Location: '.\Eleanor\SITEDIR.'install/');
	die('Eleanor CMS is not installed!');
}

$Eleanor=new Eleanor(true);

LoadOptions('site');
SetService('index');

include DIR.'functions.php';

/** Сдвиг URL (удаление первого элемента)
 * @param array $url URL, который будет сдвигаться */
function UnshiftUrl(array&$url)
{
	if(!isset($url[1]) or count($url)==2 and $url[1]=='')
		$url=[];
	else
		array_splice($url,0,1);
}

/** Генерация нового URL после перехода на новый язык
 * @param string|null $url Старый URL, в случае NULL берется текущий запрос
 * @param string $newlang Название нового языка
 * @param string|null Идентификатор старого языка
 * @return string */
function LangNewUrl($url,$newlang,$oldlang=null)
{
	$Url=new Url(false);

	if(Eleanor::$vars['multilang'])
		$Url->prefix=Url::Encode(Eleanor::$langs[ $newlang ]['uri']).'/';

	if($url===null)
	{
		$url=$GLOBALS['Eleanor']->Url->parts;
		$query=$_GET;

		if(!$url)
			return\Eleanor\SITEDIR.$Url->prefix;
	}
	else
	{
		#Корень сайта для очистки URL
		$base=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;

		#Если запрос пришел с чужого домена - считаем такой запрос некорректным
		if(strpos($url,'://')!==false and strpos($url,$base)!==0)
			$url='';
		else
		{
			$url=preg_replace('#^'.preg_quote($base,'#').'#i','',$url);
			list($url,$query)=Url::ParseRequest($url);
		}

		if(!$url)
			return\Eleanor\SITEDIR.Url::Make([ Eleanor::$langs[ $newlang ]['uri'] ],'/',$query);

		$url=explode('/',$url);
	}

	#Возможно, старый URL содержал в себе идентификатор языка
	if(!$oldlang)
		foreach(Eleanor::$langs as $k=>$v)
			if($v['uri']==$url[0])
			{
				$oldlang=$k;

				if(!isset($url[1]) or count($url)==2 and $url[1]=='')
					return\Eleanor\SITEDIR.$Url->prefix;

				UnshiftUrl($url);
				break;
			}

	$modules=GetModules(null,$oldlang);

	if(isset($modules['uri2id'][ $url[0] ]))
	{
		$id=(int)$modules['uri2id'][ $url[0] ];
		$section=$modules['uri2section'][ $url[0] ];
		UnshiftUrl($url);
	}
	else
	{
		$id=(int)Eleanor::$vars['prefix_free_module'];
		$section=null;
	}

	$modules=GetModules(null,$newlang);

	if(!isset($modules['id2module'][$id]))
		return\Eleanor\SITEDIR.Url::Make($url,'',$query);

	$module=$modules['id2module'][$id];

	if($section)
	{
		$prefix=array_keys($modules['uri2id'],$id);
		$Url->prefix.=Url::Encode( reset($prefix) ).'/';
	}

	if($module['api'])
	{
		$api=DIR.$module['path'].$module['api'];
		$class='Api'.basename($module['path']);

		if(!class_exists($class,false))
			if(is_file($api))
			{
				$retapi=\Eleanor\AwareInclude($api);

				if(is_string($retapi))
					$class=$retapi;

				if(!class_exists($class,false))
					goto Bad;
			}
			else
				goto Bad;

		if(is_a($class,'CMS\Interfaces\NewLangUrl',true))
		{
			/** @var Interfaces\NewLangUrl $Api */
			$Api=new$class([
				'uris'=>$module['uris'],
				'id'=>$id,
			]);

			if($r=$Api->GetNewLangUrl($section,$url,$query,$oldlang,$newlang,$Url))
				return\Eleanor\SITEDIR.$r;
		}
	}

	Bad:

	return\Eleanor\SITEDIR.$Url($url,'',$query);
}

/** Отображение страницы с ошибкой
 * @param int $code Код ошибки
 * @return mixed */
function ExitPage($code=404)
{global$Eleanor;
	if(isset($Eleanor->modules['id2module'][3]))#3 - ID модуля "errors"
	{
		$module=$Eleanor->modules['id2module'][3];
		$path=DIR.$module['path'];
		$Eleanor->module=[
			'uri'=>$code,
			'section'=>isset($Eleanor->modules['uris'][ $code ]) ? $Eleanor->modules['uris'][ $code ] : '',
			'path'=>$path,
			'id'=>3,
			'uris'=>$module['uris'],
			'code'=>$code,
		];

		$path.=$module['file'] ? $module['file'] : 'index.php';

		if(is_file($path))
		{
			$Eleanor->Url->prefix.=$code.'/';
			return\Eleanor\AwareInclude($path);
		}
	}

	return GoAway(true);
}

/** Показ главной страницы сайта */
function MainPage()
{global$Eleanor;
	$id=(int)Eleanor::$vars['prefix_free_module'];

	if(isset($Eleanor->modules['id2module'][$id]))
	{
		$module=$Eleanor->modules['id2module'][$id];

		$path=DIR.$module['path'];
		$Eleanor->module=[
			'uri'=>reset($module['uris']),
			'section'=>key($module['uris']),
			'path'=>$path,
			'id'=>$id,
			'uris'=>$module['uris'],
		];

		if(!$Eleanor->Url->parts)
			$Eleanor->module['general']=true;

		$path.=$module['file'] ? $module['file'] : 'index.php';

		if(!is_file($path))
			throw new EE('Unable to load module',EE::DEV);

		\Eleanor\AwareInclude($path);
	}
	else
		ExitPage();

	SetSession();
}

if(isset($_GET['newtpl']) and Eleanor::$vars['templates'] and in_array($_GET['newtpl'],Eleanor::$vars['templates']))
{
	if(Eleanor::$Login->IsUser())
		UserManager::Update(['theme'=>$_GET['newtpl']]);
	else
		SetCookie('theme',$_GET['newtpl']);

	return GoAway();
}

if(Eleanor::$vars['multilang'])
{
	$isu=Eleanor::$Login->IsUser();

	if(isset($_GET['language']) and is_string($_GET['language']) and isset(Eleanor::$langs[ $_GET['language'] ]) and
		count($_GET)==1)
	{
		if($isu)
			UserManager::Update([ 'language'=>$_GET['language'] ]);
		else
			SetCookie('lang',$_GET['language']);

		return GoAway(html_entity_decode(LangNewUrl(getenv('HTTP_REFERER'),$_GET['language'])));
	}

	if($Eleanor->Url->parts)
		foreach(Eleanor::$langs as $l=>$lang)
			if($lang['uri']===$Eleanor->Url->parts[0])
			{
				UnshiftUrl($Eleanor->Url->parts);

				if($l!=Language::$main)
					Eleanor::$Language->Change($l);

				$Eleanor->Url->prefix=Url::$base=Url::Encode($lang['uri']).'/';

				foreach(Eleanor::$lvars as $lk=>$lv)
					Eleanor::$vars[$lk]=FilterLangValues($lv);

				goto SkipRedirect;
			}

	$syslang=Language::$main;

	if(!$isu and $l=GetCookie('lang') and isset(Eleanor::$langs[$l]) and $l!=Language::$main)
		Eleanor::$Language->Change($l);

	#Попробуем определить основной язык пользователя
	elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		$ua_lang=[];

		foreach(explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang)
		{
			$lang=trim($lang);

			if(false===$p=strpos($lang,';q='))
			{
				$ua_lang=[$lang=>1];
				break;
			}
			else
				$ua_lang[ substr($lang,0,$p) ]=(float)substr($lang,$p+3);
		}

		arsort($ua_lang,SORT_NUMERIC);
		$ua_lang=substr(key($ua_lang),0,2);

		foreach(Eleanor::$langs as $l=>$lang)
			if($lang['d']==$ua_lang and $l!=Language::$main)
			{
				Eleanor::$Language->Change($l);
				break;
			}
	}

	#Автоматическое прописывание языкового префикса к URL
	if($Eleanor->Url->parts and $syslang!=Language::$main)
		return GoAway(html_entity_decode(LangNewUrl(null,Language::$main,$syslang)));

	return GoAway(Url::Make([ 'lang'=>Eleanor::$langs[ Language::$main ]['uri'] ]+$Eleanor->Url->parts,
		$Eleanor->Url->parts ? '' : '/',$_GET));

	SkipRedirect:
}
else
{
	Eleanor::$lvars=[];

	if($Eleanor->Url->parts)
		foreach(Eleanor::$langs as $l=>$lang)
			if($lang['uri']==$Eleanor->Url->parts[0])
			{
				UnshiftUrl($Eleanor->Url->parts);

				return GoAway(html_entity_decode(LangNewUrl(null,Language::$main,$lang['uri'])));
			}
}

$theme=Eleanor::$Login->IsUser() ? Eleanor::$Login->Get('theme') : GetCookie('theme');
if(!Eleanor::$vars['templates'] or !in_array($theme,Eleanor::$vars['templates']))
	$theme=false;

SetTemplate($theme ? $theme : Eleanor::$services[Eleanor::$service]['theme']);

if(Eleanor::$vars['site_closed'] and !Eleanor::$Permissions->ClosedSiteAccess())
{
	/** @var Logins\Admin $class */
	$class='\CMS\Logins\\'.Eleanor::$services['admin']['login'];

	if(!$class::IsUser())
	{
		if(AJAX or ANGULAR)
			Error(Eleanor::$Language['ajax']['site_closed']);
		else
		{
			$out=(string)Eleanor::$Template->Denied();

			OutPut::SendHeaders('html',503);
			header('Retry-After: 7200',true);
			Output::Gzip($out);
		}
		die;
	}
}
unset(Eleanor::$vars['site_close_mes']);

if(Eleanor::$ban or isset(Eleanor::$Permissions) and Eleanor::$Permissions->IsBanned())
{
	$out=(string)Eleanor::$Template->Banned(Eleanor::$ban);
	OutPut::SendHeaders('html',503);
	Output::Gzip($out);
}
elseif(isset($_GET['direct']) and key($_GET)=='direct'
	and is_file($f=DIR.'direct/'.preg_replace('#[^a-z0-9\-_]+#i','',(string)$_GET['direct']).'.php'))
	include$f;
else
{
	$Eleanor->modules=GetModules();

	if($_SERVER['QUERY_STRING'] or $Eleanor->Url->parts)
	{
		if($Eleanor->Url->parts)
		{
			$uri=$Eleanor->Url->parts[0];

			if(!isset($Eleanor->modules['uri2id'][$uri]))
				return MainPage();

			$id=$Eleanor->modules['uri2id'][$uri];

			UnshiftUrl($Eleanor->Url->parts);
		}
		elseif(isset($_GET['module']))
		{
			$uri=(string)$_GET['module'];

			if(!isset($Eleanor->modules['uri2id'][$uri]))
				return MainPage();

			$id=$Eleanor->modules['uri2id'][$uri];
		}
		else
			return MainPage();

		Module:
		$module=$Eleanor->modules['id2module'][$id];
		$path=DIR.$module['path'];
		$Eleanor->module=[
			'uri'=>$uri,
			'section'=>$Eleanor->modules['uri2section'][$uri],
			'path'=>$path,
			'id'=>$id,
			'uris'=>$module['uris'],
		];

		$path.=$module['file'] ? $module['file'] : 'index.php';

		if(is_file($path))
		{

			if(Eleanor::$vars['prefix_free_module']!=$id)
				$Eleanor->Url->prefix.=Url::Encode($uri).'/';

			\Eleanor\AwareInclude($path);
		}
		else
			ExitPage();
	}
	elseif(isset($_POST['module']))
	{
		$id=(string)$_POST['module'];

		if(!isset($Eleanor->modules['id2module'][$id]))
			return ExitPage();

		goto Module;
	}
	else
		MainPage();
}

if(!AJAX and !ANGULAR)
	SetSession();