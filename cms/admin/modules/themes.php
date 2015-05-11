<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files, Eleanor\Classes\Output;
defined('CMS\STARTED')||die;

if(Eleanor::$service=='download')
{
	do
	{
		if(!isset($_GET['file']))
			break;

		$tpl_path=realpath(DIR.'../templates'.DIRECTORY_SEPARATOR);
		$path=realpath($tpl_path.Files::Windows(trim($_GET['file'],'/\\')));

		if(!$path or strncmp($path,$tpl_path,strlen($tpl_path))!=0 or !is_file($path))
			break;

		return Output::Stream(['file'=>$path]);

	}while(false);

	GoAway();

	return 1;
}

global$Eleanor,$title;
$lang=Eleanor::$Language->Load(DIR.'admin/translation/themes-*.php','themes');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Themes.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'info'=>null,
	'files'=>null,
	'config'=>null,
];

/** Создание навигации для шаблонов
 * @param string $name Имя шаблона
 * @param array $settings Установки шаблона*/
function DoNavigation($name,$settings)
{global$Eleanor;
	/** @var DynUrl $Url */
	$Url=$Eleanor->DynUrl;

	$Eleanor->module['links']=[
		'info'=>$Url(['info'=>$name]),
		'files'=>$Url(['files'=>$name]),
		'config'=>isset($settings['options']) ? $Url(['config'=>$name]) : false,
	]+$Eleanor->module['links'];
}

