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

LoadOptions(['site','rss']);
SetService('rss');

/** Страница с ошибкой
 * @param int $code Код ошибки
 * @param int $r Код редиректа на страницу ошибки */
function ExitPage($code=404,$r=301)
{
	$Url=new Url(false);

	if(Eleanor::$vars['multilang'])
		$Url->prefix=Url::Encode(Eleanor::$langs[ Language::$main ]['uri']).'/';

	$errors=array_keys(GetModules('index')['uri2section'],'errors');
	GoAway($Url([reset($errors),$code]),$r);
}

/** Подготовка текста для публикации его в RSS сообщении
 * @param string $text HTML текст публикации
 * @return string */
function RssText($text)
{
	$text=preg_replace('#(href|src)="(?![a-z]{3,6}://)#i','\1="'.\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR,$text);
	$cutoff=[ ['<!-- ',' -->'], ['<![CDATA[',']]>'], 'object', 'noscript', 'script', 'embed',];

	foreach($cutoff as $tag)
	{
		$currpos=0;
		$isa=is_array($tag);

		while(false!==$currpos=strpos($text,$isa ? $tag[0] : '<'.$tag,$currpos))
		{
			if($isa)
			{
				if(false===$len=strpos($text,$tag[1],$currpos))
					$len=strlen($tag[0]);
				else
					$len-=$currpos;
			}
			else
			{
				if(false===$len=strpos($text,'</'.$tag.'>',$currpos))
					$len=strpos($text,$tag.'>',$currpos);

				$len-=$currpos-strlen($tag)-3;
			}

			$text=substr_replace($text,'',$currpos,$len);
		}
	}

	return strip_tags($text,'<b><i><img><span><p><br><a>');
}

/** Генерация RSS записи <item>...</item> Внимание! Это <item> для пользователей, а не для Яндекс.Новости и т.п.
 * @param array $data Данные, ключи смотрите внутри функции
 * @return string */
function RssItem(array$data)
{
	$data+=[
		'title'=>false,#Заголовок сообщения
		'link'=>false,#URL сообщения
		'description'=>false,#Краткий обзор сообщения
		'author'=>false,#Адрес электронной почты автора сообщения.
		'category'=>[],#Включает сообщение в одну или более категорий. См. ниже.
		'comments'=>false,#URL страницы для комментариев, относящихся к сообщению.
		'enclosure'=>[],#Описывает медиа-объект, прикрепленный к сообщению. См. ниже.
		'guid'=>false,#Строка, уникальным образом идентифицирующая сообщение.
		'pubDate'=>false,#Показывает, когда сообщение было опубликовано. TIMESHTAMP или date('r')
		'source'=>false,#RSS-канал, из которого получено сообщение
		'files'=>[],#Файлы сообщения
	];

	$category=$files='';
	$sitelink=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;
	$data['category']=(array)$data['category'];

	foreach($data['category'] as $ck=>$cv)
		$category.=is_int($ck) ? '<category>'.$cv.'</category>' : '<category domain="'.$cv.'">'.$ck.'</category>';

	foreach($data['enclosure'] as $ev)
		if(isset($ev['url'],$ev['type']))
			$files.='<enclosure url="'.$ev['url'].'"'.(isset($ev['length']) ? ' length="'.$ev['length'].'"' : '')
				.' type="'.$ev['type'].'" />';

	foreach($data['files'] as $file)
		if(isset($file['http'],$file['path']))
			$files.='<enclosure url="'.$sitelink.$file['http'].'" length="'.filesize($file['path']).'" type="'
				.\Eleanor\Classes\Types::MimeTypeByExt($file['path']).'" />';

	return'<item>'
		.($data['title'] ? '<title>'.htmlspecialchars($data['title'],ENT,\Eleanor\CHARSET,false).'</title>' : '')
		.($data['link']
			? '<link>'.htmlspecialchars(preg_match('#^[a-z]{3,6}://#i',$data['link'])>0 ? $data['link']
			: $sitelink.$data['link'],ENT,\Eleanor\CHARSET,false).'</link>' : '')
		.($data['description'] ? '<description><![CDATA['.RssText($data['description']).']]></description>' : '')
		.($data['author'] ? '<author>'.htmlspecialchars($data['author'],ENT,\Eleanor\CHARSET,false).'</author>' : '')
		.($data['comments']
			? '<comments>'.htmlspecialchars(preg_match('#^[a-z]{3,6}://#i',$data['comments'])>0 ? $data['comments']
			: $sitelink.$data['comments'],ENT,\Eleanor\CHARSET,false).'</comments>' : '')
		.($data['guid'] ? '<guid isPermaLink="false">'.htmlspecialchars($data['guid'],ENT,\Eleanor\CHARSET,false).'</guid>' : '')
		.($data['pubDate'] ? '<pubDate>'.(is_int($data['pubDate']) ? date('r',$data['pubDate']) : $data['pubDate']).'</pubDate>' : '')
		.($data['source'] ? '<source>'.htmlspecialchars($data['source'],ENT,\Eleanor\CHARSET,false).'</source>' : '')
		.$category.$files.(isset($data['extra']) ? $data['extra'] : '')
		.'</item>';
}

