<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Шаблон по умолчанию для пользователей модуля меню.
*/
class TplUserMenu
{
	/*
		Страница отображения меню сайта
		$a - массив меню сайта, формат id=>array(), ключи внутреннего массива:
			url - ссылка
			title - название пункта меню
			params - параметры ссылки
			parents - идентификаторы всех родителей меню, разделенных запятыми (если они, конечно, есть)
			pos - число по которому отсортировано меню в пределах одного родителя (от меньшего к большему начиная с 1)
	*/
	public static function GeneralMenu($a)
	{
		return Eleanor::$Template->Title(end($GLOBALS['title']))
			->OpenTable().ApiMenu::BuildMultilineMenu($a).Eleanor::$Template->CloseTable();
	}
}