<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\BBCode, Eleanor\Classes\EE, Eleanor\Classes\EE_DB, Eleanor\Classes\Email, Eleanor\Classes\Files,
	Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

Eleanor::$Language->Load(DIR.'admin/translation/users-*.php','users');
Eleanor::$Template->queue['users']=Eleanor::$Template->classes.'Users.php';

global$Eleanor,$title;

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$lang=Eleanor::$Language['users'];
$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;
$id=0;
$uid=Eleanor::$Login->Get('id');
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$Url(['do'=>'create']),
	'online'=>$Url(['do'=>'online']),
	'letters'=>$Url(['do'=>'letters']),
	'options'=>$Url(['do'=>'options']),
];

if(isset($_REQUEST['do'])) switch($_REQUEST['do'])
{
	case'create':
		goto CreateEdit;
	break;
	case'options':
		$Url->prefix.='do=options&amp;';
		$c=$Eleanor->Settings->Group('users-on-site');

		if($c)
			Response( Eleanor::$Template->Options($c) );
	break;
	case'letters':
		$controls=[
			$lang['letter4created'],
			'created_t'=>[
				'title'=>$lang['letter-title'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'created'=>[
				'title'=>$lang['letter-descr'],
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
			$lang['letter4renamed'],
			'renamed_t'=>[
				'title'=>$lang['letter-title'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'renamed'=>[
				'title'=>$lang['letter-descr'],
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
			$lang['letter4newpass'],
			'newpass_t'=>[
				'title'=>$lang['letter-title'],
				'type'=>'input',
				'multilang'=>Eleanor::$vars['multilang'],
				'post'=>$post,
				'options'=>[
					'safe'=>true,
					'extra'=>['class'=>'need-tabindex'],
				],
			],
			'newpass'=>[
				'title'=>$lang['letter-descr'],
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

		$values=$errors=[];
		$multilang=Eleanor::$vars['multilang'] ? array_keys(Eleanor::$langs) : [Language::$main];
		if($post)
		{
			try
			{
				$letter=$Eleanor->Controls->SaveControls($controls);
			}
			catch(EE$E)
			{
				$errors['ERROR']=$E->getMessage();
				goto EditLetter;
			}

			if(Eleanor::$vars['multilang'])
				foreach($multilang as $l)
				{
					$tosave=[];

					foreach($letter as $k=>$v)
						$tosave[$k]=$controls[$k]['multilang'] ? FilterLangValues($v,$l) : $v;

					$file=DIR."admin/letters/users-{$l}.php";
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
					'created_t'=>'',
					'created'=>'',
					'renamed_t'=>'',
					'renamed'=>'',
					'newpass_t'=>'',
					'newpass'=>'',
				];

				if(Eleanor::$vars['multilang'])
					foreach($letter as $k=>$v)
						$values[$k]['value'][$l]=$v;
				else
					foreach($letter as $k=>$v)
						$values[$k]['value']=$v;
			}

		EditLetter:

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
		$fi_user='';

		if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
		{
			if($post)
				$page=1;

			if(!empty($_REQUEST['fi']['offline']))
			{
				$query['fi']['offline']=(string)$_REQUEST['fi']['offline'];

				switch($query['fi']['offline'])
				{
					case'only':
						$where['expire']="`s`.`expire`<'{$date}'";
					break;
					case'include':
						$where=[];
					break;
					default:
						unset($query['fi']['online']);
				}
			}

			if(!empty($_REQUEST['fi']['ip']))
			{
				$query['fi']['ip']=$_REQUEST['fi']['ip'];
				$ip=inet_pton($_REQUEST['fi']['ip']);
				$where[]="`guest_ip`={$ip} OR `user_ip`={$ip}";
			}

			if(!empty($_REQUEST['fi']['user_id']))
			{
				$user_id=(int)$_REQUEST['fi']['user_id'];
				$table=USERS_TABLE;

				$R=Eleanor::$UsersDb->Query("SELECT `name` FROM `{$table}` WHERE `id`={$user_id} LIMIT 1");
				if($a=$R->fetch_assoc())
				{
					$fi_user=$a['name'];
					$query['fi']['user_id']=$user_id;
					$where[]="`s`.`user_id`={$user_id}";
				}
			}
			elseif(!empty($_REQUEST['fi']['user']))
			{
				$fi_user=(string)$_REQUEST['fi']['user'];
				$user=Eleanor::$Db->Escape($fi_user,false);
				$where[]="`s`.`name` LIKE '%{$user}%'";
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
			'users_extra'=>P.'users_extra',
			'groups'=>P.'groups',
		];
		$R=Eleanor::$Db->Query("SELECT COUNT(`expire`) FROM `{$table['sessions']}` `s`{$where}");
		list($cnt)=$R->fetch_row();

		if($where)
		{
			$R=Eleanor::$Db->Query("SELECT COUNT(`expire`) FROM `{$table['sessions']}`");
			list($total)=$R->fetch_row();
		}
		else
			$total=$cnt;

		if($cnt>0)
		{
			list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['expire','ip','location','enter'],$defsort,$deforder);

			if($sort=='ip')
				$sort="`s`.`ip_guest` {$order}, `ip_user` ";

			$R=Eleanor::$Db->Query("SELECT `s`.`type`, `s`.`user_id`, `s`.`enter`, `s`.`expire`, `s`.`expire`>NOW() `_online`, `s`.`ip_guest`, `s`.`ip_user`, `s`.`service`, `s`.`browser`, `s`.`location`, `s`.`name` `botname`, `us`.`groups`, `us`.`name`, `us`.`full_name`, `ex`.`avatar`, `ex`.`avatar_type`
FROM `{$table['sessions']}` `s` LEFT JOIN `{$table['users_site']}` `us` ON `s`.`user_id`=`us`.`id` LEFT JOIN `{$table['users_extra']}` `ex` ON `ex`.`id`=`us`.`id` {$where}
ORDER BY `{$sort}` {$order}{$limit}");
			while($a=$R->fetch_assoc())
			{
				if($a['ip_guest'])
					$a['ip_guest']=inet_ntop($a['ip_guest']);

				if($a['ip_user'])
					$a['ip_user']=inet_ntop($a['ip_user']);

				if($a['user_id'])
					$a['_adetail']=$Url(['do'=>'detail','id'=>$a['user_id'],'service'=>$a['service']]);
				else
					$a['_adetail']=$Url(['do'=>'detail','ip'=>$a['ip_guest'],'service'=>$a['service']]);

				if($a['type']=='user')
					if($a['name'])
					{
						$a['avatar']=Avatar($a);
						$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : [];
						$a['_a']=UserLink($a['user_id'],$a['name'],'index');
						$a['_aedit']=$Url(['edit'=>$a['user_id']]);
						$a['_adel']=$uid==$a['user_id'] ? false : $Url(['delete'=>$a['user_id']]);

						$groups=array_merge($groups,$a['groups']);
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

				$R=Eleanor::$Db->Query("SELECT `id`, `title_l` `title`, `style` FROM `{$table['groups']}` WHERE `id`{$in}");
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
				'sort_enter'=>SortDynUrl('enter',$query,$defsort,$deforder),
				'sort_location'=>SortDynUrl('location',$query,$defsort,$deforder),
				'form_items'=>$Url($query+['page'=>$page>1 ? $page : null]),
				'pp'=>function($n)use($Url,$query){ $query['per-page']=$n; return$Url($query); },
				'pagination'=>function($n)use($Url,$query){ return$Url($query+['page'=>$n===1 ? null : $n]); },
			];
			$query['sort']=$sort;
			$query['order']=$order;
		}
		else
		{
			$pp=0;
			$links=[];
		}

		if($fi_user)
			$query['fi']['user']=$fi_user;

		$links['nofilter']=isset($query['fi']) ? $Url(['fi'=>[]]+$query) : false;
		$c=Eleanor::$Template->OnlineList($items,$groups,$total>0,$cnt,$pp,$query,$page,$links);
		Response($c);
	break;
	case'detail':
		$ip=isset($_GET['ip']) ? (string)$_GET['ip'] : '';
		$ip=filter_var($ip,FILTER_VALIDATE_IP) ? inet_pton($ip) : '';
		$ip=Eleanor::$Db->Escape($ip);
		$id=isset($_GET['id']) ? (int)$_GET['id'] : 0;
		$service=isset($_GET['service']) ? Eleanor::$Db->Escape((string)$_GET['service']) : '';
		$table=[
			's'=>P.'sessions',
			'us'=>P.'users_site',
		];

		$R=Eleanor::$Db->Query("SELECT `s`.`type`, `s`.`enter`, `s`.`ip_guest`, `s`.`ip_user`, `s`.`info`, `s`.`service`, `s`.`browser`, `s`.`location`, `s`.`name` `botname`, `us`.`groups`, `us`.`name`
FROM `{$table['s']}` `s`
LEFT JOIN `{$table['us']}` `us` ON `s`.`user_id`=`us`.`id`
WHERE `s`.`ip_guest`={$ip} AND `s`.`user_id`={$id} AND `s`.`service`={$service} LIMIT 1");
		if($session=$R->fetch_assoc())
		{
			if($session['type']=='user' and $session['groups'])
			{
				$g=[(int)ltrim($session['groups'],',')];
				$session['style']=join('',Permissions::ByGroup($g,'style'));
			}
			else
				$session['style']='';

			if($session['ip_guest']!=='')
				$session['ip_guest']=inet_ntop($session['ip_guest']);

			if($session['ip_user']!=='')
				$session['ip_user']=inet_ntop($session['ip_user']);

			$session['info']=$session['info'] ? json_decode($session['info'],true) : [];

			if(isset($session['info']['ips']))
				foreach($session['info'] as &$v)
					$v=inet_ntop($v);

			if($session['name'])
				$title[]=htmlspecialchars($session['name'],ENT,\Eleanor\CHARSET);
			else if($session['botname'])
				$title[]=htmlspecialchars($session['botname'],ENT,\Eleanor\CHARSET);
			else
				$title[]=$session['ip_guest'];
		}
		else
		{
			if(isset($_GET['iframe']))
				return Response( Eleanor::$Template->Iframe((string)$Url) );

			return GoAway((string)$Url);
		}

		Response( (string)Eleanor::$Template->SessionDetail($session) );
	break;
	case'author-autocomplete':
		$q=isset($_REQUEST['query']) ? (string)$_REQUEST['query'] : '';
		$out=[];

		if($q!='')
		{
			$q=Eleanor::$UsersDb->Escape($q,false);
			$table=USERS_TABLE;
			$R=Eleanor::$UsersDb->Query("SELECT `id`, `name` FROM `{$table}` WHERE `name` LIKE '%{$q}%' ORDER BY `name` ASC LIMIT 14");
			while($a=$R->fetch_assoc())
			{
				$a['_a']=UserLink($a['id'],$a['name'],'admin');
				$out[]=$a;
			}
		}

		OutPut::SendHeaders('application/json');
		OutPut::Gzip(json_encode($out,JSON^JSON_PRETTY_PRINT));
	break;
	default:
		GoAway(true);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$errors=[];
	$maxupload=Eleanor::$Permissions ? Eleanor::$Permissions->MaxUpload() : Files::SizeToBytes(ini_get('upload_max_filesize'));
	$groups=\Eleanor\AwareInclude(__DIR__.'/users/groups.php');
	$extra=\Eleanor\AwareInclude(__DIR__.'/users/extra.php');
	$table=[
		'main'=>USERS_TABLE,
		'us'=>P.'users_site',
		'ue'=>P.'users_extra',
		'ea'=>P.'users_external_auth',
	];

	if(AJAX)
	{
		if(isset($_FILES['avatar']) and is_uploaded_file($_FILES['avatar']['tmp_name']) and $_FILES['avatar']['size']<=$maxupload
			and preg_match('#\.(png|jpe?g|gif)$#',$_FILES['avatar']['name']) and getimagesize($_FILES['avatar']['tmp_name']))
		{
			$tempdir="avatar-{$id}-{$uid}".strrchr($_FILES['avatar']['name'],'.');
			$temppath=Template::$path['uploads'].'temp/';

			if(!is_dir($temppath))
				Files::MkDir($temppath);

			$temppath.=$tempdir;

			if(is_file($temppath))
				Files::Delete($temppath);

			if(move_uploaded_file($_FILES['avatar']['tmp_name'],$temppath))
				Response([
					'http'=>Template::$http['uploads'].'temp/'.$tempdir,
					'src'=>$tempdir,
				]);
			else
				Error();
		}
		elseif(isset($_POST['avatar-gallery']))
		{
			$galleries=$avatars=[];
			$gallery=(string)$_POST['avatar-gallery'];

			if($gallery)
				$gallery=preg_replace('#[^a-z\d_\-]+#i','',$gallery);

			if($gallery)
			{
				$dirs=[];
				$files=glob(Template::$path['static']."images/avatars/{$gallery}/*.{jpg,png,jpeg,gif}",GLOB_BRACE);
			}
			else
			{
				$dirs=glob(Template::$path['static'].'images/avatars/*', GLOB_MARK|GLOB_ONLYDIR);
				$files=[];
			}

			if($files) foreach($files as $v)
			{
				$bn=basename($v);
				$avatars[$gallery.'/'.$bn]=[
					'path'=>$v,
					'http'=>Template::$http['static']."images/avatars/{$gallery}/{$bn}",
				];
			}

			if($dirs) foreach($dirs as $v)
			{
				$title_=$bn=basename($v);
				$image=false;

				if(is_file($v.'config.ini'))
				{
					$session=parse_ini_file($v.'config.ini',true);

					if(isset($session['title']))
						$title_=FilterLangValues($session['title'],'',$bn);

					if(isset($session['options']['cover']) and is_file($v.$session['options']['cover']))
						$image=$bn.'/'.$session['options']['cover'];
				}

				if(!$image and $temp=glob($v.'*.{jpg,png,jpeg,gif}',GLOB_BRACE))
					$image=$bn.'/'.basename($temp[0]);

				if($image)
					$galleries[$bn]=[
						'title'=>$title_,
						'path'=>Template::$path['static'].'images/avatars/'.$image,
						'http'=>Template::$http['static'].'images/avatars/'.$image,
					];
			}

			Response( (string)Eleanor::$Template->MiniatureGallery($avatars,$galleries,$avatars ? '' : null) );
		}
		else
			Error();

		return;
	}

	if($id)
	{
		$R=Eleanor::$UsersDb->Query("SELECT * FROM `{$table['main']}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();

		$R=Eleanor::$Db->Query("SELECT * FROM `{$table['us']}` WHERE `id`={$id} LIMIT 1");
		if($R->num_rows)
			$orig+=$R->fetch_assoc();
		else
			return GoAway();

		$R=Eleanor::$Db->Query("SELECT * FROM `{$table['ue']}` WHERE `id`={$id} LIMIT 1");
		if($R->num_rows)
			$orig+=$R->fetch_assoc();
		else
			return GoAway();

		#Активные сессии
		$orig['login_keys']=$orig['login_keys'] ? json_decode($orig['login_keys'],true) : [];

		#Группы пользователя: основная и вторичные
		$orig['groups']=$orig['groups'] ? explode(',,',trim($orig['groups'],',')) : [];

		if($orig['groups'])
		{
			$orig['_group']=(int)reset($orig['groups']);
			$k=key($orig['groups']);

			unset($orig['groups'][$k]);
		}
		else
			$orig['_group']=UserManager::GROUP_USER;

		#Перезагрузка прав групп
		$groups_overload=[];

		$orig['groups_overload']=$orig['groups_overload'] ? json_decode($orig['groups_overload'],true) : [];

		if(isset($orig['groups_overload']['value']) and is_array($orig['groups_overload']['value']))
			$groups_overload=$orig['groups_overload']['value'];

		foreach($groups_overload as $k=>$v)
			if(isset($groups[$k]))
				$groups[$k]['value']=$v;

		#Экстра параметры
		foreach($orig as $k=>$v)
			if(isset($extra[$k]))
				$extra[$k]['value']=$v;
	}

	if($post)
	{
		include_once DIR.'crud.php';

		$C=new Controls;
		$C->name=['extra'];
		$values=$C->SaveControls($extra);
		$errors=$C->errors;

		$C=new Controls;
		$C->name=['groups_overload'];
		$values['groups_overload']['value']=$C->SaveControls($groups);

		if($C->errors)
			$errors=array_merge($errors,$C->errors);

		if($id and isset($orig['groups_overload']['value']))
			$values['groups_overload']['value']+=$orig['groups_overload']['value'];

		$values['groups_overload']['method']=isset($_POST['_groups_overload_method']) ? (array)$_POST['_groups_overload_method'] : [];

		PostValues($values,[
			'full_name'=>'string',
			'name'=>'string',
			'email'=>'string',
			'timezone'=>'string',
			'groups'=>'array',
			'language'=>Eleanor::$vars['multilang'] ? 'string' : false,
			'banned_until'=>'string',
			'ban_explain'=>'string',
			'_password'=>'string',
		]);

		#Проверка корректности бана
		if(isset($values['banned_until']) and $values['banned_until']!=='')
		{
			if(false===strtotime($values['banned_until']))
				$errors[]='ERROR_BAN_DATE';

			if(!$values['banned_until'])
				$values['banned_until']='0000-00-00 00:00:00';
		}

		#Добавление основной группы в начало списка групп
		if(isset($_POST['_group']) or isset($values['groups']))
		{
			if($id)
			{
				$group=isset($_POST['_group']) ? (int)$_POST['_group'] : $orig['_group'];
				$groups=isset($values['groups']) ? (array)$values['groups'] : $orig['groups'];
			}
			else
			{
				$group=isset($_POST['_group']) ? (int)$_POST['_group'] : UserManager::GROUP_USER;
				$groups=isset($values['groups']) ? (array)$values['groups'] : [];
			}

			if($k=array_keys($groups,$group))
				foreach($k as &$v)
					unset($groups[$v]);

			array_unshift($groups,$group);

			$values['groups']=[];

			foreach($groups as $v)
				$values['groups'][]=(int)$v;
		}

		#Проверка языка
		if(isset($values['language']) and !isset(Eleanor::$langs[ $values['language'] ]))
			$values['language']='';

		if($id)
			$email=isset($values['email']) ? $values['email'] : $orig['email'];
		elseif(isset($values['email']))
			$email=$values['email'];
		else
			$errors[]='EMPTY_EMAIL';

		if($errors)
			goto EditForm;

		$avatar=false;

		#Аватар, здесь многи хитрости: если картинка не изменялась - она не должна передаваться
		if(isset($_POST['avatar'],$_POST['avatar']['type'],$_POST['avatar']['src']) and is_array($_POST['avatar']))
			switch($_POST['avatar']['type'])
			{
				case'upload':
					$src=basename((string)$_POST['avatar']['src']);
					$path=Template::$path['uploads'].'temp/'.$src;

					if(is_file($path))
						$avatar=$path;
				break;
				case'gallery':
					$gallery=Template::$path['static'].'images/avatars/';
					$src=(string)$_POST['avatar']['src'];
					$path=$gallery.$src;
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
							'avatar_type'=>'gallery',
							'avatar'=>$src,
						];
				break;
				case'link':
					$values+=[
						'avatar_type'=>'link',
						'avatar'=>(string)$_POST['avatar']['src']
					];
				break;
				default:
					$values+=[
						'avatar_type'=>'',
						'avatar'=>''
					];
			}

		#Очистка логов
		if(isset($_POST['_clean_logins']))
			$values['failed_logins']='';

		if(isset($values['groups_overload']))
			$values['groups_overload']=json_encode($values['groups_overload'],JSON);

		#Письма и язык
		if(isset($values['language']))
			$let_lang=$values['language'] ? $values['language'] : Language::$main;
		elseif($id and isset($orig['language']))
			$let_lang=$orig['language'] ? $orig['language'] : Language::$main;
		else
			$let_lang=Language::$main;

		$letters=isset($_POST['_letter']) ? (array)$_POST['_letter'] : [];
		$let_file=DIR."admin/letters/users-{$let_lang}.php";

		if($id)
		{
			$exists=is_file($let_file);
			$letter=$email && $exists ? include($let_file) : [];

			if(!is_array($letter))
				$letter=[];

			if(isset($values['_password']) and $values['_password']==='')
				unset($values['_password']);

			#Удаление внешний авторизации
			if(isset($_POST['_kill_session']) and is_array($_POST['_kill_session']))
			{
				$values['login_keys']=$orig['login_keys'];

				foreach($_POST['_kill_external'] as $login=>$keys)
					foreach($keys as $key)
						unset($values['login_keys'][$login][$key]);

				$values['login_keys']=$values['login_keys'] ? json_encode($values['login_keys'],JSON) : '';
			}

			try
			{
				UserManager::Update($values,$id);

				#Удаление сессии пользователя
				if(isset($_POST['_kill_external']) and is_array($_POST['_kill_external']))
					foreach($_POST['_kill_external'] as $provider=>$pids)
					{
						$provider=Eleanor::$Db->Escape($provider);
						$pids=Eleanor::$Db->In($pids);

						Eleanor::$Db->Delete($table['ea'],"`provider`={$provider} AND `provider_uid`{$pids}");
					}

				if(isset($letter['renamed_t'],$letter['renamed'],$values['name']) and in_array('name',$letters) and $orig['name']!=$values['name'])
				{
					$replace=[
						'site'=>Eleanor::$vars['site_name'],
						'fullname'=>$values['full_name'],
						'name'=>htmlspecialchars($values['name'],ENT,\Eleanor\CHARSET),
						'oldname'=>htmlspecialchars($orig['name'],ENT,\Eleanor\CHARSET),
						'userlink'=>UserLink($id,$values['name']),
						'link'=>\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR,
					];
					Email::Simple(
						$email,
						BBCode::ExecLogic($letter['renamed_t'],$replace),
						BBCode::ExecLogic($letter['renamed'],$replace)
					);
				}

				if(isset($letter['newpass_t'],$letter['newpass'],$values['_password']) and in_array('pass',$letters))
				{
					$replace=[
						'site'=>Eleanor::$vars['site_name'],
						'fullname'=>$values['full_name'],
						'name'=>htmlspecialchars(isset($values['name']) ? $values['name'] : $orig['name'],ENT,\Eleanor\CHARSET),
						'pass'=>$values['_password'],
						'userlink'=>UserLink($id,$values['name']),
						'link'=>\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR,
					];
					Email::Simple(
						$email,
						BBCode::ExecLogic($letter['newpass_t'],$replace),
						BBCode::ExecLogic($letter['newpass_t'],$replace)
					);
				}
			}
			catch(EE$E)
			{
				$mess=$E->getMessage();

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
			}
		}
		else
		{
			#Генерация пароля для нового пользователя
			if(!isset($values['_password']) or $values['_password']==='')
			{
				if(!isset(Eleanor::$vars['min_pass_length']))
					LoadOptions('user-profile');

				$values['_password']=uniqid();

				while(strlen($values['_password'])<Eleanor::$vars['min_pass_length'])
					$values['_password'].=uniqid();
			}

			try
			{
				$id=UserManager::Create(['groups_overload'=>'']+$values);

				if(isset($letter['created_t'],$letter['created']) and in_array('new',$letters))
				{
					$replace=[
						'site'=>Eleanor::$vars['site_name'],
						'fullname'=>$values['full_name'],
						'name'=>htmlspecialchars($values['name'],ENT,\Eleanor\CHARSET),
						'userlink'=>UserLink($id,$values['name']),
						'link'=>\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR,
					];
					Email::Simple(
						$email,
						BBCode::ExecLogic($letter['created_t'],$replace),
						BBCode::ExecLogic($letter['created'],$replace)
					);
				}
			}
			catch(EE_DB$E)
			{
				$E->Log();
				$errors[]=$E->getMessage();
			}
			catch(EE$E)
			{
				$mess=$E->getMessage();
				$errors=[];
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
			}
		}

		if($errors)
			goto EditForm;

		if($avatar)
		{
			$newfile=Template::$path['uploads'].'avatars/av-'.$id.strrchr($avatar,'.');

			if(is_file($newfile))
				Files::Delete($newfile);
			elseif(!is_dir(Template::$path['uploads'].'avatars/'))
				Files::MkDir(Template::$path['uploads'].'avatars/');

			if(rename($avatar,$newfile))
				Eleanor::$Db->Update($table['ue'],['avatar_type'=>'upload','avatar'=>basename($newfile)],
					"`id`={$id} LIMIT 1");
		}

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	if($id)
	{
		$title[]=$lang['editing'];

		$values=$orig;
		$values['avatar']=Avatar($orig);
		$values+=[
			#Письма на отправку
			'_letter'=>['name','pass'],
			#Очистка попыток неудачных логинов
			'_clean_logins'=>false,

			#Перезагрузка групп: метод перезагрузки и значение
			'_groups_overload_method'=>isset($orig['groups_overload']['method']) ? $orig['groups_overload']['method'] : [],

			'_external_auth'=>[],
			'_password'=>'',
			'_online'=>false,
		];

		unset($orig['groups_overload'],$values['groups_overload']);

		#Активные сессии пользователя
		$lk=Eleanor::$Login->Get('login_key');
		$t=time();

		foreach($values['login_keys'] as $login=>&$data)
			foreach($data as $k=>&$v)
			{
				if($uid!=$id||$k!=$lk)
					$v['_del']=isset($_POST['_kill_session'][$login]) && is_array($_POST['_kill_session'][$login])
						&& in_array($k,$_POST['_kill_session'][$login]);

				$v['_online']=$v[0]>$t;

				if($v['_online'])
					$values['_online']=true;
			}

		unset($data,$v);

		#Неудачные попытки входа
		$values['failed_logins']=$values['failed_logins'] ? json_decode($values['failed_logins'],true) : [];

		#Внешние сервисы авторизаций (через VK, Яндекс, Гугль)
		$R=Eleanor::$Db->Query("SELECT `provider`, `provider_uid`, `identity` FROM `{$table['ea']}` WHERE `id`={$id}");
		while($a=$R->fetch_assoc())
			$values['_external_auth'][]=$a+[
					'_delete'=>isset($_POST['_kill_external'][ $a['provider'] ])
						&& is_array($_POST['_kill_external'][ $a['provider'] ]) && in_array($a['provider_uid'],$_POST['_kill_external'][ $a['provider'] ])
				];
	}
	else
	{
		$title[]=$lang['creating'];

		$values=[
			'full_name'=>'',
			'name'=>'',
			'email'=>'',
			'timezone'=>'',
			'groups'=>[],
			'language'=>'',
			'banned_until'=>'',
			'ban_explain'=>'',
			'avatar'=>null,

			#Письма на отправку
			'_letter'=>['new'],
			#Очистка попыток неудачных логинов
			'_clean_logins'=>false,

			#Перезагрузка групп: метод перезагрузки и значение
			'_groups_overload_method'=>[],
			'failed_logins'=>[],

			#Дополнительные поля
			'login_keys'=>[],
			'_password'=>'',
			'_group'=>UserManager::GROUP_USER,
			'_online'=>false,#Пользователь онлайн
			'_external_auth'=>[],
		];
	}

	if($errors)
	{
		if(!is_array($errors))
			$errors=[];

		$data=[
			'full_name'=>'string',
			'name'=>'string',
			'email'=>'string',
			'timezone'=>'string',
			'groups'=>'array',
			'language'=>'string',
			'banned_until'=>'string',
			'ban_explain'=>'string',

			#Письма на отправку
			'_letter'=>'array',
			#Очистка попыток неудачных логинов
			'_clean_logins'=>'bool',

			#Дополнительные поля
			'_password'=>'string',
			'_group'=>'int',
			'_groups_overload_method'=>'array',
		];

		PostValues($values,$data);

		#Автара, здесь есть хитрость: если картинка не изменялась - она не должна передаваться
		if(isset($_POST['avatar'],$_POST['avatar']['type'],$_POST['avatar']['src']) and is_array($_POST['avatar']))
			switch($_POST['avatar']['type'])
			{
				case'upload':
					$src=basename((string)$_POST['avatar']['src']);
					$path=Template::$path['uploads'].'temp/'.$src;

					if(is_file($path))
						$values['avatar']=[
							'post'=>true,
							'type'=>'upload',
							'path'=>$path,
							'http'=>Template::$http['uploads'].'temp/'.$src,
							'src'=>$src,
						];
					else
						$values['avatar']=null;
				break;
				case'gallery':
					$gallery=Template::$path['static'].'images/avatars/';
					$src=(string)$_POST['avatar']['src'];
					$path=realpath($gallery.$src);

					if(\Eleanor\W)
					{
						$gallery=str_replace('\\','/',$gallery);
						$path=str_replace('\\','/',$path);
					}

					if(is_file($path) and strpos($path,$gallery)===0)
						$values['avatar']=[
							'post'=>true,
							'type'=>'gallery',
							'path'=>$path,
							'http'=>Template::$http['static'].'images/avatars/'.$src,
							'src'=>$src,
						];
					else
						$values['avatar']=null;
				break;
				case'link':
					$values['avatar']=[
						'post'=>true,
						'type'=>'link',
						'http'=>$_POST['avatar']['src'],
					];
				break;
				default:
					$values['avatar']=null;
			}
	}

	$links=[
		'delete'=>$id && $uid!=$id ? $Url(['delete'=>$id,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$Groups2Html=function()use($groups){
		$C=new Controls;
		$C->name=['groups_overload'];
		return$C->DisplayControls($groups);
	};
	$Extra2Html=function()use($extra){
		$C=new Controls;
		$C->name=['extra'];
		return$C->DisplayControls($extra);
	};
	$GroupsOpts=function($selected,$optgroup=true){
		return UserManager::GroupsOpts($selected,[],$optgroup);
	};

	$c=Eleanor::$Template->CreateEdit($id,$values,$GroupsOpts,$groups,$Groups2Html,$extra,$Extra2Html,$errors,$back,$links,$maxupload);
	Response($c);
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

		$from=0;
		if(!empty($_REQUEST['fi']['last_visit_from']) and 0<$from=strtotime($_REQUEST['fi']['last_visit_from']))
		{
			$query['fi']['last_visit_from']=$_REQUEST['fi']['last_visit_from'];
			$t=date('Y-m-d H:i:00',$from);
			$where[]="`u`.`last_visit`>='{$t}'";
		}

		if(!empty($_REQUEST['fi']['last_visit_to']) and $from<$t=strtotime($_REQUEST['fi']['last_visit_to']))
		{
			$query['fi']['last_visit_to']=$_REQUEST['fi']['last_visit_to'];
			$t=date('Y-m-d H:i:59',$t);
			$where[]="`u`.`last_visit`<='{$t}'";
		}

		$from=0;
		if(!empty($_REQUEST['fi']['register_from']) and 0<$from=strtotime($_REQUEST['fi']['register_from']))
		{
			$query['fi']['register_from']=$_REQUEST['fi']['register_from'];
			$t=date('Y-m-d H:i:00',$from);
			$where[]="`u`.`register`>='{$t}'";
		}

		if(!empty($_REQUEST['fi']['register_to']) and $from<$t=strtotime($_REQUEST['fi']['register_to']))
		{
			$query['fi']['register_to']=$_REQUEST['fi']['register_to'];
			$t=date('Y-m-d H:i:59',$t);
			$where[]="`u`.`register`<='{$t}'";
		}

		if(!empty($_REQUEST['fi']['ip']))
		{
			$query['fi']['ip']=$_REQUEST['fi']['ip'];
			$where[]='`ip`='.Eleanor::$Db->Escape(inet_pton($_REQUEST['fi']['ip']));
		}

		if(!empty($_REQUEST['fi']['email']))
		{
			$query['fi']['email']=$_REQUEST['fi']['email'];
			$where[]='`email` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['email'],false).'%\'';
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';

	if($post and isset($_POST['event'],$_POST['items']))
		switch($_POST['event'])
		{
			case'delete':
				UserManager::Delete( array_diff((array)$_POST['items'],[$uid]) );
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

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table['main']}` `u` INNER JOIN `{$table['extra']}` USING(`id`){$where}");
	list($cnt)=$R->fetch_row();

	if($cnt>0)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','name','email','full_name','last_visit'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `u`.`full_name`, `u`.`name`, `email`, `groups`, `ip`, `u`.`last_visit`, `avatar`, `avatar_type` FROM `{$table['main']}` `u` INNER JOIN `{$table['extra']}` USING(`id`){$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			if($a['ip'])
				$a['ip']=inet_ntop($a['ip']);

			$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : [];
			$groups=array_merge($groups,$a['groups']);

			$a['_a']=UserLink($a['id'],$a['name'],'index');
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$uid==$a['id'] ? null : $Url(['delete'=>$a['id']]);

			$a['avatar']=Avatar($a);
			$items[$a['id']]=array_slice($a,1);
		}

		if($groups)
		{
			$GUrl=clone $Url;
			$GUrl->prefix=DynUrl::$base.'section=management&amp;module=groups&amp;';
			$table=P.'groups';
			$in=Eleanor::$Db->In($groups);
			$groups=[];

			$R=Eleanor::$Db->Query("SELECT `id`, `title_l` `title`, `style` FROM `{$table}` WHERE `id`{$in}");
			while($a=$R->fetch_assoc())
			{
				$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
				$a['_aedit']=$GUrl(['edit'=>$a['id']]);

				$groups[$a['id']]=array_slice($a,1);
			}
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