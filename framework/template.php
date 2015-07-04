<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
$lang=new Language(__DIR__.'/template-*.php');
return[
	/** HTML код капчи. Обязательно должен содержать два input (hidden и text), первый с идентификатором сессий, во
	 * втором инпуте пользователь вводит символы из капчи. Имена инпутов name[s] и name[t]
	 * @param array $data Ключи:
	 *  string name уникальное имя капчи
	 *  string session идентификатор сессии, необходимо передать в hidden поле name[s]
	 *  string src Путь к картинке капчи. В этот путь можно добавить параметры w и h для вывода картинки определенного
	 *  размера. При каждом запросе, текст на капче меняется - таким образом, их можно перезагружать при нечитаемости */
	'Captcha'=>function(array$data)use($lang)
	{
		return<<<HTML
<img onclick="this.a;if(!this.a)this.a=this.src;this.src=this.a+'&amp;new='+Math.random()" src="{$data['src']}&amp;w=120&amp;h=60" alt="" style="cursor:pointer;" id="{$data['name']}" title="{$lang['captcha_click']}" /><br />
HTML
			.Html::Input($data['name'].'[t]','',['class'=>'need-tabindex', 'style'=>'width:120px'])
			.Html::Input($data['name'].'[s]',$data['session'],['type'=>'hidden']);
	},

	/** Аналог BSOD
	 * @param string $type Тип данных: text, html, json
	 * @param string $error Текст ошибки
	 * @param array $extra Дополнительные параметры: file, line, hint - подсказка для исправления
	 * @return string*/
	'BSOD'=>function($type,$error,array$extra=[])use($lang)
	{
		$file=isset($extra['file']) ? $extra['file'] : '';
		$hint=isset($extra['hint']) ? $extra['hint'] : '';

		switch($type)
		{
			case'text':
				if($file and isset($extra['line']))
					$file.='['.$extra['line'].']';

				return$lang['the_error_occurred'].': '.$error.($file ? "\n".$file : '').($hint ? "\n Hint: ".$hint : '');
			case'json':
				return Html::JSON(['error'=>$error]+$extra);
		}

		$charset=\Eleanor\CHARSET;
		$base=\Eleanor\SITEDIR;
		$year=idate('Y');
		$line=$file && isset($extra['line']) ? '<br />'.$lang['line'].': '.$extra['line'] : '';
		$hint=$hint ? '<hr />'.$lang['hint'].': '.$extra['hint'] : '';

		if($file)
			$file='<br />'.$lang['file'].': '.$file;

		return<<<HTML
<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset={$charset}" />
<title>{$lang['the_error_occurred']}</title><base href="{$base}">
<style type="text/css">/*<![CDATA[*/
body, div { color:#1d1a15; font-size: 11px; font-family: Tahoma, Helvetica, sans-serif; }
body { text-align: left; height: 100%; line-height: 142%; padding: 0; margin: 20px; background-color: #FFFFFF; }
hr { height: 1px; border: solid #d8d8d8 0; border-top-width: 1px; }
.copyright { position:fixed; bottom:10px; right:10px; }
a { text-decoration:none; }
h1 { font-size:18px }
/*]]>*/</style></head><body>
<div class="copyright">Powered by <a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> &copy; {$year}</div>
<h1>{$lang['the_error_occurred']}</h1><hr /><code>{$error}{$file}{$line}</code>{$hint}</body></html>
HTML;
	}
];
