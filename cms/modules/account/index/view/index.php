<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
class AccountIndex
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Prefix(),
		);
	}

	public static function Content()
	{
		Eleanor::LoadOptions('user-profile',false);
		$groups=$GLOBALS['Eleanor']->module['user']['groups'] ? explode(',,',trim($GLOBALS['Eleanor']->module['user']['groups'],',')) : array();
		if($groups)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`, `style` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
			$main=reset($groups);
			$tosort=$groups=$grs=array();
			while($a=$R->fetch_assoc())
			{
				$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
				$a['_a']=$GLOBALS['Eleanor']->Url->special.$GLOBALS['Eleanor']->Url->Construct(array('module'=>$GLOBALS['Eleanor']->module['uris']['groups']),false).'#group-'.$a['id'];
				$a['_main']=$main==$a['id'];
				$grs[$a['id']]=array_slice($a,1);
				$tosort[$a['id']]=$a['title'];
			}
			asort($tosort,SORT_STRING);
			foreach($tosort as $k=>&$v)
				$groups[$k]=$grs[$k];
		}

		class_exists('OwnBB');
		include_once Eleanor::$root.'core/ownbb/url.php';
		$user=&$GLOBALS['Eleanor']->module['user'];
		if($user['signature'])
			$user['signature']=OwnBB::Parse($user['signature']);
		if($user['site'])
			$user['site']=OwnBbCode_url::PreDisplay('',false,$user['site'],true);
		if($user['vk'])
			$user['vk']=OwnBbCode_url::PreDisplay('',false,'http://vk.com/'.$user['vk'],true);
		if($user['twitter'])
			$user['twitter']=OwnBbCode_url::PreDisplay('',false,'http://twitter.com/'.$user['twitter'],true);
		if($user['facebook'])
			$user['facebook']=OwnBbCode_url::PreDisplay('',false,'http://facebook.com/'.$user['facebook'],true);

		$GLOBALS['title'][]=$GLOBALS['Eleanor']->module['user']['full_name'];
		return Eleanor::$Template->AcUserInfo($groups);
	}
}