if(isset($_GET['info']))
{
	#ToDo! Информация о шаблоне
	Output::SendHeaders('text');
	Output::Gzip('Здесь будет информация о шаблоне');

	/*$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['info']);
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	if(!is_file($f))
		return GoAway();
	$info=(array)include$f;
	DoNavigation($theme,$info);

	$name=isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme;
	$license=isset($info['license']) ? Eleanor::FilterLangValues((array)$info['license']) : false;
	$info=isset($info['info']) ? Eleanor::FilterLangValues((array)$info['info']) : false;

	$title[]=$name;
	$c=Eleanor::$Template->Info($name,$info,$license);
	echo$c;*/
}
elseif(isset($_GET['files']))
{
	#ToDo! работа с файлами шаблона
	Output::SendHeaders('text');
	Output::Gzip('Здесь будет работа с файлами шаблона');

	/*$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['files']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme.'/'))
		return GoAway();
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	$info=is_file($f) ? (array)include$f : [];
	DoNavigation($theme,$info);
	$name=isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme;
	$title[]=sprintf($lang['files_tpl'],$name);
	$Up=new Uploader(Eleanor::$root.'templates'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR);
	$Up->watermark=false;
	$Up->buttons_top=[
		'create_file'=>true,
		'create_folder'=>true,
		'update'=>true,
	];
	$Up->buttons_item=[
		'edit'=>true,
		'file_rename'=>true,
		'file_delete'=>true,
		'folder_rename'=>true,
		'folder_open'=>false,
		'folder_delete'=>true,
	];
	$c=Eleanor::$Template->Files($Up->Show('','tpl',$name),$name);
	echo$c;*/
}
elseif(isset($_GET['config']))
{
	#ToDo! работа с конфигурациями шаблона
	Output::SendHeaders('text');
	Output::Gzip('Здесь будет работа с конфигурациями шаблона');

	/*if($post)
	{
		$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['config']);
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		if(!is_file($f))
			return GoAway();
		$info=include$f;
		if(!isset($info['options']) or !is_array($info['options']))
			return GoAway();
		$C=new Controls;
		$C->throw=false;
		try
		{
			$r=$C->SaveControls($info['options']);
		}
		catch(EE$E)
		{
			return ConfigTemplate($theme,['ERROR'=>$E->getMessage()]);
		}
		$errors=$C->errors;
		if(file_put_contents(Eleanor::$root.'templates/'.$theme.'.config.php','<?php return '.var_export($r,true).';')===false)
			$errors['SAVE']='Saving error';
		if($errors)
			return ConfigTemplate($theme,$errors);
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	else
		ConfigTemplate((string)$_GET['config']);

	#Содержимое функции ConfigTemplate
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',$theme);
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	if(!is_file($f))
		return GoAway();
	$info=(array)include$f;
	if(!isset($info['options']) or !is_array($info['options']))
		return GoAway();
	DoNavigation($theme,$info);
	$values=is_file(Eleanor::$root.'templates/'.$theme.'.config.php') ? (array)include Eleanor::$root.'templates/'.$theme.'.config.php' : [];

	foreach($values as &$v)
		$v=['value'=>$v];
	if($errors)
	{
		if($errors===true)
			$error=[];
		foreach($info['options'] as &$v)
			if(is_array($v))
				$v['post']=true;
	}

	$title[]=sprintf(Eleanor::$Language['te']['config_tpl'],isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme);
	$values=$Eleanor->Controls->DisplayControls($info['options'],$values);
	$c=Eleanor::$Template->Config($info['options'],$values,$errors);
	echo$c;*/
}
elseif(isset($_GET['set'],$_GET['to']))
{
	#ToDo! установка шаблона
	Output::SendHeaders('text');
	Output::Gzip('Здесь будет установка шаблона');

	/*$name=(string)$_GET['to'];
	$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['settpl']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme) or !isset(Eleanor::$services[$name]))
		return GoAway();
	$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
	$info=is_file($f) ? (array)include$f : [];

	if(!empty($info['service']) and !in_array($name,$info['service']))
		return GoAway();
	DoNavigation($theme,$info);
	$nolic=empty($info['license']);
	if(Eleanor::$our_query and ($nolic or $_SERVER['REQUEST_METHOD']=='POST'))
	{
		if($nolic or isset($_POST['submit']))
		{
			Eleanor::$Db->Update(P.'services',['theme'=>$theme],'`name`='.Eleanor::$Db->Escape($name).' LIMIT 1');
			Eleanor::$Cache->Engine->DeleteByTag('');
		}
		elseif(isset($_POST['refuse']))
		{
			foreach(Eleanor::$services as &$v)
				if($v['theme']==$theme)
					return GoAway(empty($_POST['back']) ? true : $_POST['back']);
			Files::Delete(Eleanor::$root.'templates/'.$theme);
		}
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	elseif(!$nolic)
	{
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		$info=is_file($f) ? (array)include$f : [];
		$title[]=$lang['agreement'];
		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$c=Eleanor::$Template->License(isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme,$back,Eleanor::FilterLangValues((array)$info['license']));
		Start();
		echo$c;
	}*/
}
elseif(isset($_GET['delete']))
{
	#ToDo! удаление шаблона
	Output::SendHeaders('text');
	Output::Gzip('Здесь будет удаление шаблона');

	/*$theme=preg_replace('#[^a-z0-9\-_\.]+#i','',(string)$_GET['delete']);
	if(!is_dir(Eleanor::$root.'templates/'.$theme))
		return GoAway();

	foreach(Eleanor::$services as &$v)
		if($v['theme']==$theme)
			return GoAway();
	if(Eleanor::$our_query and isset($_POST['ok']))
	{
		Files::Delete(Eleanor::$root.'templates/'.$theme);
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.config.php');
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.init.php');
		Files::Delete(Eleanor::$root.'templates/'.$theme.'.settings.php');
		GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	else
	{
		$title[]=$lang['delc'];
		if(isset($_GET['noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
		$f=Eleanor::$root.'templates/'.$theme.'.settings.php';
		$info=is_file($f) ? (array)include$f : [];
		DoNavigation($theme,$info);
		$c=Eleanor::$Template->Delete(sprintf($lang['deleting'],isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']).' ('.$theme.')' : $theme),$back);
		Start();
		echo$c;
	}*/
}
else
{
	if(AJAX)
	{
/*		$theme=isset($_POST['theme']) ? (string)$_POST['theme'] : '';
		$newtpl=isset($_POST['newtpl']) ? (string)$_POST['newtpl'] : '';
		if(preg_match('#^[a-z0-9\-_\.]+$#i',$theme)==0 or preg_match('#^[a-z0-9\-_\.]+$#i',$newtpl)==0)
			return Error($lang['incorr_symb']);
		if(file_exists(Eleanor::$root.'templates/'.$newtpl))
			return Error(sprintf($lang['theme_exists'],$newtpl));
		if(!file_exists(Eleanor::$root.'templates/'.$theme))
			return Error(sprintf($lang['no_ish_thm'],$theme));
		$res=Files::Copy(Eleanor::$root.'templates/'.$theme,Eleanor::$root.'templates/'.$newtpl);

		$files=glob(Eleanor::$root.'templates/'.$theme.'.*');
		if($files)
			foreach($files as &$v)
				Files::Copy($v,dirname($v).DIRECTORY_SEPARATOR.preg_replace('#^[^\.]+\.#',$newtpl.'.',basename($v)));

		if($res)
			return Result(true);
		Error();*/
	}

	$title[]=$lang['list'];

	/*$a=glob(Eleanor::$root.'templates/*',GLOB_ONLYDIR);
	$tpls=[];
	foreach($a as &$v)
	{
		$theme=basename($v);
		$info=is_file($v.'.settings.php') ? (array)include$v.'.settings.php' : [];

		$tpl=[
			'image'=>is_file($v.'.png') ? 'templates/'.$theme.'.png' : false,
			'setto'=>[],
			'used'=>[],
		];
		if(!isset($info['service']) or !is_array($info['service']))
			$info['service']=[];
		foreach(Eleanor::$services as $k=>&$vs)
			if($vs['theme'] and $vs['theme']==$theme)
				$tpl['used'][]=$k;
			elseif(in_array($k,$info['service']))
				$tpl['setto'][$k]=$Eleanor->Url->Construct(['settpl'=>$theme,'to'=>$k]);
			else
				continue;

		$tpls[$theme]=$tpl+[
			'creation'=>isset($info['creation']) ? $info['creation'] : false,
			'author'=>isset($info['author']) ? $info['author'] : false,
			'title'=>isset($info['name']) ? Eleanor::FilterLangValues((array)$info['name']) : false,
			'_aopts'=>isset($info['options']) ? $Eleanor->Url->Construct(['config'=>$theme]) : false,
			'_ainfo'=>isset($info['info']) ? $Eleanor->Url->Construct(['info'=>$theme]) : false,
			'_afiles'=>$Eleanor->Url->Construct(['files'=>$theme]),
			'_adel'=>$tpl['used'] ? false : $Eleanor->Url->Construct(['delete'=>$theme]),
		];
	}*/

	$c=Eleanor::$Template->TemplatesList();
	Response($c);
}