<?php
/*
	Шаблон блока "Вертикальное одноуровневое меню"

	@var массив меню, где каждый элемент - готовая ссылка <a href="...">...</a>
*/
if(!defined('CMS'))die;
echo'<nav><ul class="vmenu"><li>',join('</li><li>',$var_0),'</ul></nav>';