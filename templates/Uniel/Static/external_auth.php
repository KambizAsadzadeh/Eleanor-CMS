<?php
/**
	Элемент шаблона. Отображает кнопки для осуществления внешней авторизации, используя систему идентификации Loginza.
*/
namespace CMS;
/** @var Url $Url */
defined('CMS\STARTED')||die;?><a style="text-decoration:none" href="#" class="externals loginza">
<img src="http://loginza.ru/img/providers/vkontakte.png" title="ВКонтакте" alt="ВКонтакте" />
<img src="http://loginza.ru/img/providers/facebook.png" title="Facebook" alt="Facebook" />
<img src="http://loginza.ru/img/providers/google.png" title="Google Accounts" alt="Google Accounts" />
<img src="http://loginza.ru/img/providers/twitter.png" title="Twitter" alt="Twitter" />
<img src="http://loginza.ru/img/providers/yandex.png" title="Yandex" alt="Yandex" />
<img src="http://loginza.ru/img/providers/mailru.png" title="Mail.ru" alt="Mail.ru" />
<img src="http://loginza.ru/img/providers/openid.png" title="OpenID" alt="OpenID" />
<img src="http://loginza.ru/img/providers/webmoney.png" title="WebMoney" alt="WebMoney" /></a>
<script>//<![CDATA[
if(typeof CORE.Loginza=="undefined")
{
	$(function(){
		$("a.externals").prop("href","https://loginza.ru/api/widget?token_url=<?php
if(!isset(Eleanor::$Template->loginzaurl))
	Eleanor::$Template->loginzaurl=urlencode(\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.$Url('externals'));

echo Eleanor::$Template->loginzaurl.'&amp;lang='.substr(Language::$main,0,3);?>");
		CORE.AddScript("http://loginza.ru/js/widget.js");
	})
	CORE.Loginza=true;
}//]]></script>