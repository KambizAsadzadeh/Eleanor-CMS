<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
defined('CMS\STARTED')||die;

return array(
	'language'=>Eleanor::$vars['multilang'] ? array(
		'title'=>$lang['language'],
		'descr'=>'',
		'type'=>'select',
		'post'=>&$Eleanor->sc_post,
		'options'=>array(
			'callback'=>function($a) use ($lang)
			{
				$sel=Eleanor::Option($lang['forallt'],'',in_array('',$a['value']));
				foreach(Eleanor::$langs as $k=>&$v)
					$sel.=Eleanor::Option($v['name'],$k,in_array($k,$a['value']));
				return$sel;
			},
			'extra'=>array(
				'tabindex'=>1
			),
		),
	) : null,
	'name'=>array(
		'title'=>$lang['tname'],
		'descr'=>'',
		'type'=>'input',
		'post'=>&$Eleanor->sc_post,
		'options'=>array(
			'safe'=>true,
			'extra'=>array(
				'tabindex'=>2
			),
		),
	),
);