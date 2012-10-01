<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
define('CMS',true);
require dirname(__file__).'/core/core.php';
$Eleanor=Eleanor::getInstance();
Eleanor::$service='rss';#ID �������
Eleanor::LoadOptions(array('site','rss','users-on-site'));
Eleanor::LoadService();

$Eleanor->error=$Eleanor->started=false;

if(Eleanor::$vars['site_closed'] and !Eleanor::$Permissions->ShowClosedSite() and !Eleanor::LoadLogin(Eleanor::$services['admin']['login'])->IsUser())
	return ExitPage();

unset(Eleanor::$vars['site_close_mes']);

$title=array();

if(Eleanor::$vars['multilang'])
{
	$isu=Eleanor::$Login->IsUser();
	if(!$isu and $l=Eleanor::GetCookie(Eleanor::$service.'_lang') and isset(Eleanor::$langs[$l]) and $l!=LANGUAGE)
	{
		Language::$main=$l;
		Eleanor::$Language->Change($l);
	}
	if(isset($_GET['lang']))
	{
		$lang=$_GET['lang'];
		foreach(Eleanor::$langs as $k=>&$v)
			if($v['uri']==$lang and Language::$main!=$lang)
			{
				Language::$main=$k;
				break;
			}
	}

	foreach(Eleanor::$lvars as $k=>&$v)
		Eleanor::$vars[$k]=Eleanor::FilterLangValues($v);
}
else
	Eleanor::$lvars=array();
if(Eleanor::$Permissions->IsBanned())
	throw new EE(Eleanor::$Login->GetUserValue('ban_explain'),EE::BAN);

$m=isset($_REQUEST['module']) ? (string)$_REQUEST['module'] : false;
if($m)
{
	$Eleanor->modules=Modules::GetCache();
	if(!isset($Eleanor->modules['ids'][$m]))
		return ExitPage();
	$R=Eleanor::$Db->Query('SELECT `id`,`services`,`sections`,`title_l`,`path`,`multiservice`,`file`,`files`,`image`,`user_groups` FROM `'.P.'modules` WHERE `id`='.(int)$Eleanor->modules['ids'][$m].' AND `active`=1 LIMIT 1');
	if(!$a=$R->fetch_assoc())
		return ExitPage(404);
	if($a['user_groups'])
	{
		$groups=explode(',,',trim($a['user_groups'],','));
		$user_groups=Eleanor::GetUserGroups();
		if(count(array_intersect($groups,$user_groups))==0)
			return ExitPage(403);
	}
	if(!$a['multiservice'])
	{
		$files=unserialize($a['files']);
		$a['file']=isset($files[Eleanor::$service]) ? $files[Eleanor::$service] : false;
	}
	if(!$a['file'])
		return ExitPage();
	$a['sections']=unserialize($a['sections']);
	foreach($a['sections'] as $k=>&$v)
		if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
			$v=reset($v[Language::$main]);
		else
			$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
	$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues(unserialize($a['title_l'])) : '';
	$Eleanor->module=array(
		'name'=>$m,
		'section'=>isset($Eleanor->modules['sections'][$m]) ? $Eleanor->modules['sections'][$m] : '',
		'title'=>$a['title_l'],
		'image'=>$a['image'],
		'path'=>Eleanor::FormatPath($a['path']).DIRECTORY_SEPARATOR,
		'id'=>$a['id'],
		'sections'=>$a['sections'],
	);
	Modules::Load($Eleanor->module['path'],$a['multiservice'],$a['file'] ? $a['file'] : 'index.php');
}
elseif(isset($_POST['direct']) and is_file($f=Eleanor::$root.'addons/direct/'.preg_replace('#[^a-z0-9]+#i','',(string)$_POST['direct']).'.php'))
	include$f;
else
	Result(array());

