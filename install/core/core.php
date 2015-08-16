<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files, Eleanor\Framework;

defined('CMS\STARTED')||die;

define('CMS\BUILD',7);
define('CMS\INSTALL',dirname(__DIR__).DIRECTORY_SEPARATOR);
define('CMS\ROOT',dirname(INSTALL).DIRECTORY_SEPARATOR);

require __DIR__.'/../../cms/core.php';
require __DIR__.'/../../cms/functions.php';
Eleanor::$Template=new Template(__DIR__.'/../template','Install');
Eleanor::$Template->default['images']='template/images/';

/** Инциализация
 * @return array Конфигурация */
function Init()
{
	$config=include INSTALL.'core/config.php';
	$config=new ExceptionArray($config);

	#Языки, дебаг, куки
	Eleanor::$langs=$config['langs'];
	Eleanor::$debug=$config['debug'];
	Eleanor::$cookie=$config['cookie-prefix'];

	#Пути
	Template::$http+=[
		'static'=>'../'.$config['static'],
		'templates'=>'../'.$config['templates'],
		'uploads'=>'../'.$config['uploads'],
		'3rd'=>'../'.$config['3rd'],
	];
	Template::$path+=[
		'static'=>$config['static-path'],
		'templates'=>$config['templates-path'],
		'uploads'=>$config['uploads-path'],
		'3rd'=>$config['3rd-path'],
	];
	Minify::$http='../cache/';

	return$config;
}

/** Установка языка
 * @param string $language Название языка */
function SetLanguage($language)
{
	Language::$main=$language;
	Eleanor::$Language=new Language(__DIR__.'/../translation/*.php','main');
	Eleanor::$Language->source=__DIR__.'/../translation/';
}

/** Проверка среды для возможности установки
 * @return array */
function CheckEnv()
{
	$result=[];
	$lang=Eleanor::$Language['main'];

	if(file_exists(INSTALL.'install.lock'))
		return[$lang['install.lock']];

	if(version_compare(PHP_VERSION,'5.6','<'))
		$result[]=sprintf($lang['low_php'],PHP_VERSION);

	if(!function_exists('imagefttext'))
		$result[]=$lang['GD'];

	if(!function_exists('mb_detect_encoding'))
		$result[]=$lang['MB'];

	if(!function_exists('mysqli_connect'))
		$result[]=$lang['no_db_driver'];

	#mbstring.func_overload
	if(ini_get('mbstring.func_overload')>0)
		$result[]=$lang['mbstring.func_overload'];

	$towrite=$toexist=[];

	#Каталог с логами
	$full=realpath(Framework::$logspath);
	$short=preg_replace('#^'.preg_quote(ROOT,'#').'#','',$full);

	if(!is_dir($full) and !Files::MkDir($full))
		$toexist[]='<span style="color:red">'.$short.'</span>';
	elseif(!is_writeable($full))
		$towrite[]='<span style="color:red">'.$short.'</span>';

	#.htaccess
	$full=DIR.'../';

	if(!is_file(DIR.'../.htaccess') and !is_writable($full))
		$toexist[]='<span style="color:red">.htaccess</span>';
	elseif(!is_writeable(DIR.'../.htaccess'))
		$towrite[]='<span style="color:red">.htaccess</span>';

	#robots.txt
	if(!is_file(DIR.'../robots.txt') and !is_writable($full))
		$toexist[]='<span style="color:red">robots.txt</span>';
	elseif(!is_writeable(DIR.'../robots.txt'))
		$towrite[]='<span style="color:red">robots.txt</span>';

	#sitemap.xml
	if(!is_file(DIR.'../sitemap.xml') and !is_writable($full))
		$toexist[]='<span style="color:red">sitemap.xml</span>';
	elseif(!is_writeable(DIR.'../sitemap.xml'))
		$towrite[]='<span style="color:red">sitemap.xml</span>';

	#Uploads
	if(!is_dir(Template::$path['uploads']))
		$toexist[]='<span style="color:red">'.Template::$http['uploads'].'</span>';
	elseif(!is_writeable(Template::$path['uploads']))
		$towrite[]='<span style="color:red">'.Template::$http['uploads'].'</span>';

	#Конфиг
	$full=DIR.'config.php';

	if(!is_file($full) and !is_writable(CMS))
		$toexist[]='<span style="color:red">cms/config.php</span>';
	elseif(!is_writeable($full))
		$towrite[]='<span style="color:red">cms/config.php</span>';


	if($toexist)
		$result[]=$lang['must_ex'].join('<br />',$toexist);

	if($towrite)
		$result[]=$lang['must_writeable'].join('<br />',$towrite);

	return$result;
}

/** Инициализация базы данных
 * @param array $config Конфигурация */
function InitDb($config)
{
	$config=new ExceptionArray($config);

	date_default_timezone_set($config['timezone']);

	if(defined('CMS\P'))
		$this->p=$config['prefix'];
	else
		define('CMS\P',$config['prefix']);

	Eleanor::$Db=new MySQL([
		'host'=>$config['db-host'],
		'user'=>$config['db-user'],
		'pass'=>$config['db-pass'],
		'db'=>$config['db'],
		'charset'=>$config['db-charset'],
	]);

	Eleanor::$Db->SyncTimeZone();

	Eleanor::$UsersDb=&Eleanor::$Db;

	if(defined('CMS\USERS_TABLE'))
		Eleanor::$UsersDb->users_table=P.'users';
	else
		define('CMS\USERS_TABLE',P.'users');
}

/** Проверка версии MySQL (не ниже 5.1) */
function CheckMySQLVersion()
{
	return version_compare(str_replace('-nt-max','',Eleanor::$Db->Driver->server_info),'5.1','>=');
}