<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html, Eleanor\Classes\Output;

define('CMS\STARTED',microtime(true));
require __DIR__.'/core/core.php';

\Eleanor\StartSession(isset($_REQUEST['s']) ? (string)$_REQUEST['s'] : '','INSTALLSESSION');

$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors=[];
$sess_id=session_id();

if(!isset($_SESSION['language']) and $step>2)
	$step=1;

switch($step)
{
	case 5:
		if(isset($_GET['tzo']))
			$_SESSION['tzo']=(int)$_GET['tzo'];

		if(isset($_GET['dst']))
			$_SESSION['dst']=(bool)$_GET['dst'];

		header('Location: '.(isset($_POST['update']) ? 'update' : 'install').'.php?s='.$sess_id,true,301);
		die;
	case 4:
		if(isset($_POST['agreed2']))
			$_SESSION['agreed2']=true;

		$config=Init();
		SetLanguage($_SESSION['language']);
		$lang=Eleanor::$Language['main'];

		if(isset($_SESSION['agreed2']))
		{
			$percent=40;
			$title=$navi=$lang['requirements'];
			$all_ok=true;
			$phpver=PHP_VERSION;
			$warn=Eleanor::$Template->default['images'].'warn.png';
			$ok=Eleanor::$Template->default['images'].'ok.png';

			$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<form method="post" action="?s={$sess_id}&amp;step=5">
			<table class="tablespec">
				<tr class="tshead">
					<th>{$lang['parameter']}</th>
					<th>{$lang['value']}</th>
					<th>{$lang['status']}</th>
				</tr>
				<tr class="tsline">
					<td class="label">{$lang['php_version']}</td>
					<td class="sense">{$phpver}</td>
HTML;

			if(version_compare(PHP_VERSION,'5.5.0','<'))
			{
				$content.=<<<HTML
					<td><img src="{$warn}" alt="{$lang['error']}" alt="{$lang['error']}" /></td>
HTML;
				$all_ok=false;
			}
			else
				$content.=<<<HTML
					<td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;

			$content.=<<<HTML
				</tr>
				<tr class="tsline">
					<td class="label">{$lang['php_gd']}</td>
HTML;

			if(function_exists('imagefttext'))
				$content.=<<<HTML
					<td class="sense">+</td><td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;
			else
			{
				$content.=<<<HTML
					<td class="sense">&minus;</td><td><img src="{$warn}" alt="{$lang['error']}" alt="{$lang['error']}" /></td>
HTML;
				$all_ok=false;
			}

			$content.=<<<HTML
				</tr>
				<tr class="tsline">
					<td class="label">{$lang['php_dom']}</td>
HTML;

			if(function_exists('dom_import_simplexml'))
				$content.=<<<HTML
					<td class="sense">+</td>
					<td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;
			else
				$content.=<<<HTML
					<td class="sense">&minus;</td>
					<td><img src="{$warn}" alt="!!!" title="!!!" /></td>
HTML;

			$content.=<<<HTML
				</tr>
				<tr class="tsline">
					<td class="label">{$lang['db_drivers']}</td>
HTML;

			if(function_exists('mysqli_connect'))
				$content.=<<<HTML
					<td class="sense">MySQLi</td>
					<td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;
			else
			{
				$content.=<<<HTML
					<td>{$lang['not-found']}</td>
					<td><img src="{$warn}" alt="{$lang['error']}" title="{$lang['error']}" /></td>
HTML;
				$all_ok=false;
			}

			$content.=<<<HTML
				</tr>
				<tr class="tsline">
					<td class="label"><b>JavaScript Object Notation</b><br />JSON</td>
HTML;

			if(function_exists('json_encode'))
				$content.=<<<HTML
					<td class="sense">+</td>
					<td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;
			else
			{
				$content.=<<<HTML
					<td>{$lang['not-found']}</td>
					<td><img src="{$warn}" alt="{$lang['error']}" title="{$lang['error']}" /></td>
HTML;
				$all_ok=false;
			}

			if(function_exists('apache_get_modules'))
			{
				$content.=<<<HTML
				</tr>
				<tr class="tsline">
					<td class="label">{$lang['mod_rewrite']}</td>
HTML;

				if(in_array('mod_rewrite',apache_get_modules()))
					$content.=<<<HTML
					<td class="sense">+</td>
					<td><img src="{$ok}" alt="Ok" title="Ok" /></td>
HTML;
				else
				{
					$content.=<<<HTML
					<td class="sense">&minus;</td>
					<td><img src="{$warn}" alt="{$lang['error']}" title="{$lang['error']}" /></td>
HTML;
					$all_ok=false;
				}
			}

			$submit=$all_ok
				? '<div class="submitline">'
					.Html::Button($lang['install'],'submit',['class'=>'button','name'=>'install','tabindex'=>1]).' '
					//.Html::Button($lang['update'],'submit',['class'=>'button','name'=>'update','tabindex'=>2])
					.'</div>'
				: '';
			$content.=<<<HTML
</tr>
				<tr class="tsline"><td class="label" colspan="3">{$lang['mysqlver']}</td></tr>
			</table>
			{$submit}
		</form>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
<script>$(function(){
	$("form:first").attr("action",function(){
		var today=new Date,
			yr=today.getFullYear(),
			dst_start=new Date("March 14, "+yr+" 02:00:00"),
			dst_end=new Date("November 07, "+yr+" 02:00:00"),
			day=dst_start.getDay();

		dst_start.setDate(14-day);
		day=dst_end.getDay();
		dst_end.setDate(7-day);

		return this.action+"&tzo="+(new Date()).getTimezoneOffset()+"&dst="+(today>=dst_start && today < dst_end ? 1 : 0);
	});
})</script>
HTML;
			break;
		}

		$errors[]=$lang['you_must_sagree'];
	case 3:
		if(isset($_POST['agreed']))
			$_SESSION['agreed']=true;

		if(!isset($config))
		{
			$config=Init();
			SetLanguage($_SESSION['language']);
			$lang=Eleanor::$Language['main'];
		}

		if(isset($_SESSION['agreed']))
		{
			$percent=25;
			$title=$navi=$lang['sanctions'];
			$f=DIR.'license/sanctions-'.$_SESSION['language'].'.html';
			$isf=is_file($f);
			$license=$isf ? file_get_contents($f) : file_get_contents(DIR.'license/sanctions-russian.html');
			$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);
			$lic_file='../cms/license/sanctions-'.($isf ? $_SESSION['language'] : 'russian').'.html';

			$errors=$errors ? Eleanor::$Template->Message($errors) : '';
			$form=[
				'agreed2'=>Html::Check('agreed2',!empty($_SESSION['agreed2']),['id'=>'agree','tabindex'=>1]),
				'back'=>Html::Button($lang['back'],'button',['class'=>'button','id'=>'back','tabindex'=>3],2),
				'next'=>Html::Button($lang['next'],'submit',['class'=>'button','tabindex'=>2],2),
			];
			$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			{$errors}
			<form method="post" action="?s={$sess_id}&amp;step=4">
				<div class="textarea license">{$license}</div>
				<p>{$form['agreed2']}<label for="agree">{$lang['i_am_agree_sanc']}</label> <a href="{$lic_file}" style="float:right" target="_blank">{$lang['print']} <img src="../templates/Admin/images/print.png" alt="" /></a></p>
				<div class="submitline">{$form['back']}{$form['next']}</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
<script>$(function(){
	$("#agree").click(function(){
		$(":submit").prop("disabled",!$(this).prop("checked"));
	}).triggerHandler("click");

	$("#back").click(function(e){
		e.preventDefault();
		location.href="?s={$sess_id}&step=2";
	});
})</script>
HTML;
			break;
		}

		$errors=[$lang['you_must_lagree']];
	case 2:
		if(isset($config))
			$language=$_SESSION['language'];
		else
		{
			Init();
			$language=false;

			if(isset($_GET['lang']))
				foreach(Eleanor::$langs as $l=>$lang)
					if($lang['uri']===$_GET['lang'])
					{
						$language=$_SESSION['language']=$l;
						break;
					}

			if(!$language and isset($_SESSION['language']))
				$language=$_SESSION['language'];

			if($language)
				SetLanguage($language);
		}

		if($language)
		{
			$percent=10;
			$lang=Eleanor::$Language['main'];
			$title=$navi=$lang['license'];
			$f=DIR.'license/license-'.$language.'.html';
			$isf=is_file($f);
			$license=$isf ? file_get_contents($f) : file_get_contents(DIR.'license/license-russian.html');
			$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);
			$lic_file='../cms/license/license-'.($isf ? $language : 'russian').'.html';

			$errors=$errors ? Eleanor::$Template->Message($errors) : '';
			$form=[
				'agreed'=>Html::Check('agreed',!empty($_SESSION['agreed']),['id'=>'agree','tabindex'=>1]),
				'select-lang'=>Html::Button($lang['select-lang'],'button',['class'=>'button','id'=>'back','tabindex'=>3],2),
				'next'=>Html::Button($lang['next'],'submit',['class'=>'button','tabindex'=>2],2),
			];
			$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			{$errors}
			<form method="post" action="?s={$sess_id}&amp;step=3">
				<div class="textarea license">{$license}</div>
				<p>{$form['agreed']}<label for="agree">{$lang['i_am_agree_lic']}</label><a href="{$lic_file}" style="float:right" target="_blank">{$lang['print']} <img src="../templates/Admin/images/print.png" alt="" /></a></p>
				<div class="submitline">{$form['select-lang']}{$form['next']}</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
