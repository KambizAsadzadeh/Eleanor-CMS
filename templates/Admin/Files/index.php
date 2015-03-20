<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html, CMS\Templates\Admin\T;

defined('CMS\STARTED')||die;

/** Общий шаблон админки
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $js Путь к каталогу js
 * @var string $ico Путь к каталогу ico
 * @var array $config Конфигурация шаблона
 * @var string $content Содержимое модуля */

include_once __DIR__.'/../../html.php';

$file=Eleanor::$services['admin']['file'];
$lang=Eleanor::$Language['main'];

if(isset(T::$data['speedbar'])):
global$Eleanor,$title;

#Список модулей для верхнего меню
$modules=Eleanor::$Cache->Get('tpl_admin_modules_'.Language::$main);
if($modules===false)
{
	$modules='';
	$items=\Eleanor\AwareInclude(__DIR__.'/../php/top-menu-modules.php');

	foreach($items as $module)
	{
		$image=T::$http['static'].'images/modules/default-16x16.png';

		if($module['miniature'])
		{
			if(!isset($module['miniature']['http']))
			{
				ksort($module['miniature'],SORT_STRING);
				$module['miniature']=reset($module['miniature']);
			}

			$image=$module['miniature']['http'];
		}

		$modules.=<<<HTML
<li><a href="{$module['_a']}" title="{$module['descr']}"><img src="{$image}" alt="" />{$module['title']}</a></li>
HTML;
	}

	Eleanor::$Cache->Put('tpl_admin_modules_'.Language::$main,$modules,3600);
}
#/Список модулей для верхнего меню

#Список управления для верхнего меню
$manage='';
$info=\Eleanor\AwareInclude(DIR.'admin/modules.php');

foreach($info as $name=>$module)
{
	if(isset($module['hidden']))
		continue;

	$image=false;

	if($module['image'])
	{
		$module['image']='images/modules/'.str_replace('*','16x16',$module['image']);

		if(is_file(T::$path['static'].$module['image']))
			$image=$module['image'];
	}

	$url=DynUrl::$base.DynUrl::Query(['section'=>'management','module'=>$name]);
	$image=$image ? '<img src="'.T::$http['static'].$image.'" alt="" />' : '';

	/*$submenu=$manage ? '' : '<ul class="dropdown-menu">
<li><a title="" href="#"><img alt="" src="static/images/modules/account-16x16.png">Аккаунт пользователя</a></li>
<li><a title="" href="#"><img alt="" src="static/images/modules/account-16x16.png">Аккаунт пользователя</a></li>
</ul>';*/

	$manage.=<<<HTML
<li><a href="{$url}" title="{$module['descr']}">{$image}{$module['title']}</a>{$submenu}</li>
HTML;
}
#/Список управления для верхнего меню

#Список языков
if(Eleanor::$vars['multilang'])
{
	$langname=include __DIR__.'/../translation/language.php';
	$curlang=$langname[ 'lang-'.Language::$main ].' - '.Eleanor::$langs[ Language::$main ]['name'];
	$altlangs='';

	foreach(Eleanor::$langs as $k=>$lng)
		if($k!=Language::$main)
			#Класс change-language нужен для DraftButton
			$altlangs.='<li><a href="'.$file.'?language='.$k.'" class="change-language">'.$langname[ 'lang-'.$k ].' - '
				.$lng['name'].'</a></li>';
}
else
	$curlang=$altlangs='';
#/Список языков

#Хлебные крошки
$speedbar=$back='';
if(isset(T::$data['speedbar']))
{
	$back=$file;
	$speedbar.='<li><a href="'.$back.'">Главная</a></li>';

	foreach(T::$data['speedbar'] as $item)
		if(is_array($item) and isset($item[0],$item[1]))
		{
			$speedbar.=<<<HTML
<li><a href="{$item[0]}">{$item[1]}</a></li>
HTML;

			$back=$item[0];
		}
		elseif(is_string($item))
			$speedbar.='<li>'.$item.'</li>';
}
#/Хлебные крошки

