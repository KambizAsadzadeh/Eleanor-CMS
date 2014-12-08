<?php
defined('CMS\STARTED')||die;

/** Внешний вид контрола "массив чекбоксов".
 * @var array @var_0 Чексбосы, ключи:
 *  [int 0] Непосредственно чекбокс
 *  [int 1] Название */

$html='';

foreach($var_0 as $v)
	$html.="<label>{$v[0]} {$v[1]}</label><br />";

return$html ? substr($html,0,-6) : '';