<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

return[
	'load'=>function($co)
	{
		if(!is_array($co['value']))
			$co['value']=[];

		$r='';
		$logins=glob(DIR.'logins/*.php');

		if($logins)
			foreach($logins as $v)
			{
				$v=basename($v,'.php');
				$uses='';

				foreach(Eleanor::$services as $sk=>$sv)
					if($sv['login']==$v)
						$uses.=$sk.', ';

				if($uses)
				{
					$uses=rtrim($uses,', ');
					$r.='<li style="margin-top:5px"><b>'.$uses.'</b>'.($uses==$v ? '' : ' ('.$v.')').':<br />'
						.Html::Input($co['controlname'].'['.$v.']',isset($co['value'][$v]) ? $co['value'][$v] : 900)
						.'</li>';
				}
			}

		return$r ? '<ul>'.$r.'</ul>' : '';
	},
	'save'=>function($co,$Obj)
	{/** @var $Obj Controls */
		$r=[];
		$data=$Obj->GetPostVal($co['name'],[]);

		$logins=glob(DIR.'logins/*.php');

		if($logins)
			foreach($logins as $v)
			{
				$v=basename($v,'.php');
				$r[$v]=isset($data[$v]) ? (int)$data[$v] : 900;
			}

		return$r;
	}
];