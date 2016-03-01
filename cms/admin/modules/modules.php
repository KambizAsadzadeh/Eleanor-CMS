<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files;
use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
$lang=Eleanor::$Language->Load(DIR.'admin/translation/modules-*.php','modules');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Modules.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$table=P.'modules';
$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;
$id=0;
$Eleanor->module=[
	'title'=>Eleanor::$Language['main']['modules'],
	'links'=>[
		'list'=>(string)$Url,
		'create'=>$Url(['do'=>'create']),
	]
];

#Фикс, если модуль не загружается
if(isset($_GET['module']))
	return GoAway(true);

/** Формирование значения миниатюры для шаблона
 * @param array $a Входящие данные
 * @return array*/
function Miniature(array$a)
{
	$image=false;

	if($a['miniature'] and $a['miniature']) switch($a['miniature_type'])
	{
		case'gallery':
			if(strpos($a['miniature'],'*')!==false)
			{
				$files=glob(Template::$path['static'].'images/modules/'.$a['miniature']);

				if($files)
					foreach($files as $v)
					{
						$bn=basename($v);
						$ext=strrchr($bn,'.');
						$compose=preg_match('#\-(\d+x\d+)\\'.$ext.'$#',$bn,$m)>0;

						if($compose)
						{
							if(!is_array($image))
								$image=[];

							$image[ $m[1] ]=[
								'path'=>$v,
								'http'=>Template::$http['static'].'images/modules/'.$bn,
							];
						}
						else
							$image=[
								'path'=>$v,
								'http'=>Template::$http['static'].'images/modules/'.$bn,
							];
					}
			}
			elseif(is_file($f=Template::$path['static'].'images/modules/'.$a['miniature']))
				$image=[
					'type'=>'gallery',
					'path'=>$f,
					'http'=>Template::$http['static'].'images/modules/'.$a['miniature'],
					'src'=>$a['miniature'],
				];
		break;
		case'upload':
			if(is_file($f=Template::$path['uploads'].'modules/'.$a['miniature']))
				$image=[
					'type'=>'upload',
					'path'=>$f,
					'http'=>Template::$http['uploads'].'modules/'.$a['miniature'],
					'src'=>$a['miniature'],
				];
		break;
		case'link':
			$image=[
				'type'=>'link',
				'http'=>$a['miniature'],
			];
	}

	return$image;
}

