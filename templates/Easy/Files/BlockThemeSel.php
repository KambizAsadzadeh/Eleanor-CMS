<?php
/*
	Шаблон блока выбора возможной темы оформления

	@var массив возможных шаблонов, формат name=>название
*/
if(!defined('CMS'))die;
$thm=basename(Eleanor::$Template->default['theme']);
$opts='';
foreach($var_0 as $k=>&$v)
	$opts.=Eleanor::Option($v,$k,$k==$thm);
echo Eleanor::Select(false,$opts,array('id'=>'themesel','style'=>'width:100%')).'<script type="text/javascript">//<![[CDATA[
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
})
//]]></script>';