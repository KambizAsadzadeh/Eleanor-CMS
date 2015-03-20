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
		$opts=[];
		$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'modules` WHERE `services` LIKE \'%,index,%\' AND `status`=1');
		while($a=$R->fetch_assoc())
			$opts[$a['id']]=$a['title_l'] ? FilterLangValues((array)unserialize($a['title_l'])) : '';

		return$opts;
	},
];