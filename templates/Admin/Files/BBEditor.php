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
if(isset(T::$data['speedbar'])):
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
<?php else:
$spacer=T::$http['static'].'images/spacer.png';
$GLOBALS['head']['bbeditor']='<link rel="stylesheet" type="text/css" href="'.$css.'bbeditor.css" media="screen" />';
$lang=Eleanor::$Language->Load(__DIR__.'/../translation/bbeditor-*.php',false);?><!-- BB EDITOR TEXTAREA+PANEL -->
<div class="bb_editor" id="ed-<?=$id?>">

<!-- BB PANEL -->
<div class="bb_panel">
<div class="bb_rpanel">
	<a href="#" title="<?=$lang['preview']?>" class="bb_preview"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['increase_field']?>" class="bb_plus"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['decrease_field']?>" class="bb_minus"><img src="<?=$spacer?>" alt="" /></a>
</div>
	<a href="#" title="<?=$lang['bold']?>" class="bb_bold"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['italic']?>" class="bb_italic"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['underline']?>" class="bb_underline"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['strike']?>" class="bb_strike"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['left']?>" class="bb_left"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['center']?>" class="bb_center"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['right']?>" class="bb_right"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['justify']?>" class="bb_justify"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['hr']?>" class="bb_hr"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['link']?>" class="bb_url"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['email']?>" class="bb_mail"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['image']?>" class="bb_img"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['ul']?>" class="bb_ul"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['ol']?>" class="bb_ol"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['li']?>" class="bb_li"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['tm']?>" class="bb_tm"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['copyright']?>" class="bb_c"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['registered']?>" class="bb_r"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['font']?>" class="bb_font"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['tab']?>" class="bb_tab"><img src="<?=$spacer?>" alt="" /></a>
	<a href="#" title="<?=$lang['nobb']?>" class="bb_nobb"><img src="<?=$spacer?>" alt="" /></a>
<div class="clr"></div>
</div>
<!--END BB PANEL -->
	<div class="dtarea"><?=Html::Text($name,$value,$extra+['style'=>'width:99.5%','rows'=>10])?></div>
	<div class="bb_fonts" style="position:absolute;display:none;">
	<table><tr>
	<td><?=$lang['color']?>:</td>
	<td>
		<select class="bb_color" size="1">
			<option value="0"><?=$lang['select']?></option>
			<option style="background-color: black; color: #ffffff;">black</option>
			<option style="background-color: gray; color: #ffffff;">gray</option>
			<option style="background-color: white; color: #000000;">white</option>
			<option style="background-color: maroon; color: #ffffff;">maroon</option>
			<option style="background-color: orange; color: #ffffff;">orange</option>
			<option style="background-color: orangered; color: #ffffff;">orangered</option>
			<option style="background-color: red; color: #ffffff;">red</option>
			<option style="background-color: purple; color: #ffffff;">purple</option>
			<option style="background-color: fuchsia; color: #ffffff;">fuchsia</option>
			<option style="background-color: green; color: #ffffff;">green</option>
			<option style="background-color: lime; color: #ffffff;">lime</option>
			<option style="background-color: olive; color: #ffffff;">olive</option>
			<option style="background-color: yellow; color: #000000;">yellow</option>
			<option style="background-color: navy; color: #ffffff;">navy</option>
			<option style="background-color: blue; color: #ffffff;">blue</option>
			<option style="background-color: teal; color: #ffffff;">teal</option>
			<option style="background-color: aqua; color: #ffffff;">aqua</option>
		</select>
	</td>
		</tr><tr>
	<td><?=$lang['background']?>:</td>
	<td>
		<select class="bb_background" size="1">
			<option value="0"><?=$lang['select']?></option>
			<option style="background-color: black; color: #ffffff;">black</option>
			<option style="background-color: gray; color: #ffffff;">gray</option>
			<option style="background-color: white; color: #000000;">white</option>
			<option style="background-color: maroon; color: #ffffff;">maroon</option>
			<option style="background-color: orange; color: #ffffff;">orange</option>
			<option style="background-color: orangered; color: #ffffff;">orangered</option>
			<option style="background-color: red; color: #ffffff;">red</option>
			<option style="background-color: purple; color: #ffffff;">purple</option>
			<option style="background-color: fuchsia; color: #ffffff;">fuchsia</option>
			<option style="background-color: green; color: #ffffff;">green</option>
			<option style="background-color: lime; color: #ffffff;">lime</option>
			<option style="background-color: olive; color: #ffffff;">olive</option>
			<option style="background-color: yellow; color: #000000;">yellow</option>
			<option style="background-color: navy; color: #ffffff;">navy</option>
			<option style="background-color: blue; color: #ffffff;">blue</option>
			<option style="background-color: teal; color: #ffffff;">teal</option>
			<option style="background-color: aqua; color: #ffffff;">aqua</option>
		</select>
	</td>
		</tr><tr>
	<td><?=$lang['size']?>:</td>
	<td>
		<select class="bb_size" size="1">
			<option value="0"><?=$lang['select']?></option>
			<option>8</option>
			<option>10</option>
			<option>12</option>
			<option>14</option>
			<option>16</option>
			<option>18</option>
			<option>20</option>
			<option>22</option>
			<option>24</option>
			<option>26</option>
			<option>28</option>
			<option>30</option>
			<option>32</option>
		</select>
	</td>
	</tr><tr>
	<td><?=$lang['font']?>:</td>
	<td>
		<select class="bb_font" size="1">
			<option value="0"><?=$lang['select']?></option>
			<option style="font-family: Arial, Helvetica, sans-serif;">Arial</option>
			<option style="font-family: 'Times New Roman', Times, serif;">Times New Roman</option>
			<option style="font-family: 'Courier New', Courier, monospace;">Courier New</option>
			<option style="font-family: Geneva, Arial, Helvetica, sans-serif;">Geneva</option>
			<option style="font-family: Verdana, Arial, Helvetica, sans-serif;">Verdana</option>
			<option style="font-family: Georgia, 'Times New Roman', Times, serif;">Georgia</option>
			<option style="font-family: 'Comic Sans MS', Georgia, Times, cursive;">Comic Sans MS</option>
		</select>
	</td>
	</tr></table>
</div>

</div><!--
<script>/*<![CDATA[*/new CORE.BBEditor({id:"<?=$id?>",preview:"<?=htmlspecialchars_decode($preview,\CMS\ENT)?>",Preview:function(html){
	var editor=$("#ed-<?=$id?>"),
		place=editor.parent().children("div.preview").remove().end().find("div.bb_yourpanel"),
		preview=$("<div class=\"preview\">").width( editor.parent().width() ).insertAfter( place.size()>0 ? place : editor ),
		hide=$("<div style=\"text-align:center\"><input type=\"button\" class=\"button\" value=\""+CORE.Lang('hide')+"\" /></div>")
			.find("input").click(function(){
				preview.remove();
			}).end();

	preview.html(html+"<br />").append(hide).show();
}})//]]></script> -->
<!-- END BB EDITOR TEXTAREA+PANEL -->
<?php endif?>