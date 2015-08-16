<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, \CMS\Language, \Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Кнопка "Сохранить черновик".
 * @var string $var_0 URL, куда отправлять сохраняемые данные
 * @var array $var_1 Экстра параметры */

$url=isset($var_0) ? $var_0 : '';
$extra=isset($var_1) ? (array)$var_1 : [];

array_push($GLOBALS['scripts'],
	T::$http['static'].'js/drafts.js',
	T::$http['static'].'js/drafts-'.Language::$main.'.js');

if(!isset(Eleanor::$vars['drafts_autosave']))
	\CMS\LoadOptions('drafts');

$interval=Eleanor::$vars['drafts_autosave'];

if(!isset($extra['id']))
	$extra['id']=uniqid();

$GLOBALS['head']['draft']=<<<'HTML'
<script>
CORE.drafts=[];
$(function(){
	var lnk="",
		cnt,
		After=function(){
			if(--cnt==0)
				location.href=lnk;
		};

	//Кнопки переключения языков
	$("a.change-language").click(function(e){
		$.each(CORE.drafts,function(i,v){
			v.OnSave.add(After);
		});

		cnt=CORE.drafts.length;
		lnk=$(this).prop("href");

		$.each(CORE.drafts,function(i,v){
			if(v.changed)
				v.Save();
			else
				cnt--;
		});

		if(cnt>0)
			e.preventDefault();

		CORE.ShowLoading();

		$("a.change-language").off("click").click(function(){
			e.preventDefault();
		});
	});
})</script>
HTML;

	if(!isset($extra['class']))
		$extra['class']='btn btn-default need-tabindex';

	$id=$extra['id'];
	$extra=Html::TagParams($extra);

	echo<<<HTML
<button type="button"{$extra} disabled></button><script>
$(function(){
	var D{$id}=new CORE.DRAFT({
			form:$("#{$id}").closest("form"),
			url:"{$url}",
			enabled:false,
			interval:{$interval},
			OnSave:function(){
				$("#{$id}").text(CORE.Lang("draft_saved")).prop("disabled",true);
			},
			OnChange:function(){
				$("#{$id}").text(CORE.Lang("save_draft")).prop("disabled",false);
			}
		});

	CORE.drafts.push(D{$id});
	$("#{$id}").click(function(){
		D{$id}.Save();
	}).text(CORE.Lang("draft_saved"));

	//После того, как пройдут все события формы
	setTimeout(function(){
		D{$id}.enabled=true;
	},2500);
})</script>
HTML
;