if(isset($_GET['do']))switch($_GET['do'])
{
	case'create':
		goto CreateEdit;
	break;
	default:
		GoAway(true);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$uid=(int)Eleanor::$Login->Get('id');
	$maxupload=Eleanor::$Permissions ? Eleanor::$Permissions->MaxUpload() : Files::SizeToBytes(ini_get('upload_max_filesize'));
	$gallery=[
		'path'=>Template::$path['static'].'images/modules/',
		'http'=>Template::$http['static'].'images/modules/',
	];

	if(AJAX)
	{
		if(isset($_FILES['miniature']) and is_uploaded_file($_FILES['miniature']['tmp_name']) and $_FILES['miniature']['size']<=$maxupload
			and preg_match('#\.(png|jpe?g|gif)$#',$_FILES['miniature']['name']) and getimagesize($_FILES['miniature']['tmp_name']))
		{
			$temp="modules-{$id}-{$uid}".strrchr($_FILES['miniature']['name'],'.');
			$temppath=Template::$path['uploads'].'temp/';

			if(!is_dir($temppath))
				Files::MkDir($temppath);

			$temppath.=$temp;

			if(is_file($temppath))
				Files::Delete($temppath);

			if(move_uploaded_file($_FILES['miniature']['tmp_name'],$temppath))
				Response([
					'http'=>Template::$http['uploads'].'temp/'.$temp,
					'src'=>$temp,
				]);
			else
				Error('Unknown event');
		}
		elseif(isset($_POST['miniature-gallery']))
		{
			$items=[];
			$files=glob($gallery['path'].'*.{png,jpeg,gif,jpg}',GLOB_BRACE);

			if($files)
				foreach($files as $v)
				{
					$bn=basename($v);
					$compose=preg_match('#(.+?)\-(\d+x\d+)\.(png|jpe?g|gif)$#',$bn,$m)>0;
					$name=$compose ? $m[1].'-*.'.$m[3] : $bn;

					if($compose)
					{
						if(!isset($items[$name]) or !is_array($items[$name]))
							$items[$name]=[];

						$items[$name][ $m[2] ]=[
							'path'=>$v,
							'http'=>$gallery['http'].$bn,
						];
					}
					else
						$items[$name]=[
							'path'=>$v,
							'http'=>$gallery['http'].$bn,
						];
				}

			Response( Eleanor::$Template->MiniatureGallery($items,null) );
		}
		elseif(isset($_GET['config'],$_GET['path']))
		{
			$uris=$values=[];
			$config=is_file($v=DIR.rtrim((string)$_GET['path'],'/\\').'/'.(string)$_GET['config'])
				? \Eleanor\AwareInclude($v)
				: false;

			if(is_array($config) and isset($config['sections']))
				foreach((array)$config['sections'] as $k=>$v)
					$uris[ is_int($k) ? $v : $k ]=is_scalar($v) ? $v : FilterLangValues($v);

			$def=Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : [];

			if($uris)
				foreach($uris as $k=>$v)
					$values[$k]=$def;
			else
			{
				$uris=['main'=>'Main'];
				$values['main']=$def;
			}

			OutPut::SendHeaders('text');
			Output::Gzip( (string)Eleanor::$Template->AjaxUris($uris,$values) );
		}
		else
		{
			$path=isset($_GET['path']) ? trim((string)$_GET['path'],'/\\') : '';
			$path=preg_replace('#[^a-z0-9\-_\.\\\\/]+#i','',$path);
			$dirlen=strlen(DIR);
			$dirs=$files=[];

			if($path)
				$path.='/';

			if($items=glob(DIR.$path.'*',GLOB_MARK))
				foreach($items as $k=>$v)
					if(substr($v,-1)==DIRECTORY_SEPARATOR)
					{
						$v=str_replace('\\','/',$v);
						$v=substr($v,$dirlen);
						$dirs[$k]=addslashes($v);
					}
					elseif($path)
					{
						$v=str_replace('\\','/',$v);
						$v=basename($v);
						$files[$k]=addslashes($v);
					}

			OutPut::SendHeaders('json');
			Output::Gzip('{"dirs":['.($dirs ? '"'.join('","',$dirs).'"' : '').'],"files":['
				.($files ? '"'.join('","',$files).'"' : '').']}');
		}

		return 1;
	}

	$errors=$config=[];

	if($id)
	{
		$R=Eleanor::$Db->Query("SELECT * FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();

		$orig['uris']=$orig['uris'] ? json_decode($orig['uris'],true) : [];
	}

	if($post)
	{
		include_once DIR.'crud.php';
		$values=[];

		if(Eleanor::$vars['multilang'])
		{
			$title_=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : [];
			$descr=isset($_POST['descr']) ? (array)Eleanor::$POST['descr'] : [];
		}
		else
		{
			$title_=isset($_POST['title']) ? [''=>(string)Eleanor::$POST['title']] : [];
			$descr=isset($_POST['descr']) ? [''=>(string)Eleanor::$POST['descr']] : [];
		}

		foreach($title_ as $k=>&$v)
		{
			$v=trim($v);

			if($v=='')
				if($id or $k==Language::$main or !isset($title_[ Language::$main ]))
					$errors['EMPTY_TITLE'][]=$k;
				elseif($id)
					unset($title_[$k]);
				else
					$v=$title_[ Language::$main ];
		}

		if(!$id and !$title_)
			$errors['EMPTY_TITLE'][]=Language::$main;

		foreach($descr as $k=>&$v)
		{
			$v=trim($v);

			if($v=='')
				if(!$id and $k!=Language::$main and isset($descr[ Language::$main ]))
					$v=$descr[ Language::$main ];
				else
					unset($descr[$k]);
		}
		unset($v);

		if(isset($errors['EMPTY_TITLE']))
			$errors['EMPTY_TITLE']=$lang['EMPTY_TITLE']( $errors['EMPTY_TITLE']==[''] ? [] : $errors['EMPTY_TITLE'] );

		if($id)
		{
			$unlocked=!$orig['protected'];
			$values+=[
				'path'=>$orig['path'],
				'config'=>$orig['config'],
			];
		}
		else
		{
			$unlocked=true;
			$orig=['uris'=>[]];
		}

		if($unlocked)
		{
			if(isset($_POST['path']))
			{
				$values['path']=rtrim($_POST['path'],'/\\').'/';

				if(!$values['path'] or !is_dir(DIR.$values['path']))
					$errors[]='WRONG_PATH';
			}
			elseif(!$id)
				$errors[]='UNFILLED_PATH';

			if(isset($_POST['file']))
			{
				$values['file']=preg_replace('#(\.\.|[\\/]+)#','',(string)$_POST['file']);

				if(!$values['file'] or !is_file(DIR.$values['path'].$values['file']))
					$errors[]='FILE_DOES_NOT_EXISTS';
			}
			elseif(!$id)
				$errors[]='UNFILLED_FILE';

			if(isset($_POST['api']))
			{
				$values['api']=preg_replace('#(\.\.|[\\/]+)#','',(string)$_POST['api']);

				if($values['api'] and !is_file(DIR.$values['path'].$values['api']))
					$errors[]='API_DOES_NOT_EXISTS';
			}

			if(isset($_POST['config']))
			{
				$values['config']=preg_replace('#(\.\.|[\\/]+)#','',(string)$_POST['config']);

				if($values['config'] and !is_file(DIR.$values['path'].$values['config']))
					$errors[]='CONFIG_DOES_NOT_EXISTS';
			}
		}

		$uris=$services=[];
		$config=$values['path'] && !empty($values['config']) && is_file($f=DIR.$values['path'].'/'.$values['config'])
			? \Eleanor\AwareInclude($f)
			: false;

		if(is_array($config))
		{
			if(isset($config['sections']))
				foreach((array)$config['sections'] as $k=>$v)
					$uris[]=is_int($k) ? $v : $k;

			if(isset($config['services']) and $unlocked)
			{
				$services=(array)$config['services'];
				natsort($services);
				$values['services']=$services ? ','.implode(',,',$services).',' : '';
			}
		}

		if(isset($_POST['uris']))
		{
			$values['uris']=[];
			$preuris=(array)$_POST['uris'];

			if(!$uris and $orig['uris'])
				$uris=array_keys($orig['uris']);

			if(Eleanor::$vars['multilang'])
				foreach($uris as $uri)
				{
					foreach(Eleanor::$langs as $lng=>$_)
						if(isset($preuris[$uri][$lng]))
						{
							$value=(array)$preuris[$uri][$lng];

							foreach($value as $kv=>&$vv)
							{
								$vv=trim($vv);
								if($vv=='')
									unset($value[$kv]);
							}

							if($value)
								$values['uris'][$uri][$lng]=$value;
						}
						elseif(isset($orig['uris'][$uri][$lng]))
							$values['uris'][$uri][$lng]=$orig['uris'][$uri][$lng];
				}
			else
				foreach($uris as $uri)
					if(isset($preuris[$uri]))
					{
						$value=(array)$preuris[$uri];

						foreach($value as $kv=>&$vv)
						{
							$vv=trim($vv);
							if($vv=='')
								unset($value[$kv]);
						}

						if($value)
							$values['uris'][$uri]['']=$value;
					}
					elseif(isset($orig['uris'][$uri]['']))
						$values['uris'][$uri]['']=$orig['uris'][$uri][''];

			$exists=[];
			$Filter=function($v)use($id)
			{
				return$id!=$v;
			};

			#Если заданы сервисы и включена мультиязычность
			if($services and Eleanor::$vars['multilang'])
				foreach($services as$service)
					foreach(Eleanor::$langs as $lng=>$_)
					{
						$current=GetModules($service,$lng)['ids'];
						$current=array_keys(array_filter($current,$Filter));

						foreach($values['uris'] as$luri)
							if(isset($luri[$lng]) and $cross=array_intersect($luri[$lng],$current))
								$exists=array_merge($exists,$cross);
					}
			elseif($services)
				foreach($services as $service)
				{
					$current=GetModules($service)['ids'];
					$current=array_keys(array_filter($current,$Filter));

					foreach($values['uris'] as$uri)
						if($cross=array_intersect($uri,$current))
							$exists=array_merge($exists,$cross);
				}
			elseif(Eleanor::$vars['multilang'])
				foreach(Eleanor::$langs as $lng=>$_)
				{
					$current=GetModules('',$lng)['ids'];
					$current=array_keys(array_filter($current,$Filter));

					foreach($values['uris'] as$luri)
						if(isset($luri[$lng]) and $cross=array_intersect($luri[$lng],$current))
							$exists=array_merge($exists,$cross);
				}
			else
			{
				$current=GetModules('')['ids'];
				$current=array_keys(array_filter($current,$Filter));

				foreach($values['uris'] as$uri)
					if($cross=array_intersect($uri[''],$current))
						$exists=array_merge($exists,$cross);
			}

			if($exists)
				$errors['URI_EXISTS']=$lang['URI_EXISTS'](array_unique($exists));

			if(!$errors)
				$values['uris']=$values['uris'] ? json_encode($values['uris'],JSON) : '';
		}

		if($errors)
			goto EditForm;

		$miniature=false;

		#Миниатюра, здесь многи хитрости: если картинка не изменялась - она не должна передаваться
		if(isset($_POST['miniature'],$_POST['miniature']['type'],$_POST['miniature']['src']) and is_array($_POST['miniature']))
			switch($_POST['miniature']['type'])
			{
				case'upload':
					$src=basename((string)$_POST['miniature']['src']);
					$path=Template::$path['uploads'].'temp/'.$src;

					if(is_file($path))
						$miniature=$path;
				break;
				case'gallery':
					$src=(string)$_POST['miniature']['src'];
					$path=$gallery['path'].$src;
					$files=glob($path);

					if($files)
						$path=$files[0];
					else
						break;

					$path=realpath($path);
					$gallery=$gallery['path'];

					if(\Eleanor\W)
					{
						$gallery=str_replace('\\','/',$gallery);
						$path=str_replace('\\','/',$path);
					}

					if(is_file($path) and strpos($path,$gallery)===0)
						$values+=[
							'miniature_type'=>'gallery',
							'miniature'=>$src,
						];
				break;
				case'link':
					$values+=[
						'miniature_type'=>'link',
						'miniature'=>(string)$_POST['miniature']['src']
					];
				break;
				default:
					$values+=[
						'miniature_type'=>'',
						'miniature'=>''
					];
			}

		if(isset($_POST['title']))
			$values['title_l']=$title_ ? json_encode($title_,JSON) : '';

		if(isset($_POST['descr']))
			$values['descr_l']=$descr ? json_encode($descr,JSON) : '';

		if($unlocked)
			IntValue($values,'status',[0,1]);
		else
			unset($values['path'],$values['config']);

		if($id)
		{
			if($orig['miniature_type']=='upload' and isset($values['miniature']) and is_file($f=Template::$path['uploads'].'modules/'.$orig['miniature'])
				and ($values['miniature']!=$orig['miniature'] or $values['miniature_type']!='upload'))
				Files::Delete($f);
			
			Eleanor::$Db->Update($table,$values,"`id`={$id} LIMIT 1");
		}
		else
			$id=Eleanor::$Db->Insert($table,$values);

		if($miniature)
		{
			$newfile=Template::$path['uploads'].'modules/module-'.$id.strrchr($miniature,'.');

			if(is_file($newfile))
				Files::Delete($newfile);
			elseif(!is_dir(Template::$path['uploads'].'modules/'))
				Files::MkDir(Template::$path['uploads'].'modules/');

			if(rename($miniature,$newfile))
				Eleanor::$Db->Update($table,['miniature_type'=>'upload','miniature'=>basename($newfile)],
					"`id`={$id} LIMIT 1");
		}

		Eleanor::$Cache->Engine->DeleteByTag('modules');
		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	if($id)
	{
		$values=$orig;
		$values['miniature']=Miniature($orig);
		$values['title']=$values['title_l'] ? json_decode($values['title_l'],true) : [''=>''];
		$values['descr']=$values['descr_l'] ? json_decode($values['descr_l'],true) : [''=>''];

		if(!Eleanor::$vars['multilang'])
			foreach($values['uris'] as &$uri)
				$uri=FilterLangValues($uri,null,[]);

		if(!Eleanor::$vars['multilang'])
		{
			$values['title']=FilterLangValues($values['title']);
			$values['descr']=FilterLangValues($values['descr']);
		}

		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['creating'];
		$def=Eleanor::$vars['multilang'] ? [] : '';
		$values=[
			'protected'=>false,
			'uris'=>[],
			'title'=>$def,
			'descr'=>$def,
			'status'=>1,
			'path'=>'',
			'file'=>'',
			'api'=>'',
			'config'=>'',
			'miniature'=>false,
		];
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		$data=[
			'uris'=>'array',
			'path'=>'string',
			'file'=>'string',
			'api'=>'string',
			'config'=>'string',
		];

		if(Eleanor::$vars['multilang'])
			$data+=[
				'title'=>'array',
				'descr'=>'array',
			];
		else
			$data+=[
				'title'=>'string',
				'descr'=>'string',
			];

		PostValues($values,$data);
		IntValue($values,'status',[0,1]);

		if($id)
		{
			$R=Eleanor::$Db->Query("SELECT `protected`, `path`, `file`, `status`, `api`, `config` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
			$module=$R->fetch_assoc();
			if($module['protected'])
				$values=$module+$values;
		}
		else
			$values['protected']=isset($_POST['protected']);

		#Миниатюра, здесь есть хитрость: если картинка не изменялась - она не должна передаваться
		if(isset($_POST['miniature'],$_POST['miniature']['type'],$_POST['miniature']['src']) and is_array($_POST['miniature']))
			switch($_POST['miniature']['type'])
			{
				case'upload':
					$src=basename((string)$_POST['miniature']['src']);
					$path=Template::$path['uploads'].'temp/'.$src;

					if(is_file($path))
						$values['miniature']=[
							'post'=>true,
							'type'=>'upload',
							'path'=>$path,
							'http'=>Template::$http['uploads'].'temp/'.$src,
							'src'=>$src,
						];
					else
						$values['miniature']=null;
				break;
				case'gallery':
					$gallery=$gallery['path'];
					$src=(string)$_POST['miniature']['src'];
					$path=realpath($gallery.$src);

					if(\Eleanor\W)
					{
						$gallery=str_replace('\\','/',$gallery);
						$path=str_replace('\\','/',$path);
					}

					if(is_file($path) and strpos($path,$gallery)===0)
						$values['miniature']=[
							'post'=>true,
							'type'=>'gallery',
							'path'=>$path,
							'http'=>$gallery['http'].$src,
							'src'=>$src,
						];
					else
						$values['miniature']=null;
				break;
				case'link':
					$values['miniature']=[
						'post'=>true,
						'type'=>'link',
						'http'=>$_POST['miniature']['src'],
					];
				break;
				default:
					$values['miniature']=null;
			}
	}

	$values['api']=preg_replace('#(\.\.|[\\/]+)#','',$values['api']);
	$uris=[];

	if(!$config)
		$config=$values['path'] && $values['config'] && is_file($v=DIR.rtrim($values['path'],'/\\').'/'.$values['config'])
			? \Eleanor\AwareInclude($v)
			: false;

	if(is_array($config) and isset($config['sections']))
		foreach((array)$config['sections'] as $k=>$v)
			$uris[ is_int($k) ? $v : $k ]=is_scalar($v) ? $v : FilterLangValues($v);

	if($uris)
	{
		foreach($uris as $k=>$v)
			if(!isset($values['uris'][$k]))
				$values['uris'][$k]=[];
	}
	elseif(isset($orig['uris']))
	{
		$uris=array_keys($orig['uris']);
		$uris=array_combine($uris,$uris);
	}
	else
	{
		$uris=['main'=>'Main'];
		$values['uris']['main']=[];
	}

	if(Eleanor::$vars['multilang'])
		foreach(Eleanor::$langs as $lng=>$_)
		{
			if(!isset($values['title'][$lng]))
				$values['title'][$lng]='';

			if(!isset($values['descr'][$lng]))
				$values['descr'][$lng]='';

			foreach($values['uris'] as &$uri)
				if(!isset($uri[$lng]))
					$uri[$lng]=[];
		}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=[
		'delete'=>!$id || $values['protected'] ? null : $GLOBALS['Eleanor']->DynUrl(['delete'=>$id]),
	];

	Response(Eleanor::$Template->CreateEdit($id,$values,$uris,$maxupload,$errors,$back,$links));
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title_l` `title`, `miniature_type`, `miniature` FROM `{$table}` WHERE `id`={$id} AND `protected`=0 LIMIT 1");
	if(!$module=$R->fetch_assoc() or !Eleanor::$ourquery)
		return GoAway();

	if(isset($_POST['ok']))
	{
		if($module['miniature_type']=='upload' and is_file($f=Template::$path['static'].'images/modules/'.$module['miniature']))
			Files::Delete($f);

		Eleanor::$Db->Delete($table,"`id`={$id} AND `protected`=0");
		Eleanor::$Cache->Engine->DeleteByTag('modules');

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];
	$module['title']=$module['title'] ? FilterLangValues(json_decode($module['title'],true)) : '';

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response(Eleanor::$Template->Delete($module,$back));
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		Eleanor::$Db->Update($table,['!status'=>'NOT `status`'],"`id`={$id} AND `protected`=0 LIMIT 1");
		Eleanor::$Cache->Engine->DeleteByTag('modules');
	}

	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#it'.$id : true);
}
else
{
	$Urls=[
		'index'=>new Url(false),
	];

	if(Eleanor::$vars['multilang'])
		$Urls['index']->prefix.=Url::Encode(Eleanor::$langs[ Language::$main ]['uri']).'/';

	$titles=$preitems=$items=[];

	$service=Eleanor::$service;
	$R=Eleanor::$Db->Query("SELECT `id`, `services`, `uris`, `title_l` `title`, `descr_l` `descr`, `protected`, `miniature_type`, `miniature`, `status` FROM `{$table}` WHERE `services`='' OR `services` LIKE '%,{$service},%'");
	while($module=$R->fetch_assoc())
	{
		$module['miniature']=Miniature($module);
		$module['title']=$module['title'] ? FilterLangValues(json_decode($module['title'],true)) : '';
		$module['descr']=$module['descr'] ? FilterLangValues(json_decode($module['descr'],true)) : '';
		$module['uris']=$module['uris'] ? json_decode($module['uris'],true) : [];

		foreach($module['uris'] as &$v)
			if(isset($v[ Language::$main ]))
				$v=reset($v[ Language::$main ]);
			elseif(isset($v['']))
				$v=reset($v['']);
			else
				$v=null;

		unset($v);

		$module['_links']=[];

		if($module['status']==1)
		{
			$module['_links']['index']=$module['_links']['admin']=false;
			$services=$module['services'] ? explode(',,',trim($module['services'],',')) : array_keys(Eleanor::$services);
			$url=['module'=>reset($module['uris'])];

			foreach($services as $service)
				if($service=='admin')
					$module['_links']['admin']=$Url($url);
				else
				{
					if(!isset($Urls[$service]))
					{
						$prefix=Eleanor::$services[$service]['file'].'?';

						if(Eleanor::$vars['multilang'])
							$prefix.='lang='.urlencode(Eleanor::$langs[ Language::$main ]['uri']).'&amp;';

						$Urls[$service]=new DynUrl($prefix);
					}

					$module['_links'][$service]=$Urls[$service]($url);
				}

			if($module['_links']['admin']===false)
				unset($module['_links']['admin']);

			if($module['_links']['index']===false)
				unset($module['_links']['index']);
		}

		$module['_aedit']=$Url(['edit'=>$module['id']]);

		if($module['protected'])
			$module['_adel']=$module['_atoggle']=false;
		else
		{
			$module['_adel']=$Url(['delete'=>$module['id']]);
			$module['_atoggle']=$Url(['toggle'=>$module['id']]);
		}

		$titles[ $module['id'] ]=$module['title'];
		$preitems[ $module['id'] ]=array_slice($module,1);
	}

	asort($titles,SORT_STRING);

	foreach($titles as $k=>$v)
		$items[$k]=$preitems[$k];

	Response(Eleanor::$Template->ModulesCover($items));
}