<?php
namespace CMS;
defined('CMS\STARTED')||die;

/** Оформление содержимого блока "тучка тегов"
 * @var array $var_0 Опции */

$var_0+=[
	'width'=>150,#Ширина
	'height'=>150,#Высота
	'color'=>'fbf9f8',#Цвет текста
	'color2'=>'00ff00',
	'hicolor'=>'ff0000',
	'bgcolor'=>'fbf9f8',#Цвет фона
	'trans'=>true,#Прозрачность
	'speed'=>100,#Скорость движения
	'distr'=>'true',#Равномерное распределение
];

global$Eleanor;
if(isset($Eleanor->module['tags']))
{
	$tags='';

	foreach($Eleanor->module['tags'] as $v)
		$tags.='<a href="'.$v['_a'].'" style="font-size:12px" rel="tag">'.$v['name'].'</a>';

	echo'<div id="tag-cloud" style="text-align:center">',$tags,'</div><script>',
		'/*<![CDATA[*/CORE.AddScript("js/swfobject.js",function(){swfobject.embedSWF("addons/flash/tagcloud.swf?r="',
		'+Math.random(),"tag-cloud","',$var_0['width'],'","',$var_0['height'],'", "9.0.0",null,{tcolor:"0x',
		$var_0['color'],'",tcolor2:"0x',$var_0['color2'],'",hicolor2:"0x',$var_0['hicolor'].'",tspeed:"',
		$var_0['speed'],'",distr:"',$var_0['distr'],'",mode:"tags",tagcloud:"<tags>',
		str_replace(['a href="','%','?','&amp;','&','"'],
			['a href="'.\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR,'%25','%3F','%26','%26','\\"'],$tags),
		'</tags>"},{},{',$var_0['trans'] ? 'wmode:"transparent",' : '','allowscriptaccess:"always",bgcolor:"#',
		$var_0['bgcolor'],'"})})//]]></script>';
}