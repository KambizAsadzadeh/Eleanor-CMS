<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;

class Spoiler extends \CMS\Abstracts\OwnBbCode
{
	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? \Eleanor\Classes\Strings::ParseParams($p,'t') : [];

		if(isset($p['noparse']))
			return'['.$t.']'.$c.'[/'.$t.']';

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		$ex=isset($p['ex']);

		$GLOBALS['head']['spoiler']='<script>//<![CDATA[
$(function(){
	$(this).on("click",".spoiler .top",function(e){
		e.preventDefault();
		var th=$(this).toggleClass("sp-expanded sp-contracted");
		if(th.is(".sp-expanded"))
			th.next().fadeIn("fast");
		else
			th.next().fadeOut("fast");
	});
})//]]></script>';

		return'<div class="spoiler">
<div class="top'.($ex ? ' sp-expanded' : ' sp-contracted').'">'.(isset($p['t']) ? $p['t'] : 'Spoiler').'</div>
<div class="text"'.($ex ? '' : ' style="display:none"').'>'.$c.'</div>
</div>';
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		$c=preg_replace("#^(\r?\n?<br />\r?\n?)+#i",'<br />',$c);
		$c=preg_replace("#(\r?\n?<br />\r?\n?)+$#i",'<br />',$c);

		return parent::PreSave($t,$p,$c,$cu);
	}
}

return Spoiler::class;