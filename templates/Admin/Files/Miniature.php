<?php
namespace CMS\Templates\Admin;

/** Шаблон интерфейса миниатюры
 * @var array $var_0 Текущее значение
 * ['type'=>'gallery|upload|link','path'=>'...','http'=>'...','src'=>'Значение для POST запроса','post'=>true|null] либо []
 * @var array|string $var_1 Галерея, либо ссылка на загрузку галереи
 * @var string $var_2 Имя элемента, по умолчанию - miniature
 * @var string $var_3 Максимально допустимый размер файла для загрукзи
 * @var string $var_4 Название элемента
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $js Путь к каталогу js
 * @var string $ico Путь к каталогу ico */
use CMS\Language, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

$GLOBALS['head']['fancybox.css']='<link rel="stylesheet" href="//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.css" type="text/css" media="screen" />';
array_push($GLOBALS['scripts'],
	'//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.pack.js',
	$js.'miniature.js',
	$js.'miniature-'.Language::$main.'.js');

$name=isset($var_2) ? (string)$var_2 : 'miniature';

#Параметры галереи
if(isset($var_1) and $var_1)
{
	$gallery=is_array($var_1) ? T::$T->MiniatureGallery($var_1,null) : '';
	$loadlink=is_string($var_1) ? ',"'.$var_1.'"' : '';
}
else
	$loadlink=$gallery='';
#/Параметры галереи

#Параметры скрытх полей type и src
$type=$src=['disabled'=>true];

#Параметры картинки
$img=[];
if($var_0)
{
	if(!isset($var_0['http']))
	{
		ksort($var_0,SORT_STRING);
		$var_0=end($var_0);
	}

	$img['src']=$var_0['http'];

	if(isset($var_0['post']))
	{
		unset($type['disabled'],$src['disabled']);
		$type['value']=$var_0['type'];
		$src['value']=$var_0['type']=='link' ? $var_0['http'] : $var_0['src'];
	}
}
else
{
	$img['src']=T::$http['static'].'images/spacer.png';
	$img['data-empty']=1;
}
#/Параметры картинки

$img=Html::TagParams($img);
$src=Html::TagParams($src);
$type=Html::TagParams($type);
$lang=\CMS\Eleanor::$Language->Load(__DIR__.'/../translation/miniature-*.php',false);
$t_lang=T::$lang;
$title=isset($var_4) ? $var_4 : $lang['miniature'];
echo<<<HTML
<!-- Миниатюра -->
<div class="block-t expand">
	<p class="btl" data-toggle="collapse" data-target="#{$name}">{$title}</p>
	<div id="{$name}" class="collapse in">
		<input type="file" accept="image/gif,image/png,image/jpg,image/jpeg" style="display:none" />
		<input type="hidden" name="{$name}[type]"{$type} />
		<input type="hidden" name="{$name}[src]"{$src} />

		<div class="bcont">
			<div class="thumbnails-frame">
				<a href="#"><img alt=""{$img}></a>
			</div>
			<button class="pull-right ibtn ib-delete" type="button"><i class="ico-del"></i><span class="thd">{$t_lang['delete']}</span></button>
			<div class="btn-group">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">{$t_lang['modify']} <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="#" class="select-file">{$lang['upload']}</a></li>
					<li><a data-toggle="modal" href="#{$name}-gallery">{$lang['from-gallery']}</a></li>
					<li><a href="#" class="by-link">{$lang['link']}</a></li>
				</ul>
			</div>
		</div>

		<div class="bcont">
			<div class="upl-loadframe center">
				<button class="btn btn-primary select-file"><b>{$lang['select-files']}</b></button>
				<p class="upl-loadframe-text">
					<b>{$lang['drag-and-drop']}</b><br>
					<small>{$lang['img-formats']}</small>
				</p>
			</div>
			<button class="pull-right btn btn-default by-link">{$lang['link']}</button>
			<button class="btn btn-default" data-toggle="modal" data-target="#{$name}-gallery" type="button">{$lang['from-gallery']}</button>
		</div>
	</div>
</div>
<script>$(function(){ Miniature("{$name}",{$var_3}{$loadlink}) })</script>
<!-- Окно галереи -->
<div class="modal fade bs-example-modal-lg" id="{$name}-gallery" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="gallery-label">{$lang['gallery']}</h4>
			</div>
			<div class="modal-body">{$gallery}</div>
		</div>
	</div>
</div>
<!-- /Миниатюра -->
HTML
;
