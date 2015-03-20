<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

defined('Eleanor\Classes\ENT')||define('Eleanor\Classes\ENT',ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED);

/** Библиотека html примитивов */
class Html extends Eleanor\BaseClass
{
	/** Массовы вывод массивов в JSON и JavaScript переменные.
	 * @param array $a Массив со скалярными переменными
	 * @param bool $tags Включение обрамления результата в <script...>...</script>
	 * @param bool|string $json Формата вывода: false - набор переменных, true - JSON, string - var Object={}.
	 * @param string $p Префикс переменных
	 * @return string */
	public static function JSON($a,$tags=false,$json=true,$p='var ')
	{
		if($json && array_keys($a)===range(0,count($a)-1))
		{
			$r='[';
			$e=$p=$s='';
		}
		elseif($json)
		{
			$r=$json===true ? '{' : $p.$json.'={';
			$p='"';
			$s='":';
			$e=',';
		}
		else
		{
			$r='';
			$s='=';
			$e=';';
		}

		foreach($a as $k=>$v)
		{
			if(is_array($v))
				$v=$v ? static::JSON($v) : '[]';
			elseif(is_bool($v))
				$v=$v ? 'true' : 'false';
			elseif($v===null)
				$v='null';
			elseif(substr($k,0,1)=='!')
				$k=substr($k,1);
			else
				$v=is_int($v) || is_float($v) || ($v instanceof StringCallback) ? (string)$v : '"'.addcslashes($v,"\n\r\t\"\\").'"';

			if($e)
				$r.=$p.$k.$s.$v.$e;
			else
				$r.=$v.',';
		}

		if($json)
		{
			$r=rtrim($r,',').($e ? '}' : ']');

			if($json===true)
				return$r;

			$r.=';';
		}

		return$tags ? "<script>/*<![CDATA[*/{$r}//]]></script>" : $r;
	}

	/** Преобразование ассоциативного массива в параметры тега
	 * @param array $a Ассоциативный массив с параметрами название параметра=>значение параметра
	 * @return string */
	public static function TagParams(array$a)
	{
		$params='';

		foreach($a as $k=>$v)
			if($v!==null and $v!==false)
				if(is_int($k))
					$params.=' '.$v;
				else
				{
					$params.=' '.$k;
					if($v!==true)
						$params.='="'.str_replace('"','&quot;',(string)$v).'"';
				}

		return$params;
	}

	/** Обработка строки для безопасного её использования в качестве значения параметра тега
	 * @param string $s Данные
	 * @param int $mode Режим работы:
	 *  0 Текст прогоняется через htmlspecialchars, таким образом мы правим строку в таком виде, в каком мы ее получили
	 *  1 Текст прогняется сначала через htmlspecialchars_decode, а потом через htmlspecialchars. Таким образом, мы 
	 *    правим HTML в таком виде, в котором его видит пользователь. Циферные задания символов как &#93; выводятся
	 *    символами.
	 *  2 В тексте заменяются только < и > на &lt; и &gt; соответственно.
	 *  3 Править HTML в таком виде, в котором его видит пользователь.
	 * @param string $ch Кодировка
	 * @return string */
	public static function ParamValue($s,$mode=1,$ch=Eleanor\CHARSET)
	{
		if($mode==1)
			$s=htmlspecialchars_decode($s,ENT);

		if($mode==2)
			return str_replace(['<','>'],['&lt;','&gt;'],$s);

		if($s2=htmlspecialchars($s,ENT,$ch,$mode<3) or !$ch)
			return$s2;

		#Заплатка глюка, когда на UTF версии мы пытаемся открыть 1251 Файл.
		return static::ParamValue($s,$mode,null);
	}

	/** Генерация <input type="radio" />
	 * @param string|false $name Имя
	 * @param string|int $value Значение
	 * @param bool $checked Флаг отмеченности
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Radio($name,$value=1,$checked=false,array$extra=[],$mode=1)
	{
		return'<input'.static::TagParams($extra+[
			'type'=>'radio',
			'value'=>$value ? static::ParamValue($value,(int)$mode) : $value,
			'name'=>$name,
			'checked'=>(bool)$checked
		]).' />';
	}

	/** Генерация <textarea>
	 * @param string|false $name Имя
	 * @param string $value Значение
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Text($name,$value='',array$extra=[],$mode=1)
	{
		return'<textarea'.static::TagParams($extra+['name'=>$name]).'>'
			.static::ParamValue($value,(int)$mode).'</textarea>';
	}

	/** Генерация <input type="checkbox" />. Из-за особенностей работы этого элемента формы, метод не содержит
	 * отдельного аргумента для передачи значения, поскольку 99% чекбоксам не важно какое у них значение - важно чтобы
	 * они передались на сервер. Поэтому значение чекбокса можно установить через массив $a.
	 * @param string $name Имя
	 * @param bool $checked Флаг отмеченности
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @return string */
	public static function Check($name,$checked=false,array$extra=[])
	{
		return'<input'.static::TagParams($extra+['type'=>'checkbox','value'=>1,'name'=>$name,'checked'=>(bool)$checked])
			.' />';
	}

	/** Генерация <input> type по умолчанию равно text
	 * @param string $name Имя
	 * @param string|int|null $value Значение
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Input($name,$value=null,array$extra=[],$mode=1)
	{
		return'<input'.static::TagParams($extra+[
			'value'=>$value ? static::ParamValue($value,(int)$mode) : $value,
			'type'=>'text',
			'name'=>$name,
		]).' />';
	}

	/** Генерация кнопок на основе <input>
	 * @param string $value Надпись на кнопке (значение)
	 * @param string $type Тип кнопки: submit, button, reset
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Button($value='OK',$type='submit',array$extra=[],$mode=1)
	{
		return static::Input(false,$value,$extra+['type'=>$type],$mode);
	}

	/** Генерация <option> для Select
	 * @param string $view Выводимое значение
	 * @param string|null $value Значение
	 * @param bool $checked Флаг отмеченности
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Option($view,$value=null,$checked=false,array$extra=[],$mode=1)
	{
		return'<option'.static::TagParams($extra+[
			'value'=>$value ? static::ParamValue($value,(int)$mode) : $value,
			'selected'=>(bool)$checked
		]).'>'.static::ParamValue($view,(int)$mode).'</option>';
	}

	/** Генерация <optgroup> для Select
	 * @param string $label Название группы
	 * @param string $options Перечень option-ов
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @param int $mode Метод вывода значения, подробности описаны в методе ParamValue
	 * @return string */
	public static function Optgroup($label,$options,array$extra=[],$mode=2)
	{
		return'<optgroup'.static::TagParams($extra+['label'=>$label ? static::ParamValue($label,$mode) : $label])
			.'>'.$options.'</optgroup>';
	}

	/** Генерация <select> с одиночным выбором
	 * @param string $name Название select-а
	 * @param string $options Перечень option-ов
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @return string */
	public static function Select($name,$options='',array$extra=[])
	{
		if(!$options)
		{
			$options=self::Option('');
			$extra['disabled']=true;
		}

		return'<select'.static::TagParams($extra+['name'=>$name]).'>'.$options.'</select>';
	}

	/** Генерация <select> с множественным выбором
	 * @param string $name Название select-а
	 * @param string $options Перечень option-ов
	 * @param array $extra Ассоциативных массив дополнительных параметров
	 * @return string */
	public static function Items($name,$options='',array$extra=[])
	{
		return static::Select(substr($name,-2)=='[]' ? $name : $name.'[]',$options,$extra+['size'=>5,'multiple'=>true]);
	}
} 