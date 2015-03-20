<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** @var string $REQUEST Ссылка на AJAX запросы */

$index=Eleanor::$service=='index';

if(AJAX and !$index)
{
	$Url=new DynUrl;
	$Url->prefix=DynUrl::$base.'section=management&amp;module=users&amp;';

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

	Response(Eleanor::$Template->BlockWhoOnline(compact('sessions','by_service','by_type')));
	return;
}

if($index)
{
	$users=$bots=[];
	$g=$u=$b=0;
	$limit=30;

	$R=Eleanor::$Db->Query('SELECT `s`.`type`,`s`.`user_id`,`s`.`enter`,`s`.`name` `botname`,`us`.`groups`,`us`.`name`
FROM `'.P.'sessions` `s` INNER JOIN `'.P.'users_site` `us` ON `s`.`user_id`=`us`.`id`
WHERE `s`.`expire`>\''.date('Y-m-d H:i:s').'\' AND `s`.`service`=\''.Eleanor::$service
		.'\' ORDER BY `s`.`expire` DESC LIMIT '.$limit);
	while($session=$R->fetch_assoc())
	{
		$limit--;

		if($session['user_id']>0 and $session['type']=='user')
		{
			if($session['groups'])
			{
				$gs=[(int)ltrim($session['groups'],',')];
				$s=join('',Permissions::ByGroup($gs,'style'));
			}
			else
				$s='';

			$users[$session['user_id']]=[
				's'=>$s,
				'n'=>$session['name'],
				't'=>$session['enter'],
			];
			$u++;
		}
		elseif($session['botname'])
		{
			if(isset($bots[ $session['botname'] ]))
				$bots[ $session['botname'] ]['cnt']++;
			else
				$bots[ $session['botname'] ]=[
					'cnt'=>1,
					't'=>$session['enter'],
				];
			$b++;
		}
		else
			$g++;
	}

	if($limit<=0)
	{
		$R=Eleanor::$Db->Query('SELECT `type`, COUNT(`type`) `cnt` FROM `'.P.'sessions` WHERE `expire`>\''
			.date('Y-m-d H:i:s').'\' AND `service`=\''.Eleanor::$service.'\' GROUP BY `type`');
		while($session=$R->fetch_row())
			$ucnt[$session[0]]=$session[1];

		if(isset($ucnt['guest']))
			$g=$ucnt['guest'];

		if(isset($ucnt['user']))
			$u=$ucnt['user'];

		if(isset($ucnt['bot']))
			$b=$ucnt['bot'];
	}
}

try
{
	if($index)
		return(string)Eleanor::$Template->BlockWhoOnline($REQUEST,$users,$bots,$u,$b,$g);

	return(string)Eleanor::$Template->BlockWhoOnline($REQUEST);
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockWhoOnline does not exists.';
}