<?php
defined('CMS\STARTED')||die;

/*
	Обертка для редакторов

	@var идентификатор редактора
	@var HTML код редактора
	@var массив кнопок внизу редактора
	@var массив смайлов
	@var массив "своих" BB кодов
*/
$id=&$var_0;
$html=&$var_1;
$smiles=&$v_2;
$ownbb=&$v_3;

$lang=Eleanor::$Language->Load($theme.'langs/editor-*.php',false);
foreach($smiles as $k=>&$v)
	if($v['show'])
		$v='<a href="#" style="background: transparent url('.$v['path'].') no-repeat 50% 50%;" data-em="'.reset($v['emotion']).'"></a>';
	else
		unset($smiles[$k]);

$obb='';
foreach($ownbb as &$v)
{
	$t=$v['l'] ? $v['l'] : $v['t'];
	$obb.='<a href="#" class="bb_'.$v['t'].'" onclick="EDITOR.Insert(\'['.$v['t'].']\',\''.($v['s'] ? '' : '[/'.$v['t'].']').'\',0,\''.$id.'\'); return false;" title="'.$t.'">'.$t.'</a>';
}

$sm=uniqid('sm-');
$GLOBALS['javascript'][]='js/dropdown.js';

echo'<div class="editor" id="ed-',$id,'">',$html;
if($obb or $smiles)
	echo'<div class="bb_fpanel clrfix">',
		$smiles
			? '<span class="bb_rpanel"><a title="'.$lang['smiles'].'" href="#" class="bb_smiles" id="a-'.$sm.'">'.$lang['smiles'].'</a></span>
<script>//<![CDATA[
$(function(){
	var D=new DropDown({
		selector:"#a-'.$sm.'",
		left:false,
		top:true,
		rel:"#'.$sm.'"
	});
	$("#'.$sm.' a").click(function(){
		EDITOR.Insert(" "+$(this).data("em")+" ","",false,"'.$id.'");
		D.hide();
		return false;
	});
});//]]></script><div class="smiles" id="'.$sm.'">'.join($smiles).'</div>'
		: '',
		$obb,
		'</div>';
echo'</div>';