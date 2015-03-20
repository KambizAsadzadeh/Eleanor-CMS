<?php
/*
	Элемент шаблона: оформление тегов.

	@var array(
		_a - ссылка на материалы с этим тегом
		cnt - количество материалов с этим тегом
		name - название тега
	)
*/
if(!defined('CMS'))die;
if(!isset($cnt) or $cnt<5)
	$size='xsmall';
elseif($cnt>=5 and $cnt<10)
	$size='small';
elseif($cnt>=10 and $cnt<15)
	$size='medium';
elseif($cnt>=15 and $cnt<20)
	$size='large';
elseif($cnt>=20)
	$size='xlarge';
echo'<a href="'.$_a.'" class="clouds_'.$size.'" rel="tag">'.$service.'</a>';