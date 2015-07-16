<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\OwnBB;
defined('CMS\STARTED')||die;
use \Eleanor\Classes\Strings, \CMS\OwnBB;

/** OwnBB код вложения. Выводит вложение наиболее удобным образом в завимисоти от его типа */
class Attach extends \CMS\Abstracts\OwnBbCode
{
	/** Флаг одиночного тега. По умолчанию все теги являются двойными */
	const SINGLE=true;

	/** Обработка информации перед показом на странице
	 * @param string $t Тег
	 * @param string $p Параметры тега
	 * @param string $c Содержимое тега
	 * @param bool $cu Флаг возможности использования тега
	 * @return string */
	public static function PreDisplay($t,$p,$c,$cu)
	{
		$p=$p ? Strings::ParseParams($p,'file') : [];

		if(isset($p['noparse']))
		{
			unset($p['noparse']);
			return parent::PreSave($t,$p,$c,true);
		}

		if(!$cu)
			return static::RestrictDisplay($t,$p,$c);

		#Если параметр пропущен - тег считаем сбойным и не показываем
		if($p['file']===true)
			return'';

		if(strpos($p['file'],'://')===false)
			$p['file']=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.$p['file'];

		$path3rd=\CMS\Template::$http['3rd'].'static/';
		$type=isset($p['type']) ? $p['type'] : substr(strrchr($p['file'],'.'),1);
		$type=strtolower($type);

		switch($type)
		{
			case'mp4':
			case'webm':
			case'ogv':
				$GLOBALS['head'][__CLASS__]='<link rel="stylesheet" type="text/css" href="'.$path3rd
					.'flowplayer/skin/minimalist.css" />';
				$GLOBALS['scripts'][]=$path3rd.'flowplayer/flowplayer.min.js';

				return<<<HTML
<div class="flowplayer" data-swf="{$path3rd}flowplayer/flowplayer.swf">
	<video><source type="video/{$type}" src="{$p['file']}" /></video>
</div>
HTML
				.(\CMS\AJAX
						? '<script>$(function(){ $(".flowplayer").flowplayer(); })</script>'
						: '');
			case'flv':
				$p['height']=isset($p['height']) ? (int)$p['height'] : 300;
			case'mp3':
				$p['width']=isset($p['width']) ? (int)$p['width'] : 400;
				$p['height']=isset($p['height']) ? (int)$p['height'] : 30;

				$align=isset($p['align']) && in_array($p['align'],['left','center','right']) ? 'float:'.$p['align'] : '';
				$GLOBALS['scripts'][]=$path3rd.'flowplayer/flowplayer-3.2.13.min.js';
				$pl=uniqid('player_');
				$autohide=$type=='mp3' ? 'false' : 'true';

				return<<<HTML
<a href="{$p['file']}" style="display:block;width:{$p['width']}px;height:{$p['height']}px;{$align}" id="{$pl}"></a>
<script>flowplayer("{$pl}","{$path3rd}flowplayer/flowplayer-3.2.18.swf",{
	// pause on first frame of the video
	clip: {
		autoPlay: false,
		autoBuffering: false
	},
	plugins:{
		controls:{
			autoHide: {$autohide}
		}
	}
})</script>
HTML;
			case'jpeg':
			case'jpg':
			case'png':
			case'bmp':
			case'gif':
				$pi=isset($p['mw']) ? ['style'=>' style="max-width:'.(int)$p['mw'].'px"'] : [];

				if(!isset($p['preview']))
					$p['preview']=$p['file'];

				if(!isset($p['alt']) and isset(OwnBB::$opts['alt']))
					$p['alt']=OwnBB::$opts['alt'];

				foreach($p as $k=>$v)
					switch($k)
					{
						case'border':
							$v=abs((int)$v);
							if($v>5)
								$v=5;
							$pi['border']=' border="'.$v.'"';
						break;
						case'alt':
						case'title':
							$pi['alt']=' alt="'.$v.'" title="'.$v.'"';
						break;
						break;
						case'height':
						case'class':
						case'width':
							$pi[$k]=' '.$k.'="'.$v.'"';
					}

				if(!isset($GLOBALS['head']['colorbox']))
				{
					$GLOBALS['head']['fancybox.css']='<link rel="stylesheet" href="//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.css" type="text/css" media="screen" />';
					$GLOBALS['scripts'][]='//cdn.jsdelivr.net/fancybox/2/jquery.fancybox.pack.js';

					$GLOBALS['head'][]=<<<'HTML'
<script>$(function(){ $("a.gallery").fancybox(); })</script>
HTML;
				}

				$params=join(',',$pi);
				return<<<HTML
<a href="{$p['file']}" target="_blank" data-fancybox-group="gallery" data-fancybox-title="{$pi['alt']}" class="gallery"><img src="{$p['preview']}"{$params} /></a>
HTML;
			break;
			case'mpg':
			case'mpeg':
			case'avi':
				$p['height']=isset($p['height']) ? (int)$p['height'] : 420;
			case'mid':
			case'kar':
				$p['width']=isset($p['width']) ? (int)$p['width'] : '100%';

				return<<<HTML
<embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/windows/mediaplayer/en/default.asp" src="{$p['file']}" width="{$p['width']}" height="{$p['height']}" autostart="0" showcontrols="true" showstatusbar="true" showdisplay="true" />
HTML;
			break;
			case'mov':
				$p['width']=isset($p['width']) ? (int)$p['width'] : 520;
				$p['height']=isset($p['height']) ? (int)$p['height'] : 330;

				return<<<HTML
<embed type="application/x-mplayer2" pluginspage="http://www.apple.com/quicktime/download/indext.html" src="{$p['file']}" width="{$p['width']}" height="{$p['height']}" autostart="0" showcontrols="true" showstatusbar="true" showdisplay="true" />
HTML;
			break;
			default:
				return<<<HTML
<a href="{$p['file']}" target="_blank">{$p['file']}</a>
HTML;
		}
	}
}