<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
class AccountLogOut
{
	public static function Content($master)
	{
		if($master)
		{
			Eleanor::$Login->Logout();
			GoAway(isset($_GET['return']) ? (string)$_GET['return'] : false);
		}
	}
}