/** "Чердак" RSS ленты
 * @param array $head Заголовки и служебные параметры шапки
 * @return string */
function RssHead(array$head=[])
{global$Eleanor,$title;

	if(!$title)
		$t=Eleanor::$vars['site_name'];
	elseif(is_string($title))
		$t=$title;
	else
		$t=(is_array($title) ? join(Eleanor::$vars['site_defis'],$title) : $title)
			.(Eleanor::$vars['site_name'] ? Eleanor::$vars['site_defis'].Eleanor::$vars['site_name'] : '');

	$t=htmlspecialchars($t,ENT,\Eleanor\CHARSET,false);
	$sitelink=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;

	$head+=[
		#Обязательное
		'title'=>$t,#Название канала
		'description'=>htmlspecialchars(isset($Eleanor->module,$Eleanor->module['description'])
			? $Eleanor->module['description']
			: Eleanor::$vars['site_description'],ENT,\Eleanor\CHARSET,false),#Описание канала
		'link'=>$sitelink,#URL веб-сайта, связанного с каналом

		#Необязательное
		'language'=>Eleanor::$langs[ Language::$main ]['d'],#Язык, на котором написан канал
		'copyright'=>false,#Информация об авторском праве
		'managingEditor'=>false,#Адрес электронной почты ответственного за редакторский текст
		'webMaster'=>false,#Адрес электронной почты ответственного за технические аспекты
		'pubDate'=>false,#Дата публикации текста в канале. TIMESHTAMP или date('r')
		'lastBuildDate'=>false,#Время Последнего изменения содержимого канала. TIMESHTAMP или date('r')
		'category'=>false,#Указывает одну и более категорию, к которой относится канал.
		'ttl'=>1,#Время жизни; количество минут, на которые канал может кешироваться перед обновлением с ресурса.
		'image'=>[],#Изображение GIF, JPEG или PNG, которое может отображаться с каналом. Смотри ниже.
	];

	if(Eleanor::$vars['rss_image'])
		$head['image']+=[
			#Обязательное
			'url'=>strpos(Eleanor::$vars['rss_image'],'://')
					? $sitelink.Eleanor::$vars['rss_image'] : Eleanor::$vars['rss_image'],
			'title'=>$t,
			'link'=>$sitelink,

			#Необязательное
			'width'=>false,
			'height'=>false,
			'description'=>false,
		];

	$image=$head['image']
		? '<image><url>'.$head['image']['url'].'</url><title><![CDATA['.$head['image']['title'].']]></title><link>'.$head['image']['link'].'</link>'
		.($head['image']['width'] ? '<width>'.(int)$head['image']['width'].'</width>' : '')
		.($head['image']['height'] ? '<height>'.(int)$head['image']['height'].'</height>' : '')
		.($head['image']['description']
			? '<description>'.htmlspecialchars($head['image']['description'],ENT,\Eleanor\CHARSET,false).'</description>'
			: '')
		.'</image>'
		: '';

	return'<?xml version="1.0" encoding="'.\Eleanor\CHARSET
		.'"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title><![CDATA['.$head['title']
		.']]></title><description>'.$head['description'].'</description><link>'.$head['link'].'</link><language>'
		.$head['language'].'</language><generator>Eleanor RSS Generator</generator><atom:link href="'.\Eleanor\PROTOCOL
		.getenv('HTTP_HOST').htmlspecialchars(getenv('REQUEST_URI')).'" rel="self" type="application/rss+xml" />'
		.($head['copyright'] ? '<copyright>'.htmlspecialchars($head['copyright'],ENT,\Eleanor\CHARSET,false).'</copyright>' : '')
		.($head['managingEditor'] ? '<managingEditor>'.htmlspecialchars($head['managingEditor'],ENT,\Eleanor\CHARSET,false).'</managingEditor>' : '')
		.($head['webMaster'] ? '<webMaster>'.htmlspecialchars($head['webMaster'],ENT,\Eleanor\CHARSET,false).'</webMaster>' : '')
		.($head['pubDate'] ? '<pubDate>'.(is_int($head['pubDate']) ? date('r',$head['pubDate']) : $head['pubDate']).'</pubDate>' : '')
		.($head['lastBuildDate'] ? '<lastBuildDate>'.(is_int($head['lastBuildDate']) ? date('r',$head['lastBuildDate']) : $head['lastBuildDate']).'</lastBuildDate>' : '')
		.($head['category'] ? '<category>'.htmlspecialchars($head['category'],ENT,\Eleanor\CHARSET,false).'</category>' : '')
		.($head['ttl'] ? '<ttl>'.(int)$head['ttl'].'</ttl>' : '')
		.$image
		.(isset($head['extra']) ? $head['extra'] : '');
}

