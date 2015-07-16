<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use Eleanor\Classes\Html;

defined('CMS\STARTED')||die;
/** Элемент шаблона. Интерфейс выбора автора материала. Обязательно должен быть размещен внутри
 * <div class="form-group has-feedback author">...</div> !
 * @var string|array $var_0 Значение поля: имя автора и [имя автора] (пользователя нет), [имя автора, id, ссылка на автора]
 * @var array $var_1 extra поля выбора автора. Внимание! Генерируется 2 <input>. ID пользователя хранится в data-id.
 * По умолчанию name="author" Имя <input>-a с ID образуется путем добавления окончания _id name="author_id" */

$value=isset($var_0) ? (array)$var_0 : [];
$extra=isset($var_1) ? (array)$var_1 : [];

$GLOBALS['scripts'][]='//cdn.rawgit.com/bassjobsen/Bootstrap-3-Typeahead/master/bootstrap3-typeahead.min.js';
$GLOBALS['head']['author']=<<<'HTML'
<script>$(function(){
	var cache={};
	$(".author").on("clone",function(){
		var div=$(this),
			id=$("input[type=hidden]",this),
			name=$("input:text",this),
			a=$("a",this);

		if(id.val() && name.val() && a.prop("href")!="#")
		{
			div.addClass("has-success");
			a.css("pointer-events","auto").removeClass("text-muted");
		}

		var input=$('input:text',this),
			new_int=input.clone().insertAfter(input);

		input.remove();

		$('input:text',this).typeahead({
			source:function(query,cb){
				if(query in cache)
					cb(cache[query]);
				else
					$.post(location.href,{"do":"author-autocomplete",query:query},function(json){
						cache[query]=json;
						cb(json);
					},"json");
			},
			afterSelect:function(item){
				id.val(item.id);
				div.addClass("has-success");
				a.css("pointer-events","auto").attr("href",item._a.replace(/&amp;/g,'&')).removeClass("text-muted");
			},
			showHintOnFocus: true
		}).on("input",function(){
			div.removeClass("has-success");
			id.val("");
			a.css("pointer-events","").addClass("text-muted");
		});
	}).on("clean",function(){
		$(this).removeClass("has-success");
		$("input[type=hidden]",this).val("");
		$("a",this).css("pointer-events","").addClass("text-muted");
	}).trigger("clone");
})</script>
HTML;

$value+=['',''];
$name=isset($extra['name']) ? (string)$extra['name'] : 'author';

if(substr($name,-3)=='][]')
	$name_id=substr_replace($name,'_id',-3,0);
else
	$name_id=substr($name,-1)==']' ? substr_replace($name,'_id',-1,0) : $name.'_id';

if(!isset($value[2]))
	$value[2]=$value[0] && $value[1] ? \CMS\UserLink($value[1],$value[0],'admin') : '#';

if(!isset($extra['class']))
	$extra['class']='form-control';

if(!isset($extra['autocomplete']))
	$extra['autocomplete']='off';

return Html::Input($name_id,$value[1],['type'=>'hidden']).Html::Input($name,$value[0],$extra)
	.<<<HTML
<a href="{$value[2]}" class="glyphicon glyphicon-user form-control-feedback text-muted" aria-hidden="true" target="_blank"></a>
HTML;
