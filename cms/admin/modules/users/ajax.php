<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor;

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$lang=Eleanor::$Language['users'];
$event=isset($_GET['do']) ? (string)$_GET['do'] : '';

switch($event)
{
	case'remove':
		if(isset($_POST['provider'],$_POST['pid']))
		{
			Eleanor::$Db->Delete(P.'users_external_auth','`provider`='.Eleanor::$Db->Escape((string)$_POST['provider'])
				.' AND `provider_uid`='.Eleanor::$Db->Escape((string)$_POST['pid']));
			Response(true);
		}
		else
			Error();
	break;
	case'online':
		$Url->prefix=DynUrl::$base.'section=management&amp;module=users&amp;';
		$sessions=['admin'=>[]];
		$ser_cnt=$ss_cnt=[];
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
			$R=Eleanor::$Db->Query('SELECT `service`, COUNT(`service`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''
				.date('Y-m-d H:i:s').'\' GROUP BY `service`');
			while($session=$R->fetch_row())
				$ser_cnt[ $session[0] ]=$session[1];

			$q=[];
			foreach($ser_cnt as $k=>&$v)
				$q[]='(SELECT `type`,`service`, COUNT(`type`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''
					.date('Y-m-d H:i:s').'\' AND `service`=\''.$k.'\' GROUP BY `type`)';

			if($q)
			{
				$R=Eleanor::$Db->Query(join('UNION ALL',$q));
				while($session=$R->fetch_row())
					$ss_cnt[ $session[1] ][$session[0]]=$session[2];
			}
		}

		Eleanor::$Template->queue['users']=Eleanor::$Template->classes.'UsersOnline.php';
		Response( (string)Eleanor::$Template->BlockOnline($sessions,$ser_cnt,$ss_cnt) );
	break;
	case'details':
		$ip=isset($_POST['ip']) ? (string)$_POST['ip'] : '';
		$ip=filter_var($ip,FILTER_VALIDATE_IP) ? inet_pton($ip) : '';
		$ip=Eleanor::$Db->Escape($ip);
		$id=isset($_POST['id']) ? (int)$_POST['id'] : 0;
		$service=isset($_POST['service']) ? Eleanor::$Db->Escape((string)$_POST['service']) : '';
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
				$session['_style']=join('',Permissions::ByGroup($g,'style'));
			}
			else
				$session['_style']='';

			if($session['ip_guest']!=='')
				$session['ip_guest']=inet_ntop($session['ip_guest']);

			if($session['ip_user']!=='')
				$session['ip_user']=inet_ntop($session['ip_user']);

			$session['info']=$session['info'] ? (array)unserialize($session['info']) : [];

			if(isset($session['info']['ips']))
				foreach($session['info'] as &$v)
					$v=inet_ntop($v);
		}

		Eleanor::$Template->queue['users']=Eleanor::$Template->classes.'UsersOnline.php';
		Response( (string)Eleanor::$Template->SessionDetail($session) );
	break;
	case'galleries':
		$galleries=[];
		$gals=glob(Template::$path['static'].'images/avatars/*',GLOB_MARK | GLOB_ONLYDIR);

		foreach($gals as &$v)
		{
			$descr=$name=basename($v);
			$image=false;

			if(is_file($v.'config.ini'))
			{
				$session=parse_ini_file($v.'config.ini',true);

				if(isset($session['title']))
					$descr=FilterLangValues($session['title'],'',$name);

				if(isset($session['options']['cover']) and is_file($v.$session['options']['cover']))
					$image='images/avatars/'.$name.'/'.$session['options']['cover'];
			}

			if(!$image and $temp=glob($v.'*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE))
				$image=Template::$http['static'].'images/avatars/'.$name.'/'.basename($temp[0]);

			if($image)
				$galleries[]=['n'=>$name,'i'=>$image,'d'=>$descr];
		}
		Response( (string)Eleanor::$Template->Galleries($galleries) );
	break;
	case'avatars':
		$gallery=isset($_POST['gallery']) ? (string)$_POST['gallery'] : false;
		$files=$gallery
			? glob(Template::$path['static'].'images/avatars/'.$gallery.'/*.{jpg,png,jpeg,bmp,gif}',GLOB_BRACE)
			: false;

		if(!$files)
			return Error();

		foreach($files as &$v)
			$v=['p'=>Template::$http['static'].'images/avatars/'.$gallery.'/','f'=>basename($v)];

		Eleanor::$Template->queue[]='Users';
		Response( (string)Eleanor::$Template->Avatars($files) );
	break;
	case'killsession':
		$key=isset($_POST['key']) ? (string)$_POST['key'] : '';
		$uid=isset($_POST['uid']) ? (string)$_POST['uid'] : '';
		$login=isset($_POST['login']) ? (string)$_POST['login'] : '';

		$R=Eleanor::$Db->Query('SELECT `login_keys` FROM `'.P.'users_site` WHERE `id`='.$uid.' LIMIT 1');
		if($session=$R->fetch_assoc())
		{
			$lks=$session['login_keys'] ? (array)unserialize($session['login_keys']) : [];
			unset($lks[$login][$key]);

			if(empty($lks[$login]))
				unset($lks[$login]);

			Eleanor::$Db->Update(P.'users_site',['login_keys'=>$lks ? serialize($lks) : ''],'`id`='.$uid.' LIMIT 1');
		}

		Response(true);
	break;
	default:
		Error(Eleanor::$Language['ajax']['unknown_event']);
}