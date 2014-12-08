<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/

class AccountExternals
{
	public static function Handler()
	{
		if(isset($_POST['provider'],$_POST['pid']))
		{
			Eleanor::$Db->Delete(P.'users_external_auth','`provider`='.Eleanor::$Db->Escape((string)$_POST['provider']).' AND `provider_uid`='.Eleanor::$Db->Escape((string)$_POST['pid']).' AND `id`='.(int)Eleanor::$Login->Get('id'));
			Result('');
		}
		else
			Error();
	}
}