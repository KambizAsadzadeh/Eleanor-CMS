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
			$GLOBALS['head']['select2']='<link href="//cdn.jsdelivr.net/select2/3/select2.css" rel="stylesheet"/>
<link href="//cdn.jsdelivr.net/select2/3/select2-bootstrap.css" rel="stylesheet"/>';
			$GLOBALS['scripts'][]='//cdn.jsdelivr.net/select2/3/select2.min.js';

			if(Language::$main!='english')
				$GLOBALS['scripts'][]=T::$http['3rd'].'static/select2/select2_locale_'.Eleanor::$langs[ Language::$main ]['d'].'.js';
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

		return Html::Select($n,$o,$a).<<<HTML
<script>/*<![CDATA[*/$(function(){ $("#{$a['id']}").select2({$js_obj}); });//]]></script>
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
	 * @param string|array $v Перечень тегов: либо массив, либо строка разделенная \t
	 * @param array $a ассоциативный массив дополнительных параметров
	 * @return string */
	public static function Tags($n,$v,array$a=[])
	{
		static::Init();

		if(!isset($a['id']))
			$a['id']=uniqid();

		$a['type']='hidden';

		return Html::Input($n,is_array($v) ? join(',',$v) : $v,$a).<<<HTML
<script>/*<![CDATA[*/$(function(){ $("#{$a['id']}").select2({
	tags: [],
	initSelection : function (element, callback) {
		var data=[];

		$.each(element.val().split(","),function () {
			data.push({id: this, text: this});
		});

		element.val("");
		callback(data);
	}
}); });//]]></script>
HTML;
	}
}

return Select2::class;