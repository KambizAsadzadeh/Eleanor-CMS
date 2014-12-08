<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use Eleanor\Classes\Strings, CMS\OwnBB;

/** Вставка кода на страницу с подсветкой синтаксиса */
class Code extends \CMS\Abstracts\OwnBbCode
{
	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		$GLOBALS['scripts'][]='//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/highlight.min.js';
		$GLOBALS['head']['highlight']='<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/styles/default.min.css" />';
		$GLOBALS['head'][]=<<<HTML
<script>//<![CDATA[
hljs.tabReplace="    ";
$(function(){
	$("pre code").each(function(){
		if(!$(this).data("hlled"))
		{
			hljs.highlightBlock(this);
			$(this).data("hlled",true);
		}
	});
})//]]></script>
HTML;
		return'<pre><code'
			.(isset($p['auto']) ? '' : ' class="'.(isset($p[$t]) ? 'language-'.$p[$t] : 'no-highlight').'"')
			.'><!-- NOBR -->'.$c.'</code></pre><!-- NOBR -->';
	}

	/** Обработка информации перед её правкой
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreEdit($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,$t) : [];

		if(isset($p[$t]) and $p[$t]=='no-highlight')
			unset($p[$t]);

		if(!empty(OwnBB::$opts['visual']))
		{
			$c=str_replace("\t",'    ',$c);
			$c=str_replace(' ','&nbsp;',$c);
			$c=nl2br($c);
		}
		else
			$c=htmlspecialchars_decode($c,\CMS\ENT);

		return parent::PreSave($t,$p,$c,true);
	}

	/** Обработка информации перед её сохранением
	 * @param string $t Тег
	 * @param string $p Параметры
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreSave($t,$p,$c,$cu)
	{
		if(!empty(OwnBB::$opts['visual']))
		{
			$c=preg_replace('#<br ?/?>#i',"\n",$c);
			$c=strip_tags($c,'<span><a><img><input><b><i><u><s><em><strong>');
		}
		else
			$c=htmlspecialchars($c,\CMS\ENT,\Eleanor\CHARSET);

		$c=\Eleanor\Classes\SafeHtml::Make($c);

		return parent::PreSave($t,$p,$c,$cu);
	}
}

return Code::class;