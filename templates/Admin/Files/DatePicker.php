<?php
namespace CMS;
use Eleanor\Classes\Html;

/** Bootstrap datetimepicker http://eonasdan.github.io/bootstrap-datetimepicker/
 * @var string $var_0 Имя элемента формы
 * @var string $var_1 Значение элемента
 * @var bool $var_2 Включение использования времени
 * @var array $var_3 Extra
 * @var bool $var_4 Флаг вывода кнопки вывода даты */
defined('CMS\STARTED')||die;

global$head,$scripts;

$use_lang=Language::$main!='english' ? substr(Language::$main,0,2) : false;
$time=!empty($var_2);

if(!isset($var_3))
	$var_3=[];

if(!isset($head['datetimepicker']))
{
	$head['datetimepicker']='<link href="//cdn.jsdelivr.net/bootstrap.datetimepicker/3/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>';

	$scripts[]='//cdn.jsdelivr.net/g/momentjs';
	$scripts[]='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js';

	if($use_lang)
		$scripts[]=Template::$http['3rd'].'static/datetimepicker/bootstrap-datetimepicker.'.$use_lang.'.js';
}

if(!isset($var_3['id']))
	$var_3['id']=uniqid();

if(!isset($var_3['class']))
	$var_3['class']='form-control';

if(!isset($var_3['data-date-format']))
	$var_3['data-date-format']='YYYY-MM-DD'.($time ? ' HH:mm' : '');

if(!isset($var_4))
	$var_4=true;

$input=Html::Input(isset($var_0) ? $var_0 : false,isset($var_1) ? $var_1 : false,$var_3);
$lang=$use_lang ? '{ language: "'.$use_lang.'"}' : '';

if($var_4)
	echo<<<HTML
<div class="input-group date" id="picker-{$var_3['id']}">
	{$input}
	<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
</div>
<script>/*<[!CDATA[*/$(function(){ $("#picker-{$var_3['id']}").datetimepicker({$lang}) })//]]></script>
HTML;
else
	echo<<<HTML
{$input}
<script>/*<[!CDATA[*/$(function(){ $("#{$var_3['id']}").datetimepicker({$lang}) })//]]></script>
HTML;
