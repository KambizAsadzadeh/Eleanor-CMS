<?php
#ToDo!
/*
	Шаблон блока "Вертикальное многоуровневое меню"

	@var строка меню, без начального <ul>, представляет собой последовательность <li><a...>...</a><ul><li>...</li></ul></li></ul>
*/
if(!defined('CMS'))die;
$GLOBALS['scripts'][]='js/menu_multilevel.js';
$u=uniqid();
echo'Template in dev';