<?php
defined('CMS\STARTED')||die;

/** Вставка кода на страницу с подсветкой синтаксиса
 * @var string $var_0 Содержимое тега
 * @var array $var_1 Параметры тега */

$GLOBALS['scripts'][]='//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/highlight.min.js';
$GLOBALS['head']['highlight']='<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/styles/default.min.css" />';
$GLOBALS['head'][]=<<<HTML
<script>
hljs.tabReplace="    ";
$(function(){
	$("pre code").each(function(){
		if(!$(this).data("hlled"))
		{
			hljs.highlightBlock(this);
			$(this).data("hlled",true);
		}
	});
})</script>
HTML;
$style=isset($var_1['auto']) ? '' : ' class="'.(isset($var_1[$t]) ? 'language-'.$var_1[$t] : 'no-highlight').'"';
return"<pre><code{$style}><!-- NOBR -->{$var_0}</code></pre><!-- NOBR -->";