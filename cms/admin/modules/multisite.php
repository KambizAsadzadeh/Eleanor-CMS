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
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
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
			$pref=isset($data['prefix']) ? (string)$data['prefix'] : '';

			if(isset($data['host'],$data['user'],$data['pass'],$data['db']))
				try
				{
					$Db=new MySQL($data);
				}
				catch(EE$E)
				{
					Response('connect');
					break;
				}
			else
			{
				if($pref==P)
				{
					Response('prefix');
					break;
				}

				$Db=Eleanor::$Db;
			}

			if(strpos($pref,'`.`')!==false)
				list($db,$pref)=explode('`.`',$pref,2);
			else
				$db=false;

			$R=$Db->Query('SHOW TABLES'.($db ? " FROM `}$db}`" : '').' LIKE \''.$Db->Escape($pref,false).'multisite_jump\'');
			Response($R->num_rows>0 ? false : 'table');

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
						'class'=>'db need-tabindex',
					]
				]
			],
			'user'=>[
				'title'=>$lang['db-user'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
						'class'=>'db need-tabindex',
					]
				]
			],
			'pass'=>[
				'title'=>$lang['db-pass'],
				'type'=>'input',

				'options'=>[
					'extra'=>[
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

				foreach($keys as $id=>$site)
				{
					$Eleanor->Controls->name=['sites',$site];
					$data[$id]=$Eleanor->Controls->SaveControls($controls);

					if(!$data[$id]['secret'])
					{
						$pref=isset($data[$id]['prefix']) ? (string)$data[$id]['prefix'] : '';

						if(isset($data[$id]['host'],$data[$id]['user'],$data[$id]['pass'],$data[$id]['db']))
							$Db=new MySQL($data[$id]);
						else
						{
							if($pref==P)
								throw new EE(sprintf($lang['THIS_SITE'],isset($data[$id]['title']) && is_array($data[$id]['title'])
									? FilterLangValues($data[$id]['title']) : $data[$id]['title']),EE::USER);

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

				$sites[$key]=$C->DisplayControls($controls,$values);
			}

			return$sites;
		};

		$title[]=$lang['config'];
		$c=Eleanor::$Template->Multisite($Controls2Html,$controls,$errors);
		Response($c);
}