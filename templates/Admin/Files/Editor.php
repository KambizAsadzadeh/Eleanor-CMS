<?php
/**
Eleanor CMS © 2014
http://eleanor-cms.ru
info@eleanor-cms.ru
 */
namespace CMS\Templates\Admin;
use \Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

/** Обертка для редакторов
 * @var string $var_0 Идентификатор редактора, уникальный в пределах страницы
 * @var string $var_1 HTML код редактора
 * @var array $var_2 Смайлы, ключи
 *  string path Путь к смайлу относительно каталог static
 *  array emotion Эмоции
 *  string show Флаг отображения смайла в редакторе
 * @var array $var_3 OwbBB коды, ключи:
 *  string t Имя тега (tag)
 *  bool s Флаг одиночного тега (single)
 *  string l Краткое человеческое описаение тега
 * @var string $var_4 Тип редактора*/

if(!isset($var_0,$var_1,$var_2,$var_3))
	throw new EE('Incorrect editor input',EE::DEV);

#Smiles
$smiles='';
foreach($var_2 as $smile)
	if($smile['show'])
		$smiles.='<button data-em="'.reset($smile['emotion']).'" style="background-image: url('.T::$http['static'].$smile['path'].');"></button>';

if($smiles)
	$smiles=<<<HTML
<div class="bb-right">
	<div class="bb-combo dropdown dropup">
		<button data-toggle="dropdown" class="bb" title="Вставить смайл"><span class="ico-smile"></span></button>
		<div class="dropdown-menu smiles" id="smiles-{$var_0}">{$smiles}</div>
	</div>
</div>
HTML;
#/Smiles

#OwnBB
$ownbb='';
$n=0;

foreach($var_3 as $k=>$tag)
{
	switch($k)
	{
		case'script':
			$k='js';
			break;
		case'onlinevideo':
			$k='youtube';
	}

	$title=$tag['l'] ? ' title="'.$tag['l'].'"' : '';
	$ownbb.=<<<HTML
<button class="bb"{$title} data-code="{$tag['t']}" data-single="{$tag['s']}"><span class="ico-{$k}"></span></button>
HTML;

	if(++$n%4==0)
		$ownbb.='<span class="bb-sep"></span>';
}

if(++$n%4==0)
	$ownbb.='<span class="bb-sep"></span>';

if($var_3)
	$ownbb.=<<<HTML
<div class="dropdown dropup bb-combo">
	<button class="bb" title="Вставка блоков" data-toggle="dropdown" data-code="special-block"><span class="ico-plus"></span></button>
	<ul class="dropdown-menu bb-custombbcode" id="blocks-{$var_0}">
		<li><a href="#" data-code="slider">Вставить слайдер</a></li>
		<li class="divider"></li>
		<li><a href="#" data-code="1/4">Вставить колонку 1/4</a></li>
		<li><a href="#" data-code="1/3">Вставить колонку 1/3</a></li>
		<li><a href="#" data-code="1/2">Вставить колонку 1/2</a></li>
	</ul>
</div>
HTML;
#/OwnBB

$foot=$smiles|| $ownbb ? '<div class="bb-foot" id="foot-'.$var_0.'">'.$smiles.$ownbb.'</div>' : '';

if($var_4=='ckeditor')
	$GLOBALS['head']['ckeditor_admin']='<script>CKEDITOR_CONFIG.uiColor="#EBEBEB";</script>';

echo<<<HTML
<div class="bb-editor">{$var_1}{$foot}</div>
<script>
$(function(){
	$("#smiles-{$var_0} button").click(function(e){
		e.preventDefault();
		EDITOR.Insert(" "+$(this).data("em")+" ","",false,"{$var_0}");
	});

	$("#foot-{$var_0} > button").click(function(e){
		var th=$(this),
			c=th.data("code");

		EDITOR.Insert("["+c+"]",th.data("single") ? "" : "[/"+c+"]",false,"{$var_0}");
		e.preventDefault();
	});

	$("#blocks-{$var_0} a").click(function(e){
		e.preventDefault();
		var c=$(this).data("code");
		EDITOR.Insert("[block-"+c+"]","[/block-"+c+"]",false,"{$var_0}");
	});
})</script>
HTML;