#Верхнее меню
$topmenu='';
if(isset(T::$data['navigation']))
	foreach(T::$data['navigation'] as $k=>$item)
		if($item)
		{
			$active=!empty($item['act']);
			$liextra=isset($item['li-extra']) ? (array)$item['li-extra'] : [];
			$submenu='';

			if(isset($item['submenu']))
			{
				foreach($item['submenu'] as $subitem)
					if($subitem)
					{
						$sliextra=isset($subitem['li-extra']) ? (array)$subitem['li-extra'] : [];
						$sliextra=$liextra ? Html::TagParams($sliextra) : '';

						$extra=isset($subitem['extra']) ? (array)$subitem['extra'] : [];
						if(!empty($subitem['act']))
						{
							$active=true;
							$extra+=['class'=>'active'];
						}
						$extra=$extra ? Html::TagParams($extra) : '';

						$submenu.='<li'.$sliextra.'><a href="'.$subitem[0].'"'.$extra.'>'.$subitem[1].'</a></li>';
					}

				if($submenu)
				{
					$extra=isset($item['submenu-extra']) ? (array)$item['submenu-extra'] : [];
					$extra+=['class'=>'dropdown-menu','rol'=>'menu','aria-labelledby'=>'top-menu-'.$k];
					$extra=$extra ? Html::TagParams($extra) : '';
					$active=$active ? ' active' : '';

					$submenu=<<<HTML
<a href="#" class="dropdown-toggle{$active}" id="top-menu-{$k}" data-toggle="dropdown"><span class="caret"></span></a><ul{$extra}>{$submenu}</ul>
HTML;

					$liextra+=['class'=>'dropdown'];
				}
			}

			$liextra=$liextra ? Html::TagParams($liextra) : '';

			$extra=isset($item['extra']) ? (array)$item['extra'] : [];
			if($active)
				$extra+=['class'=>'active'];

			if(isset($item['type']))
				switch($item['type']){
					case'select':
						$li='';

						foreach($item['option'] as $v=>$opt)
							$li.=is_array($opt) ? Html::Option($opt[0],$v,isset($opt[1])) : Html::Option($opt,$v);

						$li=Html::Select($item[0],$li,$extra);
					break;
					case'input':
						$li=Html::Input($item[0],$item[1],$extra);
					break;
					case'button':
						$extra=$extra ? Html::TagParams($extra) : '';
						$li='<button'.$extra.'>'.$item[1].'</button>';
					break;
					default:
						continue 2;
				}
			else
			{
				$extra=$extra ? Html::TagParams($extra) : '';
				$li=<<<HTML
<a href="{$item[0]}"{$extra}>{$item[1]}</a>
HTML;
			}

			$topmenu.=<<<HTML
<li{$liextra}>{$li}{$submenu}</li>
HTML;
		}
#/Верхнее меню

array_push($GLOBALS['scripts'],'https://cdn.socket.io/socket.io-1.2.0.js',T::$http['3rd'].'static/socket.js',
	T::$http['3rd'].'static/angular.ng-modules.js',$js.'admin.js',$js.'admin-'.Language::$main.'.js',$js.'admin-angular.js');
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap-theme.css" type="text/css">
	<script src="//cdn.jsdelivr.net/g/angularjs,angular.bootstrap(ui-bootstrap.min.js+ui-bootstrap-tpls.min.js),jquery,bootstrap@3"></script>
	<meta name="viewport" content="width=1140">

	<?=Templates\GetHead(true,false)?>
	<!--[if lt IE 9]><script src="//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->

	<link rel="stylesheet" href="<?=$css?>style.css" type="text/css">
	<link rel="stylesheet" href="<?=$ico?>ico.css" type="text/css">