#���������������� �������.
function GoAway($info=false,$code=301,$hash='')
{global$Eleanor;
	if(!$ref=getenv('HTTP_REFERER') or $ref==PROTOCOL.Eleanor::$punycode.$_SERVER['REQUEST_URI'] or $info)
	{
		if(is_bool($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.($info ? $Eleanor->Url->Prefix() : '');
		elseif(is_array($info))
			$info=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$Eleanor->Url->Construct($info);
		elseif($d=parse_url($info) and isset($d['host'],$d['scheme']) and preg_match('#^[a-z0-9\-\.]+$#',$d['host'])==0)
			$info=preg_replace('#^'.$d['scheme'].'://'.preg_quote($d['host']).'#',$d['scheme'].'://'.Punycode::Domain($d['host']),$info);
		$ref=$info;
	}
	if($hash)
		$ref=preg_replace('%#.*$%','',$ref).'#'.$hash;
	header('Cache-Control: no-store');
	header('Location: '.rtrim(html_entity_decode($ref),'&?'),true,$code);
	die;
}

function Start($ch=array())
{global$Eleanor,$title;
	if($Eleanor->started)
		return;
	Eleanor::AddSession();
	Eleanor::$content_type='application/xml';
	Eleanor::HookOutPut('Finish');
	if(!$title)
		$t=Eleanor::$vars['site_name'];
	elseif(is_string($title))
		$t=$title;
	else
		$t=(is_array($title) ? implode(Eleanor::$vars['site_defis'],$title) : $title).(Eleanor::$vars['site_name'] ? Eleanor::$vars['site_defis'].Eleanor::$vars['site_name'] : '');
	$t=htmlspecialchars($t,ELENT,CHARSET,false);
	$sl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
	#http://beshenov.ru/rss2.html
	$ch+=array(
		#������������
		'title'=>$t,#�������� ������
		'description'=>htmlspecialchars(isset($Eleanor->module,$Eleanor->module['description']) ? $Eleanor->module['description'] : Eleanor::$vars['site_description'],ELENT,CHARSET,false),#�������� ������
		'link'=>$sl,#URL ���-�����, ���������� � �������.

		#��������������
		'language'=>substr(Language::$main,0,2),#����, �� ������� ������� �����
		'copyright'=>false,#���������� �� ��������� �����
		'managingEditor'=>false,#����� ����������� ����� �������������� �� ������������ �����
		'webMaster'=>false,#����� ����������� ����� �������������� �� ����������� �������
		'pubDate'=>false,#���� ���������� ������ � ������. TIMESHTAMP ��� date('r')
		'lastBuildDate'=>false,#����� ���������� ��������� ����������� ������. TIMESHTAMP ��� date('r')
		'category'=>false,#��������� ���� � ����� ���������, � ������� ��������� �����.
		'ttl'=>round(Eleanor::$caching/60),#����� �����; ���������� �����, �� ������� ����� ����� ������������ ����� ����������� � �������.
		'image'=>array(),#����������� GIF, JPEG ��� PNG, ������� ����� ������������ � �������. ������ ����.
	);
	if(Eleanor::$vars['rss_image'])
		$ch['image']+=array(
			#������������
			'url'=>$sl.Eleanor::$vars['rss_image'],
			'title'=>$t,
			'link'=>$sl,
			#��������������
			'width'=>false,
			'height'=>false,
			'description'=>false,
		);
	$image=$ch['image']
		? '<image><url>'.$ch['image']['url'].'</url><title>'.$ch['image']['title'].'</title><link>'.$ch['image']['link'].'</link>'
			.($ch['image']['width'] ? '<width>'.(int)$ch['image']['width'].'</width>' : '')
			.($ch['image']['height'] ? '<height>'.(int)$ch['image']['height'].'</height>' : '')
			.($ch['image']['description'] ? '<description>'.htmlspecialchars($ch['image']['description'],ELENT,CHARSET,false).'</description>' : '')
			.'</image>'
		: '';
	echo'<?xml version="1.0" encoding="'.DISPLAY_CHARSET.'"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>'.$ch['title'].'</title><description>'.$ch['description'].'</description><link>'.$ch['link'].'</link><language>'.$ch['language'].'</language><generator>Eleanor RSS Generator</generator><atom:link href="'.PROTOCOL.$_SERVER['HTTP_HOST'].htmlspecialchars(getenv('REQUEST_URI')).'" rel="self" type="application/rss+xml" />'
	.($ch['copyright'] ? '<copyright>'.htmlspecialchars($ch['copyright'],ELENT,CHARSET,false).'</copyright>' : '')
	.($ch['managingEditor'] ? '<managingEditor>'.htmlspecialchars($ch['managingEditor'],ELENT,CHARSET,false).'</managingEditor>' : '')
	.($ch['webMaster'] ? '<webMaster>'.htmlspecialchars($ch['webMaster'],ELENT,CHARSET,false).'</webMaster>' : '')
	.($ch['pubDate'] ? '<pubDate>'.(is_int($ch['pubDate']) ? date('r',$ch['pubDate']) : $ch['pubDate']).'</pubDate>' : '')
	.($ch['lastBuildDate'] ? '<lastBuildDate>'.(is_int($ch['lastBuildDate']) ? date('r',$ch['lastBuildDate']) : $ch['lastBuildDate']).'</lastBuildDate>' : '')
	.($ch['category'] ? '<category>'.htmlspecialchars($ch['category'],ELENT,CHARSET,false).'</category>' : '')
	.($ch['ttl'] ? '<ttl>'.(int)$ch['ttl'].'</ttl>' : '')
	.$image
	.(isset($ch['addon']) ? $ch['addon'] : '');
	$Eleanor->started=true;
}


function Finish($s)
{global$Eleanor;
	return $Eleanor->started && !$Eleanor->error ? $s.'</channel></rss>' : $s;
}

function Error($e=false,$extra=array())
{global$Eleanor;
	$csh=!headers_sent();
	$le=Eleanor::$Language['errors'];
	if(empty($extra['ban']))
	{
		if(isset($extra['date']))
			$e=$le['banlock']($extra['date'],$e);
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/error.html',
			array(
				'title'=>$le['happened'],
				'error'=>$e,
				'extra'=>$extra,
			)
		);
		if($csh)
			header('Retry-After: 7200');
	}
	else
		$e=Eleanor::LoadFileTemplate(
			Eleanor::$root.'templates/ban.html',
			array(
				'title'=>$le['you_are_banned'],
				'message'=>$e ? OwnBB::Parse($e) : Eleanor::$vars['blocked_message'],
				'extra'=>$extra,
			)
		);

	if(isset($Eleanor,$Eleanor->started) and $Eleanor->started)#������ ����� �������� � � ������ �������� ������� $Eleanor
	{
		$Eleanor->error=true;
		if($csh)
			header('Content-Type: text/html; charset='.Eleanor::$charset,true,isset($extra['httpcode']) ? (int)$extra['httpcode'] : 503);
		while(ob_get_contents()!==false)
			ob_end_clean();
		ob_start();ob_start();#�������� ���� PHP... ���������� ������� Parse error � index.php ���� � Core::FinishOutPut ����� �������� ������ ��������
		echo$e;
	}
	else
	{
		Eleanor::$content_type='text/html';
		Eleanor::HookOutPut(false,isset($extra['httpcode']) ? (int)$extra['httpcode'] : 503,$e);
		die;
	}
}

function Result($rss,$ch=array())
{
	Start($ch);
	foreach($rss as &$v)
		echo Rss($v);
}

function RssText($s)
{
	$s=preg_replace('#(href|src)="(?![a-z]{3,6}://)#i','\1="'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,$s);
	$a=array(
		array(
			'<!-- ',
			' -->'
		),
		array(
			'<![CDATA[',
			']]>'
		),
		'object',
		'noscript',
		'script',
		'embed',
	);
	foreach($a as &$v)
	{
		$cp=0;
		$t=is_array($v);
		while(false!==$cp=strpos($s,$t ? $v[0] : '<'.$v,$cp))
			{
				if($t)
				{
					if(false===$l=strpos($s,$v[1],$cp))
						$l=strlen($v[0]);
					else
						$l-=$cp;
				}
				else
				{
					if(false===$l=strpos($s,'</'.$v.'>',$cp))
						$l=strpos($s,$v.'>',$cp);
					$l-=$cp-strlen($v)-3;
				}
				$s=substr_replace($s,'',$cp,$l);
			}
		}
	return$s;
}

#��������! ��� RSS ��� �������������, � �� ��� ������.������� � �.�.
function Rss($v)
{
	$v+=array(
		'title'=>false,#��������� ���������
		'link'=>false,#URL ���������
		'description'=>false,#������� ����� ���������
		'author'=>false,#����� ����������� ����� ������ ���������.
		'category'=>array(),#�������� ��������� � ���� ��� ����� ���������. ��. ����.
		'comments'=>false,#URL �������� ��� ������������, ����������� � ���������.
		'enclosure'=>array(),#��������� �����-������, ������������� � ���������. ��. ����.
		'guid'=>false,#������, ���������� ������� ���������������� ���������.
		'pubDate'=>false,#����������, ����� ��������� ���� ������������. TIMESHTAMP ��� date('r')
		'source'=>false,#RSS-�����, �� �������� �������� ���������.
	);
	$cats=$en='';
	$sl=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
	$v['category']=(array)$v['category'];
	foreach($v['category'] as $ck=>&$cv)
		$cats.=is_int($ck) ? '<category>'.$cv.'</category>' : '<category domain="'.$cv.'">'.$ck.'</category>';
	foreach($v['enclosure'] as &$ev)
		if(isset($ev['url'],$ev['type']))
			$en.='<enclosure url="'.$ev['url'].'"'.(isset($ev['length']) ? ' length="'.$ev['length'].'"' : '').' type="'.$ev['type'].'" />';
	if(isset($v['files']) and is_array($v['files']))
		foreach($v['files'] as &$f)
			if(is_file($fp=Eleanor::FormatPath($f)))
				$en.='<enclosure url="'.$sl.$f.'" length="'.filesize($fp).'" type="'.Types::MimeTypeByExt($f).'" />';
	return'<item>'
	.($v['title'] ? '<title>'.htmlspecialchars($v['title'],ELENT,CHARSET,false).'</title>' : '')
	.($v['link'] ? '<link>'.htmlspecialchars(preg_match('#^[a-z]{3,6}://#i',$v['link'])>0 ? $v['link'] : $sl.$v['link'],ELENT,CHARSET,false).'</link>' : '')
	.($v['description'] ? '<description><![CDATA['.RssText($v['description']).']]></description>' : '')
	.($v['author'] ? '<author>'.htmlspecialchars($v['author'],ELENT,CHARSET,false).'</author>' : '')
	.($v['comments'] ? '<comments>'.htmlspecialchars(preg_match('#^[a-z]{3,6}://#i',$v['comments'])>0 ? $v['comments'] : $sl.$v['comments'],ELENT,CHARSET,false).'</comments>' : '')
	.($v['guid'] ? '<guid isPermaLink="false">'.htmlspecialchars($v['guid'],ELENT,CHARSET,false).'</guid>' : '')
	.($v['pubDate'] ? '<pubDate>'.(is_int($v['pubDate']) ? date('r',$v['pubDate']) : $v['pubDate']).'</pubDate>' : '')
	.($v['source'] ? '<source>'.htmlspecialchars($v['source'],ELENT,CHARSET,false).'</source>' : '')
	.$cats.$en.(isset($v['addon']) ? $v['addon'] : '')
	.'</item>';
}

function ExitPage($code=403,$r=301)
{global$Eleanor;
	BeAs('user');
	$Eleanor->Url->file=Eleanor::$services['user']['file'];
	GoAway(PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>'errors','code'=>$code),false,true,Eleanor::$vars['furl']),$r);
}

