<?php
/*
	Шаблон оформления. Системного BB редактора

	@var array(
		id - идентификатор редактора
		name - имя контрола редактора
		value - значение редактора
		extra - дополнительные параметры textarea
		smiles - флаг включения смайлов в предпросмотре
		ownbb - флаг включения "своих" BB кодов в предпросмотре
	)
*/

#ToDo! А как же размер и цвет?
if(!defined('CMS'))die;
$lang=Eleanor::$Language->Load($theme.'langs/bbeditor-*.php',false);
?>
							<!-- BB PANEL -->
							<div class="bb_tpanel clrfix">
								<span class="bb_rpanel">
									<a title="<?=$lang['increase_field']?>" href="#" class="bb_plus"><?=$lang['increase_field']?></a>
									<a title="<?=$lang['decrease_field']?>" href="#" class="bb_minus"><?=$lang['decrease_field']?></a>
									<a title="<?=$lang['preview']?>" class="bb_preview" href="#"><?=$lang['preview']?></a>
								</span>
								<a title="<?=$lang['bold']?> (Ctrl+B)" href="#" class="bb_bold"><?=$lang['bold']?></a>
								<a title="<?=$lang['italic']?> (Ctrl+I)" href="#" class="bb_italic"><?=$lang['italic']?></a>
								<a title="<?=$lang['underline']?> (Ctrl+U)" href="#" class="bb_uline"><?=$lang['underline']?></a>
								<a title="<?=$lang['strike']?> (Ctrl+Shift+S)" href="#" class="bb_strike"><?=$lang['strike']?></a>
								<span class="bb_sep">|</span>
								<a title="<?=$lang['right']?> (Ctrl+Shift+R)" href="#" class="bb_right"><?=$lang['right']?></a>
								<a title="<?=$lang['center']?> (Ctrl+Shift+M)" href="#" class="bb_center"><?=$lang['center']?></a>
								<a title="<?=$lang['left']?> (Ctrl+Shift+L)" href="#" class="bb_left"><?=$lang['left']?></a>
								<a title="<?=$lang['justify']?> (Ctrl+Shift+J)" href="#" class="bb_justify"><?=$lang['justify']?></a>
								<span class="bb_sep">|</span>
								<a title="<?=$lang['link']?> (Ctrl+L)" href="#" class="bb_url"><?=$lang['link']?></a>
								<a title="<?=$lang['email']?> (Ctrl+E)" href="#" class="bb_mail"><?=$lang['email']?></a>
								<a title="<?=$lang['image']?> (Ctrl+Shift+I)" href="#" class="bb_img"><?=$lang['image']?></a>
								<a title="<?=$lang['hr']?> (Ctrl+H)" href="#" class="bb_hr"><?=$lang['hr']?></a>
								<span class="bb_sep">|</span>
								<a title="<?=$lang['ul']?>" href="#" class="bb_ul"><?=$lang['ul']?></a>
								<a title="<?=$lang['ol']?>" href="#" class="bb_ol"><?=$lang['ol']?></a>
								<a title="<?=$lang['li']?>" href="#" class="bb_li"><?=$lang['li']?></a>
							</div>
							<!-- END BB PANEL -->

							<!-- EDITOR TEXTAREA -->
							<div class="editor-text"><?= Eleanor::Text($service,$value,$extra+array('rows'=>7))?></div>
							<!-- END EDITOR TEXTAREA -->
<script type="text/javascript">/*<![CDATA[*/new CORE.BBEditor({id:"<?=$id,'"',$ownbb ? ',ownbb:true' : '',$smiles ? ',smiles:true' : '',',service:"',Eleanor::$service?>",Preview:function(html){
	{
		var pr=$("<div class=\"preview\">").width($("#ed-<?=$id?>").width()).appendTo($("#ed-<?=$id?>").children("div.preview").remove().end()),
			hide=$("<div style=\"text-align:center\"><input type=\"button\" class=\"button\" value=\""+CORE.Lang('hide')+"\" /></div>").find("input").click(function(){
				pr.remove();
			}).end();
		pr.html(html+"<br />").append(hide).show();
	}
}});//]]></script>
<!-- END BB EDITOR TEXTAREA+PANEL -->