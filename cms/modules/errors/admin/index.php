<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Files, Eleanor\Classes\Output, Eleanor\Classes\StringCallback;

defined('CMS\STARTED')||die;
global$Eleanor,$title;

/** @var DynUrl $Url
 * @var array $config */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$config['n']);
$uid=(int)Eleanor::$Login->Get('id');
$id=0;
$langmain=Language::$main;
$Eleanor->module+=[
	'config'=>$config,
	'links'=>[
		'list'=>(string)$Url,
		'create'=>$Url(['do'=>'create']),
		'letters'=>$Url(['do'=>'letters']),
	]
];

/** Формирование значения миниатюры для шаблона
 * @param array $a Входящие данные
 * @return array*/
function Miniature(array$a)
{
	$config=$GLOBALS['Eleanor']->module['config'];
	$image=false;

	if($a['miniature'] and $a['miniature']) switch($a['miniature_type'])
	{
		case'gallery':
			if(is_file($f=$config['gallery-path'].$a['miniature']))
				$image=[
					'type'=>'gallery',
					'path'=>$f,
					'http'=>$config['gallery-http'].$a['miniature'],
					'src'=>$a['miniature'],
				];
		break;
		case'upload':
			if(is_file($f=$config['uploads-path'].$a['miniature']))
				$image=[
					'type'=>'upload',
					'path'=>$f,
					'http'=>$config['uploads-http'].$a['miniature'],
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

if(isset($_GET['do'])) switch($_GET['do'])
{
	case'create':
		goto CreateEdit;
	break;
	case'letters':
		#ToDo! Удалить:
		Templates\Admin\T::$data['speedbar']=[];

		$controls=[
			$lang['letter_error'],
			'error_t'=>[
				'title'=>$lang['letter-title'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'error'=>[
				'title'=>$lang['letter-descr'],
				'type'=>'editor',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'checkout'=>false,
					'ownbb'=>false,
					'smiles'=>false,
					'extra'=>['class'=>'need-tabindex','rows'=>14],
				],
			],
		];

		$values=[];
		$multilang=Eleanor::$vars['multilang'] ? array_keys(Eleanor::$langs) : [Language::$main];
		if($post)
		{
			$letter=$Eleanor->Controls->SaveControls($controls);

			if(Eleanor::$vars['multilang'])
				foreach($multilang as $l)
				{
					$tosave=[];

					foreach($letter as $k=>$v)
						$tosave[$k]=$controls[$k]['multilang'] ? FilterLangValues($v,$l) : $v;

					$file=$Eleanor->module['path'].'letters-'.$l.'.php';
					file_put_contents($file,'<?php return '.var_export($tosave,true).';');
				}
			else
			{
				$file=$Eleanor->module['path'].'letters-'.Language::$main.'.php';
				file_put_contents($file,'<?php return '.var_export($letter,true).';');
			}
		}
		else
			foreach($multilang as $l)
			{
				$file=$Eleanor->module['path'].'letters-'.$l.'.php';
				$letter=file_exists($file) ? (array)include$file : [];
				$letter+=[
					'error_t'=>'',
					'error'=>'',
				];

				if(Eleanor::$vars['multilang'])
					foreach($letter as $k=>$v)
						$values[$k]['value'][$l]=$v;
				else
					foreach($letter as $k=>$v)
						$values[$k]['value']=$v;
			}

		$values=$Eleanor->Controls->DisplayControls($controls,$values)+$values;
		$title[]=$lang['letters'];
		$c=Eleanor::$Template->Letters($controls,$values,$post,[]);

		Response($c);
	break;
	case'draft':
		$id=isset($_POST['_draft']) ? (int)$_POST['_draft'] : 0;

		unset($_POST['_draft'],$_POST['back']);
		Eleanor::$Db->Replace(P.'drafts',['key'=>$config['n'].'-'.Eleanor::$Login->Get('id').'-'.$id,'value'=>json_encode($_POST,JSON)]);
		OutPut::SendHeaders('text');
		Output::Gzip('ok');
	break;
	default:
		GoAway(true);
}
elseif(isset($_GET['delete-draft']))
{
	$id=(int)$_GET['delete-draft'];
	Eleanor::$Db->Delete(P.'drafts','`key`=\''.$config['n'].'-'.Eleanor::$Login->Get('id').'-'.$id.'\' LIMIT 1');
	GoAway(false);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$maxupload=Eleanor::$Permissions ? Eleanor::$Permissions->MaxUpload() : Files::SizeToBytes(ini_get('upload_max_filesize'));

	if(AJAX)
	{
		$data=false;

		if(isset($_FILES['miniature']) and is_uploaded_file($_FILES['miniature']['tmp_name']) and $_FILES['miniature']['size']<=$maxupload
			and preg_match('#\.(png|jpe?g|gif)$#',$_FILES['miniature']['name']) and getimagesize($_FILES['miniature']['tmp_name']))
		{
			$tempdir="{$config['n']}-{$id}-{$uid}".strrchr($_FILES['miniature']['name'],'.');
			$temppath=Template::$path['uploads'].'temp/';

			if(!is_dir($temppath))
				Files::MkDir($temppath);

			$temppath.=$tempdir;

			if(is_file($temppath))
				Files::Delete($temppath);

			if(move_uploaded_file($_FILES['miniature']['tmp_name'],$temppath))
				$data=[
					'http'=>Template::$http['uploads'].'temp/'.$tempdir,
					'src'=>$tempdir,
				];
		}
		elseif(isset($_POST['miniature-gallery']))
		{
			$items=[];
			$files=glob($config['gallery-path'].'*.{png,gif,jpg}',GLOB_BRACE);

			if($files)
				foreach($files as $v)
				{
					$bn=basename($v);
					$items[$bn]=[
						'path'=>$v,
						'http'=>$config['gallery-http'].$bn,
					];
				}

			$data=Eleanor::$Template->MiniatureGallery($items,null);
		}

		return $data ? Response($data) : Error('Unknown event');
	}

	$tempdir="temp/{$config['n']}-{$id}-{$uid}";
	$temphttp=Template::$http['uploads'].$tempdir.'/';
	$temppath=Template::$path['uploads'].$tempdir;
	$errors=[];

	if($id)
	{
		$R=Eleanor::$Db->Query("SELECT * FROM `{$config['t']}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway(true);
	}

	if($post)
	{
		include_once DIR.'crud.php';

		#Получение языков контента
		if(Eleanor::$vars['multilang'] and !isset($_POST['single-lang']))
		{
			$langs=isset($_POST['language']) ? (array)$_POST['language'] : [];
			$langs=array_intersect(array_keys(Eleanor::$langs),$langs);

			if(!$langs)
				$langs=[Language::$main];
		}
		else
			$langs=[''];

		#Получение языковых данных из формы в формате имя=>значение(я)
		$tt=Eleanor::$vars['multilang'] ? 'array' : 'string';#TextType
		$values=$lvalues=[];

		PostValues($values,[
			'title'=>$tt,
			'uri'=>$tt,
			'text'=>$tt,
			'document_title'=>$tt,
			'meta_descr'=>$tt,
		]);
		#/Полученые языковых данных из формы

		#Преобразование данных в формат язык=>имя=>значение
		if(Eleanor::$vars['multilang'])
			foreach($langs as $lng)
			{
				$l=$lng ? $lng : Language::$main;

				foreach($values as $field=>$data)
					if(isset($data[$l]))
						$lvalues[$lng][$field]=$data[$l];
			}
		else
			$lvalues=[''=>$values];
		#/Преобразование

		$values=[
			'log'=>isset($_POST['log']),
		];
		PostValues($values,['http_code'=>'int','email'=>'string','log_language'=>'string']);

		if(isset($values['email']) and $values['email']!=='' and !filter_var($values['email'],FILTER_VALIDATE_EMAIL))
			$errors[]='INCORRECT_EMAIL';

		if(isset($values['log_language']) and !isset(Eleanor::$langs[ $values['log_language'] ]))
			unset($values['log_language']);

		#Фильтрация языковых данных
		foreach($lvalues as $lng=>&$fields)
		{
			foreach($fields as $name=>&$value)
				switch($name)
				{
					case'uri':
						$value=Url::Filter($value,$lng);

						if($value!='')
						{
							$uri=Eleanor::$Db->Escape($value);
							$lin=$lng ? 'IN(\'\',\''.$lng.'\')' : '=\'\'';
							$R=Eleanor::$Db->Query("SELECT `id` FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`) WHERE `uri`={$uri} AND `language`{$lin} AND `id`!='.$id.' LIMIT 1");
							if($R->num_rows>0)
								$value='';
						}
						break;
					case'title':
						$value=GlobalsWrapper::Filter($value);

						if($value==='')
							$errors['EMPTY_TITLE'][]=$lng;
						break;
					case'text':
						$value=$Eleanor->Saver->Save($value);

						if($value==='')
							$errors['EMPTY_TEXT'][]=$lng;
						break;
					default:
						$value=GlobalsWrapper::Filter($value);
				}

			if(!$id)
			{#Проверка незаполненных полей при создании
				if(!isset($fields['title']))
					$errors['EMPTY_TITLE'][]=$lng;

				if(!isset($fields['title']))
					$errors['EMPTY_TEXT'][]=$lng;
			}
		}
		unset($fields,$value);
		#/Фильрация языковых данных

		$lang=Eleanor::$Language[ $config['n'] ];

		foreach(['EMPTY_TITLE','EMPTY_TEXT'] as $k)
			if(isset($errors[$k]))
				$errors[$k]=$lang[$k]( $errors[$k]==[''] ? [] : $errors[$k] );

		if($errors)
			goto EditForm;

		#Флаг загруженной миниатюры
		$miniature=false;

		#Миниатюра, здесь есть хитрость: если картинка не изменялась - она не должна передаваться
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
					$path=realpath($config['gallery-path'].$src);
					$gallery=$config['gallery-path'];

					if(\Eleanor\W)
					{
						$gallery=str_replace('\\','/',$gallery);
						$path=str_replace('\\','/',$path);
					}

					if(is_file($path) and strpos($path,$gallery)===0)
						$values+=[
							'miniature_type'=>'gallery',
							'miniature'=>substr($path,strlen($gallery)),
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

		Eleanor::$Db->Delete(P.'drafts',"`key`='{$config['n']}-{$uid}-{$id}' LIMIT 1");

		if($id)
		{
			$dirpath=$config['uploads-path'].$id;
			$dirhttp=$config['uploads-http'].$id;

			try
			{
				Files::UpdateDir($temppath,$dirpath);
			}
			catch(EE$E)
			{
				$errors['EXCEPTION']=$E->getMessage();
				goto EditForm;
			}

			if($orig['miniature_type']=='upload' and isset($values['miniature']) and is_file($f=$config['uploads-path'].$orig['miniature'])
				 and ($values['miniature']!=$orig['miniature'] or $values['miniature_type']!='upload'))
				Files::Delete($f);

			Eleanor::$Db->Update($config['t'],$values,'id='.$id.' LIMIT 1');

			if($langs==[''])
				Eleanor::$Db->Update($config[ 'tl' ], ['language'=>''], "`id`={$id} AND `language`='{$langmain}'");

			$lin=Eleanor::$Db->In($langs,true);
			Eleanor::$Db->Delete($config['tl'],"`id`={$id} AND `language`{$lin}");

			$exists=[];
			$lin=Eleanor::$Db->In($langs);
			$R=Eleanor::$Db->Query("SELECT `language` FROM `{$config['tl']}` WHERE `id`={$id} AND `language`{$lin}");
			while($a=$R->fetch_row())
				$exists[]=$a[0];

			foreach($lvalues as $language=>$data)
			{
				if(isset($data['text']))
					$data['text']=str_replace($temphttp,$dirhttp,$data['text']);

				if(in_array($language,$exists))
				{
					if($data)
						Eleanor::$Db->Update($config['tl'],$data,"`id`={$id} AND `language`='{$language}'");
				}
				else
					Eleanor::$Db->Insert($config['tl'],['id'=>$id,'language'=>$language]+$data);
			}
		}
		else
		{
			$id=Eleanor::$Db->Insert($config['t'],$values);

			$dirpath=$config['uploads-path'].$id;
			$dirhttp=$config['uploads-http'].$id.'/';

			if(is_dir($temppath))
			{
				Files::MkDir(dirname($dirpath));
				rename($temppath, $dirpath);
			}

			foreach($lvalues as $language=>$data)
			{
				if(isset($data['text']))
					$data['text']=str_replace($temphttp,$dirhttp,$data['text']);

				Eleanor::$Db->Insert($config['tl'],['id'=>$id,'language'=>$language]+$data);
			}
		}

		if($miniature)
		{
			$newfile=$config['uploads-path'].$config['n'].'-'.$id.strrchr($miniature,'.');

			if(is_file($newfile))
				Files::Delete($newfile);
			elseif(!is_dir($config['uploads-path']))
				Files::MkDir($config['uploads-path']);

			if(rename($miniature,$newfile))
				Eleanor::$Db->Update($config['t'],['miniature_type'=>'upload','miniature'=>basename($newfile)],
					"`id`={$id} LIMIT 1");
		}

		Eleanor::$Cache->Engine->DeleteByTag($config['n']);
		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	$def=Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : '';

	if($id)
	{
		$values=$orig;
		$dirpath=$config['uploads-path'].$id;
		$values['language']=[];
		$values['miniature']=Miniature($orig);

		if(is_dir($dirpath))
		{
			if(!is_dir($temppath) or !$errors)
				Files::SymLink($dirpath,$temppath);

			$dirhttp=$config['uploads-http'].$id;
		}
		else
			$dirhttp=false;

		$R=Eleanor::$Db->Query("SELECT `language`, `uri`, `title`, `text`, `document_title`, `meta_descr` FROM `{$config['tl']}` WHERE `id`={$id}");
		while($a=$R->fetch_assoc())
		{
			if($dirhttp)
				$a['text']=str_replace($dirhttp,$temphttp,$a['text']);

			if(!Eleanor::$vars['multilang'] and (!$a['language'] or $a['language']==Language::$main))
			{
				if(Eleanor::$vars['multilang'])
					$values['language'][]=Language::$main;

				foreach(array_slice($a,1) as $tk=>$tv)
					$values[$tk]=$tv;

				if(!$a['language'])
					break;
			}
			elseif(!$a['language'] and Eleanor::$vars['multilang'])
			{
				$values['language']=[Language::$main];

				foreach(array_slice($a,1) as $tk=>$tv)
				{
					$values[$tk]=$def;
					$values[$tk][Language::$main]=$tv;
				}

				$values['single-lang']=true;

				break;
			}
			elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$a['language']]))
			{
				$values['language'][]=$a['language'];

				foreach(array_slice($a,1) as $tk=>$tv)
				{
					$values[$tk][$a['language']]=$tv;
					$values[$tk]+=$def;
				}
			}
		}

		if(Eleanor::$vars['multilang'] and !isset($values['single-lang']))
			$values['single-lang']=false;

		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['creating'];
		$values=[
			'http_code'=>404,
			'email'=>'',
			'log'=>false,
			'miniature'=>false,

			'title'=>$def,
			'uri'=>$def,
			'text'=>$def,
			'document_title'=>$def,
			'meta_descr'=>$def,
		];

		if(Eleanor::$vars['multilang'])
		{
			$values['log_language']=Language::$main;
			$values['single-lang']=true;
			$values['language']=array_keys(Eleanor::$langs);
		}
	}

	$draft=false;
	if(!$errors)
	{
		$table=P.'drafts';
		$R=Eleanor::$Db->Query("SELECT `value` FROM `{$table}` WHERE `key`='{$config['n']}-{$uid}-{$id}' LIMIT 1");
		if($a=$R->fetch_row() and $a[0])
		{
			$draft=true;
			$_POST+=json_decode($a[0],true);
			include_once DIR.'crud.php';
		}
	}

	if($errors or $draft)
	{
		if(!is_array($errors))
			$errors=[];

		$data=[];

		if(Eleanor::$vars['multilang'])
		{
			$values['single-lang']=isset($_POST['single-lang']);
			$values['language']=isset($_POST['language']) ? (array)$_POST['language'] : [];

			if(!$values['language'])
				$values['language']=[Language::$main];

			$tt='array';
		}
		else
			$tt='string';#TextType

		$data+=[
			'http_code'=>'int',
			'email'=>'string',
			'log_language'=>'string',

			'title'=>$tt,
			'uri'=>$tt,
			'text'=>$tt,
			'document_title'=>$tt,
			'meta_descr'=>$tt,
		];

		PostValues($values,$data);
		$values['log']=isset($_POST['log']);

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
					$gallery=$config['gallery-path'];
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
							'http'=>$config['gallery-http'].$src,
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

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$Editor=function()use($Eleanor){
		return call_user_func_array([$Eleanor->Editor,'Area'],func_get_args());
	};
	$links=[
		'delete'=>$id ? $Url(['delete'=>$id,'noback'=>1]) : false,
		'delete-draft'=>$draft ? $Url(['delete-draft'=>$id]) : false,
		'draft'=>$Url(['do'=>'draft']),
	];

	$c=Eleanor::$Template->CreateEdit($id,$values,$Editor,$Eleanor->Uploader->Show($tempdir),$errors,$back,$draft,$links,$maxupload);
	Response($c);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title`, `miniature_type`, `miniature` FROM `{$config['t']}` LEFT JOIN `{$config['tl']}` USING(`id`) WHERE `id`={$id} AND `language` IN ('','{$langmain}') LIMIT 1");
	if(!Eleanor::$ourquery or !$error=$R->fetch_assoc())
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Files::Delete($config['uploads-path'].$id);

		if($error['miniature_type']=='upload' and is_file($f=$config['uploads-path'].$error['miniature']))
			Files::Delete($f);

		Eleanor::$Db->Delete($config['t'],"`id`={$id} LIMIT 1");
		Eleanor::$Db->Delete($config['tl'],'`id`='.$id);
		Eleanor::$Db->Delete(P.'drafts',"`key`='{$config['n']}-{$uid}-{$id}' LIMIT 1");

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response(Eleanor::$Template->Delete($error,$back));
}
else
{
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$where=$query=$items=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$query['fi']['title']=(string)$_REQUEST['fi']['title'];
			$where[]='`title` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['title'],false).'%\'';
		}

		if(!empty($_REQUEST['fi']['email']))
		{
			$query['fi']['email']=(string)$_REQUEST['fi']['email'];
			$where[]='`email` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['email'],false).'%\'';
		}
	}

	$where[]='`language` IN (\'\',\''.Language::$main.'\')';
	$where=' WHERE '.join(' AND ',$where);

	if($post and isset($_POST['event'],$_POST['items']))
		switch($_POST['event'])
		{
			case'delete':
				$in=Eleanor::$Db->In($_POST['items']);

				$R=Eleanor::$Db->Query("SELECT `miniature` FROM `{$config['t']}` WHERE `id`{$in} AND `miniature_type`='upload' AND `miniature`!=''");
				while($a=$R->fetch_assoc())
					if(is_file($f=$config['uploads-path'].$a['miniature']))
						Files::Delete($f);

				Eleanor::$Db->Delete($config['t'],'`id`'.$in);
				Eleanor::$Db->Delete($config['tl'],'`id`'.$in);

				foreach($_POST['items'] as $v)
					Files::Delete($config['uploads-path'].(int)$v);
		}

	$defsort='title';
	$deforder='desc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$config['t']}` `s` INNER JOIN `{$config['tl']}` `l` USING(`id`){$where}");
	list($cnt)=$R->fetch_row();

	if(isset($query['fi']))
	{
		$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`) WHERE `language` IN ('','{$langmain}')");
		list($total)=$R->fetch_row();
	}
	else
		$total=$cnt;

	if($cnt>0)
	{
		$IndexUrl=new Url(false);
		$IndexUrl->prefix.=Url::Encode($Eleanor->module['uri']).'/';

		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','title','http_code','email'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `http_code`, `miniature_type`, `miniature`, `email`, `log`, `uri`, `title` FROM `{$config['t']}` `s` INNER JOIN `{$config['tl']}` `l` USING(`id`){$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['miniature']=Miniature($a);
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$Url(['delete'=>$a['id']]);
			$a['_a']=$IndexUrl($a['uri'] ? [$a['uri']] : [],'',$a['uri'] ? [] : ['id'=>$a['id']]);

			$items[$a['id']]=array_slice($a,1);
		}

		$links=[
			'sort_title'=>SortDynUrl('title',$query,$defsort,$deforder),
			'sort_email'=>SortDynUrl('email',$query,$defsort,$deforder),
			'sort_http_code'=>SortDynUrl('http_code',$query,$defsort,$deforder),
			'sort_id'=>SortDynUrl('id',$query,$defsort,$deforder),
			'form_items'=>$Url($query+['page'=>$page>1 ? $page : false]),
			'pp'=>function($n)use($Url,$query){ $query['per-page']=$n; return$Url($query); },
			'first_page'=>$Url($query),
			'pagination'=>function($n)use($Url,$query){ return$Url($query+['page'=>$n]); },
		];
		$query['sort']=$sort;
		$query['order']=$order;
	}
	else
	{
		$pp=0;
		$links=[];
	}

	$links['nofilter']=isset($query['fi']) ? $Url(['fi'=>[]]+$query) : false;
	$c=Eleanor::$Template->ShowList($items,$total>0,$cnt,$pp,$query,$page,$links);
	Response($c);
}