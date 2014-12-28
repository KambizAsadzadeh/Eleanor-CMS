<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor,$title;
$lang=Eleanor::$Language->Load(DIR.'admin/translation/users-*.php','users');

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$id=0;
$uid=Eleanor::$Login->Get('id');
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$Url(['do'=>'create']),
	'online'=>$Url(['do'=>'online']),
	'letters'=>$Url(['do'=>'letters']),
	'options'=>$Url(['do'=>'options']),
];

/** Формирование значения аватара (миниатюры) для шаблона
 * @param array $a Входящие данные
 * @return array*/
function Avatar(array$a)
{
	$image=false;

	if($a['miniature'] and $a['miniature']) switch($a['miniature_type'])
	{
		case'gallery':
			if(is_file($f=Template::$path['static'].'images/avatars/'.$a['miniature']))
				$image=[
					'type'=>'gallery',
					'path'=>$f,
					'http'=>Template::$http['static'].'images/avatars/'.$a['miniature'],
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
	case'add':
		goto CreateEdit;
	break;
	case'options':
		$Url->prefix.='do=options&amp;';
		$c=$Eleanor->Settings->Group('users-on-site');

		if($c)
			Response( Eleanor::$Template->Options($c) );
	break;
	case'letters':
		#ToDo! Удалить:
		Templates\Admin\T::$data['speedbar']=[];

		$controls=[
			$lang['letter4new'],
			'new_t'=>[
				'title'=>$lang['lettertitle'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'new'=>[
				'title'=>$lang['letterdescr'],
				'type'=>'editor',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'checkout'=>false,
					'ownbb'=>false,
					'smiles'=>false,
					'extra'=>['class'=>'need-tabindex','rows'=>7],
				],
			],
			$lang['letter4name'],
			'name_t'=>[
				'title'=>$lang['lettertitle'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'name'=>[
				'title'=>$lang['letterdescr'],
				'type'=>'editor',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'checkout'=>false,
					'ownbb'=>false,
					'smiles'=>false,
					'extra'=>['class'=>'need-tabindex','rows'=>7],
				],
			],
			$lang['letter4pass'],
			'pass_t'=>[
				'title'=>$lang['lettertitle'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'pass'=>[
				'title'=>$lang['letterdescr'],
				'type'=>'editor',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'checkout'=>false,
					'ownbb'=>false,
					'smiles'=>false,
					'extra'=>['class'=>'need-tabindex','rows'=>7],
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

					$file=DIR.'admin/letters/users-'.$l.'.php';
					file_put_contents($file,'<?php return '.var_export($tosave,true).';');
				}
			else
			{
				$file=DIR.'admin/letters/users-'.Language::$main.'.php';
				file_put_contents($file,'<?php return '.var_export($letter,true).';');
			}
		}
		else
			foreach($multilang as $l)
			{
				$file=DIR.'admin/letters/users-'.$l.'.php';
				$letter=file_exists($file) ? (array)include$file : [];
				$letter+=[
					'new_t'=>'',
					'new'=>'',
					'name_t'=>'',
					'name'=>'',
					'pass_t'=>'',
					'pass'=>'',
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
	case'online':
		$title[]=$lang['online-list'];
		$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$date=date('Y-m-d H:i:s');
		$query=['do'=>'online'];
		$where=['expire'=>"`s`.`expire`>'{$date}'"];


		if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
		{
			if($post)
				$page=1;

			if(isset($_REQUEST['fi']['online']))
			{
				$query['fi']['online']=(string)$_REQUEST['fi']['online'];

				switch($query['fi']['online'])
				{
					case'expired':
						$where['expire']="`s`.`expire`<'{$date}'";
					break;
					case'all':
						$where=[];
					break;
					default:
						unset($query['fi']['online']);
				}
			}
		}

		$groups=$items=[];
		$where=$where ? ' WHERE '.join(' AND ',$where) : '';
		$defsort='expire';
		$deforder='desc';
		include DIR.'sort-helper.php';

		$table=[
			'sessions'=>P.'sessions',
			'users_site'=>P.'users_site',
			'groups'=>P.'groups',
		];
		$R=Eleanor::$Db->Query("SELECT COUNT(`expire`) FROM `{$table['sessions']}` `s`{$where}");
		list($cnt)=$R->fetch_row();

		if($where)
		{
			$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table['sessions']}`");
			list($total)=$R->fetch_row();
		}
		else
			$total=$cnt;

		if($cnt>0)
		{
			list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['expire','ip','location'],$defsort,$deforder);

			if($sort=='ip')
				$sort="`s`.`ip_guest` {$order}, `ip_user` ";

			$R=Eleanor::$Db->Query("SELECT `s`.`type`, `s`.`user_id`, `s`.`enter`, `s`.`expire`, `s`.`expire`>NOW() `_online`, `s`.`ip_guest`, `s`.`ip_user`, `s`.`service`, `s`.`browser`, `s`.`location`, `s`.`name` `botname`, `us`.`groups`, `us`.`name`, `us`.`full_name`
FROM `{$table['sessions']}` `s` INNER JOIN `{$table['users_site']}` `us` ON `s`.`user_id`=`us`.`id` {$where}
ORDER BY `{$sort}` {$order}{$limit}");
			while($a=$R->fetch_assoc())
			{
				if($a['type']=='user')
					if($a['name'])
					{
						$a['_group']=(int)ltrim($a['groups'],',');
						$a['_aedit']=$Url(['edit'=>$a['user_id']]);
						$a['_adel']=$uid==$a['user_id'] ? false : $Url(['delete'=>$a['user_id']]);

						$groups[]=$a['_group'];
					}
					else
						$a['type']='guest';

				$items[]=$a;
			}

			if($groups)
			{
				$in=Eleanor::$Db->In($groups);
				$groups=[];
				$GUrl=clone $Url;
				$GUrl->prefix=DynUrl::$base.'section=management&amp;module=groups&amp;';

				$R=Eleanor::$Db->Query("SELECT `id`,`title_l` `title`,`style` FROM `{$table['groups']}` WHERE `id`{$in}");
				while($a=$R->fetch_assoc())
				{
					$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
					$a['_aedit']=$GUrl(['edit'=>$a['id']]);

					$groups[$a['id']]=array_slice($a,1);
				}
			}

			$links=[
				'sort_ip'=>SortDynUrl('ip',$query,$defsort,$deforder),
				'sort_expire'=>SortDynUrl('expire',$query,$defsort,$deforder),
				'sort_location'=>SortDynUrl('location',$query,$defsort,$deforder),
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

		$c=Eleanor::$Template->UsersOnline($items,$groups,$total>0,$cnt,$pp,$query,$page,$links);
		Response($c);
	break;
	default:
		GoAway(true);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$errors=[];
	$groups=\Eleanor\AwareInclude(__DIR__.'/users/groups.php');
	$users=\Eleanor\AwareInclude(__DIR__.'/users/users.php');
	$avatar=[
		'type'=>'uploadimage',
		'name'=>'a',
		'default'=>'',
		'post'=>&$Eleanor->us_post,
		'options'=>array(
			'types'=>array('png','jpeg','jpg','bmp','gif'),
			'path'=>Eleanor::$uploads.'/avatars',
			'max_size'=>Eleanor::$vars['avatar_bytes'],
			'max_image_size'=>Eleanor::$vars['avatar_size'],
			'filename'=>function($a)
			{
				return isset($a['id']) ? 'av-'.$a['id'].strrchr($a['filename'],'.') : $a['filename'];
			},
		),
	];


	if($id)
	{
		/*$R=Eleanor::$Db->Query("SELECT * FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();

		if($orig['protected'])
			unset($controls['is_admin'],$controls['banned'],$controls['closed_site_access']);*/
	}
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$table=USERS_TABLE;
	$R=Eleanor::$UsersDb->Query("SELECT `name`,`full_name` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
	if(!$user=$R->fetch_assoc() or !Eleanor::$ourquery or $id==$uid)
		return GoAway();

	if(isset($_POST['ok']))
	{
		UserManager::Delete($id);

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->Iframe((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($user,$back) );
}
else
{
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$where=$query=$items=$groups=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['name']) and $_REQUEST['fi']['name']!='')
		{
			$query['fi']['name']=(string)$_REQUEST['fi']['name'];
			$where[]='`u`.`name` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['name'],false).'%\'';
		}

		if(isset($_REQUEST['fi']['full_name']) and $_REQUEST['fi']['full_name']!='')
		{
			$query['fi']['full_name']=(string)$_REQUEST['fi']['full_name'];
			$where[]='`u`.`full_name` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['full_name'],false).'%\'';
		}

		if(!empty($_REQUEST['fi']['id']))
		{
			$ints=explode(',',Tasks::FillInt($_REQUEST['fi']['id']));
			$query['fi']['id']=(string)$_REQUEST['fi']['id'];
			$where[]='`id`'.Eleanor::$Db->In($ints);
		}

		if(!empty($_REQUEST['fi']['group']))
		{
			$query['fi']['group']=(int)$_REQUEST['fi']['group'];
			$where[]="`groups` LIKE ',{$query['fi']['group']},'";
		}

		if(!empty($_REQUEST['fi']['last_visit_from']) and 0<$t=strtotime($_REQUEST['fi']['last_visit_from']))
		{
			$query['fi']['last_visit_from']=$_REQUEST['fi']['last_visit_from'];
			$t=date('Y-m-d H:i:s',$t);
			$where[]="`u`.`last_visit`>='{$t}'";
		}

		if(!empty($_REQUEST['fi']['last_visit_to']) and 0<$t=strtotime($_REQUEST['fi']['last_visit_to']))
		{
			$query['fi']['last_visit_to']=$_REQUEST['fi']['last_visit_to'];
			$t=date('Y-m-d H:i:s',$t);
			$where[]="`u`.`last_visit`<='{$t}'";
		}

		if(!empty($_REQUEST['fi']['register_from']) and 0<$t=strtotime($_REQUEST['fi']['register_from']))
		{
			$query['fi']['register_from']=$_REQUEST['fi']['register_from'];
			$t=date('Y-m-d H:i:s',$t);
			$where[]="`u`.`register`>='{$t}'";
		}

		if(!empty($_REQUEST['fi']['register_to']) and 0<$t=strtotime($_REQUEST['fi']['register_to']))
		{
			$query['fi']['register_to']=$_REQUEST['fi']['register_to'];
			$t=date('Y-m-d H:i:s',$t);
			$where[]="`u`.`register`<='{$t}'";
		}

		if(!empty($_REQUEST['fi']['ip']))
		{
			$query['fi']['ip']=$_REQUEST['fi']['ip'];
			$where[]='`u`.`ip`='.Eleanor::$Db->Escape(inet_pton($_REQUEST['fi']['ip']));
		}

		if(!empty($_REQUEST['fi']['email']))
		{
			$query['fi']['email']=$_REQUEST['fi']['email'];
			$where[]='`u`.`email` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['email'],false).'%\'';
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';

	if($post and isset($_POST['event'],$_POST['items']))
		switch($_POST['event'])
		{
			case'delete':
				$items=array_diff((array)$_POST['items'],[$uid]);

				UserManager::Delete($items);
		}

	$defsort='id';
	$deforder='desc';
	include DIR.'sort-helper.php';

	$table=[
		'main'=>USERS_TABLE,
		'site'=>P.'users_site',
		'extra'=>P.'users_extra',
	];

	if(Eleanor::$Db===Eleanor::$UsersDb)
		$where=" INNER JOIN `{$table['site']}` USING(`id`){$where}";
	else
		$table['main']=P.'users_site';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table['main']}` `u` INNER JOIN `{$table['text']}` USING(`id`){$where}");
	list($cnt)=$R->fetch_row();

	if($cnt>0)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','name','email','full_name','last_visit'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `u`.`full_name`, `u`.`name`, `email`, `groups`, `ip`, `u`.`last_visit` FROM `{$table['main']}` `u` INNER JOIN `{$table['extra']}` USING(`id`){$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			if($a['ip'])
				$a['ip']=inet_ntop($a['ip']);

			$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : [];
			$groups=array_merge($groups,$a['groups']);

			$a['_a']=UserLink($a['id'],$a['name'],'index');
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$uid==$a['id'] ? false : $Url(['delete'=>$a['id']]);

			$items[$a['id']]=array_slice($a,1);
		}

		if($groups)
		{
			$old=$Url->prefix;
			$Url->prefix=DynUrl::$base.'section=management&amp;module=groups&amp;';
			$table=P.'groups';
			$in=Eleanor::$Db->In($groups);
			$groups=[];

			$R=Eleanor::$Db->Query("SELECT `id`, `title_l` `title`, `style` FROM `{$table}` WHERE `id`{$in}");
			while($a=$R->fetch_assoc())
			{
				$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
				$a['_aedit']=$Url(['edit'=>$a['id']]);
				$groups[$a['id']]=array_slice($a,1);
			}

			$Url->prefix=$old;
		}

		$links=[
			'sort_name'=>SortDynUrl('name',$query,$defsort,$deforder),
			'sort_email'=>SortDynUrl('email',$query,$defsort,$deforder),
			'sort_last_visit'=>SortDynUrl('last_visit',$query,$defsort,$deforder),
			'sort_ip'=>SortDynUrl('ip',$query,$defsort,$deforder),
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
	$c=Eleanor::$Template->ShowList($items,$groups,$cnt,$pp,$query,$page,$links);
	Response($c);
}

/*function AddEdit($id,$error='')
{global$Eleanor,$title;
	$uid=Eleanor::$Login->Get('id');
	$overload=$values=array();
	$lang=Eleanor::$Language['users'];
	if($id)
	{
		$R=Eleanor::$UsersDb->Query('SELECT * FROM `'.USERS_TABLE.'` WHERE `id`='.$id.' LIMIT 1');
		if(!$values=$R->fetch_assoc())
			return GoAway(true);
		$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'users_site` WHERE `id`='.$id.' LIMIT 1');
		$values+=$R->fetch_assoc();
		$values+=array(
			'_slnew'=>false,
			'_slname'=>true,
			'_slpass'=>true,
			'_cleanfla'=>false,
			'_overskip'=>array(),
			'_externalauth'=>array(),
			'_sessions'=>array(),
			'pass'=>'',
			'pass2'=>'',
		);
		$values['failed_logins']=$values['failed_logins'] ? (array)unserialize($values['failed_logins']) : array();

		$R=Eleanor::$Db->Query('SELECT `provider`,`provider_uid`,`identity` FROM `'.P.'users_external_auth` WHERE `id`='.$id);
		while($a=$R->fetch_assoc())
			$values['_externalauth'][]=$a;

		$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$id.' LIMIT 1');
		if($a=$R->fetch_assoc())
		{
			$cl=get_class(Eleanor::$Login);
			$lk=Eleanor::$Login->Get('login_key');
			$values['_sessions']=$a['login_keys'] ? (array)unserialize($a['login_keys']) : array();
			foreach($values['_sessions'] as $cl=>&$sess)
				foreach($sess as $k=>&$v)
					$v['_candel']=$uid!=$id || $k!=$lk;
		}

		if(!$error)
		{
			$values['groups']=$values['groups'] ? explode(',,',trim($values['groups'],',')) : array();
			if($values['groups'])
			{
				$values['_group']=reset($values['groups']);
				$k=key($values['groups']);
				unset($values['groups'][$k]);
			}
			else
				$values['_group']=GROUP_USER;

			$values['groups_overload']=$values['groups_overload'] ? (array)unserialize($values['groups_overload']) : array();
			if(!isset($values['groups_overload']['value']) or !is_array($values['groups_overload']['value']))
				$values['groups_overload']['value']=array();
			foreach($Eleanor->gp as &$gpv)
				foreach($values['groups_overload']['value'] as $k=>&$v)
					if(isset($gpv[$k]))
					{
						$overload[$k]['value']=$v;
						unset($values['groups_overload']['value'][$k]);
						continue;
					}
			if(!isset($values['groups_overload']['method']) or !is_array($values['groups_overload']['method']))
				$values['groups_overload']['method']=array();
			$values['_overskip']=$values['groups_overload']['method'];
			unset($values['groups_overload']);

			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'users_extra` WHERE `id`='.$id.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway(true);
			if($a['avatar_type']=='local' and $a['avatar_location'])
				$a['avatar_location']='images/avatars/'.$a['avatar_location'];
			foreach($a as $k=>&$v)
				if(isset($Eleanor->us[$k]))
					$values[$k]['value']=$v;
				else
					$values[$k]=$v;
			$values['_aupload']=$a['avatar_type']!='local';
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$values=array(
			'full_name'=>'',
			'name'=>'',
			'email'=>'',
			'_group'=>GROUP_USER,
			'groups'=>array(),
			'language'=>'',
			'banned_until'=>'',
			'ban_explain'=>'',
			'last_visit'=>'',
			'_slnew'=>true,
			'_slname'=>true,
			'_slpass'=>true,
			'_cleanfla'=>false,
			'_overskip'=>array(),
			'_aupload'=>false,
			'_sessions'=>array(),
			'pass'=>'',
			'pass2'=>'',
			'timezone'=>'',
			'failed_logins'=>array(),
			'avatar_location'=>false,
		);
		foreach($Eleanor->gp as $k=>&$v)
			if(is_array($v))
				$values['_overskip'][]=$k;
		$values['register']=date('Y-m-d H:i:s');
	}

	if($error)
	{
		if($error===true)
			$error='';
		$Eleanor->us_post=$bypost=true;
		$values['full_name']=isset($_POST['full_name']) ? (string)$_POST['full_name'] : '';
		$values['name']=isset($_POST['name']) ? (string)$_POST['name'] : '';
		$values['email']=isset($_POST['email']) ? (string)$_POST['email'] : '';
		$values['_group']=isset($_POST['_group']) ? (int)$_POST['_group'] : '';
		$values['groups']=isset($_POST['groups']) ? (array)$_POST['groups'] : array();
		$values['banned_until']=isset($_POST['banned_until']) ? (string)$_POST['banned_until'] : '';
		$values['ban_explain']=isset($_POST['ban_explain']) ? (string)$_POST['ban_explain'] : '';
		$values['language']=isset($_POST['language']) ? (string)$_POST['language'] : '';
		$values['timezone']=isset($_POST['timezone']) ? (string)$_POST['timezone'] : '';
		$values['_slnew']=isset($_POST['_slnew']);
		$values['_slname']=isset($_POST['_slname']);
		$values['_slpass']=isset($_POST['_slpass']);
		$values['_cleanfla']=isset($_POST['_cleanfla']);
		$values['_overskip']=isset($_POST['_overskip']) ? (array)$_POST['_overskip'] : array();
		$values['_atype']=isset($_POST['_atype']) ? $_POST['_atype']=='upload' : false;
		$values['pass']=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
		$values['pass2']=isset($_POST['pass2']) ? (string)$_POST['pass2'] : '';
		$values['_aupload']=isset($_POST['_atype']) && $_POST['_atype']=='upload';
		$values['avatar_location']=isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '';
	}
	else
	{
		$al=$values['avatar_location'] ? ($values['_aupload'] && strpos($values['avatar_location'],'://')===false ? Eleanor::$uploads.'/avatars/' : '').$values['avatar_location'] : '';
		if($values['_aupload'])
		{
			$Eleanor->avatar['value']=$al;
			$values['avatar_location']='';
		}
		else
			$values['avatar_location']=$al;
		$bypost=false;
	}

	$Eleanor->Controls->arrname=array('avatar');
	$upavatar=$Eleanor->Controls->DisplayControl($Eleanor->avatar);

	$Eleanor->Controls->arrname=array('extra');
	$extra=$Eleanor->Controls->DisplayControls($Eleanor->us,$values);

	$Eleanor->Controls->arrname=array('overload');
	$overload=$Eleanor->Controls->DisplayControls($Eleanor->gp,$values);

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id && $id!=$uid ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEditUser($id,$values,$Eleanor->gp,$overload,$upavatar,$Eleanor->us,$extra,$bypost,$error,$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$lang=Eleanor::$Language['users'];
	$C=new Controls;
	$C->throw=false;
	$C->name=array('extra');
	try
	{
		$values=$C->SaveControls($Eleanor->us);
	}
	catch(EE$E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}

	$C->name=array('overload');
	try
	{
		$overload=$C->SaveControls($Eleanor->gp);
	}
	catch(EE $E)
	{
		return AddEdit($id,array('ERROR'=>$E->getMessage()));
	}
	$errors=$C->errors;

	$values+=array(
		'full_name'=>isset($_POST['full_name']) ? (string)Eleanor::$POST['full_name'] : '',
		'name'=>isset($_POST['name']) ? (string)$_POST['name'] : '',
		'email'=>empty($_POST['email']) ? null : (string)$_POST['email'],
		'groups'=>isset($_POST['groups']) ? (array)$_POST['groups'] : array(),
		'banned_until'=>isset($_POST['banned_until']) ? (string)$_POST['banned_until'] : '',
		'ban_explain'=>$Eleanor->Editor_result->GetHtml('ban_explain'),
		'language'=>isset($_POST['language']) ? (string)$_POST['language'] : '',
		'timezone'=>isset($_POST['timezone']) ? (string)$_POST['timezone'] : '',
	);

	if($values['banned_until'] and false===strtotime($values['banned_until']))
		$errors[]='ERROR_BANDATE';
	if(!$values['banned_until'])
		$values['banned_until']=null;

	$extra=array(
		'_group'=>isset($_POST['_group']) ? (int)$_POST['_group'] : 0,
		'_slnew'=>isset($_POST['_slnew']),
		'_slname'=>isset($_POST['_slname']),
		'_slpass'=>isset($_POST['_slpass']),
		'_cleanfla'=>isset($_POST['_cleanfla']),
		'_overskip'=>isset($_POST['_overskip']) ? (array)$_POST['_overskip'] : array(),
		'_atype'=>isset($_POST['_atype']) ? (string)$_POST['_atype'] : false,
		'pass'=>isset($_POST['pass']) ? (string)$_POST['pass'] : '',
		'pass2'=>isset($_POST['pass2']) ? (string)$_POST['pass2'] : '',
		'avatar'=>isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '',
	);

	if($extra['pass'] and $extra['pass']!=$extra['pass2'])
		$errors[]='PASSWORD_MISMATCH';

	if($k=array_keys($values['groups'],$extra['_group']))
		foreach($k as &$v)
			unset($values['groups'][$v]);
	array_unshift($values['groups'],$extra['_group']);
	if($extra['_cleanfla'])
		$values['failed_logins']='';

	$C->name=array('avatar');
	if($id)
	{
		$Eleanor->avatar['id']=$id;
		$R=Eleanor::$Db->Query('SELECT `avatar_location`,`avatar_type` FROM `'.P.'users_extra` WHERE `id`='.$id.' LIMIT 1');
		$oldavatar=$R->fetch_assoc();
	}

	if($extra['_atype']=='upload')
		try
		{
			$avatar=$C->SaveControl($Eleanor->avatar+array('value'=>isset($oldavatar) && $oldavatar['avatar_type']=='upload' && $oldavatar['avatar_location'] ? Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location'] : ''));
		}
		catch(EE$E)
		{
			return AddEdit($id,array('ERROR'=>$E->getMessage()));
		}
	else
		$avatar=$extra['avatar'];

	if($extra['_atype']=='upload' and $avatar)
		$atype=strpos($avatar,'://')===false ? 'upload' : 'url';
	else
		$atype=$avatar ? 'local' : '';

	if(($atype=='upload' or $atype=='local') and $avatar and !is_file(Eleanor::$root.$avatar))
		$errors[]='AVATAR_NOT_EXISTS';

	if($atype=='local' and $avatar)
		$avatar=preg_replace('#^images/avatars/#','',$avatar);

	foreach($extra['_overskip'] as $k=>&$v)
		if($v=='inherit' and isset($overload[$k]))
			unset($overload[$k],$extra['_overskip'][$k]);

	$values['groups_overload']=$overload ? serialize(array('method'=>$extra['_overskip'],'value'=>$overload)) : '';

	$letterlang=$values['language'] ? $values['language'] : Language::$main;

	if($errors)
		return AddEdit($id,$errors);

	if($id)
	{
		$R=Eleanor::$UsersDb->Query('SELECT `full_name`,`name` FROM `'.USERS_TABLE.'` WHERE `id`='.$id.' LIMIT 1');
		if(!$old=$R->fetch_assoc())
			return GoAway();

		$isf=is_file($f=Eleanor::$root.'addons/admin/letters/users-'.$letterlang.'.php');
		$cansend=$values['email'] && ($extra['_slname'] or $extra['_slpass']) && $isf && ($l=include($f)) && is_array($l);

		if($extra['pass'])
			$values['_password']=$extra['pass'];
		try
		{
			UserManager::Update($values,$id);
			if($cansend and $old['name']!=$values['name'] and $extra['_slname'] and isset($l['name_t'],$l['name']))
			{
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'newlogin'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'oldlogin'=>$old['name'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$values['email'],
					Eleanor::ExecBBLogic($l['name_t'],$repl),
					Eleanor::ExecBBLogic($l['name'],$repl)
				);
			}
			if($cansend and $extra['pass'] and $extra['_slpass'] and isset($l['pass_t'],$l['pass']))
			{
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'login'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'pass'=>$extra['pass'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				Email::Simple(
					$values['email'],
					Eleanor::ExecBBLogic($l['pass_t'],$repl),
					Eleanor::ExecBBLogic($l['pass'],$repl)
				);
			}
		}
		catch(EE$E)
		{
			$mess=$E->getMessage();
			$errors=array();
			switch($mess)
			{
				case'NAME_TOO_LONG':
					$errors['NAME_TOO_LONG']=$lang['NAME_TOO_LONG']($E->extra['max'],$E->extra['you']);
				break;
				case'PASS_TOO_SHORT':
					$errors['PASS_TOO_SHORT']=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
				break;
				default:
					$errors[]=$mess;
			}
			return AddEdit($id,$errors);
		}

		if($atype=='upload')
			$avatar=basename($avatar);
		if($oldavatar['avatar_location']!=$avatar or $oldavatar['avatar_type']!=$atype)
		{
			if($oldavatar['avatar_type']=='upload' and $oldavatar['avatar_location'] and $oldavatar['avatar_location']!=$avatar)
				Files::Delete(Eleanor::$root.Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location']);
			UserManager::Update(array('avatar_location'=>$avatar,'avatar_type'=>$atype),$id);
		}
	}
	else
	{
		if($values['full_name']=='')
			$values['full_name']=htmlspecialchars($values['full_name'],ELENT,CHARSET,true);
		if(!$extra['pass'])
		{
			Eleanor::LoadOptions('user-profile',false);
			$extra['pass']=uniqid();
			$extra['pass']=strlen($extra['pass'])>=Eleanor::$vars['min_pass_length'] ? substr($extra['pass'],0,Eleanor::$vars['min_pass_length']>7 ? Eleanor::$vars['min_pass_length'] : 7) : str_pad($extra['pass'],Eleanor::$vars['min_pass_length'],uniqid(),STR_PAD_RIGHT);
		}
		try
		{
			$newid=UserManager::Add($values+array('_password'=>$extra['pass']));
		}
		catch(EE_SQL$E)
		{
			$E->Log();
			return AddEdit($id,array($E->getMessage()));
		}
		catch(EE$E)
		{
			$mess=$E->getMessage();
			$errors=array();
			switch($mess)
			{
				case'NAME_TOO_LONG':
					$errors['NAME_TOO_LONG']=$lang['NAME_TOO_LONG']($E->extra['max'],$E->extra['you']);
				break;
				case'PASS_TOO_SHORT':
					$errors['PASS_TOO_SHORT']=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
				break;
				default:
					$errors[]=$mess;
			}
			return AddEdit($id,$errors);
		}
		if($avatar)
		{
			if($atype=='upload')
			{
				rename(Eleanor::$root.$avatar,Eleanor::$root.Eleanor::$uploads.'/avatars/'.($newa='av-'.$newid.strrchr($avatar,'.')));
				$avatar=$newa;
			}
			UserManager::Update(array('avatar_location'=>$avatar,'avatar_type'=>$atype),$newid);
		}

		if($values['email'] and $extra['_slnew'])
			do
			{
				if(!is_file($f=Eleanor::$root.'addons/admin/letters/users-'.$letterlang.'.php'))
					break;
				$l=include($f);
				if(!is_array($l) or !isset($l['new_t'],$l['new']))
					break;
				$repl=array(
					'site'=>Eleanor::$vars['site_name'],
					'name'=>$values['full_name'],
					'login'=>htmlspecialchars($values['name'],ELENT,CHARSET),
					'pass'=>$extra['pass'],
					'link'=>PROTOCOL.Eleanor::$domain.Eleanor::$site_path,
				);
				try
				{
					Email::Simple(
						$values['email'],
						Eleanor::ExecBBLogic($l['new_t'],$repl),
						Eleanor::ExecBBLogic($l['new'],$repl)
					);
				}
				catch(EE$E){}
			}while(false);
	}
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}*/