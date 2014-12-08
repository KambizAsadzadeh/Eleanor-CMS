<?php
namespace CMS\Templates\Admin;
use \CMS\Language, \CMS\Eleanor;

/** Элемент шаблона. Отображение мультиязычных контролов в виде табов
 * @var array|string $var_0 ['russuan'=>'russian content','english'=>'english content',...]
 * @var string $var_1 Идентификатор таблов */
defined('CMS\STARTED')||die;
$tabs=isset($var_0) ? $var_0 : [];

if(is_array($tabs))
{
	if(!isset($var_1))
	{
		if(!isset(T::$data['lang-edit']))
			T::$data['lang-edit']=0;

		$var_1='tab-'.++T::$data['lang-edit'];
	}

	echo'<div class="sel-lang tab-content">';

	$flags='';
	$f_path=T::$http['static'].'images/flags/';

	foreach($tabs as $k=>$v)
	{
		if(!$k)
			$k=Language::$main;

		$id='tab-'.$var_1.'-'.$k;
		$active=$k==Language::$main ? ' active' : '';
		$name=Eleanor::$langs[$k]['name'];

		echo<<<HTML
<div id="{$id}" class="tab-pane{$active}">{$v}</div>
HTML;

		if($active)
			$active=' class="active"';

		$flags.=<<<HTML
<li{$active}><a href="#{$id}" class="{$k}" id="a-{$id}" data-toggle="tab" data-language="{$k}" title="{$name}"><img src="{$f_path}{$k}.png" alt="" /></a></li>
HTML;
	}

	echo'</div><ul class="lang-tabs" data-for="',$var_1,'">',$flags,'</ul>';
}
else
	echo$tabs;