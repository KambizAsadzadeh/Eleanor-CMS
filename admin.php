<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, \Eleanor\Classes\OutPut, Eleanor\Classes\EE_DB;

define('CMS\STARTED',microtime(true));

require __DIR__.'/cms/core.php';

$Eleanor=new Eleanor(true);

LoadOptions(['site','users-on-site']);
SetService('admin');
Eleanor::$Language->queue['main']=DIR.'translation/admin-*.php';

include DIR.'functions.php';

/** Генерация нового URL после перехода на новый язык
 * @param string|null $url Старый URL, в случае NULL берется текущий запрос
 * @param string $newlang Название нового языка
 * @param string|null Идентификатор старого языка
 * @return string */
function LangNewUrl($url,$newlang,$oldlang=null)
{
	$Url=new DynUrl;

	if(Eleanor::$vars['multilang'])
		$Url->prefix.='lang='.urlencode(Eleanor::$langs[ $newlang ]['uri']).'&amp;';

	if(!$url)
	{
		Prefix:
		return \Eleanor\SITEDIR.(string)$Url;
	}

	#Если запрос пришел с чужого домена - считаем такой запрос некорректным
	if(strpos($url,'://')!==false and strpos($url,\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR)!==0)
		goto Prefix;

	if(strpos($url,'?')===false)
		goto Prefix;

	$url=substr(strchr($url,'?'),1);
	parse_str($url,$query);

	#Возможно, старый URL содержал в себе идентификатор языка
	if(!$oldlang and isset($query['lang']))
		foreach(Eleanor::$langs as $k=>$v)
			if($v['uri']==$query['lang'])
			{
				$oldlang=$k;
				unset($query['lang']);#В новом запросе не дложно быть старого языка
				break;
			}

	if(!isset($query['section'],$query['module']) or $query['section']!='modules')
		return\Eleanor\SITEDIR.$Url($query);

	$Url->prefix.='section=modules&amp;';

	$modules=GetModules(null,$oldlang);

	if(!isset($modules['uri2id'][ $query['module'] ]))
		return\Eleanor\SITEDIR.$Url($query);

	$id=(int)$modules['uri2id'][ $query['module'] ];
	$section=$modules['uri2section'][ $query['module'] ];

	$modules=GetModules(null,$newlang);

	if(!isset($modules['id2module'][$id]))
		return\Eleanor\SITEDIR.$Url($query);

	$module=$modules['id2module'][$id];
	$prefix=array_keys($modules['uri2id'],$id);
	$Url->prefix.='module='.Url::Encode( reset($prefix) ).'&amp;';

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

			unset($query['section'],$query['module']);
			if($r=$Api->GetNewLangUrl($section,[],$query,$oldlang,$newlang,$Url))
				return\Eleanor\SITEDIR.$r;
		}
	}

	Bad:

	return\Eleanor\SITEDIR.$Url($query);
}

/** Отображение страницы с ошибкой
 * @param int $code Код ошибки
 * @return mixed */
