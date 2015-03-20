<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;

/** Шаблон блока выбора темы оформления
 * @var array $var_0 возможные шаблонов, формат name=>название */
defined('CMS\STARTED')||die;

$opts='';
foreach($var_0 as $k=>$v)
	$opts.=Html::Option($v,$k,$k=='Uniel');?>
<div style="text-align:center"><?=Html::Select(false,$opts,['id'=>'themesel','style'=>'max-width:100%'])?>
</div><script>//<![CDATA[
$(function(){
	var n=localStorage.getItem("newtheme");

	if(n)
	{
		localStorage.removeItem("newtheme");
		if(n!=$("#themesel").val())
			window.location.reload();
	}

	$("#themesel").change(function(){
		var v=$(this).val();
		localStorage.setItem("newtheme",v);
		window.location="index.php?newtpl="+v;
	});
})//]]></script>