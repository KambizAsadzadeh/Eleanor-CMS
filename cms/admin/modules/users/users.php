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

		'options'=>[
			'options'=>[-1=>$lang['no-gender'],$lang['female'],$lang['male']],
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'bio'=>[
		'title'=>$lang['bio'],
		'type'=>'text',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'interests'=>[
		'title'=>$lang['interests'],
		'type'=>'text',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'location'=>[
		'title'=>$lang['location'],
		'type'=>'input',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'site'=>[
		'title'=>$lang['site'],
		'descr'=>$lang['site_'],
		'type'=>'url',
		'save'=>function($control)
		{
			if($control['value'] and !filter_var($control['value'],FILTER_VALIDATE_URL))
				throw new EE('SITE_ERROR',EE::USER);

			return$control['value'];
		},

		'options'=>[
			'type'=>'url',
			'safe'=>false,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'signature'=>[
		'title'=>$lang['signature'],
		'type'=>'editor',
		'extra'=>['class'=>'need-tabindex'],
	],

	$lang['connect'],
	'jabber'=>[
		'title'=>'Jabber',
		'type'=>'input',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'skype'=>[
		'title'=>'Skype',
		'type'=>'input',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'icq'=>[
		'title'=>'ICQ',
		'type'=>'input',
		'save'=>function($control)
		{
			$icq=preg_replace('#[^0-9\s,]+#','',$control['value']);
			if($icq and !isset($icq[4]))
				throw new EE('SHORT_ICQ',EE::USER);

			return$icq;
		},

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'vk'=>[
		'title'=>$lang['vk'],
		'descr'=>$lang['vk_'],
		'type'=>'input',
		'save'=>$SaveNick,

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'facebook'=>[
		'title'=>'Facebook',
		'type'=>'input',
		'save'=>$SaveNick,

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'twitter'=>[
		'title'=>'Twitter',
		'descr'=>$lang['twitter_'],
		'type'=>'input',

		'options'=>[
			'safe'=>true,
			'extra'=>['class'=>'need-tabindex'],
		],
	],

	Eleanor::$Language['main']['options'],
	'theme'=>[
		'title'=>$lang['theme'],
		'type'=>'select',

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
			'extra'=>['class'=>'need-tabindex'],
		],
	],
	'editor'=>[
		'title'=>$lang['editor'],
		'type'=>'select',

		'options'=>[
			'callback'=>function()use($lang)
			{
				return [''=>$lang['by_default']]+Traits\Editor::$editors;
			},
			'extra'=>['class'=>'need-tabindex'],
		],
	],
];
