<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
class AccountLogout
{
	public static function Content($master=false)
	{
		if($master)
			GoAway(true);
	}
}