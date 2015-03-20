<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use CMS\Template as Tpl;

defined('CMS\STARTED')||die;
#Old....
return[
	'form'=>function($a=[])
	{
		if(!is_array($a))
			$a=['action'=>$a];

		$a+=['method'=>'post'];

		return'<form'.Html::TagParams($a).'>';
	},
	'endform'=>function()
	{
		$tip=defined('POSHYTIP');

		if($tip)
			$GLOBALS['scripts'][]=Tpl::$http['static'].'js/jquery.poshytip.js';

		return'</form>'.($tip ? '<script>//<![CDATA[
$(function(){
	$("span.labinfo").poshytip({
		className: "tooltip",
		offsetX: -7,
		offsetY: 16,
		allowTipHover: false
	});
});//]]></script>' : '');
	},
	'begin'=>function()
	{
		$a=func_get_args();

		if(!isset($a[0]) or !is_array($a[0]))
			$a[0]=[];

		$a[0]+=['class'=>'tabstyle tabform'];

		return'<table'.Html::TagParams($a[0]).'>';
	},
	'head'=>function($a)
	{
		$a=func_num_args()>1 ? func_get_args() : (array)$a;
		$a+=['tr'=>[]];
		$a['tr']+=['class'=>'infolabel first'];
		return'<tr'.Html::TagParams($a['tr']).'>'
			.(empty($a[1]) ? '<td colspan="2">'.$a[0].'</td>' : '<td>'.$a[0].'</td><td>'.$a[1].'</td>').'</tr>';
	},
	'item'=>function($a)
	{
		if(func_num_args()>1)
			$a=func_get_args();

		if(!isset($a['tr']) or !is_array($a['tr']))
			$a['tr']=[];

		if(!isset($a['td1']) or !is_array($a['td1']))
			$a['td1']=[];

		$a['td1']+=['class'=>'label'];

		if(!isset($a['td2']) or !is_array($a['td2']))
			$a['td2']=[];

		$t=!empty($a['tip']);

		if($t)
			defined('POSHYTIP')||define('POSHYTIP',1);

		return'<tr'.Html::TagParams($a['tr']).'><td'.Html::TagParams($a['td1']).'>'
			.($t ? '<span class="labinfo" title="'.htmlspecialchars($a['tip'],\ENT_COMPAT,\Eleanor\CHARSET)
			.'">(?)</span> ' : '').$a[0].(empty($a['imp']) ? '' : ' <span class="imp">*</span>')
			.(empty($a['descr']) ? '' : '<br /><span class="small">'.$a['descr'].'</span>').'</td>'
			.(isset($a[1]) ? '<td'.Html::TagParams($a['td2']).'>'.$a[1].'</td>' : '').'</tr>';
	},
	'button'=>'<tr><td colspan="2" style="text-align:center">{0}</td></tr>',
	'submitline'=>'<div class="submitline">{0}</div>',
	'end'=>'</table>',
	'tabs'=>function()
	{static$n=0;
		$GLOBALS['scripts'][]=Tpl::$http['static'].'js/tabs.js';
		$tabs=func_get_args();

		if(count($tabs)==1 and isset($tabs[0]) and is_array($tabs[0]))
			$tabs=$tabs[0];

		$top=$c='';
		$first=true;

		foreach($tabs as &$tab)
			if(is_array($tab) and isset($tab[0],$tab[1]))
			{
				$id=isset($tab['id']) ? $tab['id'] : 'tab'.$n++;
				$top.='<li><a href="#" data-rel="'.$id.'"'.($first ? ' class="selected"' : '')
					.'><b>'.$tab[0].'</b></a></li>';
				$c.='<div id="'.$id.'" class="tabcontent">'.$tab[1].'</div>';
				$first=false;
			}

		$u=uniqid();

		return'<ul id="'.$u.'" class="linetabs">'.$top.'</ul>'.$c
			.'<script>/*<![CDATA[*/$(function(){$("#'.$u.' a").Tabs();});//]]></script>';
	},
];