<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Controls;
use CMS\Eleanor, CMS\Controls_Manager, CMS\Controls, Eleanor\Classes\EE, Eleanor\Classes\Html;

/** Multiple select на чекбоксах */
class Checks extends \Eleanor\BaseClass implements \CMS\Interfaces\Control
{
	/** Получение настроек контрола
	 * @param Controls_Manager $CM
	 * @return array */
	public static function Settings($CM)
	{
		return$CM->GetSettings('items');
	}

	/** Вывод контрола пользователю в форму
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @param array $other
	 * @throws EE
	 * @return mixed */
	public static function Control($control,$Controls,$other=[])
	{
		$options=$control['options'];
		$options+=['extra'=>[],'options'=>[],'callback'=>'','eval'=>'','type'=>null/*options|callback|eval*/];
		$value=$control['post'] ? (array)$Controls->GetPostVal($control['name'],$control['value']) : (array)$control['value'];

		if(!is_array($options['extra']))
			$options['extra']=[];

		if(!is_array($options['options']))
			$options['options']=[];

		if(is_callable($options['callback']) and (!isset($options['type']) or $options['type']=='callback'))
			$options['options']=call_user_func($options['callback'],['value'=>$value]+$control,$Controls);
		elseif($options['eval'] and (!isset($options['type']) or $options['type']=='eval'))
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls', $options['eval']);

			if($f===false)
			{
				ob_end_clean();

				$error=error_get_last();
				throw new EE('Error #'.$error['type'].' in options eval: '.$error['message'],EE::DEV,$error);
			}

			$options['options']=$f(['value'=>$value]+$control,$Controls,$other);
			ob_end_clean();
		}

		if(!is_array($options['options']))
			throw new EE('Incorrect options',EE::DEV);

		$html=[];

		foreach($options['options'] as $k=>&$v)
			$html[$k]=[Html::Check($control['controlname'].'[]',in_array($k,$value),['value'=>$k]+$options['extra']),$v];

		return Eleanor::$Template->ControlChecks($html,null);
	}

	/** Сохранение контрола (получение данных их контрола после сабмита пользователем формы)
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @return mixed */
	public static function Save($control,$Controls)
	{
		$control+=['default'=>[]];
		$res=$Controls->GetPostVal($control['name'],$control['default']);

		if(!is_array($res))
			$res=[];

		return$res;
	}

	/** Вывод результата (представление в доступной форме данных, которые заданы пользователем в контроле)
	 * @param array $control Опции контрола
	 * @param Controls $Controls
	 * @param array $other Остальные контролы, выводимые группой
	 * @throws EE
	 * @return html */
	public static function Result($control,$Controls,$other)
	{
		$options=$control['options'];
		$options+=['options'=>false,'return-value'=>false,'callback'=>'','eval'=>'',
			'type'=>null/*options|callback|eval*/];

		if($options['return-value'])
			return$control['value'];

		if(is_callable($options['callback']) and (!isset($options['type']) or $options['type']=='callback'))
			$options['options']=call_user_func($options['callback'],['value'=>$control['value']]+$control,$Controls);
		elseif($options['eval'] and (!isset($options['type']) or $options['type']=='eval'))
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls', $options['eval']);

			if($f===false)
			{
				ob_end_clean();

				$error=error_get_last();
				$mess='Error #'.$error['type'].' in options eval: '.$error['message'];

				throw new EE($mess,EE::DEV,$error);
			}

			$options['options']=$f(['value'=>$control['value']]+$control,$Controls,$other);
			ob_end_clean();
		}

		if(!is_array($options['options']))
			return$control['value'];

		$r=[];

		foreach($control['value'] as &$v)
			if(isset($options['options'][$v]))
				$r[]=$options['options'][$v];

		return$r;
	}
}