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

Eleanor::$Language->Load(DIR.'admin/translation/users-*.php','users');
Eleanor::$Template->queue['users']=Eleanor::$Template->classes.'Users.php';

global$Eleanor,$title;

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$lang=Eleanor::$Language['users'];
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

	if($a['avatar'] and $a['avatar']) switch($a['avatar_type'])
	{
		case'gallery':
			if(is_file($f=Template::$path['static'].'images/avatars/'.$a['avatar']))
				$image=[
					'type'=>'gallery',
					'path'=>$f,
					'http'=>Template::$http['static'].'images/avatars/'.$a['avatar'],
					'src'=>$a['avatar'],
				];
			break;
		case'upload':
			if(is_file($f=Template::$path['uploads'].$a['avatar']))
				$image=[
					'type'=>'upload',
					'path'=>$f,
					'http'=>Template::$http['uploads'].$a['avatar'],
					'src'=>$a['avatar'],
				];
			break;
		case'link':
			$image=[
				'type'=>'link',
				'http'=>$a['avatar'],
			];
	}

	return$image;
}

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
		#ToDo! Удалить:
		Templates\Admin\T::$data['speedbar']=[];

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

		$values=$Eleanor->Controls->DisplayControls($controls,$values)+$values;
		$title[]=$lang['letters'];
		$c=Eleanor::$Template->Letters($controls,$values,$post,[]);

		Response($c);
	break;
	case'online':
		if(AJAX)
		{
			$sessions=['admin'=>[]];
			$by_service=$by_type=[];
			$date=date('Y-m-d H:i:s');
			$table=[
				's'=>P.'sessions',
				'us'=>P.'users_site',
			];

			$R=Eleanor::$Db->Query("SELECT `s`.`type`, `s`.`user_id`, `s`.`enter`, `s`.`ip_guest`, `s`.`service`, `s`.`name` `botname`, `us`.`groups`, `us`.`name`
FROM `{$table['s']}` `s` LEFT JOIN `{$table['us']}` `us` ON `s`.`user_id`=`us`.`id`
WHERE `s`.`expire`>'{$date}' ORDER BY `s`.`expire` DESC LIMIT 30");

			while($session=$R->fetch_assoc())
			{
				if($session['type']=='user' and $session['groups'])
				{
					$g=[(int)ltrim($session['groups'],',')];

					$session['_style']=join('',Permissions::ByGroup($g,'style'));
				}
				else
					$session['_style']='';

				if($session['ip_guest']!=='')
					$session['ip_guest']=inet_ntop($session['ip_guest']);

				if($session['user_id'])
					$session['_adetail']=$Url(['do'=>'detail','id'=>$session['user_id'],'service'=>$session['service']]);
				else
					$session['_adetail']=$Url(['do'=>'detail','ip'=>$session['ip_guest'],'service'=>$session['service']]);

				switch($session['type'])
				{
					case'user':
						if($session['name'])
						{
							$session['_aedit']=$Url(['edit'=>$session['user_id']]);
							$sessions[ $session['service'] ]['users'][]=array_slice($session,1);
							break;
						}
					case'bot':
						if($session['botname'] and !$session['user_id'])
						{
							$sessions[ $session['service'] ]['bots'][]=array_slice($session,1);
							break;
						}
					default:
						$sessions[ $session['service'] ]['guests'][]=array_slice($session,1);
				}
			}

			if($R->num_rows>=30)
			{
				$date=date('Y-m-d H:i:s');
				$R=Eleanor::$Db->Query("SELECT `service`, COUNT(`service`) `cnt` FROM `{$table['s']}` WHERE `expire`>'{$date}' GROUP BY `service`");
				while($session=$R->fetch_row())
					$by_service[ $session[0] ]=$session[1];

				$q=[];
				foreach($by_service as $k=>&$v)
					$q[]="(SELECT `type`,`service`, COUNT(`type`) `cnt` FROM `{$table['s']}` WHERE `expire`>'{$date}' AND `service`='{$k}' GROUP BY `type`)";

				if($q)
				{
					$R=Eleanor::$Db->Query(join('UNION ALL',$q));
					while($session=$R->fetch_row())
						$by_type[ $session[1] ][$session[0]]=$session[2];
				}
			}

			/** Содержимое блока "Кто онлайн"
			 * @param array $sessions Сессии пользователя в формате сервис=>users|bots|guests=>[], ключи:
			 *  [string _adetail] Ссылка на детали
			 *  [int user_id] ID пользователя
			 *  [string enter] Дата и время входа
			 *  [string ip_guest] IP гостя и бота
			 *  [string ip_user] IP пользователя
			 *  [string service] Название сервиса
			 *  [string botname] Имя бота
			 *  [string name] Имя пользоватя
			 *  [string _style] Стиль группы пользователя
			 * @param array $by_service Если переданы не все сессии , здесь в формате будет сервис=>всего сессий
			 * @param array $by_type Если переданы не все сессии, здесь в формате сервис=>(user|bot|guest)=>число сессий всего
			 * @return string */

			OutPut::SendHeaders('application/json');
			OutPut::Gzip(json_encode(compact('sessions','by_service','by_type'),JSON^JSON_PRETTY_PRINT));
			break;
		}

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

		$groups=$galleries=[];
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

				$galleries[]=$a;
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
				'form_items'=>$Url($query+['page'=>$page>1 ? $page : false]),
				'pp'=>function($n)use($Url,$query){ $query['per-page']=$n; return$Url($query); },
				'first_page'=>$Url($query),
				'pagination'=>function($n)use($Url,$query){ return$Url($query+['page'=>$n]); },
			];
			$query['sort']=$sort;
			$query['order']=$order;

			if($fi_user)
				$query['fi']['user']=$fi_user;
		}
		else
		{
			$pp=0;
			$links=[];
		}

		$links['nofilter']=isset($query['fi']) ? $Url(['fi'=>[]]+$query) : false;
		$c=Eleanor::$Template->OnlineList($galleries,$groups,$total>0,$cnt,$pp,$query,$page,$links);
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
				$avatars[$bn]=[
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
		elseif(isset($_POST['kill_login_key'],$_POST['login']))
		{#Удаление сессии пользователя
			$key=(string)$_POST['kill_login_key'];
			$login=(string)$_POST['login'];
			$R=Eleanor::$Db->Query("SELECT `login_keys` FROM `{$table['us']}` WHERE `id`={$id} LIMIT 1");
			if($a=$R->fetch_assoc())
			{
				$lks=$a['login_keys'] ? json_decode($a['login_keys'],true) : [];
				unset($lks[$login][$key]);

				if(empty($lks[$login]))
					unset($lks[$login]);

				Eleanor::$Db->Update($table['us'],['login_keys'=>$lks ? json_encode($lks,JSON) : ''],"`id`={$uid} LIMIT 1");
			}

			Response(true);
		}
		elseif(isset($_POST['kill_provider'],$_POST['provider_uid']))
		{#Удаление внешний авторизации
			$provider=Eleanor::$Db->Escape((string)$_POST['provider']);
			$pid=Eleanor::$Db->Escape((string)$_POST['provider_uid']);

			Eleanor::$Db->Delete($table['ea'],"`provider`={$provider} AND `provider_uid`={$pid}");

			Response(true);
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
	}

	/*	if($post)
	{
		$C=new Controls;
		$values=$C->SaveControls($controls);
		$errors=$C->errors;

		include_once DIR.'crud.php';

		PostValues($values,[
			'_inherit'=>'array',
			'parent'=>'int',
			'style'=>'string',
		]);

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
				else
					$v=$title_[ Language::$main ];
		}

		if(!$id and !$title_)
			$errors['EMPTY_TITLE'][]=Language::$main;

		foreach($descr as $k=>&$v)
		{
			$v=trim($v);

			if($v=='' and !$id and $k!=Language::$main and isset($descr[ Language::$main ]))
				$v=$descr[ Language::$main ];
		}
		unset($v);

		if(isset($errors['EMPTY_TITLE']))
			$errors['EMPTY_TITLE']=$lang['EMPTY_TITLE']( $errors['EMPTY_TITLE']==[''] ? [] : $errors['EMPTY_TITLE'] );

		if(isset($values['_inherit']))
			foreach($values['_inherit'] as $v)
				unset($values[$v]);

		if($errors)
			goto EditForm;

		unset($values['_inherit']);

		#Установка поля parents
		if(isset($_POST['parent']))
		{
			$parent=(int)$_POST['parent'];
			$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$table}` WHERE `id`={$parent}");
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
		#/Установка поля parents

		if($title_)
			$values['title_l']=json_encode($title_,JSON);

		if($descr)
			$values['descr_l']=json_encode($descr,JSON);

		if($id)
			Eleanor::$Db->Update($table,$values,"`id`={$id} LIMIT 1");
		else
			Eleanor::$Db->Insert($table,$values);

		Eleanor::$Cache->Engine->DeleteByTag('groups');

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->IframeResponse((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}*/

	EditForm:

	$groups_overload=[];

	if($id)
	{
		$title[]=$lang['editing'];

		$values=$orig;
		$values['miniature']=Avatar($orig);
		$values+=[
			#Письма на отправку
			'_letter'=>['name','pass'],
			#Очистка попыток неудачных логинов
			'_clean_logins'=>false,

			#Перезагрузка групп: метод перезагрузки и значение
			'_groups_overload_method'=>[],

			'_external_auth'=>[],
			'_sessions'=>$orig['login_keys'] ? json_decode($orig['login_keys'],true) : [],
			'_password'=>'',
		];

		#Активные сессии пользователя
		$lk=Eleanor::$Login->Get('login_key');

		foreach($values['_sessions'] as $ks=>&$sess)
			foreach($sess as $k=>&$v)
				$v['_del']=$uid!=$id || $k!=$lk;

		#Неудачные попытки входа
		$values['failed_logins']=$values['failed_logins'] ? json_decode($values['failed_logins'],true) : [];

		#Внешние сервисы авторизаций (через VK, Яндекс, Гугль)
		$R=Eleanor::$Db->Query("SELECT `provider`, `provider_uid`, `identity` FROM `{$table['ea']}` WHERE `id`={$id}");
		while($a=$R->fetch_assoc())
			$values['_external_auth'][]=$a;

		#Группы пользователя: основная и вторичные
		$values['groups']=$values['groups'] ? explode(',,',trim($values['groups'],',')) : [];

		if($values['groups'])
		{
			$values['_group']=reset($values['groups']);
			$k=key($values['groups']);

			unset($values['groups'][$k]);
		}
		else
			$values['_group']=UserManager::GROUP_USER;

		#Перезагрузка прав групп
		$values['groups_overload']=$values['groups_overload'] ? json_decode($values['groups_overload'],true) : [];

		if(isset($values['groups_overload']['method']) and is_array($values['groups_overload']['method']))
			$values['_groups_overload_method']=$values['groups_overload']['method'];

		if(isset($values['groups_overload']['value']) and is_array($values['groups_overload']['value']))
			$groups_overload=$values['groups_overload']['value'];
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
			'avatar'=>false,

			#Письма на отправку
			'_letter'=>['new','name','pass'],
			#Очистка попыток неудачных логинов
			'_clean_logins'=>false,

			#Перезагрузка групп: метод перезагрузки и значение
			'_groups_overload_method'=>[],

			#Дополнительные поля
			'_external_auth'=>[],
			'_sessions'=>[],
			'_password'=>'',
			'_group'=>UserManager::GROUP_USER,
		];

		foreach($groups as $k=>$v)
			$values['_groups_overload_method'][$k]='replace';
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		$data=[
		];
		/*		$values['full_name']=isset($_POST['full_name']) ? (string)$_POST['full_name'] : '';
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
		$values['pass']=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
		$values['avatar']=isset($_POST['avatar']) ? (string)$_POST['avatar'] : '';*/

		PostValues($values,$data);

		#Автара, здесь есть хитрость: если картинка не изменялась - она не должна передаваться
		if(isset($_POST['miniature'],$_POST['miniature']['type'],$_POST['miniature']['src']) and is_array($_POST['miniature']))
			switch($_POST['miniature']['type'])
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
						$values['avatar']=false;
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
						$values['avatar']=false;
				break;
				case'link':
					$values['avatar']=[
						'post'=>true,
						'type'=>'link',
						'http'=>$_POST['avatar']['src'],
					];
				default:
					$values['avatar']=[];
			}
	}

	$links=[
		'delete'=>$id && $uid!=$id ? $Url(['delete'=>$id,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$Groups2Html=function()use($groups_overload,$groups){
		foreach($groups_overload as $k=>$v)
			if(isset($groups[$k]))
				$groups[$k]['value']=$v;

		$C=new Controls;
		$C->name=['groups_overload'];
		return$C->DisplayControls($groups);
	};
	$Extra2Html=function()use($values,$extra){
		foreach($values as $k=>$v)
			if(isset($groups[$k]))
				$groups[$k]['value']=$v;

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
	$where=$query=$galleries=$groups=[];

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
				$galleries=array_diff((array)$_POST['items'],[$uid]);

				UserManager::Delete($galleries);
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
			$galleries[$a['id']]=array_slice($a,1);
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
	$c=Eleanor::$Template->ShowList($galleries,$groups,$cnt,$pp,$query,$page,$links);
	Response($c);
}

/*function Save($id)
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
		'avatar'=>isset($_POST['avatar']) ? (string)$_POST['avatar'] : '',
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
		$R=Eleanor::$Db->Query('SELECT `avatar`,`avatar_type` FROM `'.P.'users_extra` WHERE `id`='.$id.' LIMIT 1');
		$oldavatar=$R->fetch_assoc();
	}

	if($extra['_atype']=='upload')
		try
		{
			$avatar=$C->SaveControl($Eleanor->avatar+array('value'=>isset($oldavatar) && $oldavatar['avatar_type']=='upload' && $oldavatar['avatar'] ? Eleanor::$uploads.'/avatars/'.$oldavatar['avatar'] : ''));
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
		$atype=$avatar ? 'gallery' : '';

	if(($atype=='upload' or $atype=='gallery') and $avatar and !is_file(Eleanor::$root.$avatar))
		$errors[]='AVATAR_NOT_EXISTS';

	if($atype=='gallery' and $avatar)
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
		if($oldavatar['avatar']!=$avatar or $oldavatar['avatar_type']!=$atype)
		{
			if($oldavatar['avatar_type']=='upload' and $oldavatar['avatar'] and $oldavatar['avatar']!=$avatar)
				Files::Delete(Eleanor::$root.Eleanor::$uploads.'/avatars/'.$oldavatar['avatar']);
			UserManager::Update(array('avatar'=>$avatar,'avatar_type'=>$atype),$id);
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
			UserManager::Update(array('avatar'=>$avatar,'avatar_type'=>$atype),$newid);
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