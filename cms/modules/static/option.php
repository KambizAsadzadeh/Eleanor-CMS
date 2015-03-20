<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

return[
	'load'=>function($co){
		$config=include __DIR__.'/config.php';

		if(!class_exists('CMS\ApiStatic',false))
			include __DIR__.'/api.php';

		$Api=new ApiStatic($config);
		$list=$Api->GetSubstance();
		$now=$items='';

		foreach($list as $k=>$v)
			$items.=Html::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'›&nbsp;' : '')
				.$v['title'],$k,false,[],2);

		$value=$co['value'] ? explode(',',trim($co['value'],',')) : [];

		foreach($value as $v)
			if(isset($list[$v]))
				$now.=Html::Option($list[$v]['title'],$v,false,[],2);

		$u=uniqid();
		$select=Html::Select('',$items,['id'=>'sel-'.$u,'style'=>'float:left','class'=>'need-tabindex']);
		$input=Html::Input($co['controlname'],$co['value'],['id'=>'input-'.$u,'type'=>'hidden'])
			.Html::Select('',$now,['id'=>'res-'.$u,'style'=>'float:left','size'=>14,'class'=>'need-tabindex']);

		return<<<HTML
<div>{$select}<a href="#" id="add-{$u}" style="float:left;margin:0px 5px">Add</a></div>{$input}
<div style="float:left;padding:0px 5px;width:16px">
	<a href="#" id="up-{$u}">Up</a>
	<a href="#" id="down-{$u}">Down</a>
	<a href="#" id="del-{$u}">Del</a>
</div>
<script>//<![CDATA[
$(function(){
	var sel=$("#sel-{$u}"),
		add=$("#add-{$u}"),
		res=$("#res-{$u}"),
		input=$("#input-{$u}"),
		UpdateInput=function(){
			var arr=[];
			res.find("option").each(function(){
				arr.push($(this).val());
			});
			input.val(arr.join(","));
		},
		butt=$("#up-{$u},#down-{$u},#del-{$u}");

	sel.change(function(){
		if( res.find("[value="+$(this).val()+"]").prop("selected",true).size()>0 )
			add.hide();
		else
			add.show();
	}).change();

	res.change(function(){
		butt.hide();
		if($(this).val())
		{
			if($("option",this).size()==1)
				$("#del-{$u}").show();
			else if($("option:last",this).prop("selected"))
				$("#up-{$u},#del-{$u}").show();
			else if($("option:last",this).prop("selected"))
				$("#up-{$u},#del-{$u}").show();
			else if($("option:first",this).prop("selected"))
				$("#down-{$u},#del-{$u}").show();
			else
				butt.show();
		}
	}).change();

	add.click(function(){
		if(res.find("[value="+sel.val()+"]").size()==0)
			sel.find("option:selected:first").clone().each(function(){
				$(this).html($(this).html().replace(/^(&nbsp;|›)+/g,""));
			}).prop("selected",false).appendTo(res);
		res.change();
		add.hide();
		UpdateInput();
		return false;
	});

	$("#del-{$u}").click(function(){
		res.find("option:selected:first").remove();
		add.show();
		res.change();
		UpdateInput();
		return false;
	});

	$("#up-{$u}").click(function(){
		res.find("option:selected").each(function(){
			var th=$(this);
			if(th.prev().size()==0)
				return false;
			th.insertBefore(th.prev());
			UpdateInput();
		}).end().change();
		return false;
	});

	$("#down-{$u}").click(function(){
		res.find("option:selected").each(function(){
			var th=$(this);
			if(th.next().size()==0)
				return false;
			th.insertAfter(th.next());
			UpdateInput();
		}).end().change();
		return false;
	});

	res.filter("[disabled]").prop("disabled",false).find("option").remove();
	UpdateInput();
});//]]></script>
HTML;
	},
	'save'=>function($co,$Obj)
	{/** @var Controls $Obj */
		return$Obj->GetPostVal($co['name'],$co['default']);
	},
];