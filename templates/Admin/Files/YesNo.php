<?php
/*
	Элемент шаблона. Формирует визуальное представление понятий "да" и "нет" (включено или выключено).

	@var флаг "да" или "нет"
*/
defined('CMS\STARTED')||die;
$yes=!empty($var_0);
$t=$yes ? T::$lang['yes'] : T::$lang['no'];
return'<img src="'.($yes ? Eleanor::$Template->default['theme'].'images/active.png' : Eleanor::$Template->default['theme'].'images/inactive.png').'" alt="" title="'.$t.'" />';