<?php
namespace CMS;
/** Невидимый элемент шаблона. Заставляет браузер перейти на определенный адрес
 * @var string $var_0 Адрес перехода
 * @var int $var_1 Время задержки в секундах */
defined('CMS\STARTED')||die;

if(strpos($var_0,'//')===false)
	$var_0=\Eleanor\SITEDIR.$var_0;

$GLOBALS['head']['redirect']='<meta http-equiv="refresh" content="'.$var_1.'; url='.$var_0.'" />';?>
<script>
$(function(){
	if($("meta[http-equiv=refresh]").length==0)
		setTimeout(function(){location.href="<?=$var_0?>"},<?=$var_1?>*1000)
})</script>