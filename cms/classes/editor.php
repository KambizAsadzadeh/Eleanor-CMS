<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;

/** Поддержка полноценного редактора на странице. Логическое продолжение (сохранение) смотри в классе saver */
class Editor extends \Eleanor\BaseClass
{use Traits\Editor;

	public static
		/** @var string Автоматическое прописывание альтов к картинкам */
		$alt='',

		/** @var string Путь на генерацию превью для AJAX запроса */
		$preview='?direct=ajax&amp;';

	/** Получение кода редактора, поместив полученный код на странице получается готовый редактор.
	 * @param string $name Имя элемента формы редактора
	 * @param string $value HTML содержимое редактора
	 * @param array $extra Дополнительные параметры textarea редактора
	 * @param array $settings Дополнительные параметры редактора, ключи
	 *  mixed nosmiles Если присутствует флаг (не равен null) смайлы не будут отображаться (но будут обрабатываться)
	 *  string syntax Название синтаксиса, для его подсветки. Работает пока только для codemirror
	 *  bool post Указывает редактору, что переданное $value взято из POST запроса, и не является сохраненным HTML
	 *  string codemirror_embed Тело функции для вставки объектов. Должна вернуть строку, входящие переменные: type,data
	 *  string ckeditor_package На выбор пакет CKeditor: basic, standar, full (default)
	 *  array bb,no,ckeditor Определяют дополнительные параметры тега textarea при помощи которого создается редактор.
	 * @param null|Template $Template
	 * @return string */
	public function Area($name,$value='',array$extra=[],array$settings=[],$Template=null)
	{
		$value=(string)$value;

		if(!isset($settings['post']))
			$settings['post']=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;

		if(!$settings['post'])
			$value=$this->GetEdit($value);

		if(isset($extra['id']))
			$id=$extra['id'];
		else
			$extra['id']=$id=preg_replace('#\W+#','',$name);

		if(!$Template)
			$Template=Eleanor::$Template;

		switch($this->type)
		{
			case'bb':#Родной ББ
				$preview=static::$preview;

				if($this->smiles)
					$preview.='smiles=1&amp;';

				$html=$Template->BBEditor([
					'id'=>$id,
					'name'=>$name,
					'value'=>$value,
					'extra'=>$extra,
					'preview'=>$this->ownbb ? $preview.'ownbb=1' : preg_replace('#(&amp;|\?|&)$#','',$preview),
				]);
			break;
			case'ckeditor':
/* Некоторые настройки ckeditor
,
toolbarGroups:[
	{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
	{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
	{ name: 'links' },
	{ name: 'insert' },
	{ name: 'tools' },
	{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
	{ name: 'others' },
	'/',
	{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
	{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
	{ name: 'styles' },
	{ name: 'colors' }
],
removeButtons:'Underline,Subscript,Superscript,Smiley,Flash,Templates,Save,Newpage,Print,Language,'+
'CreateDiv,Styles,Iframe,NewPage,PageBreak',
format_tags:'p;h1;h2;h3;pre',
removeDialogTabs:'image:advanced;link:advanced'*/

				$language=substr(Language::$main,0,2);
				$ckeditor='//cdn.ckeditor.com/4.4.7/'.(isset($settings['ckeditor_package']) ? $settings['ckeditor_package'] : 'full').'/';
				$GLOBALS['head']['ckeditor']=<<<HTML
<script>window.CKEDITOR_BASEPATH="{$ckeditor}";</script><script src="{$ckeditor}ckeditor.js"></script><script>
var CKEDITOR_CONFIG={
	language:"{$language}",
	toolbarGroups:[
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		{ name: 'styles' },
		{ name: 'colors' }
	],
	removeButtons:'Underline,Subscript,Superscript,Smiley,Flash,Templates,Save,Newpage,Print,Language,CreateDiv,Styles,Iframe,NewPage,PageBreak',
	format_tags:'p;h1;h2;h3;pre'
};
</script>
HTML;
				$html=Html::Text($name,$value,$extra)
					.<<<HTML
<script>
(function(){
	if(typeof CKEDITOR.instances.{$id}!="undefined")
		try{ CKEDITOR.instances.{$id}.destroy(); }catch(e){};

	var editor=CKEDITOR.replace("{$id}",CKEDITOR_CONFIG);

	if(editor)
	{
		EDITOR.New(
			editor.name,
			{
				Embed:function(type,data)
				{
					if(type=="image" && data.src)
						editor.insertElement(CKEDITOR.dom.element.createFromHtml("<img src=\""+data.src+"\" title=\""+(data.title||"")+"\" alt=\""+(data.alt||data.title||"")+"\" />"));
					else if(type=="nick" && data.name)
						editor.insertElement(CKEDITOR.dom.element.createFromHtml("<b>"+data.name.replace(/</g,"&lt;").replace(/>/g,"&gt;")+"</b>, "));
				},
				Insert:function(pre,after,F){
					var s=editor.getSelection().getSelectedText();
					if($.isFunction(F))
						s=F(s);
					editor.insertHtml(pre+s+after);
				},
				Get:function(){ return editor.getData(); },
				Set:function(text){ editor.setData(text); },
				Selection:function(){ return editor.getSelection().getSelectedText(); }
			}
		);
		editor.on("focus",function(){EDITOR.Active(this.name)});
	}
	else
		console.error("CKEditor was no loaded...");

})()</script>
HTML;
			break;
			case'tinymce':#Tiny MCE
				#Загружаем из CDN
				$GLOBALS['scripts'][]='//tinymce.cachefly.net/4.1/tinymce.min.js';

				static$tinyalr=true;#Каждый раз при запуске страницы, нужно вызывать этот код

				if($tinyalr)
					$GLOBALS['head'][]=<<<HTML
<script>
(function(){
	var Run=function(){
		tinymce.init({
			selector: "#{$id}",
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
		});
	};
	if(CORE.in_ajax.length)
		CORE.after_ajax.push(Run);
	else
		Run()
})()</script>
HTML;
				$tinyalr=false;

				$html=Html::Text($name,$value,$extra);
			break;
			case'codemirror':
				$cm='//cdnjs.cloudflare.com/ajax/libs/codemirror/5.1.0/';
				array_push($GLOBALS['scripts'],$cm.'codemirror.js',
					$cm.'addon/selection/active-line.js',/* styleActiveLine:true, lineNumbers:true, ineWrapping: true */
					$cm.'addon/edit/closebrackets.js',/* autoCloseBrackets: true */

					/* autoCloseTags: true */
					$cm.'addon/edit/closetag.js',
					$cm.'addon/fold/xml-fold.js',

					/* extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }}, foldGutter: true,
 gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"] */
					$cm.'addon/fold/foldcode.js',
					$cm.'addon/fold/foldgutter.js',
					$cm.'addon/fold/brace-fold.js',
					$cm.'addon/fold/xml-fold.js',
					$cm.'addon/fold/markdown-fold.js',
					$cm.'addon/fold/comment-fold.js',

					/* extraKeys: {
  "F11": function(cm) {
    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
  },
  "Esc": function(cm) {
    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
  }
} */
					$cm.'addon/display/fullscreen.js',

					/* matchBrackets:true */
					$cm.'addon/edit/matchbrackets.js',

					$cm.'addon/search/searchcursor.js',
					$cm.'addon/search/match-highlighter.js',
					$cm.'addon/dialog/dialog.js',
					$cm.'addon/search/search.js',

					$cm.'addon/display/placeholder.js',

					/* showTrailingSpace: true */
					$cm.'addon/edit/trailingspace.js'
				);
				$GLOBALS['head']['codemirror']=<<<HTML
<link rel="stylesheet" href="{$cm}codemirror.css" />
<link rel="stylesheet" href="{$cm}addon/fold/foldgutter.css" />
<link rel="stylesheet" href="{$cm}addon/display/fullscreen.css" />
<link rel="stylesheet" href="{$cm}addon/dialog/dialog.css" />
<style type="text/css">
.CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black; font-size:12px; }
.CodeMirror-focused .cm-matchhighlight {
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFklEQVQI12NgYGBgkKzc8x9CMDAwAAAmhwSbidEoSQAAAABJRU5ErkJggg==);
	background-position: bottom;
	background-repeat: repeat-x;
}
.CodeMirror-matchingtag { background: rgba(255, 150, 0, .3); }
.CodeMirror-empty { outline: 1px solid #c22; }
.CodeMirror-empty.CodeMirror-focused { outline: none; }
.CodeMirror pre.CodeMirror-placeholder { color: #999; }
dt {font-family: monospace; color: #666;}
.cm-trailingspace {
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAACCAYAAAB/qH1jAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QUXCToH00Y1UgAAACFJREFUCNdjPMDBUc/AwNDAAAFMTAwMDA0OP34wQgX/AQBYgwYEx4f9lQAAAABJRU5ErkJggg==);
	background-position: bottom left;
	background-repeat: repeat-x;
}
.cm-tab {
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAMCAYAAAAkuj5RAAAAAXNSR0IArs4c6QAAAGFJREFUSMft1LsRQFAQheHPowAKoACx3IgEKtaEHujDjORSgWTH/ZOdnZOcM/sgk/kFFWY0qV8foQwS4MKBCS3qR6ixBJvElOobYAtivseIE120FaowJPN75GMu8j/LfMwNjh4HUpwg4LUAAAAASUVORK5CYII=);
	background-position: right;
	background-repeat: no-repeat;
}
</style>
HTML;
				$syntax=isset($settings['syntax']) ? preg_replace('#[^a-z0-9]+#','',(string)$settings['syntax']) : '';
				$params=',autoCloseBrackets: true';
				$extrakeys='';

				switch($syntax)
				{
					case'php':
						array_push($GLOBALS['scripts'],$cm.'addon/edit/closebrackets.js');
					break;
					case'js':
					case'javascript';
						array_push($GLOBALS['scripts'],$cm.'addon/hint/show-hint.js',
							$cm.'addon/hint/javascript-hint.js',
							$cm.'addon/edit/closebrackets.js',
							$cm.'mode/javascript/javascript.js');
						$GLOBALS['head']['codemirror'].='<link rel="stylesheet" href="'.$cm
							.'addon/hint/show-hint.css" />';
						$extrakeys.=',"Ctrl-Space": "autocomplete"';
						$params.=', mode: {name: "javascript", globalVars: true} ';
					break;
					case'sql':
						array_push($GLOBALS['scripts'],$cm.'mode/sql/sql.js');
						$params.=', mode: "sql"';
					break;
					case'css':
						array_push($GLOBALS['scripts'],$cm.'mode/css/css.js');
						$params.=', mode: "css"';
					break;
					case'http':
						array_push($GLOBALS['scripts'],$cm.'mode/http/http.js');
					break;
					default:#HTML
						array_push($GLOBALS['scripts'],$cm.'addon/hint/show-hint.js',
							$cm.'addon/hint/xml-hint.js',
							$cm.'addon/hint/html-hint.js',
							$cm.'addon/edit/closebrackets.js',
							$cm.'addon/edit/matchtags.js',
							$cm.'addon/fold/xml-fold.js',
							$cm.'mode/javascript/javascript.js',
							$cm.'mode/xml/xml.js',
							$cm.'mode/css/css.js',
							$cm.'mode/htmlmixed/htmlmixed.js',
							$cm.'mode/clike/clike.js',
							$cm.'mode/php/php.js'
						);
						$GLOBALS['head']['codemirror'].='<link rel="stylesheet" href="'.$cm
							.'addon/hint/show-hint.css" />';
						$params.=', mode: "application/x-httpd-php"';
				}

				$replace=isset($settings['codemirror_embed'])
					? $settings['codemirror_embed']
					: 'if(type=="image" && data.src)return"<img src=\""+data.src+"\""+(data.title ? " title=\""+data.title+"\"" : "")+" />";';

				$html=Html::Text($name,$value,$extra)
					.<<<HTML
<script>
$(function(){
	$("#{$id}").addClass("cloneable").on("clone",function(){
		var id=$(this).attr("id"),
			editor=CodeMirror.fromTextArea(
				this,
				{
					lineNumbers:true,
					autoCloseBrackets: true,
					styleActiveLine: true,
					lineWrapping: true,
					autoCloseTags: true,
					extraKeys: { "Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); },
						"F11": function(cm) {
							cm.setOption("fullScreen", !cm.getOption("fullScreen"));
						},
						"Esc": function(cm) {
							if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
						}{$extrakeys}
					},
					foldGutter: true,
					gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
					highlightSelectionMatches: {showToken: /\w/},
					indentWithTabs:true,
					indentUnit:4,
					matchBrackets:true,
					showTrailingSpace: true{$params}
				}
			);

		editor.on("focus",function(){EDITOR.Active(id)});
		EDITOR.New(
			id,
			{
				Embed:function(type,data)
				{
					var replace=(function(){{$replace}})();

					if(replace)
						editor.replaceSelection(replace);
				},
				Insert:function(pre,after,F){
					var s=editor.getSelection();
					if($.isFunction(F))
						s=F(s);
					editor.replaceSelection(pre+s+after);
				},
				Set:function(text){ editor.setValue(text); },
				Get:function(){ return editor.getValue(); },
				Selection:function(){ return editor.getSelection(); }
			}
		);
	}).trigger("clone");
})</script>
HTML;
			break;
			default:#Без редактора
				$GLOBALS['scripts'][]=Template::$http['static'].'js/bb_editor.js';
				$html=Html::Text($name,$value,(isset($extra['no']) ? (array)$extra['no'] : [])+['id'=>$id]).<<<HTML
<script>$(function(){
$("#{$id}").addClass("cloneable").on("clone",function(){
	var th=$(this),
		id=th.attr("id");

	EDITOR.New(id,{
			Embed:function(type,data)
			{
				if(type=="image" && data.src)
					SetSelectedText(th,data.src);
			},
			Insert:function(pre,after,F){ SetSelectedText(th,pre,after,F); },
			Get:function(){ return th.val(); },
			Set:function(text){ th.val(text); }
		}
	);
	$(this).focus(function(){EDITOR.Active(id)});
}).trigger("clone");
</script>
HTML
;
		}

		$ownbb=[];
		if($this->ownbb)
		{
			$lang=Eleanor::$Language['editor'];
			$ug=GetGroups();

			foreach(OwnBB::$bbs as $bb)
				if($bb['sb'] and !$bb['gr_use'] || count(array_intersect($bb['gr_use'],$ug))>0)
				{
					$tag=reset($bb['tags']);

					$ownbb[ $bb['handler'] ]=[
						't'=>$tag,
						's'=>constant('\CMS\OwnBB\\'.$bb['handler'].'::SINGLE'),
						'l'=>isset($lang[ $bb['handler'] ]) ? $lang[ $bb['handler'] ] : $bb['title'],
					];
				}
		}

		return$Template->Editor($id,$html,$this->smiles && !isset($settings['nosmiles']) ? static::GetSmiles() : [],$ownbb,$this->type);
	}

	/** Получение содержимого редактора: преобразование смайлов в их текстовое представление, конвертирование HTML в BB
	 * (для bb редактора), удаление автоматически прописанных альтов у картинок
	 * @param string $text Редактируемый HTML текст
	 * @return string Содержимое необходимо использовать непосредственно как значение для редактора */
	public function GetEdit($text)
	{
		if($this->ownbb)
		{
			OwnBB::$opts['visual']=$this->visual;
			$text=OwnBB::Parse($text,OwnBB::EDIT);
			$text=OwnBB::StoreNotParsed($text,OwnBB::SAVE);
		}

		$text=preg_replace('#(<[^>]+href=")go\.php\?([a-z]{3,7}://[^>]*>)#','\1\2',$text);
		$text=preg_replace('#<img class="smile" alt="([^"]+)"[^>]*>#i','\1',$text);

		if(static::$alt)
		{
			$imgalt=htmlspecialchars(static::$alt,ENT,\Eleanor\CHARSET,false);
			$text=str_replace([' alt="'.$imgalt.'" title="'.$imgalt.'"',' alt="'.$imgalt.'"'],'',$text);
		}

		if($this->type=='bb')
			$text=\Eleanor\Classes\BBCode::HTML2BB($text);

		if($this->ownbb)
			$text=OwnBB::ParseNotParsed($text,false);

		return$text;
	}
}

Editor::$preview=(Eleanor::$service=='index' ? '' : Eleanor::$services[Eleanor::$service]['file']).Editor::$preview;