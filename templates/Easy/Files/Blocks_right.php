<?php
/*
	Элемент шаблона. Оформление правых блоков

	@var массив с ключами:
		title - название блока
		content - содержимое блока
*/
defined('CMS\STARTED')||die;
if(is_array($content))
{
	if(isset($content['title']))
		$content['title']=$title;
	return join($content);
}
?>
				<div class="box">
					<h3 class="btl"><?php echo$title?></h3>
					<div class="bcont">
						<?php echo$content?>
					</div>
				</div>