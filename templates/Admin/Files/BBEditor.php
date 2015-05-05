<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

defined('CMS\STARTED')||die;
/** Шаблон оформления BB редактора
 * @var string $id Идентификатор редактора
 * @var string $name Имя редактора
 * @var string $value Значение редактора
 * @var array $extra Дополнительные параметры для textarea
 * @var string $preview Адрес, куда посылать AJAX запрос для получения превью текста
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images */

$lang=Eleanor::$Language->Load(__DIR__.'/../translation/bbeditor-*.php',false);
$GLOBALS['scripts'][]=T::$http['static'].'js/bb_editor.js';
$textid=isset($extra['id']) ? $extra['id'] : $id;?>
<div class="bb-top" id="editor-<?=$id?>">
	<div class="bb-right">
		<button class="bb bb-fullscreen" title="<?=$lang['fullscreen']?>"><span class="ico-fullscreen"></span></button>
		<button class="bb" title="<?=$lang['preview']?>" data-url="<?=$preview?>"><span class="ico-view"></span></button>
	</div>
	<button class="bb" title="<?=$lang['bold']?>"><span class="ico-bold"></span></button>
	<button class="bb" title="<?=$lang['italic']?>"><span class="ico-italic"></span></button>
	<button class="bb" title="<?=$lang['underline']?>"><span class="ico-underline"></span></button>
	<button class="bb" title="<?=$lang['strike']?>"><span class="ico-strike"></span></button>
	<span class="bb-sep"></span>
	<button class="bb" title="<?=$lang['left']?>"><span class="ico-left"></span></button>
	<button class="bb" title="<?=$lang['center']?>"><span class="ico-center"></span></button>
	<button class="bb" title="<?=$lang['right']?>"><span class="ico-right"></span></button>
	<button class="bb" title="<?=$lang['justify']?>"><span class="ico-justify"></span></button>
	<span class="bb-sep"></span>
	<button class="bb" title="<?=$lang['hr']?>"><span class="ico-hr"></span></button>
	<button class="bb" title="<?=$lang['link']?>"><span class="ico-link"></span></button>
	<button class="bb" title="<?=$lang['image']?>"><span class="ico-img"></span></button>
	<span class="bb-sep"></span>
	<button class="bb" title="<?=$lang['ul']?>"><span class="ico-ul"></span></button>
	<button class="bb" title="<?=$lang['ol']?>"><span class="ico-ol"></span></button>
	<button class="bb" title="<?=$lang['li']?>"><span class="ico-li"></span></button>
	<div class="bb-combo dropdown">
		<button class="bb" data-toggle="dropdown" title="Размер шрифта"><span class="ico-fontsize"></span></button>
		<ul class="dropdown-menu bb-fontsize">
			<li class="bb-f-h1"><a href="#"><?=$lang['h1']?></a></li>
			<li class="bb-f-h2"><a href="#"><?=$lang['h2']?></a></li>
			<li class="bb-f-h3"><a href="#"><?=$lang['h3']?></a></li>
			<li class="bb-f-small"><a href="#"><?=$lang['small']?></a></li>
			<li class="divider"></li>
			<li class="bb-f-custom"><a href="#"><?=$lang['input-size']?></a></li>
		</ul>
	</div>
	<button class="bb" title="<?=$lang['tab']?>"><span class="ico-tab"></span></button>
	<button class="bb" title="<?=$lang['nobb']?>"><span class="ico-nobb"></span></button>
</div>
<?=Html::Text($name,$value,$extra+['class'=>'form-control','rows'=>8,'id'=>$id])?>
<script>$(function(){
	new CORE.BBEditor("<?=$id?>",$("#<?=$textid?>"),$("#editor-<?=$id?> button.bb,#editor-<?=$id?> li>a"));
})</script>