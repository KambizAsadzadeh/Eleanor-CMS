<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'callback'=>function()
	{
		$r=[];
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'groups`');
		while($a=$R->fetch_assoc())
			$r[$a['id']]=$a['title_l'] ? FilterLangValues((array)unserialize($a['title_l'])) : '';

		asort($r,SORT_STRING);

		return$r;
	}
];