</head>
<body>
<!-- TopBar -->
<header id="topbar" class="darkgrad topbar-fix">
	<a class="el-logo" target="_blank" href="http://eleanor-cms.ru"><span class="ico-logo"></span><span class="thd">Eleanor CMS</span></a>
	<ul class="el-menu" id="main-menu">
		<li>
			<a href="" class="nav-ico" title="<?=T::$lang['view site']?>"><span class="i-sel ico-view"></span><span class="thd"><?=T::$lang['view site']?></span></a>
		</li>
		<li class="dropdown">
			<a href="<?=$file?>?section=options" class="nav-ico" title="Основные настройки"><span class="i-sel ico-setting"></span><span class="thd"><?=$lang['options']?></span></a>
			<ul class="dropdown-menu">
				<li><a href="#">Основные настройки</a></li>
				<li><a href="#">Шаблоны</a></li>
				<li><a data-toggle="modal" href="#cashe">Очистить кэш</a></li>
			</ul>
		</li>
		<li class="active dropdown">
			<a href="<?=$file?>?section=modules" class="dropdown-toggle"><?=$lang['modules']?></a>
			<ul class="dropdown-menu menu_icons">
				<?=$modules?>
			</ul>
		</li>
		<li class="dropdown">
			<a href="<?=$file?>?section=management" class="dropdown-toggle"><?=$lang['management']?></a>
			<ul class="dropdown-menu menu_icons">
				<?=$manage?>
			</ul>
		</li>
		<li>
			<a href="<?=$file?>?section=statistic" class="dropdown-toggle"><?=$lang['statistic']?></a>
		</li>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle">Магазин</a>
			<ul class="dropdown-menu">
				<li><a href="#">Топ бесплатных</a></li>
				<li><a href="#">Топ платных</a></li>
				<li><a href="#">Обновления</a></li>
			</ul>
		</li>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle">Помощь</a>
			<ul class="dropdown-menu">
				<li><a href="#">Справка Eleanor CMS</a></li>
				<li class="divider"></li>
				<li class="dropdown-header">Онлайн сервисы</li>
				<li><a href="#">Форум Техподдержки</a></li>
				<li><a href="#">Официальный сайт</a></li>
				<li><a href="#">Оставить отзыв</a></li>
				<li><a href="#">О Eleanor CMS</a></li>
			</ul>
		</li>
	</ul>
	<ul class="el-menu el-right">
		<?php if($altlangs):?>
			<li class="dropdown">
				<!-- Этот блок нужно конкретно переработать. Во-первых, человеку, который не знает русского языка должно быть понятно, что в этом блоке изменяется язык. Представь, к примеру, что ты смотришь сайт на китайском или иврите: поймешь ли ты в каком блоке изменяется язык? -->
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=$curlang?></a>
				<ul class="dropdown-menu">
					<?=$altlangs?>
				</ul>
			</li>
		<?php endif ?>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle nav-ico" data-toggle="dropdown" title="Быстрая публикация"><span class="i-sel ico-plus"></span><span class="thd">Быстрое добавление</span></a>
			<ul class="dropdown-menu">
				<li><a href="#">Добавить новость</a></li>
				<li><a href="#">Добавить страницу</a></li>
				<li><a href="#">Добавить файл</a></li>
				<li class="divider"></li>
				<li><a href="#">Настроить...</a></li>
			</ul>
		</li>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle nav-ico" data-toggle="dropdown" title="Уведомления - 2">
				<span class="i-sel ico-info"></span>
				<span class="thd">Уведомления</span>
				<span class="badge">2</span>
			</a>
			<ul class="dropdown-menu drop-notify">
				<li class="notify-group">
					<b>Модуль новости</b>
					<ul class="nf-list">
						<li class="nf-item">
							<a href="#">
								<div class="nf-meta"><span>12.08.2014г.</span></div>
								<div class="nf-text">Lorem ipsum dolor sit amet, consectetur adipisicing</div>
							</a>
						</li>
						<li class="nf-item">
							<a href="#">
								<div class="nf-meta"><span>11.08.2014г.</span></div>
								<div class="nf-text">Excepteur sint occaecat cupidatat non proident</div>
							</a>
						</li>
					</ul>
				</li>
				<li class="notify-group">
					<b>Пользователи</b>
					<ul class="nf-list">
						<li class="nf-item">
							<a href="#">
								<div class="nf-meta"><span>12.08.2014г.</span></div>
								<div class="nf-text">Alexander</div>
							</a>
						</li>
						<li class="nf-item">
							<a href="#">
								<div class="nf-meta"><span>11.08.2014г.</span></div>
								<div class="nf-text">SuperMan</div>
							</a>
						</li>
					</ul>
				</li>
				<li class="notify-group">
					<b>Комментарии</b>
					<ul class="nf-list">
						<li class="nf-item">
							<a href="#">
								<div class="nf-meta"><span>12.08.2014г.</span> <span>Alexander</span></div>
								<div class="nf-text">Excepteur sint occaecat cupidatat non proident</div>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</li>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle top-ava" data-toggle="dropdown">
				<span class="thumb" style="background-image: url(<?=T::$http['static']?>images/noavatar.png);"></span>
				<span class="thd"><?=htmlspecialchars(Eleanor::$Login->Get('name'),ENT,\Eleanor\CHARSET)?></span>
			</a>
			<ul class="dropdown-menu">
				<li><a href="<?=DynUrl::$base,Url::Query(['section'=>'management','module'=>'users','edit'=>Eleanor::$Login->Get('id')])?>">Настройка аккаунта</a></li>
				<li><a href="<?=$file?>?logout=true"><?=T::$lang['exit']?></a></li>
			</ul>
		</li>
	</ul>
