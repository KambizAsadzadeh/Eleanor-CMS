<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

return[
	'load'=>function($co,$Obj)
	{/** @var $Obj Controls */
		$GLOBALS['scripts'][]=Template::$http['3rd'].'static/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="'
			.Template::$http['3rd'].'static/autocomplete/style.css" />';

		if($co['post'])
			$value=$Obj->GetPostVal($co['name'],'');
		else
		{
			$value='';

			if($co['value'])
			{
				$co['value']=explode(',,',trim($co['value'],','));
				$R=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `id`'.Eleanor::$UsersDb->In($co['value']));
				while($a=$R->fetch_assoc())
					$value.=$a['name'].', ';

				$value=rtrim($value,', ');
			}
		}

		$u=uniqid();
		return \Eleanor\Classes\Html::Input($co['controlname'],$value,['id'=>$u])
		.'<script>//<![CDATA[
$(function(){
	$("#'.$u.'").autocomplete({
		serviceUrl:"'.Eleanor::$services['admin']['file'].'",
		minChars:2,
		delimiter:/,\s*/,
		params:{
			direct:"admin-autocomplete",
			goal:"users"
		}
	});
})//]]></script>';
	},
	'save'=>function($co,$Obj)
	{/** @var $Obj Controls */
		$value=$Obj->GetPostVal($co['name'],'');
		if($value=='')
			return'';

		$value=explode(',',$value);
		foreach($value as &$v)
			$v=trim($v);

		$R=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`'.Eleanor::$UsersDb->In($value));
		$value='';
		while($a=$R->fetch_assoc())
			$value.=','.$a['id'].',';

		return$value;
	}
];