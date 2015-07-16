<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use \Eleanor\Classes\Strings, \CMS\Eleanor;

/** Вставка видео с видехостингов по ссылке */
class OnlineVideo extends \CMS\Abstracts\OwnBbCode
{
	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p) : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		$c=trim($c);

		#YouTube
		if(preg_match('%(?:\?|#|;|&)v=([a-z0-9\-_]+)%i',$c,$m)>0 or
			preg_match('%embed/([a-z0-9\-_]+)%i',$c,$m)>0 or
			preg_match('%youtu\.be/([a-z0-9\-_]+)%i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 425;
			$h=isset($p['height']) ? (int)$p['height'] : 344;

			return<<<HTML
<iframe width="{$w}" height="{$h}" src="http://www.youtube.com/embed/{$m[1]}" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
HTML;
		}

		#RuTube
		if(preg_match('#v=([a-f0-9]{32})#i',$c,$m)>0 or preg_match('#video/([a-f0-9]{32})#i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 470;
			$h=isset($p['height']) ? (int)$p['height'] : 353;
			return<<<HTML
<object width="{$w}" height="{$h}">
	<param name="movie" value="http://video.rutube.ru/{$m[1]}" />
	<param name="wmode" value="window" />
	<param name="allowfullscreen" value="true" />
	<embed src="http://video.rutube.ru/{$m[1]}" type="application/x-shockwave-flash" wmode="window" width="{$w}" height="{$h}" allowfullscreen="true" ></embed>
</object>
HTML;
		}

		#Smotri.com
		if(preg_match('#\?id=([a-z0-9]+)#i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 400;
			$h=isset($p['height']) ? (int)$p['height'] : 330;

			return<<<HTML
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="{$w}" height="{$h}">
	<param name="movie" value="http://pics.smotri.com/scrubber_custom8.swf?file={$m[1]}&amp;bufferTime=3&amp;autoStart=false&amp;str_lang=rus&amp;xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&amp;xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowFullScreen" value="true" />
	<param name="bgcolor" value="#ffffff" />
	<embed src="http://pics.smotri.com/scrubber_custom8.swf?file={$m[1]}&amp;bufferTime=3&amp;autoStart=false&amp;str_lang=rus&amp;xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&amp;xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" quality="high" allowscriptaccess="always" allowfullscreen="true" wmode="window"  width="{$w}" height="'.$h.'" type="application/x-shockwave-flash"></embed>
</object>
HTML;
		}

		#Big)mir
		if(preg_match('#show/([0-9]+)/#i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 625;
			$h=isset($p['height']) ? (int)$p['height'] : 395;

			return<<<HTML
<object width="{$w}" height="{$h}">
	<param name="movie" value="http://video.bigmir.net/extplayer/{$m[1]}/" />
	<param name="wmode" value="transparent" />
	<param name="flashvars" value="" />
	<embed src="http://video.bigmir.net/extplayer/{$m[1]}/" type="application/x-shockwave-flash" wmode="transparent" width="{$w}" height="{$h}" flashvars=""></embed>
</object>
HTML;
		}

		#PLAY.ukr.net
		if(preg_match('#key/([a-f0-9]+)/#i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 585;
			$h=isset($p['height']) ? (int)$p['height'] : 345;

			return<<<HTML
<object width="{$w}" height="'.$h.'" align="middle" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,18,0" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">
	<param value="true" name="allowFullScreen"/>
	<param name="allowScriptAccess" value="always" />
	<param name="FlashVars" value="StopAsking=0&self=0" />
	<param value="http://play.ukr.net/player.swf?key=key/{$m[1]}" name="movie" />
	<embed name="player" allowScriptAccess="always" width="585" height="345" align="middle" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowfullscreen="true" src="http://play.ukr.net/player.swf?key=key/{$m[1]}&amp;StopAsking=0&amp;self=0"/>
</object>
HTML;
		}

		#Vimeo.com
		if(preg_match('#vimeo.com/([0-9]+)#i',$c,$m)>0)
		{
			$w=isset($p['width']) ? (int)$p['width'] : 400;
			$h=isset($p['height']) ? (int)$p['height'] : 225;

			return<<<HTML
<iframe src="http://player.vimeo.com/video/{$m[1]}?title=0&amp;byline=0&amp;portrait=0" width="{$w}" height="{$h}" frameborder="0"></iframe>
HTML;
		}

		if(filter_var($c,FILTER_VALIDATE_URL))
		{
			$href=$c;
			$rel=$pref='';

			if(false!==$pos=strpos($href,'://') and $pos<7)
			{
				$sitepref=\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR;

				if(strpos($href,$sitepref)===0)
					$href=substr($href,strlen($sitepref));
				else
				{
					if(!isset(Eleanor::$vars['antidirectlink']))
						\CMS\LoadOptions('editor');

					if(Eleanor::$vars['antidirectlink'])
						if(Eleanor::$vars['antidirectlink']=='nofollow')
							$rel=' rel="nofollow"';
						else
							$pref='go.php?';
				}
			}

			$href=htmlspecialchars($href,\CMS\ENT,\Eleanor\CHARSET,false);
			return<<<HTML
<a href="{$pref}{$href}" target="_blank"{$rel}>{$c}</a>
HTML;
		}

		return$c;
	}
}

return OnlineVideo::class;