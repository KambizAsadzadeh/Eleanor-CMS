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
		$themes=[];
		$fs=glob(DIR.'../templates/*',GLOB_ONLYDIR);

		if($fs)
			foreach($fs as $v)
			{
				$temp=[];
				$sett=$v.'/settings.php';

				if(is_file($sett))
				{
					$temp=(array)include$sett;

					if(!isset($temp['service']) or !in_array('index',$temp['service'],true))
						continue;
				}

				$tpl=basename($v);
				$themes[$tpl]=isset($temp['title'])
					? (is_array($temp['title']) ? FilterLangValues($temp['title']) : $temp['title'])
					: $tpl;
			}

		return$themes;
	}
];