</header>
<!-- TopBar [E] -->
<div id="wrp">
	<?php if($speedbar or $topmenu): ?>
		<!-- Заголовок & Хлебные крошки -->
		<div id="content-head">
			<?php if($speedbar): ?>
				<div class="module-breadcrumb">
					<a class="ico-arrow-left" href="<?=$back?>" title="Вернуться"><span class="thd">Вернуться</span></a>
					<ul class="breadcrumb over">
						<?=$speedbar?>
					</ul>
				</div>
			<?php endif ?>
			<h2><?=isset($Eleanor->module['title']) ? $Eleanor->module['title'] : (is_array($title) ? end($title) : $title)?></h2>
			<!-- Module Menu -->
<?php
$extra=isset(T::$data['navigation-extra']) ? (array)T::$data['navigation-extra'] : [];
$extra+=['class'=>'module-menu'];
$extra=Html::TagParams($extra);
?>
			<ul<?=$extra?>>
				<?=$topmenu?>
			</ul>
			<!-- Module Menu [E] -->
		</div>
		<!-- Заголовок & Хлебные крошки [E] -->
	<?php endif ?>
	<div id="container">
		<section id="content">
		<!-- Content --><?=$content?><!-- Content [E] -->
		</section>
	</div>
</div>

<footer class="footer">
	<p class="systext" id="poweredby"><?=
		#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
		'Powered by ',COPYRIGHT,RUNTASK ? '<img src="'.RUNTASK.'" alt="" />' : ''?></p>
	<p class="systext" id="sysinfo"><?=Templates\GetPageInfo(T::$lang['page_status'])?></p>
	<?php
	if(Eleanor::$debug)
		echo'<div class="systext">',Templates\GetDebugInfo(),'</div>';
	#ToDo! Мультисайт
	?>
</footer>

<!-- Загрузка -->
<div class="loading" id="loading"><span class="ico-load"></span></div>
</body>
</html>
<?php else:
$GLOBALS['scripts'][]='//cdn.jsdelivr.net/jquery/2.1.1/jquery.min.js';
$GLOBALS['scripts'][]=$js.'admin-audora.js';
global$Eleanor;
?><!DOCTYPE html><html><head><?=Templates\GetHead(true,false)?>
	<link rel="shortcut icon" href="favicon.ico" />
	<link media="screen" href="<?=$css?>styles.css" type="text/css" rel="stylesheet" />
	<link media="screen" href="<?=$css?>main.css" type="text/css" rel="stylesheet" />
	<style type="text/css">
		.pagebg {
		<?php if($config['imagebg']):?>		background-image: url(<?=$config['imagebg']?>);<?php endif ?>
			background-color: <?=$config['colorbg']?>;
			background-repeat: <?=$config['bgrepeat']?>;
			background-position: <?=$config['positionimg']?>;
			background-attachment: <?=$config['bgattachment'] ? 'scroll' : 'fixed'?>;
		}
		<?=$config['sizethm']=='r' ? '.wrapper { width: 90%; max-width: 1400px; min-width: 996px; }' : '.wrapper { width: 996px; }';?>
	</style>