#������� "���� ���", ������ ������ ������. ��������� :)
function BeAs($n)
{global$Eleanor;
	if(Eleanor::$service==$n or !isset(Eleanor::$services[$n]))
		return;

	Eleanor::$filename=Eleanor::$services[$n]['file'];

	Eleanor::InitTemplate(Eleanor::$services[$n]['theme']);
	if(Eleanor::$services[$n]['login']!=Eleanor::$services[Eleanor::$service]['login'])
		Eleanor::ApplyLogin(Eleanor::$services[$n]['login']);
	Eleanor::$service=$n;

	Eleanor::$Language->queue['main'][]='langs/'.$n.'-*.php';
	if(Eleanor::$vars['multilang'])
	{
		if(!Eleanor::$Login->IsUser() and $l=Eleanor::GetCookie(Eleanor::$service.'_lang') and isset(Eleanor::$langs[$l]) and $l!=LANGUAGE)
		{
			Language::$main=$l;
			Eleanor::$Language->Change($l);
		}
		foreach(Eleanor::$lvars as $k=>&$v)
			Eleanor::$vars[$k]=Eleanor::FilterLangValues($v);
	}
	else
		Eleanor::$lvars=array();

	if($n=='user')
	{
		$Eleanor->Url->furl=Eleanor::$vars['furl'];
		$Eleanor->Url->delimiter=Eleanor::$vars['url_static_delimiter'];
		$Eleanor->Url->defis=Eleanor::$vars['url_static_defis'];
		$Eleanor->Url->ending=Eleanor::$vars['url_static_ending'];

		$Eleanor->Url->special=$Eleanor->Url->furl ? '' : Eleanor::$filename.'?';
		if(Language::$main!=LANGUAGE)
			$Eleanor->Url->special.=$Eleanor->Url->Construct(array('lang'=>Eleanor::$langs[Language::$main]['url']),false,false).$Eleanor->Url->GetDel();
		if(isset($Eleanor->module,$Eleanor->module['name']))
			$Eleanor->Url->SetPrefix(Eleanor::$vars['multilang'] && Language::$main!=LANGUAGE ? array('lang'=>Eleanor::$langs[Language::$main]['url'],'module'=>$Eleanor->module['name']) : array('module'=>$Eleanor->module['name']));
	}
}