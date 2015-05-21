<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Помощник вывода Html
*/
namespace CMS\Templates;
use CMS, CMS\Eleanor, CMS\Url, CMS\Language, CMS\Template, CMS\OwnBB;
use Eleanor\Classes\Strings;

defined('CMS\STARTED')||die;

/** Генератор секции head
 * @param bool $ms Включение мультисайта
 * @param bool $search Включение Google поиска в заголовки
 * @return string*/
function GetHead($ms=true,$search=true)
{global$Eleanor,$title,$scripts,$head;/** @var Eleanor $Eleanor */
	$hms=[];

	if($ms and isset(Eleanor::$Login))
	{
		$ms=include CMS\DIR.'config_multisite.php';

		if($ms)
		{
			$hms=[
				'is_user'=>Eleanor::$Login->IsUser(),
				'service'=>Eleanor::$service,
			];

			foreach($ms as $name=>$data)
				$hms['sites'][$name]=[
					'address'=>$data['address'],
					'title'=>CMS\FilterLangValues($data['title']),
					'secret'=>(bool)$data['secret'],
				];

			Eleanor::$Template->multisite=true;
		}
		else
			Eleanor::$Template->multisite=false;
	}

	if(!$title)
		$t=$og_t=Eleanor::$vars['site_name'];
	elseif(is_string($title))
		$t=$og_t=$title;
	else
	{
		$isa=is_array($title);
		$og_t=$isa ? reset($title) : (string)$title;
		$t=($isa ? join(Eleanor::$vars['site_defis'], $title) : $title)
			.(Eleanor::$vars['site_name'] ? Eleanor::$vars['site_defis'].Eleanor::$vars['site_name'] : '');
	}

	$t=htmlspecialchars($t,CMS\ENT,\Eleanor\CHARSET,false);

	if(isset($Eleanor->module['description']))
		$descr=$Eleanor->module['description'];
	elseif(isset($Eleanor->module['general']))
		$descr=Eleanor::$vars['site_description'];
	else
		$descr=false;

	$heads='<meta charset="'.\Eleanor\CHARSET.'" /><base href="'
		.\Eleanor\SITEDIR.'" /><title>'.$t.'</title><meta name="generator" content="Eleanor CMS '.Eleanor::VERSION
		.'" /><meta property="og:title" content="'.$og_t.'" /><meta property="og:site_name" content="'.Eleanor::$vars['site_name'].'" />';

	if($descr)
	{
		$descr=strip_tags($descr);
		$descr=Strings::CutStr($descr,255);
		$descr=htmlspecialchars($descr, CMS\ENT, \Eleanor\CHARSET, false);

		$heads.=<<<HTML
<meta name="description" content="{$descr}" />
<meta property="og:description" content="{$descr}" />
HTML;
	}

	#Подключение поиска
	if($search)
	{
		$xml=Eleanor::$services['xml']['file'].'?';

		if(Eleanor::$vars['multilang'])
			$xml.='lang='.urlencode(Eleanor::$langs[ Language::$main ]['uri']).'&amp;';

		$heads.='<link rel="search" type="application/opensearchdescription+xml" title="'
			.Eleanor::$vars['site_name'].'" href="'.$xml.'direct=google-search" />';
	}

	#Если модулем задан оригинальный URL страницы, сравним его с полученным
	if(isset($Eleanor->module['general']))
		$orig=\Eleanor\SITEDIR.Url::$base;
	elseif(isset($Eleanor->module['origurl']))
	{
		$orig=parse_url($Eleanor->module['origurl']);
		$orig=\Eleanor\SITEDIR.$orig['path'].(isset($orig['query']) ? '?'.$orig['query'] : '');
	}
	else
		goto SkipCanonical;

	$orig=Url::Decode($orig);
	$orig=str_replace('&amp;','&',$orig);

	$request=Url::Decode($_SERVER['REQUEST_URI']);

	if(!Eleanor::$ourquery or strcasecmp($orig,$request)!=0)
		$heads.='<link rel="canonical" href="'.\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.$orig.'" />';

	if(!isset($head['og:uri']))
		$heads.='<meta property="og:uri" content="'.\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.$orig.'" />';

	SkipCanonical:

	array_unshift($scripts,
		Template::$http['static'].'js/core.js',
		is_file(Template::$path['static'].'js/'.Language::$main.'.js') ? Template::$http['static'].'js/'.Language::$main.'.js' : false,
		Template::$http['static'].'js/lang-'.Language::$main.'.js');
	$scripts=array_unique($scripts);

	$ourjs=$js=[];

	foreach($scripts as $v)
		if($v and strpos($v,'//')===false and !Eleanor::$debug)
		{
			$js[]=$v;
			$ourjs[]=\CMS\DIR.'../'.$v;
		}
		else
			$heads.=<<<HTML
<script src="{$v}"></script>
HTML;

	if($ourjs)
		$heads.='<script src="'.\CMS\Minify::Script($ourjs).'"></script>';

	return$heads.\Eleanor\Classes\Html::JSON([
		'cookie'=>Eleanor::$cookie,
		'dir'=>\Eleanor\SITEDIR,
		'language'=>Language::$main,
		'head'=>$head&&is_array($head) ? array_keys($head) : [],
		'scripts'=>$js,
	]+$hms,true,false,'CORE.').join('',$head);

	#ToDo!
	/*<link rel="alternate" href="http://site.ru/eng/" hreflang="en" />
<link rel="alternate" href="http://site.ru/ukr/" hreflang="uk" />*/
}

