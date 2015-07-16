<?php
defined('CMS\STARTED')||die;

/** Вставка скрытого по умолчанию текста, который проявляется если пользователь того захочет
 * @var string $var_0 Содержимое тега
 * @var array $var_1 Параметры тега */

$ex=isset($var_1['ex']);

$GLOBALS['head']['spoiler']=<<<'HTML'
$(function(){
	$(this).on("click",".spoiler .top",function(e){
		e.preventDefault();
		var th=$(this).toggleClass("sp-expanded sp-contracted");
		if(th.is(".sp-expanded"))
			th.next().fadeIn("fast");
		else
			th.next().fadeOut("fast");
	});
})</script>
HTML;
?>
<div class="spoiler">
	<div class="top<?=$ex ? ' sp-expanded' : ' sp-contracted'?>"><?=isset($p['t']) ? $p['t'] : 'Spoiler'?></div>
	<div class="text"<?=$ex ? '' : ' style="display:none"'?>><?=$var_0?></div>
</div>