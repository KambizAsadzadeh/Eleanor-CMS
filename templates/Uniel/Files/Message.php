<?php
namespace CMS\Templates\Uniel;
/** Элемент шаблона. Отображает информацию в рамке с иконкой "внимание", "информация" и "ошибка"
 * @var array|string $var_0 Текст
 * @var string error|warning|info $var_1 Тип иконки. По умолчанию тип warning
 * @var int|false $var_2 Время в секундах, после которое объявление самоликвидируется
 * @var string $images Путь к каталогу images */
defined('CMS\STARTED')||die;

$type=isset($var_1) ? $var_1 : 'warning';
$isa=is_array($var_0);
$ttl=isset($var_2) ? (int)$var_2 : false; ?>
<div class="base"<?php
if($ttl)
{
	$id=uniqid();
	echo' id="',$id,'"';
}?>>
	<div class="binner">
		<div class="warning">
			<img src="<?=$images,$type?>.png" alt="" title="<?php
if($isa and count($var_0)>1 and $type=='error')
	$type.='s';
$title=isset(T::$lang[$type]) ? T::$lang[$type] : 'warning';
echo$title;?>" />
			<h4><?=$title?></h4>
			<?=$isa ? join('<br />',$var_0) : $var_0?>
			<div class="clr"></div>
		</div>
	</div>
</div>
<?php if($ttl):?>
<script>//<![CDATA[
$(function(){
	setTimeout(function(){
		$("#<?=$id?>").fadeOut("slow");
	},<?php echo$ttl*1000?>);
})//]]></script>
<?php endif?>