/** Комплексная функция обработки текста из базы данных. Выполняет OwnBB::Parse() и сокрытие внешних ссылок.
 * Рекомендуется вызывать из шаблонов
 * @param $s string Текст
 * @return string */
function Content($s)
{
	switch(Eleanor::$vars['antidirectlink'])
	{
		case'go':
			$s=preg_replace('#<a href="([a-z]{3,7}://)#','<a href="go.php?\1',$s);
		break;
		case'nofollow':
			$s=preg_replace('#<a href="([^"]+)" rel="[^"]+"#','<a href="\1" rel="nofollow"',$s);
			$s=preg_replace('#<a href="([^"]+)" (?!rel)#','<a href="\1" rel="nofollow" ',$s);
	}

	return OwnBB::Parse($s);
}

/** Создание RSS ссылки
 * @param string $href Адрес
 * @param string $title Название */
function RssLink($href,$title)
{
	$GLOBALS['head'][]='<link rel="alternate" type="application/rss+xml" href="'.$href.'" title="'.$title.'" />';
}

/** Поддержка соцсетей
 * @param string|array $name Имя параметра или связка имя=>значение
 * @param string $value Значение*/
function OG($name,$value='')
{
	$meta='';

	if(is_array($name))
	{
		foreach($name as $k=>$v)
			if($v)
				$meta.='<meta property="og:'.$k.'" content="'.htmlspecialchars($v,CMS\ENT,\Eleanor\CHARSET).'" />';
	}
	elseif($value)
		$meta.='<meta property="og:'.$name.'" content="'.htmlspecialchars($value,CMS\ENT,\Eleanor\CHARSET).'" />';

	if($meta)
		$GLOBALS['head'][]=$meta;
}

/** Получение информации для о генерации страницы
 * @param string $format Формат
 * @return string */
function GetPageInfo($format)
{
	if(Eleanor::$vars['show_status']==2 or (Eleanor::$vars['show_status']==1 and Eleanor::$Permissions->IsAdmin()))
		return sprintf(
			$format,
			round(microtime(true)-CMS\STARTED,3),
			//Число запросов. +1 это запрос SetSession() в /index.php
			Eleanor::$Db===Eleanor::$UsersDb ? Eleanor::$Db->queries+1 : Eleanor::$Db->queries+Eleanor::$UsersDb->queries+1,
			round(memory_get_usage()/1048576,3),
			round(memory_get_peak_usage()/1048576,3)
		);

	return'';
}

if(Eleanor::$debug)
{
	/** Получение дебаг-списка */
	function GetDebugInfo()
	{
		$debug='<ul class="debug">';

		foreach(CMS\Template::$debug as $v)
		{
			if(!isset($v['f']))
				$v['f']='?';
			elseif(isset($v['l']))
				$v['f'].='['.$v['l'].']';

			$debug.="<li><b>{$v['t']}</b> - <span title=\"{$v['f']}\">{$v['e']}</span></li>";
		}

		return'</ul>';
	}
}