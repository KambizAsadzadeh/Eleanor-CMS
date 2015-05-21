<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
$lang=Eleanor::$Language->Load(DIR.'admin/translation/multisite-*.php','multisite');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Multisite.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;
$Eleanor->module['links']=[
	'main'=>(string)$Url,
	'options'=>$Url(['do'=>'options']),
];

$d=isset($_GET['do']) ? (string)$_GET['do'] : '';
switch($d)
{
	case'options':
		$Url->prefix.='do=options&amp;';
		$c=$Eleanor->Settings->Group('multisite');

		if($c)
			Response( Eleanor::$Template->Options($c) );
	break;
	default:
		if(AJAX)
		{
			$data=isset($_POST['data']) ? (array)$_POST['data'] : [];
			$pref=isset($_POST['prefix']) ? (string)$_POST['prefix'] : '';

			if(isset($data['host'],$data['user'],$data['pass'],$data['db']) and $data['host'] and $data['user'] and $data['db'])
				try
				{
					$data['now']=true;
					$Db=new MySQL($data);
				}
				catch(EE$E)
				{
					Error($lang['UNABLE_TO_CONNECT_TO_DB']);
					break;
				}
			else
			{
				if($pref==P)
				{
					Error($lang['THIS_SITE']);
					break;
				}

				$Db=Eleanor::$Db;
			}

			if(strpos($pref,'`.`')!==false)
				list($db,$pref)=explode('`.`',$pref,2);
			else
				$db=false;

			$R=$Db->Query('SHOW TABLES'.($db ? " FROM `{$db}`" : '').' LIKE \''.$Db->Escape($pref,false).'multisite_jump\'');
			if($R->num_rows>0)
				Response(true);
			else
				Error($lang['MULTI_SITE_TABLE_WAS_NOT_FOUND']);

			break;
		}

		$controls=[
			'site',
			'title'=>[
				'title'=>$lang['site-name'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'save'=>function($control) use ($lang)
				{
					if($control['multilang'])
					{
						foreach($control['value'] as $k=>&$v)
							if($v=='')
								if($k==Language::$main or !isset($control['value'][ Language::$main ]))
									throw new EE($lang['EMPTY_SITE_NAME'],EE::USER);
								else
									$v=$control['value'][ Language::$main ];

						return$control['value'];
					}

					if($control['value']=='')
						throw new EE($lang['EMPTY_SITE_NAME'],EE::USER);

					return[''=>$control['value']];
				},
				'load'=>function($control)
				{
					if($control['multilang'])
						return['value'=>(array)$control['value']];

					return['value'=>is_array($control['value']) ? FilterLangValues($control['value']) : $control['value']];
				},

				'options'=>[
					'safe'=>true,
					'extra'=>[
						'id'=>'title',
						'class'=>'need-tabindex',
						'required'=>true,
					],
				],
			],
			'url'=>[
				'title'=>$lang['site-url'],
				'descr'=>$lang['site-url_'],
				'default'=>\Eleanor\PROTOCOL,
				'type'=>'input',
				'save'=>function($control) use ($lang)
				{
					if(!filter_var($control['value'],FILTER_VALIDATE_URL))
						throw new EE($lang['WRONG_SITE_URL'],EE::USER);

					if(substr($control['value'],-1)!='/')
						$control['value'].='/';

					return$control['value'];
				},

				'options'=>[
					'extra'=>[
						'id'=>'uri',
						'type'=>'url',
						'data-default'=>\Eleanor\PROTOCOL,
						'class'=>'need-tabindex',
						'required'=>true,
					],
				],
			],
			'sync'=>[
				'title'=>$lang['sync'],
				'descr'=>$lang['sync_'],
				'type'=>'check',
				'default'=>false,

				'options'=>[
					'extra'=>[
						'id'=>'sync',
						'data-default'=>0,
						'class'=>'need-tabindex',
					],
				],
			],
			'secret'=>[
				'title'=>$lang['secret'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
						'id'=>'secret',
						'class'=>'need-tabindex',
						'required'=>true,
					],
				],
			],

			'db',
			'prefix'=>[
				'title'=>$lang['prefix'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
						'id'=>'prefix',
						'class'=>'db need-tabindex',
						'required'=>true,
					]
				]
			],
			'host'=>[
				'title'=>$lang['db-host'],
				'descr'=>$lang['db-host_'],
				'type'=>'input',
				'default'=>'localhost',

				'options'=>[
					'extra'=>[
						'id'=>'host',
						'class'=>'db need-tabindex',
						'data-default'=>'localhost',
					]
				]
			],
			'db'=>[
				'title'=>$lang['db-name'],
				'type'=>'input',
				
				'options'=>[
					'extra'=>[
						'id'=>'db',
						'class'=>'db need-tabindex',
					]
				]
			],
			'user'=>[
				'title'=>$lang['db-user'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
						'id'=>'user',
						'class'=>'db need-tabindex',
					]
				]
			],
			'pass'=>[
				'title'=>$lang['db-pass'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
						'id'=>'pass',
						'class'=>'db need-tabindex',
					]
				]
			],
		];

		$config=DIR.'config_multisite.php';
		$errors=[];

		if($post)
		{
			$keys=isset($_POST['sites']) ? array_keys((array)$_POST['sites']) : [];

			if($keys) try
			{
				$data=[];
				$n=0;

				foreach($keys as $site)
				{
					$Eleanor->Controls->name=['sites',$site];
					$data[++$n]=$Eleanor->Controls->SaveControls($controls);

					if(!$data[$n]['secret'])
					{
						$pref=isset($data[$n]['prefix']) ? (string)$data[$n]['prefix'] : '';

						if(isset($data[$n]['host'],$data[$n]['user'],$data[$n]['pass'],$data[$n]['db']) and $data[$n]['host'] and $data[$n]['user'] and $data[$n]['db'])
							$Db=new MySQL(['now'=>true]+$data[$n]);
						else
						{
							if($pref==P)
								throw new EE(sprintf($lang['THIS_SITE_'],isset($data[$n]['title']) && is_array($data[$n]['title'])
									? FilterLangValues($data[$n]['title']) : $data[$n]['title']),EE::USER);

							$Db=Eleanor::$Db;
						}

						if(strpos($pref,'`.`')!==false)
							list($db,$pref)=explode('`.`',$pref,2);
						else
							$db=false;

						$R=$Db->Query('SHOW TABLES'.($db ? " FROM `{$db}`" : '').' LIKE \''.$Db->Escape($pref,false).'multisite_jump\'');
						if($R->num_rows==0)
							throw new EE($lang['MULTI_SITE_TABLE_WAS_NOT_FOUND'],EE::UNIT);
					}
				}

				file_put_contents($config,'<?php return '.var_export($data,true).';');
			}
			catch(EE$E)
			{
				$errors['ERROR']=$E->getMessage();
			}
			else
				file_put_contents($config,'<?php
/*
	Смотрите пример заполнения этого файла в дистрибутиве системы, который можно взять по адресу https://eleanor-cms.ru
	See example of this file in distributive of Eleanor CMS https://eleanor-cms.ru
*/
return[];');
		}
		else
		{
			$data=is_file($config) ? (array)include$config : [];
			$keys=array_keys($data);
		}

		$Controls2Html=function()use($keys,$controls){
			if(!$keys)
				$keys=[0];

			$sites=[];

			foreach($keys as $key)
			{
				$values=[];

				if(isset($data[$key]))
					foreach($data[$key] as $id=>$site)
						$values[$id]['value']=$site;

				$C=new Controls;
				$C->name=['sites',$key];

				$control2=$controls;
				foreach($control2 as $k=>&$v)
				{
					if(!is_array($v) or !isset($v['options']['extra']['id']))
						continue;

					if(empty($v['multilang']))
						$v['options']['extra']['id'].='-'.$key;
					else
					{
						$values[$k]['options']=array_fill_keys(array_keys(Eleanor::$langs), $v['options']);

						foreach($values[$k]['options'] as $l=>&$opts)
							$opts['extra']['id'].="-{$l}-".$key;
					}
				}

				unset($opts,$v);

				$sites[$key]=$C->DisplayControls($control2,$values);
			}

			return$sites;
		};

		$title[]=$lang['config'];
		$c=Eleanor::$Template->Multisite($Controls2Html,$controls,$errors,$post && !$errors);
		Response($c);
}