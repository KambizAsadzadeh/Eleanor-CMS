<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS;
use \Eleanor\Classes\Html;
defined('CMS\STARTED')||die;

$config=include DIR.'modules/menu/config.php';
$lang=Eleanor::$Language->Load(DIR.'modules/menu/admin-*.php',false);

return[
	'parent'=>[
		'title'=>$lang['parent'],
		'descr'=>'',
		'type'=>'select',
		'save'=>function($option)use($config)
		{
			return(int)$option['value'];
		},
		'options'=>[
			'exclude'=>0,
			'callback'=>function($option)use($config)
			{
				if(!class_exists('CMS\ApiMenu',false))
					include DIR.'modules/menu/api.php';

				$Api=new ApiMenu($config);
				$items=$Api->GetOrderedList();
				$options=Html::Option('&mdash;',0,in_array('',$option['value']),[],2);

				foreach($items as $id=>$item)
				{
					if($id==$option['options']['exclude'] or
						strpos(','.$item['parents'],','.$option['options']['exclude'].',')!==false)
						continue;

					$options.=Html::Option(($item['parents'] ? str_repeat('&nbsp;',substr_count($item['parents'],','))
						.'›&nbsp;' : '').$item['title'],$id,in_array($id,$option['value']),
						['style'=>$item['status']==0 ? 'color:gray;' : ''],2);
				}

				return$options;
			},
			'extra'=>[ 'class'=>'need-tabindex' ],
		],
	],
];