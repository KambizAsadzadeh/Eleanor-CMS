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

if(isset(T::$data['speedbar']))
{
	#Smiles
	$smiles='';
	foreach($var_2 as $v)
		if($v['show'])
			$smiles.='<button data-em="'.reset($v['emotion']).'" style="background-image: url('.T::$http['static'].$v['path'].');"></button>	';

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

	foreach($var_3 as $k=>$v)
	{
		switch($k)
		{
			case'script':
				$k='js';
			break;
			case'onlinevideo':
				$k='youtube';
		}

		$ownbb.='<button class="bb"'.($v['l'] ? ' title="'.$v['l'].'"' : '').' data-code="'.$v['t'].'" data-single="'.$v['s'].'"><span class="ico-'.$k.'"></span></button>';

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
<script>//<![CDATA[
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
})//]]></script>
HTML;
}
else
{
	$GLOBALS['scripts'][]=T::$http['static'].'js/dropdown.js';

	$smiles='';
	foreach($var_2 as$v)
		if($v['show'])
			$smiles.='<a href="#" style="background: transparent url('.T::$http['static'].$v['path']
				.') no-repeat 50% 50%;" data-em="'.reset($v['emotion']).'"></a>';

	$ownbb='';
	foreach($var_3 as $v)
		$ownbb.='<a href="#" class="bbe_ytext" onclick="EDITOR.Insert(\'['.$v['t'].']\',\''.($v['s'] ? '' : '[/'.$v['t'].']')
			.'\',0,\''.$var_0.'\'); return false;"'.($v['l'] ? ' title="'.$v['l'].'"' : '').'><span>['.$v['t'].']</span></a>';

	$sm=uniqid('sm-');
	$lsm=T::$lang['smiles'];

	if($ownbb or $smiles)
		echo'<div>',$var_1,
			$smiles
				? <<<HTML
<div class="bb_footpanel"><b><a href="#" id="a-{$sm}" class="bbf_smiles">{$lsm}</a></b></div>
<script>//<![CDATA[
$(function(){
	var D=new DropDown({
		selector:"#a-{$sm}",
		left:false,
		top:true,
		rel:"#{$sm}"
	});
	$("#{$sm} a").click(function(e){
		e.preventDefault();
		EDITOR.Insert(" "+$(this).data("em")+" ","",false,"{$var_0}");
		D.hide();
	});
})//]]></script><div class="bb_smiles" id="{$sm}">{$smiles}</div>
HTML
				: '',
				'<div class="bb_yourpanel">',$ownbb,'<div class="clr"></div></div></div>';
	else
		echo$var_1,'<div class="clr"></div>';
}