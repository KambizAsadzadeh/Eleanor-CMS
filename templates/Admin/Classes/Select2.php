<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Language, CMS\Eleanor, Eleanor\Classes\Html;

/** Поддержка селектов типа SELECT 2 */
class Select2
{
	/** Инициализатор Select2: прописывание стилей и скриптов */
	public static function Init()
	{
		if(!isset($GLOBALS['head']['select2']))
		{
			$GLOBALS['head']['select2']='<link href="//cdn.jsdelivr.net/select2/4/css/select2.min.css" rel="stylesheet"/>';

			if(Language::$main=='english')
				$GLOBALS['scripts'][]='//cdn.jsdelivr.net/select2/4/js/select2.full.min.js';
			else
				$GLOBALS['scripts'][]='//cdn.jsdelivr.net/g/select2@4(js/select2.full.min.js+js/i18n/'.Eleanor::$langs[ Language::$main ]['d'].'.js)';
		}
	}

	/** Генерация select2 с одиночным выбором
	 * @param string $n Имя select-а
	 * @param string $o Перечень option-ов
	 * @param array $a Ассоциативный массив дополнительных параметров
	 * @param string $js_obj Дополнительные параметры JS
	 * @return string */
	public static function Select($n,$o='',array$a=[],$js_obj='')
	{
		static::Init();

		if(!isset($a['id']))
			$a['id']=uniqid();

		if(Language::$main!='english')
			$a['lang']=Eleanor::$langs[ Language::$main ]['d'];

		if(!isset($a['data-width']))
			$a['data-width']='style';

		return Html::Select($n,$o,$a).<<<HTML
<script>$(function(){ $("#{$a['id']}").select2({$js_obj}); })</script>
HTML;
	}

	/** Генерация select2 с множественным выбором
	 * @param string $n Имя select-а
	 * @param string $o Перечень option-ов
	 * @param array $a Ассоциативный массив дополнительных параметров
	 * @param string $js_obj Дополнительные параметры JS
	 * @return string */
	public static function Items($n,$o='',array$a=[],$js_obj='')
	{
		return static::Select(substr($n,-2)=='[]' ? $n : $n.'[]',$o,$a+['size'=>5,'multiple'=>true],$js_obj);
	}

	/** Генерация специального контрола для ввода тегов
	 * @param string $n Имя
	 * @param string|array $v Перечень тегов: либо массив, либо строка разделенная \t, либо <option>...<option>
	 * @param array $a ассоциативный массив дополнительных параметров
	 * @return string */
	public static function Tags($n,$v,array$a=[],$js_obj='')
	{
		if(is_string($v) and strpos($v,'<option')===0)
			$opts=$v;
		elseif($v)
		{
			if(is_scalar($v))
				$v=explode("\t",$v);

			$opts='';
			foreach($v as $k=>$opt)
			{
				$int=is_int($k);

				$opts.=Html::Option($int ? $opt : $k,false,$int ? true : $opt);
			}
		}
		else
			$opts='<option></option>';

		if($js_obj==='tags')
			$js_obj=<<<'HTML'
, tokenSeparators: ['\t',','],ajax:{url:location.href,cache:"true",processResults:function(pre){
				var data=[];
				$.each(pre,function(i,v){
					data.push("string"===typeof v ? {id:v,text:v} : v);
				});
				return{results: data};
			}}
HTML;
		else
		{
			$js_obj=trim($js_obj,',');

			if($js_obj)
				$js_obj=','.$js_obj;
		}

		return static::Items($n,$opts,$a,"{tags: true{$js_obj}}");
	}
}

return Select2::class;