/** "Подвал" RSS ленты */
function RssFoot()
{
	return'</channel></rss>';
}

/** Упрощенный вариант вывода RSS, включает в себя вызов Head() и Foot(). Нужно передать только массив сообщений
 * @param array $rss Сообщения, ключи внутренних массивов смотреть в функции Rss()
 * @param array $head Заголовки и служебные параметры шапки */
function Rss(array$rss,array$head=[])
{
	$out=RssHead($head);

	foreach($rss as$v)
		$out.=RssItem($v);

	$out.=RssFoot();

	OutPut::SendHeaders('xml');
	Output::Gzip($out);
}

if(Eleanor::$vars['multilang'])
{
	if(isset($_REQUEST['language']) and is_string($_REQUEST['language']) and $_REQUEST['language']!=Language::$main)
	{
		Eleanor::$Language->Change($_REQUEST['language']);
		$Eleanor->Url(false)->prefix=Url::$base=Url::Encode(Eleanor::$langs[ Language::$main ]['uri']).'/';
	}

	foreach(Eleanor::$lvars as $lk=>$lv)
		Eleanor::$vars[$lk]=FilterLangValues($lv);
}
else
{
	Eleanor::$lvars=[];
	$Eleanor->Url(false);
}

if(Eleanor::$vars['site_closed'] and (!isset(Eleanor::$Permissions) or !Eleanor::$Permissions->ClosedSiteAccess()))
{
	/** @var Logins\Admin $class */
	$class='\CMS\Logins\\'.Eleanor::$services['admin']['login'];

	if(!$class::IsUser())
		return ExitPage(403);
}

unset(Eleanor::$vars['site_close_mes']);

if(Eleanor::$ban or isset(Eleanor::$Permissions) and Eleanor::$Permissions->IsBanned())
	return ExitPage(403);
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
	{
		if(Eleanor::$vars['prefix_free_module']!=$id)
			$Eleanor->Url->prefix.=Url::Encode($uri).'/';

		\Eleanor\AwareInclude($path);
	}
	else
		return ExitPage();
}
else
	Rss([]);

SetSession();