</head>

<body class="pagebg">
<div id="loading"><div>&nbsp;</div></div>
<script>//<![CDATA[
	$(function(){
		$("#loading").on("show",function(){
			$(this).css({
				left:Math.round(($(window).width()-$(this).width())/2),
				top:Math.round(($(window).height()-$(this).height())/2)
			});
		}).triggerHandler("show");
		$(window).resize(function(){
			$("#loading").triggerHandler("show");
		});
	});//]]></script>
<div class="wrapper">
<div class="elh"><div class="elh"><div class="elh">
			<div class="elhead">
				<h1><a href="http://eleanor-cms.ru" title="Eleanor CMS" target="_blank">Eleanor CMS</a></h1>
				<div><ul class="reset">
						<li class="bnt1"><a href="<?=$file?>?logout=true" title="<?=T::$lang['exit']?>"><img src="<?=T::$http['static']?>images/spacer.png" alt="" /></a></li>
						<li class="bnt2">
							<h3><?='<a href="',DynUrl::$base,Url::Query(['section'=>'management','module'=>'users','edit'=>Eleanor::$Login->Get('id')]),'">',Eleanor::$Login->Get('name'),'</a>'?></h3>
							<span class="small">
<?php
if(isset(Eleanor::$Permissions))
{
	$ug=Eleanor::$Permissions->Get('title_l');
	$ug=FilterLangValues(reset($ug));
	echo$ug;
} ?></span>
						</li>
					</ul></div>
			</div>
			<div class="elhmenu"><?php
if(Eleanor::$vars['multilang'])
{
	echo'<div class="hlang"><ul class="reset">';

	foreach(Eleanor::$langs as $k=>$lng)
	{
		$image='<img src="'.T::$http['static'].'images/flags/'.$k.'.png" title="'.$lng['name'].'" alt="'
			.$lng['name'].'" />';

		echo'<li>',
		$k==Language::$main ? '<span class="active">'.$image.'</span>' : '<a href="'.$file.'?language='.$k.'">'.$image.'</a>',
		'</li>';
	}
	echo'</ul></div>';
}?>
				<div class="hviewsite">
					<span><a href="<?=\Eleanor\SITEDIR?>"><span><?=T::$lang['view site']?></span></a></span>
				</div>
				<ul class="reset hmenu" id="crmenu">
					<li><a href="<?=$file?>"><span><?=$lang['main page']?></span></a></li>
					<li><a href="<?=DynUrl::$base?>section=modules" class="ddrop" data-rel="#subm1"><span><?=$lang['modules']?></span></a></li>
					<li><a href="<?=DynUrl::$base?>section=management" class="ddrop" data-rel="#subm2"><span><?=$lang['management']?></span></a></li>
					<li><a href="<?=DynUrl::$base?>section=options"><span><?=$lang['options']?></span></a></li>
					<li><a href="<?=DynUrl::$base?>section=statistic"><span><?=$lang['statistic']?></span></a></li>
				</ul>