function ExitPage($code=404)
{
	$Url=new Url(false);

	if(Eleanor::$vars['multilang'])
		$Url->prefix=Url::Encode(Eleanor::$langs[ Language::$main ]['uri']).'/';

	$errors=array_keys(GetModules('index')['uri2section'],'errors');
	GoAway($Url([reset($errors),$code]),301);

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

	$mislang=true;

	if(isset($_GET['lang']))
		foreach(Eleanor::$langs as $l=>$lang)
			if($lang['uri']===$_GET['lang'])
			{
				if($l!=Language::$main)
					Eleanor::$Language->Change($l);

				$mislang=false;
				break;
			}

	if($mislang and (!$isu or !Eleanor::$Login->Get('language')))
	{
		$syslang=Language::$main;

		if($l=GetCookie('lang') and isset(Eleanor::$langs[$l]) and $l!=Language::$main)
			Eleanor::$Language->Change($l);

		#Попробуем определить основной язык пользователя
		elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$ua_lang=[];

			foreach(explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $ur)
			{
				$ur=trim($ur);

				if(false===$p=strpos($ur,';q='))
				{
					$ua_lang=[$ur=>1];
					break;
				}
				else
					$ua_lang[ substr($ur,0,$p) ]=(float)substr($ur,$p+3);
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
		if($syslang!=Language::$main)
			return GoAway(html_entity_decode(LangNewUrl(null,Language::$main,$syslang)));
	}

	$lang=Eleanor::$langs[ Language::$main ];
	DynUrl::$base.='lang='.urlencode($lang['uri']).'&amp;';
	Url::$base=Url::Encode($lang['uri']).'/';

	foreach(Eleanor::$lvars as $lk=>$lv)
		Eleanor::$vars[$lk]=FilterLangValues($lv);
}
else
{
	Eleanor::$lvars=[];
	$Eleanor->Url=new DynUrl();
}

SetTemplate(Eleanor::$services[Eleanor::$service]['theme']);

if(isset($_GET['direct']) and key($_GET)=='direct'
	and is_file($f=DIR.'direct/'.preg_replace('#[^a-z0-9\-_]+#i','',(string)$_GET['direct']).'.php'))
	include$f;
elseif(Eleanor::$Login->IsUser())
{
	$section=isset($_REQUEST['section']) ? (string)$_REQUEST['section'] : false;

	if(!$section and isset($_GET['logout']))
	{
		Eleanor::$Login->LogOut();
		return GoAway(html_entity_decode($Eleanor->DynUrl->Prefix()));
	}

	$lang=Eleanor::$Language['main'];
	$title[]=$lang['admin'];
	$Eleanor->Url=$Eleanor->DynUrl;

	switch($section)
	{
		case'options':
		case'management':
		case'modules':
		case'statistic':
			$title[]=$lang[$section];
			$Eleanor->DynUrl->prefix.='section='.$section.'&amp;';
			\Eleanor\AwareInclude(DIR.'admin/modules/section_'.$section.'.php');
		break;
		default:
			$title[]=$lang['modules'];
			\Eleanor\AwareInclude(DIR.'admin/modules/section_general.php');
	}
}
else
{
	$lang=Eleanor::$Language->Load(DIR.'translation/admin_enter-*.php','enter');
	$login=isset($_POST['login']['name']) ? (string)$_POST['login']['name'] : '';
	$password=isset($_POST['login']['password']) ? (string)$_POST['login']['password'] : '';
	$errors=[];
	$Captcha=isset(Eleanor::$Login->BeforeLogin()['captcha']) ? Captcha(true) : false;

	if($Captcha)
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			if($Captcha->Check())
				$check=true;
			else
				$errors[]='WRONG_CAPTCHA';
	}

	if($login and $password==='')
		$errors[]='EMPTY_PASSWORD';

	if(!$errors and isset($_POST['login']))
		try
		{
			Eleanor::$Login->Login((array)$_POST['login'],$Captcha ? ['captcha'=>true] : []);

			if($_SERVER['QUERY_STRING'])
			{
				parse_str($_SERVER['QUERY_STRING'],$query);
				unset($query['lang']);
			}
			else
				$query='';

			return GoAway(basename(__FILE__).($query ? '?'.DynUrl::Query($query) : ''));
		}
		catch(EE_DB$E)
		{
			throw$E;
		}
		catch(EE$E)
		{
			$error=$E->getMessage();
			switch($error)
			{
				case'TEMPORARILY_BLOCKED':
					$errors['TEMPORARILY_BLOCKED']=$lang['TEMPORARILY_BLOCKED'](
						htmlspecialchars($login,ENT,\Eleanor\CHARSET),round($E->extra['remain']/60)
					);
				break;
				case'CAPTCHA':
					$Captcha=Captcha(true);
					$errors[]='CAPTCHA';
				break;
				default:
					$password='';
					$errors[]=$error;
			}
		}
	$title[]=$lang['enter_to'];

	$out=(string)Eleanor::$Template->Enter([
		'errors'=>$errors,'login'=>$login,'password'=>$password,'captcha'=>$Captcha ? $Captcha->GetCode() : false,
	]);

	OutPut::SendHeaders('html');
	Output::Gzip($out);
}

if(!AJAX and !ANGULAR)
	SetSession();