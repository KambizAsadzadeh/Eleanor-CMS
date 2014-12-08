<?php
namespace CMS;
/** Элемент шаблона. Отображает информацию в рамке с иконкой "ошибка"
 * @var array|string $var_0 Текст
 * @var string $images Путь к каталогу images */
defined('CMS\STARTED')||die;
$isa=is_array($var_0);?>
<div class="warning">
	<img src="<?=$images?>warning.png" />
	<div>
		<h4><?Eleanor::$Language['main']['error']?></h4>
		<b><?=$isa ? join('<br />',$var_0) : (strpos($var_0,'<br')===false ? '<br />' : '').$var_0?></b>
	</div>
	<div class="clr"></div>
</div>