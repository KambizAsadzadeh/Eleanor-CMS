<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** @var Logins\Admin $login */
$login='\CMS\Logins\\'.Eleanor::$services['admin']['login'];

if($login->IsUser())
{
	$af=Eleanor::$services['admin']['file'];
	$hfu=Eleanor::$Cache->Get('admin-header-'.Language::$main,false);

	if($hfu===false)
	{
		$def_img=Template::$http['static'].'images/modules/default-small.png';
		$manage='';
		$modules_=$modules=[];

		require DIR.'admin/modules.php';
		/** @var array $info */
		foreach($info as$k=>$m)
		{
			if(!isset($m['services']['admin']) or !empty($m['hidden']))
				continue;

			$img=$def_img;

			if($m['image'])
			{
				$m['image']='images/modules/'.str_replace('*','small',$m['image']);

				if(is_file(Template::$path['static'].$m['image']))
					$img=Template::$http['static'].$m['image'];
			}

			$manage.='<li><a href="'.$af.'?section=management&amp;module='.urlencode($k).'"><span><img src="'.$img
				.'" alt="" />'.$m['title'].'</span></a></li>';
		}

		$R=Eleanor::$Db->Query('SELECT `uris`,`title_l` `title`,`descr_l` `descr`,`protected`,`image` FROM `'.P
			.'modules` WHERE `services`=\'\' OR `services` LIKE \'%,admin,%\' AND `active`=1');
		while($m=$R->fetch_assoc())
		{
			$img=$def_img;

			if($m['image'])
			{
				$m['image']='images/modules/'.str_replace('*','small',$m['image']);
				if(is_file(Template::$path['static'].$m['image']))
					$img=Template::$http['static'].$m['image'];
			}

			$m['title']=$m['title'] ? FilterLangValues((array)unserialize($m['title'])) : '';
			$m['descr']=$m['descr'] ? FilterLangValues((array)unserialize($m['descr'])) : '';
			$m['uris']=$m['uris'] ? (array)unserialize($m['uris']) : false;

			if($m['uris'])
			{
				foreach($m['uris'] as &$section)
					if(isset($section[ Language::$main ]))
						$section=reset($section[ Language::$main ]);
					elseif(isset($section['']))
						$section=reset($section['']);
					else
						$section=null;

				$titles[]=$m['title'];
				$modules_[]='<li><a href="'.$af.'?section=modules&amp;module='.urlencode(reset($uris)).'" title="'
					.$m['descr'].'"><span><img src="'.$img.'" alt="" />'.$m['title'].'</span></a></li>';
			}
		}

		asort($titles,SORT_STRING);
		foreach($titles as $k=>&$m)
			$modules[]=$modules_[$k];

		Eleanor::$Cache->Put('admin-header-'.Language::$main,[$manage,$modules],3600);
	}
	else
		list($manage,$modules)=$hfu;

	$cnt=count($modules);

	if($three=$cnt>23)
		$slice=ceil($cnt/3);
	else
		$slice=$cnt>10 ? ceil($cnt/2) : $cnt;

	$GLOBALS['head'][]='<link rel="stylesheet" media="screen" type="text/css" href="'
		.Template::$http['templates'].'templates/'.Eleanor::$services['admin']['theme']
		.'/css/adminmenu.css" />';
	$GLOBALS['scripts'][]=Template::$http['static'].'js/admin.js';

	echo'<div id="adminblockf"><div id="subm1" class="adminsubmenu';

	if($cnt>10)
		echo $three ? ' threecol' : ' twocol';

	echo'"><div class="colomn"><ul class="reset">',
	join(array_slice($modules,0,$slice)),
	'</ul></div>';

	if($cnt>10)
		echo'<div class="colomn"><ul class="reset">',
		join(array_slice($modules,$slice,$slice)),
		'</ul></div>';

	if($three>10)
		echo'<div class="colomn"><ul class="reset">',
		join(array_slice($modules,$slice*2)),
		'</ul></div>';

	unset($m,$cnt,$slice);

	$lang=Eleanor::$Language['admin'];
	echo'<div class="clr"></div></div>
	<script>
$(function(){
	$("a.mlink").MainMenu();

	var ab=$("#adminblockh"),
		h=$("#adminblockf").height()+"px",
		abf=$("#adminblockf");

	if(localStorage.getItem("ahfu"))
	{
		ab.addClass("active");
		abf.addClass("fixmenupanel").next().css("margin-top",h)
	}

	$("#adminblockh a").click(function(){
		abf.toggleClass("fixmenupanel");

		if(ab.is(".active"))
		{
			ab.removeClass("active");
			abf.next().css("margin-top",0);
			scroll(0,0);
			localStorage.removeItem("ahfu");
		}
		else
		{
			ab.addClass("active");
			abf.next().css("margin-top",h);
			localStorage.setItem("ahfu","1");
		}

		return false;
	});
})</script>
	<div class="adminmenupanel">
	<div class="backtoadmin" id="adminblockh"><a href="#"></a></div>
	<a href="',$af,'" class="logotypepanel"><img src="',Template::$http['templates'],'templates/',
		Eleanor::$services['admin']['theme'],'/images/eleanorcms_menu.png" alt="Eleanor CMS" /></a>
	<ul class="hmenu">
		<li><a class="link" href="',$af,'?section=general"><span>',$lang['admin'],'</span></a></li>
		<li><a class="mlink" href="',$af,'?section=modules" data-rel="#subm1"><span>',$lang['modules'],'</span></a></li>
		<li><a class="mlink" href="',$af,'?section=management" data-rel="#subm2"><span>',$lang['management'],'</span></a></li>
		<li><a class="link" href="',$af,'?section=options"><span>',$lang['options'],'</span></a></li>
	</ul><div id="subm2" class="adminsubmenu"><ul class="reset">',$manage,'</ul></div></div></div>';
}