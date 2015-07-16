<?php
/*
	#ToDo!
	Элемент шаблона. Отображает информацию в рамке иконкой "внимание", "информация" и "ошибка"

	@var отображаемый текст
	@var error|warning|info определяет тип иконки. По умолчанию тип warning
*/
defined('CMS\STARTED')||die;
$type=isset($var_1) ? $var_1 : 'warning';
$isa=is_array($var_0);
?>
<div class="base">
	<div class="binner">
		<div class="warning">
			<img src="<?php echo$theme?>images/<?php echo$type?>.png" style="float:left;" alt="" title="<?php
if($isa and count($var_0)>1 and $type=='error')
	$type.='s';
$title=isset(Eleanor::$Language['tpl'][$type]) ? Eleanor::$Language['tpl'][$type] : 'warning';
echo$title;?>" />
			<h4><?php echo$title?></h4>
			<?php echo is_array($var_0) ? join('<br />',$var_0) : $var_0;?>
			<div class="clr"></div>
		</div>
	</div>
</div>