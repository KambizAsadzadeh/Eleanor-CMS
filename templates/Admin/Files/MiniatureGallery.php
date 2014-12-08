<?php
namespace CMS\Templates\Admin;

/** Элементы галереи
 * @var array $var_0 Перечень файлов
 * @var array $var_1 Перечень каталогов
 * @var string $var_2 Адрес возврата
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images
 * @var string $js Путь к каталогу js
 * @var string $ico Путь к каталогу ico */

defined('CMS\STARTED')||die;

$content='';

if(isset($var_1))
	foreach($var_1 as $k=>$v)
		$content.=<<<HTML
	<li class="gallery-item gallery-cat">
		<a href="#" data-path="{$k}">
			<img alt="{$v['title']}" src="{$v['http']}">
			<span><b>{$v['title']}</b></span>
		</a>
	</li>
HTML;

if(isset($var_0))
	foreach($var_0 as $k=>$v)
	{
		if(!isset($v['path']))
		{
			ksort($v,SORT_STRING);
			$v=end($v);#Здесь можно выбрать самую большУю превьюшку
		}

		$content.=<<<HTML
<li class="gallery-item">
	<a href="{$v['http']}" data-src="{$k}">
		<img alt="{$k}" src="{$v['http']}">
	</a>
</li>
HTML;
	}

$back=isset($var_2)
	? '<p><button class="btn btn-default" type="button" data-path="'.$var_2.'">'.T::$lang['go-back'].'</button></p>'
	: '';
echo<<<HTML
{$back}
<ul class="gallery gallery-5">{$content}</ul>
HTML
;
