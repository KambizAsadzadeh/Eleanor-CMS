<?php
defined('CMS\STARTED')||die;
/*
	Шаблон блока с опросом

	@var название опроса
	@var ссылка на подробную версию
	@var интерфейс опроса
*/
$u=uniqid('b');
$v_2->type='block';
$v_2->jparams=',AfterSwitch:'.$u;

$voting=(string)$v_2;
echo'<script>
var ',$u,'=$.Callbacks();
$(function(){
	var p=$("#',$u,'").parent();
	',$u,'.add(function(){
		p.find("a:first").prop("href","',$var_1,'").html("',$var_0,'");
	});
	p.end().remove();
	',$u,'.fire();
	',$u,'.add(function(){
		$(document).trigger("block-votings");
	});
})</script><i id="',$u,'"></i>',$voting;