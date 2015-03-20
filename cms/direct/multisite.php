<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;
use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

$type=isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$service=isset($_REQUEST['service']) ? (string)$_REQUEST['service'] : '';

if(!isset(Eleanor::$services[ $service ]))
{
	Output::SendHeaders('application/json');
	header('Access-Control-Allow-Origin: *');
	Output::Gzip(json_encode(['status'=>'error'],JSON^JSON_PRETTY_PRINT));
	return;
}

$service=Eleanor::$services[ $service ];
$Login='\CMS\Logins\\'.$service['login'];
$is_user=call_user_func([$Login,'IsUser']);

switch($type)
{
	#Проверка стороннего сайта на логин в текущем сайте
	case'check':
		$out=$is_user
				? call_user_func([$Login,'Get'],['id','name'])+['status'=>'ok','title'=>Eleanor::$vars['site_name']]
				: ['status'=>'no'];
	break;
	#Получение логина с текущего сайта для возможности логина на стороннем
	case'get-login':
		if($is_user)
		{
			LoadOptions('multisite');
			$out=call_user_func([$Login,'Get'],['id','name']);

			if(isset($_REQUEST['secret']))
			{
				$t=time()+Eleanor::$vars['multisite_ttl'];
				$out['signature']=$t.'-'.md5($t.'-'.$out['uid'].$service['name'].Eleanor::$ip.$out['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']);
			}
			else
			{
				$out['signature']=md5($service['name'].Eleanor::$ip.getenv('HTTP_USER_AGENT'));
				$out['jump_id']=Eleanor::$Db->Insert(P.'multisite_jump',[
					'type'=>'out',
					'!expire'=>'NOW() + INTERVAL 2 MINUTE',
					'user_id'=>$out['id'],
					'user_name'=>$out['name']
				]);
			}

			$out['status']='ok';
		}
		else
			$out=['status'=>'no'];
	break;
	#Логин со стороннего сайта на текущий
	case'login':
		$site=isset($_REQUEST['site']) ? (string)$_REQUEST['site'] : '';
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';
		$multisite=\Eleanor\AwareInclude(DIR.'config_multisite.php');

		if(!isset($multisite[$site]))
		{
			$out=['status'=>'error'];
			break;
		}

		$d=$multisite[$site];

		if($d['secret'])
		{
			$a=[
				'uid'=>isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			];
			list($t,$sign)=explode('-',$sign,2);

			if($t<time() or $sign!=md5($t.'-'.$a['uid'].$service.Eleanor::$ip.$a['name'].getenv('HTTP_USER_AGENT').$d['secret']))
				return Error();
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
				return Error();
			if(isset($d['db']))
				try
				{
					$Db=new Db($d);
					$Db->SyncTimeZone();
				}
				catch(EE$E)
				{
					return Error($E->getMessage());
				}
			else
				$Db=Eleanor::$Db;
			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$Db->Query('SELECT `uid`,`name` FROM `'.$d['prefix'].'multisite_jump` WHERE `id`='.$id.' AND `type`=\'out\' AND `expire`>NOW() AND `signature`='.Eleanor::$Db->Escape($sign).' LIMIT 1');
			if(!$a=$Db->fetch_assoc())
				return Error();
			$Db->Delete($d['prefix'].'multisite_jump','`id`='.$id.' LIMIT 1');
		}
		if(!$d['sync'])
		{
			Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($a['name']).' LIMIT 1');
			if(!list($a['uid'])=Eleanor::$UsersDb->fetch_row())
				return Error();
		}

		Eleanor::$Login->Auth($a['uid']);
		Result(true);
	break;
	case'pre-jump':#Подготовка к прыжку с текущего на сторонний
		if(Eleanor::$Login->IsUser())
		{
			Eleanor::LoadOptions('multisite');
			$site=isset($_REQUEST['sn']) ? (string)$_REQUEST['sn'] : '';
			$multisite=include Eleanor::$root.'addons/config_multisite.php';
			if(!isset($multisite[$site]))
				return Error();
			$d=$multisite[$site];
			$data=Eleanor::$Login->Get(['id','name'],false);
			$data=[
				'uid'=>$d['sync'] ? $data['id'] : 0,
				'name'=>$data['name'],
				'address'=>$d['address'],
			];

			if($d['secret'])
			{
				$t=time()+Eleanor::$vars['multisite_ttl'];
				$data['signature']=$t.'-'.md5($t.'-'.$data['uid'].$service.Eleanor::$ip.$data['name'].getenv('HTTP_USER_AGENT').$d['secret']);
				$data['secret']=true;
			}
			else
			{
				if(isset($d['db']))
					try
					{
						$Db=new Db($d);
						$Db->SyncTimeZone();
					}
					catch(EE$E)
					{
						return Error($E->getMessage());
					}
				else
					$Db=Eleanor::$Db;

				$data['signature']=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT'));
				$data['id']=$Db->Insert($d['prefix'].'multisite_jump',['type'=>'in','!expire'=>'NOW() + INTERVAL 2 MINUTE']+$data);
			}
			Result($data);
		}
		else
			Error();
	break;
	case'jump':#Прыжок со стороннего сайта на текущий
		if(Eleanor::$Login->IsUser())
			return GoAway(true);
		$sp=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path;
		$sign=isset($_REQUEST['signature']) ? (string)$_REQUEST['signature'] : '';
		if(isset($_REQUEST['secret']))
		{
			Eleanor::LoadOptions('multisite');
			$a=[
				'uid'=>isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0,
				'name'=>isset($_REQUEST['name']) ? (string)$_REQUEST['name'] : '',
			];
			list($t,$sign)=explode('-',$sign,2);
			if($t<time() or $sign!=md5($t.'-'.$a['uid'].$service.Eleanor::$ip.$a['name'].getenv('HTTP_USER_AGENT').Eleanor::$vars['multisite_secret']))
				return GoAway($sp);
		}
		else
		{
			if($sign!=md5($service.Eleanor::$ip.getenv('HTTP_USER_AGENT')))
				return GoAway($sp);
			$id=isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
			$R=Eleanor::$Db->Query('SELECT `uid`,`name` FROM `'.P.'multisite_jump` WHERE `id`='.$id.' AND `type`=\'in\' AND `expire`>NOW() AND `signature`='.Eleanor::$Db->Escape($sign).' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return GoAway($sp);
			Eleanor::$Db->Delete(P.'multisite_jump','`id`='.$id.' LIMIT 1');
		}
		if($a['uid']==0)
		{
			$R2=Eleanor::$UsersDb->Query('SELECT `id` FROM `'.USERS_TABLE.'` WHERE `name`='.Eleanor::$Db->Escape($a['name']).' LIMIT 1');
			if(!list($a['uid'])=$R2->fetch_row())
				return GoAway($sp);
		}
		Eleanor::$Login->Auth($a['uid']);
		GoAway(true);
}

Output::SendHeaders('application/json');
header('Access-Control-Allow-Origin: *');
Output::Gzip(json_encode($out,JSON^JSON_PRETTY_PRINT));