<script>$(function(){
	$("#agree").click(function(){
		$(":submit").prop("disabled",!$(this).prop("checked"));
	}).triggerHandler("click");

	$("#back").click(function(e){
		e.preventDefault();
		location.href="?s={$sess_id}&lang=0";
	});
})</script>
HTML;
		}
		else
			return GoAway("?s={$sess_id}&lang=0");
	break;
	default:
		$_SESSION=[];
		$uri=isset($_GET['lang']) ? (string)$_GET['lang'] : false;
		$config=Init();
		$language=false;

		if($uri)
			foreach(Eleanor::$langs as $l=>$lang)
				if($lang['uri']===$uri)
				{
					$language=$l;
					break;
				}

		if(!$language and isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
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
					$language=$l;
					break;
				}
		}

		if(!$language)
		{
			$langs=$config['langs'];
			$language=key($langs);
		}

		if($uri===false)
		{
			$_SESSION['language']=$language;
			return GoAway('?s='.$sess_id.'&step=2');
		}

		SetLanguage($language);

		$sel=[
			'russian'=>'Выбрать основной язык системы русским',
			'english'=>'Select english main language of system',
			'ukrainian'=>'Обрати основною мовою українську',
		];


		$langs='';
		foreach(Eleanor::$langs as $k=>$v)
		{
			$url=\Eleanor\Classes\Url::Query(['s'=>$sess_id,'step'=>2,'lang'=>$v['uri']]);
			$img=Template::$http[ 'static' ].'images/flags/'.$k.'-big.png';
			$langs.=<<<HTML
<a href="?{$url}" title="{$sel[$k]}"><img src="{$img}" alt="{$v['name']}" title="{$v['name']}" /><span><b>{$v['name']}</b><br />{$sel[$k]}</span></a>
HTML;
		}

		$percent=5;
		$lang=Eleanor::$Language['main'];
		$navi=$lang['lang_select'];
		$title=$lang['welcome'];
		$content=<<<HTML
	<div class="selectlang">{$langs}</div>
HTML;
}

$out=(string)Eleanor::$Template->index(compact('content','navi','percent','errors','percent'));

Output::SendHeaders('html');
Output::Gzip($out);