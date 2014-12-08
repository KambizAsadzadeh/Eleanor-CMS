<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$index=Eleanor::$service=='index';

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
				$s=join(Permissions::ByGroup($gs,'style'));
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
		return(string)Eleanor::$Template->BlockWhoOnline($users,$bots,$u,$b,$g);

	return(string)Eleanor::$Template->BlockWhoOnline();
}
catch(\Eleanor\Classes\EE$E)
{
	return'Template BlockWhoOnline does not exists.';
}