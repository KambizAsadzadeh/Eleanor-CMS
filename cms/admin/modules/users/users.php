<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$lang=Eleanor::$Language->Load(DIR.'admin/translation/users-groups-*.php');
$IntSave=function($control)
{
	return abs((int)$control['value']);
};

function SaveVK($a)
{
	return preg_replace('#[^a-z0-9_\.-]+/#','',$a['value']);
}

return array(
	$lang['personal'],
	'gender'=>array(
	'title'=>$lang['gender'],
	'descr'=>'',
	'type'=>'select',
	
	'options'=>array(
		'options'=>array(-1=>$lang['nogender'],$lang['female'],$lang['male']),
		'extra'=>array(
			'tabindex'=>15,
		),
	),
),
	'bio'=>array(
	'title'=>$lang['bio'],
	'descr'=>'',
	'type'=>'text',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>16,
		),
	),
),
	'interests'=>array(
	'title'=>$lang['interests'],
	'descr'=>'',
	'type'=>'text',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>17,
		),
	),
),
	'location'=>array(
	'title'=>$lang['location'],
	'descr'=>'',
	'type'=>'input',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>18,
		),
	),
),
	'site'=>array(
	'title'=>$lang['site'],
	'descr'=>$lang['site_'],
	'type'=>'input',
	'save'=>function($a,$Obj)
	{
		if($a['value'] and !filter_var($a['value'],FILTER_VALIDATE_URL))
			$Obj->errors[]='SITE_ERROR';
		else
			return$a['value'];
	},
	
	'options'=>array(
		'type'=>'url',
		'safe'=>false,
		'extra'=>array(
			'tabindex'=>19,
		),
	),
),
	'signature'=>array(
	'title'=>$lang['signature'],
	'descr'=>'',
	'type'=>'editor',
	
	'extra'=>array(
		'no'=>array('tabindex'=>20)
	)
),
	$lang['connect'],
	'jabber'=>array(
	'title'=>'Jabber',
	'descr'=>'',
	'type'=>'input',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>21,
		),
	),
),
	'skype'=>array(
	'title'=>'Skype',
	'descr'=>'',
	'type'=>'input',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>22,
		),
	),
),
	'icq'=>array(
	'title'=>'ICQ',
	'descr'=>'',
	'type'=>'input',
	'save'=>function($a,$Obj)
	{
		$v=preg_replace('#[^0-9\s,]+#','',$a['value']);
		if($v and !isset($v[4]))
			$Obj->errors[]='SHORT_ICQ';
		else
			return$v;
	},
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>23,
		),
	),
),
	'vk'=>array(
	'title'=>$lang['vk'],
	'descr'=>$lang['vk_'],
	'type'=>'input',
	'save'=>'SaveVK',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>24,
		),
	),
),
	'facebook'=>array(
	'title'=>'Facebook',
	'descr'=>'',
	'type'=>'input',
	'save'=>'SaveVK',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>25,
		),
	),
),
	'twitter'=>array(
	'title'=>'Twitter',
	'descr'=>$lang['twitter_'],
	'type'=>'input',
	
	'options'=>array(
		'safe'=>true,
		'extra'=>array(
			'tabindex'=>26,
		),
	),
),
	Eleanor::$Language['main']['options'],
	'theme'=>array(
	'title'=>$lang['theme'],
	'descr'=>'',
	'type'=>'select',
	
	'options'=>array(
		'callback'=>function($value) use ($lang)
		{
			$templates=Eleanor::Option($lang['by_default'],'',in_array('',$value['value']));
			if(Eleanor::$vars['templates'] and is_array(Eleanor::$vars['templates']))
				foreach(Eleanor::$vars['templates'] as &$v)
				{
					$f=Eleanor::$root.'templates/'.$v.'.php';
					if(!file_exists($f))
						continue;
					$a=include($f);
					$name=(is_array($a) and isset($a['name'])) ? $a['name'] : $v;
					$templates.=Eleanor::Option($name,$v,in_array($v,$value['value']));
				}
			return$templates;
		},
		'extra'=>array(
			'tabindex'=>27,
		),
	),
),
	'editor'=>array(
	'title'=>$lang['editor'],
	'descr'=>'',
	'type'=>'select',
	
	'options'=>array(
		'callback'=>function($value) use ($lang)
		{
			return array(''=>$lang['by_default'])+$GLOBALS['Eleanor']->Editor->editors;
		},
		'extra'=>array(
			'tabindex'=>28,
		),
	),
),
);
	
	
	[
	$lang['global-rights'],
	'is_admin'=>[
		'title'=>$lang['is-admin'],
		'descr'=>$lang['is-admin_'],
		'type'=>'check',
		'options'=>[
			'extra'=>['class'=>'need-tabindex','onclick'=>"if(this.checked) return confirm('{$lang['are_you_sure']}')"],
		],
	],
	'banned'=>[
		'title'=>$lang['ban'],
		'descr'=>$lang['ban_'],
		'type'=>'check',
		'options'=>[
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'captcha'=>[
		'title'=>$lang['captcha'],
		'descr'=>$lang['captcha_'],
		'type'=>'check',
		'options'=>[
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'moderate'=>[
		'title'=>$lang['moderate'],
		'descr'=>$lang['moderate_'],
		'type'=>'check',
		'options'=>[
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'closed_site_access'=>[
		'title'=>$lang['closed-site'],
		'descr'=>'',
		'type'=>'check',
		'options'=>[
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	
	$lang['limits'],
	'flood_limit'=>[
		'title'=>$lang['flood_limit'],
		'descr'=>$lang['flood_limit_'],
		'save'=>$IntSave,
		'type'=>'input',
		'default'=>0,
		'label-for'=>'flood-limit',
		'options'=>[
			'type'=>'number',
			'extra'=>['class'=>'need-tabindex','id'=>'flood-limit'],
		],
	],
	'search_limit'=>[
		'title'=>$lang['search_limit'],
		'descr'=>$lang['search_limit_'],
		'save'=>$IntSave,
		'type'=>'input',
		'default'=>0,
		'label-for'=>'search-limit',
		'options'=>[
			'type'=>'number',
			'extra'=>['class'=>'need-tabindex','id'=>'search-limit'],
		],
	],
	'max_upload'=>[
		'title'=>$lang['max_size'],
		'descr'=>$lang['max_size_'],
		'type'=>'input',
		'default'=>0,
		'label-for'=>'max-upload',
		'options'=>[
			'type'=>'number',
			'extra'=>['class'=>'need-tabindex','id'=>'max-upload'],
		],
	],
];