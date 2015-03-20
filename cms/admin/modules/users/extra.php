<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

$lang=Eleanor::$Language['users'];
$IntSave=function($control)
{
	return abs((int)$control['value']);
};
$SaveNick=function($control){
	return preg_replace('#[^a-z0-9_\.-]+/#','',$control['value']);
};

return[
	$lang['personal'],
	'gender'=>[
		'title'=>$lang['gender'],
		'type'=>'select',
		'label-for'=>'gender',

		'options'=>[
			'options'=>[-1=>$lang['no-gender'],$lang['female'],$lang['male']],
			'extra'=>['class'=>'need-tabindex','id'=>'gender'],
		],
	],
	'bio'=>[
		'title'=>$lang['bio'],
		'type'=>'text',
		'label-for'=>'bio',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'bio'],
		],
	],
	'interests'=>[
		'title'=>$lang['interests'],
		'type'=>'text',
		'label-for'=>'interests',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'interests'],
		],
	],
	'location'=>[
		'title'=>$lang['location'],
		'type'=>'input',
		'label-for'=>'location',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'location'],
		],
	],
	'site'=>[
		'title'=>$lang['site'],
		'descr'=>$lang['site_'],
		'type'=>'input',
		'label-for'=>'site',
		'save'=>function($control)
		{
			if($control['value'] and !filter_var($control['value'],FILTER_VALIDATE_URL))
				throw new EE('SITE_ERROR',EE::USER);

			return$control['value'];
		},

		'options'=>[
			'type'=>'url',
			'safe'=>false,
			'extra'=>['class'=>'need-tabindex','id'=>'site'],
		],
	],
	'signature'=>[
		'title'=>$lang['signature'],
		'type'=>'editor',
		'label-for'=>'signature',
		'extra'=>['class'=>'need-tabindex','id'=>'signature'],
	],

	$lang['connect'],
	'jabber'=>[
		'title'=>'Jabber',
		'type'=>'input',
		'label-for'=>'jabber',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'jabber'],
		],
	],
	'skype'=>[
		'title'=>'Skype',
		'type'=>'input',
		'label-for'=>'skype',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'skype'],
		],
	],
	'icq'=>[
		'title'=>'ICQ',
		'type'=>'input',
		'label-for'=>'icq',
		'save'=>function($control)
		{
			$icq=preg_replace('#[^0-9\s,]+#','',$control['value']);
			if($icq and !isset($icq[4]))
				throw new EE('SHORT_ICQ',EE::USER);

			return$icq;
		},

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'icq'],
		],
	],
	'vk'=>[
		'title'=>$lang['vk'],
		'descr'=>$lang['vk_'],
		'type'=>'input',
		'label-for'=>'vk',
		'save'=>$SaveNick,

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'vk'],
		],
	],
	'facebook'=>[
		'title'=>'Facebook',
		'type'=>'input',
		'label-for'=>'facebook',
		'save'=>$SaveNick,

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'facebook'],
		],
	],
	'twitter'=>[
		'title'=>'Twitter',
		'descr'=>$lang['twitter_'],
		'type'=>'input',
		'label-for'=>'twitter',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex','id'=>'twitter'],
		],
	],

	Eleanor::$Language['main']['options'],
	'theme'=>[
		'title'=>$lang['theme'],
		'type'=>'select',
		'label-for'=>'theme',
		'options'=>[
			'callback'=>function($value) use ($lang)
			{
				$templates=Html::Option($lang['by_default'],'',in_array('',$value['value']));

				if(Eleanor::$vars['templates'] and is_array(Eleanor::$vars['templates']))
					foreach(Eleanor::$vars['templates'] as $v)
					{
						$setting=Template::$http['templates'].$v.'/settings.php';

						if(!file_exists($setting))
							continue;

						$setting=\Eleanor\AwareInclude($setting);
						$name=is_array($setting) && isset($setting['name']) ? $setting['name'] : $v;
						$templates.=Html::Option($name,$v,in_array($v,$value['value']));
					}

				return$templates;
			},
			'extra'=>['class'=>'need-tabindex','id'=>'theme'],
		],
	],
	'editor'=>[
		'title'=>$lang['editor'],
		'type'=>'select',
		'label-for'=>'editor',
		'options'=>[
			'callback'=>function()use($lang)
			{
				return [''=>$lang['by_default']]+Traits\Editor::$editors;
			},
			'extra'=>['class'=>'need-tabindex','id'=>'editor'],
		],
	],
];
