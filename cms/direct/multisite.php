<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

$type=isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$service=isset($_REQUEST['service']) ? (string)$_REQUEST['service'] : Eleanor::$service;

if(!isset(Eleanor::$services[ $service ]))
{
	Output::SendHeaders('application/json');
	header('Access-Control-Allow-Origin: *');
	Output::Gzip(json_encode(['status'=>'error'],JSON^JSON_PRETTY_PRINT));
	return;
}

$service=Eleanor::$services[ $service ];
$Login=$service['login'] ? '\CMS\Logins\\'.$service['login'] : false;
$is_user=$Login && call_user_func([$Login,'IsUser']);

switch($type)
{
	#Проверка стороннего сайта на логин в текущем сайте
	case'check':
		$out=$is_user
				? call_user_func([$Login,'Get'],['id','name'])+['status'=>'ok','title'=>Eleanor::$vars['site_name']]
				: ['status'=>'error'];
	break;
	#Получение логина с текущего сайта для возможности логина на стороннем
	case'get-login':
		if(!$is_user)
		{
			$out=['status'=>'error'];
			break;
		}

		LoadOptions('multisite');

		$out=call_user_func([$Login,'Get'],['id','name']);

		if(isset($_REQUEST['secret']))
		{
			$t=time()+Eleanor::$vars['multisite_ttl'];
			$out['signature']=$t.'-'.md5($t.'-'.$out['id'].$service['name'].Eleanor::$ip.$out['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']);
		}
		else
		{
			$t=(int)Eleanor::$vars['multisite_ttl'];
			$out['signature']=md5($service['name'].Eleanor::$ip.getenv('HTTP_USER_AGENT'));
			$out['jump_id']=Eleanor::$Db->Insert(P.'multisite_jump',[
				'type'=>'out',
				'!expire'=>"NOW() + INTERVAL {$t} SECOND",
				'user_id'=>$out['id'],
				'user_name'=>$out['name']
			]);
		}

		$out['status']='ok';
	break;
	#Логин (клик по ссылке для входа на сайт вместо ввода логина и пароля) со стороннего сайта на текущий
	case'login':
		$site=isset($_REQUEST['site']) ? (string)$_REQUEST['site'] : '';
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';
		$config=\Eleanor\AwareInclude(DIR.'config_multisite.php');

		if(!isset($config[$site]) or !$Login)
		{
			$out=['status'=>'error'];
			break;
		}

		$dest=$config[$site];

		if($dest['secret'])
		{
			$user=[
				'user_id'=>isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			];
			list($t,$sign)=explode('-',$sign,2);

			if($t<time() or $sign!=md5($t.'-'.$user['user_id'].$service.Eleanor::$ip.$user['name'].getenv('HTTP_USER_AGENT').$dest['secret']))
			{
				$out=['status'=>'error'];
				break;
			}
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
			{
				$out=['status'=>'error'];
				break;
			}

			if(isset($dest['db'],$dest['host'],$dest['user'],$dest['pass'],$dest['prefix']) and $dest['db'] and $dest['host'] and $dest['user'])
				try
				{
					$Db=new MySQL($dest);
					$Db->SyncTimeZone();
				}
				catch(EE$E)
				{
					$out=['status'=>'error','error'=>$E->getMessage()];
					break;
				}
			else
				$Db=Eleanor::$Db;

			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$sign=Eleanor::$Db->Escape($sign);

			$R=$Db->Query("SELECT `user_id`,`name` FROM `{$dest['prefix']}multisite_jump` WHERE `id`={$id} AND `type`='out' AND `expire`>NOW() AND `signature`={$sign} LIMIT 1");
			if(!$user=$R->fetch_assoc())
			{
				$out=['status'=>'error'];
				break;
			}

			$Db->Delete($dest['prefix'].'multisite_jump',"`id`={$id} LIMIT 1");
		}

		if(!$dest['sync'])
		{
			$table=USERS_TABLE;
			$name=Eleanor::$UsersDb->Escape($user['name']);
			$R=Eleanor::$UsersDb->Query("SELECT `id` FROM `{$table}` WHERE `name`={$name} LIMIT 1");
			if(!list($user['user_id'])=$R->fetch_row())
			{
				$out=['status'=>'error'];
				break;
			}
		}

		call_user_func([$Login,'Auth'],$user['user_id']);
		$out=['status'=>'ok'];
	break;
	#Подготовка к прыжку (выбор доступного сайта из селекта) с текущего на сторонний
	case'pre-jump':
		if(!$Login or !$is_user)
		{
			$out=['status'=>'error'];
			break;
		}

		LoadOptions('multisite');

		$site=isset($_REQUEST['site']) ? (string)$_REQUEST['site'] : '';
		$config=include DIR.'config_multisite.php';

		if(!isset($config[$site]))
		{
			$out=['status'=>'error'];
			break;
		}

		$dest=$config[$site];
		$out=call_user_func([$Login,'Get'],['id','name']);
		$out=[
			'user_id'=>$dest['sync'] ? $out['id'] : null,
			'name'=>$out['name'],
			'address'=>$dest['address'],
		];

		if($dest['secret'])
		{
			$t=time()+Eleanor::$vars['multisite_ttl'];
			$out['signature']=$t.'-'.md5($t.'-'.$out['user_id'].$service.Eleanor::$ip.$out['name'].getenv('HTTP_USER_AGENT').$dest['secret']);
			$out['secret']=true;
		}
		else
		{
			if(isset($dest['db'],$dest['host'],$dest['user'],$dest['pass'],$dest['prefix']) and $dest['db'] and $dest['host'] and $dest['user'])
				try
				{
					$Db=new MySQL($dest);
					$Db->SyncTimeZone();
				}
				catch(EE$E)
				{
					$out=['status'=>'error','error'=>$E->getMessage()];
					break;
				}
			else
				$Db=Eleanor::$Db;

			$t=(int)Eleanor::$vars['multisite_ttl'];
			$out['signature']=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT'));
			$out['id']=$Db->Insert($dest['prefix'].'multisite_jump',['type'=>'in',"!expire'=>'NOW() + INTERVAL {$t} SECOND"]+$out);
		}

		$out['status']='ok';
	break;
	#Прыжок (выбор доступного сайта из селекта) со стороннего сайта на текущий
	case'jump':
		if($Login and $is_user)
			call_user_func([$Login,'Logout']);

		$out=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';

		if(isset($_REQUEST['secret']))
		{
			LoadOptions('multisite');

			$user=[
				'user_id'=>isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			];

			list($t,$sign)=explode('-',$sign,2);

			if($t<time() or $sign!=md5($t.'-'.$user['user_id'].$service.Eleanor::$ip.$user['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']))
				break;
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
				break;

			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$table=P.'multisite_jump';
			$sign=Eleanor::$Db->Escape($sign);
			$R=Eleanor::$Db->Query("SELECT `user_id`,`name` FROM `{$table}` WHERE `id`={$id} AND `type`='in' AND `expire`>NOW() AND `signature`={$sign} LIMIT 1");
			if(!$user=$R->fetch_assoc())
				break;

			Eleanor::$Db->Delete(P.'multisite_jump','`id`='.$id.' LIMIT 1');
		}

		if($user['user_id']==0)
		{
			$table=USERS_TABLE;
			$name=Eleanor::$Db->Escape($user['name']);
			$R=Eleanor::$UsersDb->Query("SELECT `id` FROM `{$table}` WHERE `name`={$name} LIMIT 1");
			if(!list($user['user_id'])=$R->fetch_row())
				break;
		}

		call_user_func([$Login,'Auth'],$user['user_id']);
	break;
	default:
		$out='';
}

if(is_string($out))
	GoAway($out);
else
{
	Output::SendHeaders('application/json');
	header('Access-Control-Allow-Origin: *');
	Output::Gzip(json_encode($out, JSON^JSON_PRETTY_PRINT));
}