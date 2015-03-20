<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
class AccountActivate
{
	public static function Content($master=true)
	{
		if($master)
			return AccountIndex::Content($master);
		ExitPage();
	}
}