<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;
defined('CMS\STARTED')||die;

/** Шаблон страницы входа в админку
 * @var string $css Пусть к каталогу css
 * @var string $ico Путь к каталогу ico
 * @var string $images Путь к каталогу images
 * @var array $config Конфигурация шаблона
 * @var array $errors Ошибки формы входа
 * @var string $login Логин входа
 * @var string $password Пароль входа
 * @var \Eleanor\Classes\StringCallback $captcha Капча*/

include_once __DIR__.'/../../html.php';

$lang=Eleanor::$Language->Load(__DIR__.'/../translation/enter-*.php',false);?><!DOCTYPE html>
<html>
<head>
<script src="//cdn.jsdelivr.net/g/angularjs,angular.bootstrap,jquery,bootstrap@3"></script>
<?=Templates\GetHead()?>
<link media="screen" href="<?=$css?>login.css" type="text/css" rel="stylesheet" />
<link media="screen" href="<?=$ico?>ico.css" type="text/css" rel="stylesheet" />
</head>
<body>
<!-- Окно -->
<div class="wrap">
	<div class="wrap-in">
		<h3 class="logo"><?=end($GLOBALS['title'])?></h3>
		<div class="body">
<?php
$err_name=$err_pass=false;
if($errors)
{
	foreach($errors as $k=>&$v)
	{
		if(is_int($k) and is_string($v))
		{
			if(isset($lang[$v]))
			{
				$type=$v;
				$v=$lang[$v];
			}
			elseif(in_array($v,['WRONG_PASSWORD','NOT_FOUND']))
			{
				$type='LOGIN_FAILED';
				$v=$lang['LOGIN_FAILED'];
				$err_pass=$err_name=true;
			}
			else
				$type=$v;
		}
		else
			$type=$k;

		switch($type)
		{
			case'EMPTY_PASSWORD':
				$err_pass=true;
			break;
			case'EMPTY_DATA':
				$err_pass=$err_name=true;
			break;
			case'ACCESS_DENIED':
				$err_name=true;
		}
	}
	unset($v);

	echo'				<div class="alert">',join('<br />',$errors),'</div>
';
}
$ti=0;
?>
			<div class="body-in">
				<form method="post">
					<div class="form-group<?=$err_name ? ' has-error' : ''?>">
						<label class="ico-login" for="login-name"><span class="hide"><?=$lang['login']?></span></label>
						<?=Html::Input('login[name]',$login,['tabindex'=>++$ti,'id'=>'login-name','placeholder'=>$lang['login'],'class'=>'form-control'])?>
					</div>
					<div class="form-group<?=$err_pass ? ' has-error' : ''?>">
						<label class="ico-pass" for="login-pass"><span class="hide"><?=$lang['pass']?></span></label>
						<?=Html::Input('login[password]',$password,['type'=>'password','tabindex'=>++$ti,'id'=>'login-pass','placeholder'=>$lang['pass'],'class'=>'form-control','required'=>true])?>
					</div>
<?php
if($err_name or $err_pass)
{
	$what=$err_name ? 'login-name' : 'login-pass';
	echo<<<HTML
<script>$(function(){
	$("div.alert").click(function(){
		$("#{$what}").focus().parent().addClass("has-error");
	});

	$("#{$what}").focus();
	$("#login-name,#login-pass").on("change keyup",function(){
		$(this).parent().removeClass("has-error");
	});
})</script>
HTML;
}

if(Eleanor::$vars['multilang'])
{
	$langname=include __DIR__.'/../translation/language.php';
	$admin=Eleanor::$services['admin']['file'];
	$main=$lis='';

	foreach(Eleanor::$langs as $l=>$lng)
		if($l==Language::$main )
			$main='<a data-toggle="dropdown" href="'.$admin.'?language='.$l.'">'.$langname[ 'lang-'.$l ].' - '.$lng['name'].'</a>';
		else
			$lis.='<li><a href="'.$admin.'?language='.$l.'">'.$langname[ 'lang-'.$l ].' - '.$lng['name'].'</a></li>';

	echo<<<HTML
					<!-- Выбор языка -->
					<div class="dropdown">
						{$main}
						<ul class="dropdown-menu">
							{$lis}
						</ul>
					</div>
					<!-- Выбор сайта -->
<script>$(function(){
	//Сохранение значений форм между переключениями языков
	var saved=JSON.parse(localStorage.getItem("admin-form"));

	if(saved && ("name" in saved) && ("pass" in saved))
	{
		$("#login-name").val(saved.name);
		$("#login-pass").val(saved.pass);

		localStorage.removeItem('admin-form');
	}

	$(".dropdown-menu a").click(function(){
		localStorage.setItem("admin-form",JSON.stringify({
			name:$("#login-name").val(),
			pass:$("#login-pass").val()
		}));
	});
})</script>
HTML;
}
if(Eleanor::$Template->multisite):?>
					<div class="dropdown" id="multisite">
						<a data-toggle="dropdown" href="#">Войти через</a>
						<ul class="dropdown-menu">
							<li><a href="#"></a></li>
						</ul>
					</div>
					
					<?php endif; echo$captcha?>

					<button class="btn btn-wide">
						<b><?=$lang['enter']?></b>
					</button>
				</form>
				<!-- / Форма -->
			</div>
		</div>
	</div>
</div>
<!-- / Окно -->
<!-- Меню -->
<div class="footer">
	<ul class="right hmenu">
		<li><a href="<?=\Eleanor\SITEDIR?>" title="<?=$a=\Eleanor\DOMAIN.(\Eleanor\SITEDIR=='/' ? '' : \Eleanor\SITEDIR)?>"><?=$a?></a></li>
		<li><?=
#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
'Powered by ',COPYRIGHT,RUNTASK ? '<img src="'.RUNTASK.'" alt="" />' : ''?></li>
	</ul>
	<ul class="hmenu">
		<li><a href="<?=Eleanor::$vars['link_lost_pass']?>">Забыли пароль?</a></li>
		<li><a href="http://eleanor-cms.ru/help/reset-password" target="_blank">Сбросить пароль</a></li>
	</ul>
</div>
<!-- / Меню -->
<?php if(Eleanor::$Template->multisite):?>
<script>//<![CDATA[
CORE.MultiSite.done(function(queue){
	var ms=$("#multisitee").show().find("ul").empty();
	$.each(queue,function(k,v){
		var a=$("<a>").prop({
				href:"#",
				title:v.name
			}).text(v.title).click(function(e){
				e.preventDefault();
				CORE.Login(k);
			});

		$("<li>").append(a).appendTo(ms);
	})
})//]]></script>
<?php endif?>
</body>
</html>