<?php
$di='images/modules/default-16x16.png';
$modules=Eleanor::$Cache->Get('admin-header_'.Language::$main);
if($modules===false)
{
	$modules=$titles=$items=[];
	$table=P.'modules';
	$service=Eleanor::$service;
	$R=Eleanor::$Db->Query("SELECT `uris`, `title_l` `title`,`descr_l` `descr`, `protected`, IF(`miniature_type`='gallery',`miniature`,'') `image` FROM `{$table}` WHERE `services`='' OR `services` LIKE '%,{$service},%' AND `status`=1");
	while($module=$R->fetch_assoc())
	{
		$image=$di;

		if($module['image'])
		{
			$module['image']='images/modules/'.str_replace('*','16x16',$module['image']);
			if(is_file(T::$path['static'].$module['image']))
				$image=$module['image'];
		}

		$module['title']=$module['title'] ? FilterLangValues($mt=json_decode($module['title'],true)) : '';
		$module['descr']=$module['descr'] ? FilterLangValues(json_decode($module['descr'],true)) : '';

		$uris=$module['uris'] ? json_decode($module['uris'],true) : [];

		if($uris)
		{
			foreach($uris as &$section)
				if(isset($section[ Language::$main ]))
					$section=reset($section[ Language::$main ]);
				elseif(isset($section['']))
					$section=reset($section['']);
				else
					$section=null;

			$titles[]=$module['title'];
			$items[]='<li><a href="'.DynUrl::$base.DynUrl::Query(['section'=>'modules','module'=>reset($uris)])
				.'" title="'.$module['descr'].'"><span><img src="'.T::$http['static'].$image.'" alt="" />'
				.$module['title'].'</span></a></li>';
		}
	}

	asort($titles,SORT_STRING);
	foreach($titles as $k=>$item)
		$modules[]=$items[$k];

	Eleanor::$Cache->Put('admin-header_'.Language::$main,$modules,3600);
}

$modcnt=count($modules);
if($three=$modcnt>23)
	$modcnt_d=ceil($modcnt/3);
else
	$modcnt_d=$modcnt>10 ? ceil($modcnt/2) : $modcnt;?>
				<div id="subm1" class="hmenusub<?php if($modcnt>10) echo$three ? ' threecol' : ' twocol'?>">
					<div class="colomn">
						<ul class="reset">
							<?=join('',array_slice($modules,0,$modcnt_d))?>
						</ul>
					</div>
					<?php if($modcnt>10):?>
						<div class="colomn">
							<ul class="reset">
								<?=join('',array_slice($modules,$modcnt_d,$modcnt_d))?>
							</ul>
						</div>
					<?php endif; if($three>10):?>
						<div class="colomn">
							<ul class="reset">
								<?=join('',array_slice($modules,$modcnt_d*2))?>
							</ul>
						</div>
					<?php endif; unset($module,$modcnt,$modcnt_d)?>
					<div class="clr"></div>
				</div>
				<?php
				$manage=[];
				$info=\Eleanor\AwareInclude(DIR.'admin/modules.php');

				foreach($info as $name=>$module)
				{
					if(isset($module['hidden']))
						continue;

					$image=$di;

					if($module['image'])
					{
						$module['image']='images/modules/'.str_replace('*','16x16',$module['image']);
						if(is_file(T::$path['static'].$module['image']))
							$image=$module['image'];
					}

					$manage[]='<li><a href="'.DynUrl::$base.DynUrl::Query(['section'=>'management','module'=>$name])
						.'" title="'.$module['descr'].'"><span><img src="'.T::$http['static'].$image.'" alt="" />'.$module['title']
						.'</span></a></li>';
				}
				?>
				<div id="subm2" class="hmenusub">
					<ul class="reset">
						<?=join('',$manage)?>
					</ul>
				</div>
				<script>/*<![CDATA*/$(function(){$(".ddrop").MainMenu();})//]]></script>
			</div>
		</div></div></div>
