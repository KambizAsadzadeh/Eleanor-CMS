<?php
/*
	Элемент шаблона. Оформление верхних центральных блоков

	@var массив с ключами:
		title - название блока
		content - содержимое блока
*/
defined('CMS\STARTED')||die;?>
<div class="midbanner clrfix"><a href="#" class="lcol"><?=$title?></a><div class="rcol"><?=$content?></div></div>