<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'callback'=>function($co)
	{
		return \Eleanor\Classes\Types::TimeZonesOptions($co['value']);
	},
];