<?php
namespace CMS;
use Eleanor\Classes\Html;

/** Bootstrap datetimepicker http://eonasdan.github.io/bootstrap-datetimepicker/
 * @var string $var_0 Имя элемента формы
 * @var string $var_1 Значение элемента
 * @var bool $var_2 Включение использования времени
 * @var array $var_3 Extra
 * @var bool $var_4 Флаг вывода кнопки вывода даты
 * @var string $var_5 JSON объект настройки и работы */
defined('CMS\STARTED')||die;

global$head,$scripts;

$use_lang=Language::$main!='english' ? substr(Language::$main,0,2) : false;
$time=!empty($var_2);

if(!isset($var_3))
	$var_3=[];

if(!isset($head['datetimepicker']))
{
	$head['datetimepicker']='<link href="//cdn.jsdelivr.net/bootstrap.datetimepicker/4/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>';

	$scripts[]='//cdn.jsdelivr.net/g/momentjs,bootstrap.datetimepicker@4';

	if($use_lang)
		$scripts[]=Template::$http['3rd'].'static/datetimepicker/bootstrap-datetimepicker.'.$use_lang.'.js';
}

if(!isset($var_3['id']))
	$var_3['id']=uniqid();

if(!isset($var_3['class']))
	$var_3['class']='form-control need-tabindex pim';

if(!isset($var_3['data-date-format']))
	$var_3['data-date-format']='YYYY-MM-DD'.($time ? ' HH:mm' : '');

if(!isset($var_4))
	$var_4=true;

$input=Html::Input(isset($var_0) ? $var_0 : false,isset($var_1) ? $var_1 : false,$var_3);
$lang=$use_lang ? "{ locale: '{$use_lang}'}" : '{}';
$json=isset($var_5) ? (string)$var_5 : '{}';

if($var_4)
	echo<<<HTML
<div class="input-group date" id="picker-{$var_3['id']}">
	{$input}
	<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
</div>
<script>$(function(){ $("#picker-{$var_3['id']}").each(function(){
	var format=$(":text",this).data("date-format");
	$(this).datetimepicker( $.extend(format ? {format:format} : {},{$lang},{$json}) );
}) })
</script>
HTML;
else
	echo<<<HTML
{$input}
<script>$(function(){ $("#{$var_3['id']}").each(function(){
	var format=$(this).data("date-format");
	$(this).datetimepicker( $.extend(format ? {format:format} : {},{$lang},{$json}) );
}) })</script>
HTML;
