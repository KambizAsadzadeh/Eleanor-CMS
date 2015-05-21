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
$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;
$lang=Eleanor::$Language->Load($Eleanor->module['path'].'admin-*.php',$config['n']);
$langmain=Language::$main;
$id=0;
$uid=(int)Eleanor::$Login->Get('id');
$Eleanor->module+=[
	'config'=>$config,
	'links'=>[
		'create'=>$Url(['do'=>'create']),
		'parent_create'=>null,
		'list'=>(string)$Url,
		'files'=>$Url(['do'=>'files']),
		'options'=>$Url(['do'=>'options']),
	]
];

if(isset($_GET['do']))switch($_GET['do'])
{
	case'create':
		goto CreateEdit;
	break;
	case'files':
		$title[]=$lang['fp'];
		$Uploader=new StringCallback(function()use($Eleanor){
			return$Eleanor->Uploader->Show($Eleanor->module['path'].'DIRECT',false);
		});

		$c=Eleanor::$Template->Files($Uploader);
		Response($c);
	break;
	case'options':
		$Url->prefix.='do=options&amp;';
		$c=$Eleanor->Settings->Group($config['optgroup']);

		if($c)
			Response( Eleanor::$Template->Options($c) );
	break;
	case'resort':
		$p='';
		$pos=1;

		if(isset($_GET['id']))
		{
			$id=(int)$_GET['id'];
			$R=Eleanor::$Db->Query("SELECT `id`, `parents` FROM `{$config['t']}` WHERE `id`={$id} LIMIT 1");
			if(list($id,$p)=$R->fetch_row())
				$p.=$id.',';
		}

		$R=Eleanor::$Db->Query("SELECT `id`, `pos` FROM `{$config['t']}` WHERE `parents`='{$p}' ORDER BY `pos` ASC");
		while($a=$R->fetch_assoc())
		{
			if($a['pos']!=$pos)
				Eleanor::$Db->Update($config['t'],['pos'=>$pos],"`id`={$a['id']} LIMIT 1");

			++$pos;
		}

		GoAway();
	break;
	case'draft':
		$id=isset($_POST['_draft']) ? (int)$_POST['_draft'] : 0;

		unset($_POST['_draft'],$_POST['back']);
		Eleanor::$Db->Replace(P.'drafts',['key'=>"{$config['n']}-{$uid}-{$id}",'value'=>json_encode($_POST,JSON)]);
		OutPut::SendHeaders('text');
		Output::Gzip('ok');
	break;
	default:
		GoAway($Eleanor->module['links']['list']);
}
elseif(isset($_GET['delete-draft']))
{
	$id=(int)$_GET['delete-draft'];

	Eleanor::$Db->Delete(P.'drafts',"`key`='{$config['n']}-{$uid}-{$id}' LIMIT 1");
	GoAway(false);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	if(AJAX)
	{
		$parent=$_GET['parent'] ? (int)$_GET['parent'] : 0;
		$R=Eleanor::$Db->Query("SELECT `pos` FROM `{$config['t']}` WHERE `id`={$id} LIMIT 1");
		if(!list($origpos)=$R->fetch_row())
			$origpos=0;

		$children=[];
		$lin="IN('','{$langmain}')";

		if($parent>0)
		{
			$parent='='.$parent;

			$R=Eleanor::$Db->Query("SELECT `id`, `pos`, `title`,
(SELECT COUNT(`ch`.`parent`) FROM `{$config['t']}` `ch` WHERE `ch`.`parent`=`t`.`id`) `children`
FROM `{$config['t']}` `t` INNER JOIN `{$config['tl']}` USING(`id`)
WHERE `parent`{$parent} AND `language`{$lin} AND `id`!={$id} ORDER BY `pos` ASC");
			while($a=$R->fetch_assoc())
				$children[ $a['id'] ]=array_slice($a,1);
		}
		else
			$parent='IS NULL';

		$poses=[];
		$R=Eleanor::$Db->Query("SELECT `pos`, `title`
FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`)
WHERE `parent`{$parent} AND `language`{$lin} AND `id`!={$id}
ORDER BY `pos` ASC");
		while($a=$R->fetch_assoc())
		{
			if($a['pos']<$origpos or $id==0)
				$a['pos']++;

			$poses[ $a['pos'] ]=array_slice($a,1);
		}

		$out=Eleanor::$Template->AjaxLoadChildren($children,$poses);

		OutPut::SendHeaders('json');
		Output::Gzip(is_array($out) ? \Eleanor\Classes\Html::JSON($out) : $out);

		return 1;
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

		#Получение общих данных из формы
		$values=isset($_POST['pos']) ? ['pos'=>abs((int)$_POST['pos'])] : [];

		IntValue($values,'status',[0,1]);
		#/Получение общих данных из формы

		#Установка поля parents
		if(isset($_POST['parent']))
		{
			$parent=(int)$_POST['parent'];
			$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$config['t']}` WHERE `id`={$parent}");
			if($R->num_rows>0)
			{
				$values['parents']=$R->fetch_row()[0].$parent.',';

				#Проверка, не поместили ли мы себя внутри себя
				if(strpos(','.$id.',',','.$values['parents'])===false)
					$values['parent']=$parent;
				else
					$values['parents']='';
			}
		}

		if(!isset($values['parent']))
			$values['parent']=isset($orig) ? $orig['parent'] : null;
		#/Установка поля parents

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
							$parenteq=$values['parent'] ? '='.$values['parent'] : 'IS NULL';
							$lin=$lng ? "IN('','{$lng}')" : "=''";
							$R=Eleanor::$Db->Query("SELECT `id` FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`) WHERE `uri`={$uri} AND `language`{$lin} AND `id`!={$id} AND `parent`{$parenteq} LIMIT 1");
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

			if(!isset($values['pos']))
				$values['pos']=$orig['pos'];

			if(!isset($values['parents']))
				$values['parents']=$orig['parents'];

			if($values['pos']>0)
			{
				$parenteq=$values['parent'] ? '='.$values['parent'] : 'IS NULL';
				$R=Eleanor::$Db->Query("SELECT MAX(`pos`) FROM `{$config['t']}` WHERE `parent`{$parenteq}");
				list($maxpos)=$R->fetch_row();
				$values['pos']=min($values['pos'],(int)$maxpos+1);
			}

			if($orig['pos']!=$values['pos'] or $orig['parents']!=$values['parents'])
			{
				Eleanor::$Db->Update($config['t'],['!pos'=>'`pos`-1'],"`pos`>{$orig['pos']} AND `parents`='{$orig['parents']}'");
				Eleanor::$Db->Update($config['t'],['!pos'=>'`pos`+1'],"`pos`>={$values['pos']} AND `parents`='{$values['parents']}'");
			}

			if($orig['parents']!=$values['parents'])
				Eleanor::$Db->Update($config['t'],['!parents'=>"REPLACE(`parents`,'{$orig['parents']}','{$values['parents']}')"],"`parents` LIKE '{$orig['parents']}{$id},%'");

			Eleanor::$Db->Update($config['t'],$values,'id='.$id.' LIMIT 1');

			if($langs==[''])
				Eleanor::$Db->Update($config['tl'],['language'=>''],"`id`={$id} AND `language`='{$langmain}'");

			Eleanor::$Db->Delete($config['tl'],'`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));

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
			$values+=[
				'pos'=>0,
				'parents'=>'',
			];

			if($values['pos']>0)
			{
				$parenteq=$values['parent'] ? '='.$values['parent'] : 'IS NULL';
				$R=Eleanor::$Db->Query("SELECT MAX(`pos`) FROM `{$config['t']}` WHERE `parent`{$parenteq}");
				list($maxpos)=$R->fetch_row();
				$values['pos']=min($values['pos'],(int)$maxpos+1);
			}

			Eleanor::$Db->Update($config['t'],['!pos'=>'`pos`+1'],"`pos`>={$values['pos']} AND `parents`='{$values['parents']}'");

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

		#Обновим всю ветку (у родителей могли остаться ассоциации)
		if($values['parents'])
		{
			$values['parents']=(int)$values['parents'];
			Eleanor::$Db->Update($config[ 'tl' ], ['!last_mod'=>'NOW()'], "`id` IN (SELECT `id` FROM `{$config[ 't' ]}` WHERE `parents` LIKE '{$values['parents']},%')");
		}

		Eleanor::$Cache->Engine->DeleteByTag($config['n']);
		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	$def=Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : '';

	if($id)
	{
		$values=$orig;
		$origpos=$orig['pos'];
		$dirpath=$config['uploads-path'].$id;
		$values['language']=[];

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
			'parent'=>isset($_GET['parent']) ? (int)$_GET['parent'] : null,
			'pos'=>0,
			'status'=>1,
			'title'=>$def,
			'uri'=>$def,
			'text'=>$def,
			'document_title'=>$def,
			'meta_descr'=>$def,
		];

		if(Eleanor::$vars['multilang'])
		{
			$values['single-lang']=true;
			$values['language']=array_keys(Eleanor::$langs);
		}

		$origpos=0;
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
			'parent'=>'int',
			'pos'=>'int',
			'title'=>$tt,
			'uri'=>$tt,
			'text'=>$tt,
			'document_title'=>$tt,
			'meta_descr'=>$tt,
		];

		PostValues($values,$data);
		IntValue($values,'status',[0,1]);
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

	$children=[0=>[]];
	$lin="IN('','{$langmain}')";

	$R=Eleanor::$Db->Query("SELECT `id`, `pos`, `title`,
	(SELECT COUNT(`ch`.`parent`) FROM `{$config['t']}` `ch` WHERE `ch`.`parent`=`t`.`id`) `children`
FROM `{$config['t']}` `t` INNER JOIN `{$config['tl']}` USING(`id`)
WHERE `parent` IS NULL AND `language`{$lin}
ORDER BY `parent` ASC, `pos` ASC");
	while($a=$R->fetch_assoc())
		$children[0][ $a['id'] ]=array_slice($a,1);

	if($values['parent'])
	{
		$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$config['t']}` WHERE `id`={$values['parent']} LIMIT 1");
		if($a=$R->fetch_assoc())
		{
			$R=Eleanor::$Db->Query("SELECT `id`, `parent`, `pos`, `title`,
	(SELECT COUNT(`ch`.`parent`) FROM `{$config['t']}` `ch` WHERE `ch`.`parent`=`t`.`id`) `children`
FROM `{$config['t']}` `t` INNER JOIN `{$config['tl']}` USING(`id`)
WHERE `t`.`parent` IN({$a['parents']}{$values['parent']}) AND `language`{$lin}
ORDER BY `t`.`parent` ASC, `t`.`pos` ASC");
			while($a=$R->fetch_assoc())
				$children[ (int)$a['parent'] ][ $a['id'] ]=array_slice($a,2);
		}
	}

	$poses=[];
	$parent=$values['parent'] ? '='.$values['parent'] : ' IS NULL';
	$R=Eleanor::$Db->Query("SELECT `pos`, `title`
FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`)
WHERE `parent`{$parent} AND `language`{$lin} AND `id`!={$id}
ORDER BY `pos` ASC");
	while($a=$R->fetch_assoc())
	{
		if($a['pos']<$origpos or $id==0)
			$a['pos']++;

		$poses[ $a['pos'] ]=array_slice($a,1);
	}

	$c=Eleanor::$Template->CreateEdit($id,$values,['parents'=>$children,'poses'=>$poses],$Editor,$Eleanor->Uploader->Show($tempdir),$errors,$back,$draft,$links);
	Response($c);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title`, `parents`, `pos` FROM `{$config['t']}` LEFT JOIN `{$config['tl']}` USING(`id`) WHERE `id`={$id} AND `language` IN ('','{$langmain}') LIMIT 1");
	if(!Eleanor::$ourquery or !$static=$R->fetch_assoc())
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Files::Delete($config['uploads-path'].$id);

		$ids=[$id];

		$R=Eleanor::$Db->Query("SELECT `id` FROM `{$config['t']}` WHERE `parents` LIKE '{$static['parents']}{$id},%'");
		while($a=$R->fetch_assoc())
		{
			$ids[]=$a['id'];
			Files::Delete($config['uploads-path'].$a['id']);
			Eleanor::$Db->Delete(P.'drafts','`key`=\''.$config['n'].'-'.$uid.'-'.$a['id'].'\' LIMIT 1');
		}

		$ids=Eleanor::$Db->In($ids);
		Eleanor::$Db->Delete($config['t'],'`id`'.$ids);
		Eleanor::$Db->Delete($config['tl'],'`id`'.$ids);
		Eleanor::$Db->Update($config['t'],['!pos'=>'`pos`-1'],"`pos`>{$static['pos']} AND `parents`='{$static['parents']}'");
		Eleanor::$Cache->Engine->DeleteByTag($config['n']);

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response(Eleanor::$Template->Delete($static,$back));
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		Eleanor::$Db->Update($config['t'],['!status'=>'NOT `status`'],'`id`='.$id.' LIMIT 1');
		Eleanor::$Cache->Engine->DeleteByTag($config['n']);
	}

	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#item'.$id : true);
}
else
{
	if(AJAX and isset($_POST['order']))
	{
		$parent=isset($_REQUEST['parent']) ? (int)$_REQUEST['parent'] : 0;
		$parent=$parent>0 ? '='.$parent : 'IS NULL';
		$order=explode(',',(string)$_POST['order']);
		$in=Eleanor::$Db->In($order);

		$R=Eleanor::$Db->Query("SELECT `pos` FROM `{$config['t']}` WHERE `parent`{$parent} AND `id`{$in} ORDER BY `pos` ASC");

		if(count($order)==$R->num_rows)
		{
			foreach($order as $v)
				Eleanor::$Db->Update($config['t'],$R->fetch_assoc(),'`id`='.(int)$v.' LIMIT 1');

			$status='ok';
		}
		else
			$status='error';

		OutPut::SendHeaders('text');
		Output::Gzip($status);
		return 1;
	}

	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$navi=$where=$query=$items=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']) and !AJAX)
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$query['fi']['title']=$_REQUEST['fi']['title'];
			$where[]='`title` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['title'],false).'%\'';
		}
	}

	if(isset($_REQUEST['parent']) and 0<$query['parent']=(int)$_REQUEST['parent'])
	{
		$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$config['t']}` WHERE `id`={$query['parent']} LIMIT 1");
		list($parents)=$R->fetch_row();

		$parents.=$query['parent'];
		$where[]='`parents`='.Eleanor::$Db->Escape($parents.',');

		if(!AJAX)
		{
			$R=Eleanor::$Db->Query("SELECT `id`, `title` FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`) WHERE `language` IN ('','{$langmain}') AND `id` IN ({$parents})");
			while($a=$R->fetch_assoc())
				$items[$a['id']]=$a['title'];

			foreach(explode(',', $parents) as $v)
				if(isset($items[$v]))
					$navi[$v]=$v==$query['parent'] ? $items[$v] : ['title'=>$items[$v], '_a'=>$Url(['parent'=>$v])];

			$items=[];
			$Eleanor->module['links']['parent_create']=$Url(['do'=>'create', 'parent'=>$query['parent']]);
		}
	}
	else
		$where[]='`parents`=\'\'';

	$where[]="`language` IN ('','{$langmain}')";
	$where=' WHERE '.join(' AND ',$where);

	if($post and isset($_POST['event'],$_POST['items']))
	{
		$in=Eleanor::$Db->In($_POST['items']);

		switch($_POST['event'])
		{
			case'delete':
				$ids=[];
				$R=Eleanor::$Db->Query("SELECT `id`, `parents` FROM `{$config['t']}` WHERE `id`{$in}");
				while($a=$R->fetch_assoc())
				{
					$ids[]=$a['id'];
					$R2=Eleanor::$Db->Query("SELECT `id` FROM `{$config['t']}` WHERE `parents` LIKE '{$a['parents']}{$a['id']},%'");
					while($a=$R2->fetch_assoc())
						$ids[]=$a['id'];
				}

				$in=Eleanor::$Db->In($ids);

				Eleanor::$Db->Delete($config['t'],'`id`'.$in);
				Eleanor::$Db->Delete($config['tl'],'`id`'.$in);

				foreach($ids as $v)
					Files::Delete($config['uploads-path'].$v);
			break;
			case'activate':
				Eleanor::$Db->Update($config['t'],['status'=>1],'`id`'.$in);
			break;
			case'deactivate':
				Eleanor::$Db->Update($config['t'],['status'=>0],'`id`'.$in);
		}
	}

	$defsort='pos';
	$deforder='asc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$config['t']}` INNER JOIN `{$config['tl']}` USING(`id`){$where}");
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
		include_once$Eleanor->module['path'].'api.php';

		$Api=new ApiStatic($config);
		$IndexUrl=new Url(false);
		$IndexUrl->prefix.=Url::Encode($Eleanor->module['uri']).'/';

		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','title','status','pos'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `parents`, `status`, `title`,
(SELECT COUNT(`ch`.`id`) FROM `{$config['t']}` `ch` WHERE `ch`.`parent`=`s`.`id`) `children`
FROM `{$config['t']}` `s` INNER JOIN `{$config['tl']}` USING(`id`){$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$Url(['delete'=>$a['id']]);
			$a['_achildren']=$a['children']>0 ? $Url(['parent'=>$a['id']]) : false;
			$a['_atoggle']=$Url(['toggle'=>$a['id']]);
			$a['_acreate']=$Url(['do'=>'create','parent'=>$a['id']]);
			$a['_a']=$Api->GetUrl($a['id'],$IndexUrl);

			$items[$a['id']]=array_slice($a,2);
		}

		$links=[
			'sort_id'=>SortDynUrl('id',$query,$defsort,$deforder),
			'sort_title'=>SortDynUrl('title',$query,$defsort,$deforder),
			'sort_pos'=>SortDynUrl('pos',$query,$defsort,$deforder),
			'sort_status'=>SortDynUrl('status',$query,$defsort,$deforder),
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
	$c=AJAX
		? Eleanor::$Template->LoadSubPages($items,$query)
		: Eleanor::$Template->ShowList($items,$navi,$total>0,$cnt,$pp,$query,$page,$links);
	Response($c);
}