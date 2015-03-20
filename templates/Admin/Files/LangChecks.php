<?php
namespace CMS\Templates\Admin;
use \Eleanor\Classes\Html;

/** Элемент шаблона. Отображает языковые галочки для выбора языка(ов) материала
 * @var bool $var_0 Флаг отмеченности чекбокса галочкой "Для всех языков"
 * @var array $var_1 Отмеченные чекбоксы языков. По умолчанию язык считается включенным ['lang1'=>true,'lang2'=>false]
 * @var string $var_2 Имя переменной JavaScript объекта управления галочками */
defined('CMS\STARTED')||die;

$GLOBALS['scripts'][]=T::$http['static'].'js/multilang.js';
$one=isset($var_0) ? $var_0 : false;
$langs=isset($var_1) ? (array)$var_1 : [];
$name=isset($v_2) ? $v_2.'=MC;' : '';

$check=Html::Check('single-lang',$one,['id'=>'single-lang','class'=>'need-tabindex']);
$t_lang=T::$lang;

echo<<<HTML
	<fieldset><div class="checkbox"><label>{$check} <b>{$t_lang['for_all_langs']}</b></label></div>
HTML;

foreach(\CMS\Eleanor::$langs as $k=>$v)
{
	$check=Html::Check('language[]',in_array($k,$langs),['value'=>$k,'class'=>'need-tabindex']);
	echo<<<HTML
	<div class="checkbox" lang="{$v['d']}"><label>{$check} {$v['name']}</label></div>
HTML;
}

echo<<<HTML
	</fieldset><script>$(function(){
var MC=new MultilangChecks();
MC.opts.general.click(function(){
	MC.Click();

	if(this.checked)
		MC.opts.langs.closest("div").fadeOut("fast");
	else
		MC.opts.langs.closest("div").fadeIn("fast");
});

if(MC.opts.general.prop("checked"))
	MC.opts.langs.closest("div").hide();

{$name}
})</script>
HTML
;