<div class="container">
	<div class="midside">
		<div class="container">
			<div class="mainside">
				<table class="conts"><tr><td style="padding: 0;">
					<div class="wpbox">
						<div class="wptop"><b><span>&nbsp;</span></b></div>
						<div class="wpmid">
							<?php
							$section=isset($_GET['section']) ? (string)$_GET['section'] : 'general';

							if($section!='general')
							{
								$image=isset($Eleanor->module['image']) ? 'images/modules/'.str_replace('*','big',$Eleanor->module['image']) : 'images/modules/default-48x48.png';
								$mtitl=isset($Eleanor->module['title']) ? $Eleanor->module['title'] : '';
								$descr=isset($Eleanor->module['descr']) ? $Eleanor->module['descr'] : '';

								echo'<div class="wbpad"><div class="bighead',$descr ? '' : ' nodesc','"><div><div><img src="',
								$image ? $image : T::$http['static'].'images/modules/default-48x48.png','" alt="',$mtitl,
								'" /><p><b>',$mtitl,'</b>',$descr ? '<span>'.$descr.'</span>' : '','</p></div></div></div></div>';
							}

							echo$content;
							?>
						</div>
						<div class="wpbtm"><b><span>&nbsp;</span></b></div>
					</div>
				</td></tr></table>
			</div>
			<div class="rightside">
				<?php
				$menu='';

				if(isset($Eleanor->module['navigation']))
					foreach($Eleanor->module['navigation'] as $item)
					{
						if(!$item)
							continue;

						$submenu='';

						if(isset($item['submenu']))
						{
							$submenu='';

							foreach($item['submenu'] as $subitem)
								if($subitem)
									$submenu.='<li><a href="'.$subitem[0].'"'.($subitem['act'] ? ' class="active"' : '')
										.(isset($subitem['extra']) ? ' '.$subitem['extra'] : '').'><span>'.$subitem[1]
										.'</span></a></li>';

							if($submenu)
								$submenu='<ul class="submenu">'.$submenu.'</ul>';
						}

						$image=isset($item[2]) ? '<img src="'.Eleanor::$Template->default['images'].$item[2].'.png" alt="" />' : '';
						$menu.='<li><a href="'.$item[0].'"'.(empty($item['act']) ? '' : ' class="active"')
							.(isset($item['extra']) ? Html::TagParams($item['extra']) : '')
							.' title="'.$item[1].'"><span>'.$image.$item[1].'</span></a>'.$submenu.'</li>';
					}

				$ref=getenv('HTTP_REFERER');

				if($ref)
					$menu.='<li><a href="'.htmlspecialchars($ref,ENT,\Eleanor\CHARSET,false).'"><img src="'
						.Eleanor::$Template->default['images'].'back.png" alt="" />'.T::$lang['go-back'].'</a></li>';
				if($menu):?>
					<div class="block bvnav">
						<div class="dtop">&nbsp;</div>
						<div class="dmid">
							<h3 class="dtitle"><?=T::$lang['navigation']?></h3>
							<div class="dcont"><ul class="reset navs"><?=$menu?></ul></div>
						</div>
						<div class="dbtm">&nbsp;</div>
					</div>
				<?php
				endif;
				echo Blocks::Get('right');
				?>
			</div>
		</div>
	</div>
	<div class="clr"></div>
</div>
<div class="elf"><div class="elf"><div class="elf">
			<div class="elfoot">
				<span class="copyright"><?=
					#Пожалуйста, не удаляйте и не изменяйте наши копирайты, если, конечно, у вас есть хоть немного уважения к разработчикам.
					'Powered by ',COPYRIGHT,RUNTASK ? '<img src="'.RUNTASK.'" alt="" />' : ''?>
				</span>
				<span class="version">	<?=T::$lang['sversion'],' <b>',Eleanor::VERSION,'</b>'?></span>
			</div>
		</div></div></div>
<div class="finfo">
	<span class="pageinfo"><?=Templates\GetPageInfo(T::$lang['page_status'])?></span>
	<?php
	if(Eleanor::$debug)
		echo'<div>',Templates\GetDebugInfo(),'</div>';

	if(Eleanor::$Template->multisite):
		echo Html::Select(false,Html::Option(T::$lang['msjump'],'',true),['id'=>'msjump','style'=>'float:right','onchange'=>'CORE.Jump($(this).val())'])?>
		<script>//<![CDATA[
			$(function(){
				$.each(CORE.sites,function(k,v){
					$("<option>").text(v.title).val(k).appendTo("#msjump");
				})
			})//]]></script><?php endif?>		</div>
</div>
</body>
</html>
<?php endif?>