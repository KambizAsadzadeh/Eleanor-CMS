<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Html;

/** Унификация элементов формы для удобства их массового использования */
class Controls extends \Eleanor\BaseClass
{
	public
		/** @var bool Флаг включения выброса исключений в случае ошибок, только для массовых методов */
		$throw=false,

		/** @var array Сюда помещаются ошибки, когда $throw=false */
		$errors=[],

		/** @var array Непосредственное имя для элементов формы, например ['a','b','c'] => <input name="a[b][c]".../> */
		$name=['controls'],

		/** @var array Источник данных из POST запроса. Если не задано, данные будут браться из $_POST */
		$POST,

		/** @var array Языки, с которыми контролы будут работать. По умолчанию заполняется стандартными языками */
		$langs=[];

	/** @var array Перечень всех доступных контролов */
	protected static $controls;

	/** Обычный, ничем не примечательный конструктор */
	public function __construct()
	{
		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as$k=>$_)
				$this->langs[]=$k;
	}

	/** Массовое получение результатов контролов
	 * @param array $controls Массив входящих контролов, формат имя=>данные
	 * @param array $lctrls Массив языковых параметров контрола, формат имя => язык => данные
	 * @throws EE
	 * @return array */
	public function DisplayResults(array$controls,array$lctrls=[])
	{
		$ret=$this->errors=[];

		foreach($controls as$name=>$control)
		{
			if(!is_array($control) or isset($control['skip']))
				continue;

			$lctrl=isset($lctrls[$name]) ? $lctrls[$name] : [];
			$control['multilang']=isset($control['multilang']) ? $control['multilang'] and $this->langs : false;
			$control['name']=isset($control['name']) ? (array)$control['name'] : [$name];

			if($control['multilang'])
			{
				$loaded=null;

				try
				{
					if(isset($control['load']) and is_callable($control['load']))
						$loaded=call_user_func($control['load'],$lctrl+$control+['value'=>[]],$this);
					elseif(!empty($control['load_eval']))
					{
						ob_start();
						$f=create_function('$control,$Controls',$controls[$name]['load_eval']);

						if($f===false)
						{
							ob_end_clean();
							$error=error_get_last();
							throw new EE('Error #'.$error['type'].' in load eval ('.$name.'): '.$error['message'],EE::DEV,$error);
						}

						$loaded=$f($lctrl+$control+['value'=>[]],$this);
						ob_end_clean();
					}
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
					continue;
				}

				if(is_array($loaded))
					foreach($loaded as$k=>$v)
						$lctrl[$k]=is_array($v) ? $v : [''=>$v];

				unset($control['load_eval'],$control['load'],$control['result_eval'],$control['result']);

				foreach($this->langs as$l)
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv,$l) : $lv;

					$control['name']['lang']=$l ? $l : Language::$main;

					if(isset($controls[$name]['default']) and is_array($controls[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default'],$l);

					try
					{
						if(null===$result=$this->DisplayResult($control,$ret))
							continue;
	
						if(isset($control['default']) and $control['default']===$result)
							continue;
	
						$ret[$name][$l]=$result;
					}
					catch(EE$E)
					{
						if($this->throw)
							throw$E;

						$this->errors[$name][$l]=$E;
					}
				}

				if(!isset($ret[$name]))
					continue;

				try
				{
					if(isset($controls[$name]['result']) and is_callable($controls[$name]['result']))
						$ret[$name]=call_user_func($controls[$name]['result'],['result'=>$ret[$name]]+$control,$this,$ret);
					elseif(!empty($controls[$name]['result_eval']))
					{
						ob_start();
						$f=create_function('$control,$Controls',$controls[$name]['result_eval']);

						if($f===false)
						{
							ob_end_clean();
							$error=error_get_last();
							throw new EE('Error #'.$error['type'].' in result eval ('.$name.'): '.$error['message'],EE::DEV,$error);
						}

						$ret[$name]=$f(['result'=>$ret[$name]]+$control,$this,$ret);
						ob_end_clean();
					}
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}
			}
			else
			{
				if(empty($controls[$name]['multilang']))
					$control=$lctrl+$control;
				else
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv) : $lv;

					if(isset($controls[$name]['default']) and is_array($control[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default']);
				}

				try
				{
					if(null===$result=$this->DisplayResult($control,$ret))
						continue;

					$ret[$name]=$result;
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}
			}
		}

		return$ret;
	}

	/** Массовый вывод контролов на страницу (для изменения и правки)
	 * @param array $controls Массив входящих контролов, формат имя=>данные
	 * @param array $lctrls Массив языковых параметров контрола, формат имя=>параметр=>(язык=>)данные
	 * @throws EE
	 * @return array */
	public function DisplayControls(array$controls,array$lctrls=[])
	{
		$ret=$this->errors=[];

		foreach($controls as$name=>$control)
		{
			if(!is_array($control) or isset($control['skip']))
				continue;

			$lctrl=isset($lctrls[$name]) ? $lctrls[$name] : [];
			$control['multilang']=isset($control['multilang']) ? $control['multilang'] and $this->langs : false;
			$control['name']=isset($control['name']) ? (array)$control['name'] : [$name];

			if($control['multilang'])
			{
				$loaded=null;

				try
				{
					if(isset($control['load']) and is_callable($control['load']))
						$loaded=call_user_func($control['load'],$lctrl+$control+['value'=>[]],$this);
					elseif(!empty($control['load_eval']))
					{
						ob_start();
						$f=create_function('$control,$Controls',$controls[$name]['load_eval']);

						if($f===false)
						{
							ob_end_clean();
							$error=error_get_last();
							throw new EE("Error #{$error['type']} in load eval ({$name}): {$error['message']}",EE::DEV,$error);
						}

						$loaded=$f($lctrl+$control+['value'=>[]],$this);
						ob_end_clean();
					}
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}

				if(is_array($loaded))
					foreach($loaded as$k=>$v)
						$lctrl[$k]=is_array($v) ? $v : [''=>$v];

				unset($control['load_eval'],$control['load']);

				foreach($this->langs as$l)
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv,$l) : $lv;

					$control['name']['lang']=$l ? $l : Language::$main;

					if(isset($controls[$name]['default']) and is_array($controls[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default'],$l);

					try
					{
						if(null===$result=$this->DisplayControl($control,$ret))
							continue;

						$ret[$name][$l]=$result;
					}
					catch(EE$E)
					{
						if($this->throw)
							throw$E;

						$this->errors[$name][$l]=$E;
					}
				}
			}
			else
			{
				if(empty($controls[$name]['multilang']))
					$control=$lctrl+$control;
				else
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv) : $lv;

					if(isset($controls[$name]['default']) and is_array($control[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default']);
				}

				try
				{
					if(null===$result=$this->DisplayControl($control,$ret))
						continue;

					$ret[$name]=$result;
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}
			}
		}

		return$ret;
	}

	/** Массовое сохранение контролов (после сабмита контролов на странице)
	 * @param array $controls Массив входящих контролов, формат имя=>данные
	 * @param array $lctrls Массив языковых параметров контрола, формат имя=>язык=>данные
	 * @throws EE
	 * @return array */
	public function SaveControls(array$controls,array$lctrls=[])
	{
		$ret=[];

		foreach($controls as$name=>$control)
		{
			if(!is_array($control) or isset($control['skip']))
				continue;

			$lctrl=isset($lctrls[$name]) ? $lctrls[$name] : [];
			$control['multilang']=isset($control['multilang']) ? $control['multilang'] and $this->langs : false;
			$control['name']=isset($control['name']) ? (array)$control['name'] : [$name];

			if($control['multilang'])
			{
				unset($control['save_eval'],$control['save']);

				foreach($this->langs as$l)
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv,$l) : $lv;

					$control['name']['lang']=$l ? $l : Language::$main;

					if(isset($controls[$name]['default']) and is_array($controls[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default'],$l);

					try
					{
						if(null===$result=$this->SaveControl($control,$ret))
							continue;

						$ret[$name][$l]=$result;
					}
					catch(EE$E)
					{
						if($this->throw)
							throw$E;

						$this->errors[$name][$l]=$E;
					}
				}

				if(!isset($ret[$name]))
					continue;

				try
				{
					if(isset($controls[$name]['save']) and is_callable($controls[$name]['save']))
						$ret[$name]=call_user_func($controls[$name]['save'],['value'=>$ret[$name]]+$control,$this,$ret);
					elseif(!empty($controls[$name]['save_eval']))
					{
						ob_start();
						$f=create_function('$control,$Controls,$saved',$controls[$name]['save_eval']);

						if($f===false)
						{
							ob_end_clean();
							$error=error_get_last();
							throw new EE('Error #'.$error['type'].' in save eval ('.$name.'): '.$error['message'],EE::DEV,$error);
						}

						$ret[$name]=$f(['value'=>$ret[$name]]+$control,$this,$ret);
						ob_end_clean();
					}
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}
			}
			else
			{
				if(empty($controls[$name]['multilang']))
					$control=$lctrl+$control;
				else
				{
					foreach($lctrl as$lk=>$lv)
						$control[$lk]=is_array($lv) ? FilterLangValues($lv) : $lv;

					if(isset($controls[$name]['default']) and is_array($control[$name]['default']))
						$control['default']=FilterLangValues($controls[$name]['default']);
				}

				try
				{
					if(null===$result=$this->SaveControl($control,$ret))
						continue;

					$ret[$name]=$result;
				}
				catch(EE$E)
				{
					if($this->throw)
						throw$E;

					$this->errors[$name]=$E->getMessage();
				}
			}
		}
		return$ret;
	}

	/** Вывод контрола на странице в форме
	 * @param array $control Данные контрола. Подробности в теле метода.
	 * @param array $other Предыдущие обработанные контролы, передается когда этот метод вызывается из DisplayControls
	 * @throws EE
	 * @return string */
	public function DisplayControl(array$control,array$other=[])
	{
		#Добавление недостающих ключей:
		$control+=[
			'type'=>'input',#Тип контрола

			/* 2 ключа, отвечающих за обработку данных при загрузке контрола. Пример:
			function($control,$Controls ($this),$controls ($other))
			{
				return['value'=>$control['value']+1];
			}
			Эти же переменные получает и load_eval*/
			'load'=>null,
			'load_eval'=>null,

			'default'=>null,#Значение по умолчанию
			'value'=>null,#Значение, полученное из БД. Имеет приоритет над default
			'name'=>'noname',#Имя контрола

			'options'=>[],#Дополнительные настройки. Для типов select, items этот массив содержит значения пунктов
			'prepend'=>'',#Содержимое ПЕРЕД контролом
			'append'=>'',#Содержимое ПОСЛЕ контрола
		];

		#Загрузка из POST запроса
		if(!isset($control['post']))
			$control['post']=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;

		$options=(array)$control['options'];
		$control['form-name']=$this->FormName($control['name']);

		if(!isset($control['value']))
			$control['value']=$control['default'];

		$items=false;

		if(is_callable($control['load']))
			$loaded=call_user_func($control['load'],$control,$this,$other);
		elseif($control['load_eval'])
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls',$control['load_eval']);

			if($f===false)
			{
				ob_end_clean();
				$error=error_get_last();
				throw new EE('Error #'.$error['type'].' in load eval: '.$error['message'],EE::DEV,$error);
			}

			$loaded=$f($control,$this,$other);
			ob_end_clean();
		}

		if(isset($loaded))
			$control=$loaded+$control;

		switch($control['type'])
		{
			case'user':
			case'':#Alias
				$options+=['load'=>null,'load_eval'=>null];

				if(is_callable($options['load']))
					$html=call_user_func($options['load'],$control,$this,$other);
				elseif($options['load_eval'])
				{
					ob_start();
					$f=create_function('$control,$Controls,$controls', $options['load_eval']);

					if($f===false)
					{
						ob_end_clean();
						$error=error_get_last();
						throw new EE('Error #'.$error['type'].' in load user eval: '.$error['message'],EE::DEV,$error);
					}

					$html=$f($control,$this,$other);
					ob_end_clean();
				}
				elseif(array_key_exists('content', $options))
					$html=$options['content'];
				else
					throw new EE('Incorrect callback',EE::DEV);
			break;
			case'editor':
				if($control['post'])
					$control['value']=$this->GetPostVal($control['name'],$control['value']);

				/** @var Editor $E */
				$E=new Editor(isset($options['type']) ? $options['type'] : null);

				foreach($options as$k=>$v)
					if($k=='type')
						continue;
					elseif(property_exists($E,$k))
						$E->$k=$v;

				$html=$E->Area($control['form-name'],$control['value'],isset($options['extra']) ? $options['extra'] : [],
					['post'=>$control['post']]);
			break;
			case'input':
			case'text':
				$options+=['extra'=>[],'safe'=>false];

				#Заплатка для формы редактирования контрола, поддержка поля Input type
				if(isset($options['type']) and !isset($options['extra']['type']))
					$options['extra']['type']=$options['type'];

				if($control['post'])
					$control['value']=$this->GetPostVal($control['name'],$control['value']);

				if(is_array($control['value']))
					$control['value']=join(',',$control['value']);

				$html=Html::$control['type']($control['form-name'],$control['value'],$options['extra'],$options['safe']);
			break;
			case'items':
				$value=$control['post'] ? (array)$this->GetPostVal($control['name'],[]) : (array)$control['value'];
				$items=true;
			case'select':
				$options+=['extra'=>[],'strict'=>false,'options'=>[],'callback'=>'','eval'=>'',
					'type'=>null/*options|callback|eval*/];

				if(!is_array($options['extra']))
					$options['extra']=[];

				if(!is_array($options['options']))
					$options['options']=[];

				if(!isset($value))
					$value=$control['post'] ? $this->GetPostVal($control['name'],$control['value']) : $control['value'];

				$value=(array)$value;

				if(is_callable($options['callback']) and (!isset($options['type']) or $options['type']=='callback'))
					$options['options']=call_user_func($options['callback'],['value'=>$value]+$control,$this);
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

					$options['options']=$f(['value'=>$value]+$control,$this,$other);
					ob_end_clean();
				}

				$html='';
				if(is_array($options['options']))
					foreach($options['options'] as$k=>$v)
					{
						if(is_array($v))
						{
							$n=isset($v['title']) ? $v['title'] : '';

							if(isset($v['name']))
								$k=$v['name'];

							$extra=isset($v['extra']) ? $v['extra'] : [];
							$safe=isset($v['safe']) ? $v['safe'] : 0;
						}
						else
						{
							$n=$v;
							$extra=[];
							$safe=0;
						}
						$html.=Html::Option($n,$k,in_array($k,$value,$options['strict']),$extra,$safe);
					}
				else
					$html=$options['options'];

				if($items)
					$html=Html::Items($control['form-name'],$html,$options['extra']);
				else
					$html=Html::Select($control['form-name'],$html,$options['extra']);

				unset($value);
			break;
			case'check':
				$options+=['extra'=>[]];
				$html=Html::Check($control['form-name'],
					$control['post'] ? $this->GetPostVal($control['name'],false) : $control['value'],$options['extra']);
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();

				class_exists('\CMS\Controls\\'.$control['type'],true);

				/** @var \CMS\Interfaces\Control $cl */
				$cl='\CMS\Controls\\'.$control['type'];
				$html=$cl::Control($control,$this,$other);
		}

		return is_string($html) ? $control['prepend'].$html.$control['append'] : $html;
	}

	/** Сохранение контрола (после сабмита формы)
	 * @param array $control Данные контрола. Подробности в теле метода.
	 * @param array $other Предыдущие обработанные контролы, передается когда этот метод вызывается из DisplayControls
	 * @throws EE
	 * @return mixed */
	public function SaveControl(array$control,array$other=[])
	{
		#Добавить недостающие ключи
		$control+=[
			'type'=>'input',#Тип контрола

			/* 2 ключа, отвечающих за обработку данных при сохранении контрола. Пример:
			function($control,$Controls ($this),$controls ($other))
			{
				return$control['value']-1;
			}
			Эти же переменные получает и save_eval*/
			'save'=>null,
			'save_eval'=>null,

			'default'=>null,#Значение по умолчанию
			'name'=>'noname',#Имя контрола

			'multilang'=>false,#Необходимо для save_eval и save
			'options'=>[],
		];
		$options=(array)$control['options'];

		switch($control['type'])
		{
			case'user':
			case'':#Alias
				$options+=['save'=>null,'save_eval'=>null];

				if(is_callable($options['save']))
					$res=call_user_func($options['save'],$control,$this,$other);
				elseif($options['save_eval'])
				{
					ob_start();
					$f=create_function('$control,$Controls,$controls', $options['save_eval']);

					if($f===false)
					{
						ob_end_clean();
						$error=error_get_last();
						throw new EE('Error #'.$error['type'].' in save user eval: '.$error['message'],EE::DEV,$error);
					}

					$res=$f($control,$this,$other);
					ob_end_clean();
				}
				else
					$res=null;
			break;
			case'editor':
				/** @var Saver $E */
				$E=new Saver(isset($options['type']) ? $options['type'] : null);

				foreach($options as$k=>$v)
					if($k=='type')
						continue;
					elseif(property_exists($E,$k))
						$E->$k=$v;

				$res=$E->Save($this->GetPostVal($control['name'],$control['default']));
			break;
			case'check':
				$control+=['default'=>false];
				$res=(bool)$this->GetPostVal($control['name']);
			break;
			case'text':
			case'select':
			case'input':
				$options+=['safe'=>false];
				$res=$this->GetPostVal($control['name'],$control['default']);

				if($options['safe'])
					$res=GlobalsWrapper::Filter($res);
			break;
			case'items':
				$control+=['default'=>[]];
				$res=$this->GetPostVal($control['name'],[]);

				if(!is_array($res))
					$res=[];
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();

				class_exists('\CMS\Controls\\'.$control['type'],true);

				/** @var \CMS\Interfaces\Control $cl */
				$cl='\CMS\Controls\\'.$control['type'];
				$res=$cl::Save($control,$this,$other);
		}

		if($res===$control['default'])
			return$res;

		$control['value']=$res;

		if(is_callable($control['save']))
			$res=call_user_func($control['save'],$control,$this,$other);
		elseif($control['save_eval'])
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls',$control['save_eval']);

			if($f===false)
			{
				ob_end_clean();
				$error=error_get_last();
				throw new EE('Error #'.$error['type'].' in save eval: '.$error['message'],EE::DEV,$error);
			}

			$res=$f($control,$this,$other);
			ob_end_clean();
		}

		return$res;
	}

	/** Получение результата контрола, для вывода непосредственно результата на страницу
	 * @param array $control Данные контрола. Подробности в теле метода.
	 * @param array $other Предыдущие обработанные контролы, передается когда этот метод вызывается из DisplayControls
	 * @throws EE
	 * @return mixed */
	public function DisplayResult(array$control,array$other=[])
	{
		#Добавить недостающие ключи
		$control+=[
			'type'=>'input',#Тип контрола

			/* 2 ключа, отвечающих за обработку данных при загрузке контрола. Пример:
			function($control,$Controls ($this),$controls ($other))
			{
				return['value'=>$control['value']+1];
			}
			Эти же переменные получает и load_eval*/
			'load'=>null,
			'load_eval'=>null,

			/* 2 ключа, отвечающих за обработку данных перед выводом контрола. Пример:
			function($control,$Controls ($this),$controls ($other))
			{
				return$control['value'].' лет';
			}
			Эти же переменные получает и load_eval*/
			'result'=>null,
			'result_eval'=>null,

			'multilang'=>false,#Необходимо для save_eval и save
			'options'=>[],
			'default'=>null,
		];

		$options=(array)$control['options'];

		if(!isset($control['value']))
			$control['value']=$control['default'];

		if(is_callable($control['load']))
			$loaded=call_user_func($control['load'],$control,$this,$other);
		elseif($control['load_eval'])
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls',$control['load_eval']);

			if($f===false)
			{
				ob_end_clean();
				$error=error_get_last();
				throw new EE('Error #'.$error['type'].' in load eval: '.$error['message'],EE::DEV,$error);
			}

			$loaded=$f($control,$this,$other);
			ob_end_clean();
		}

		if(isset($loaded))
			$control=$loaded+$control;

		switch($control['type'])
		{
			case'user':
				$options+=['result'=>null,'result_eval'=>null];

				if(is_callable($options['result']))
					$res=call_user_func($options['save'],$control,$this,$other);
				elseif($options['result_eval'])
				{
					ob_start();
					$f=create_function('$control,$Controls,$controls', $options['result_eval']);

					if($f===false)
					{
						ob_end_clean();
						$error=error_get_last();
						throw new EE('Error #'.$error['type'].' in save user eval: '.$error['message'],EE::DEV,$error);
					}

					$res=$f($control,$this,$other);
					ob_end_clean();
				}
				else
					$res=$control['value'];
			break;
			case'editor':
				$options+=['ownbb'=>true];
				$res=$options['ownbb'] ? OwnBB::Parse($control['value']) : $control['value'];
			break;
			case'check':
				$res=(bool)$control['value'];
			break;
			case'text':
			case'input':
				$res=$control['value'];
			break;
			case'select':
			case'items':
				$options+=['options'=>false,'return-value'=>false,'callback'=>'','eval'=>'',
					'type'=>null/*options|callback|eval*/];

				if($options['return-value'])
				{
					$res=$control['value'];
					break;
				}

				if(is_callable($options['callback']) and (!isset($options['type']) or $options['type']=='callback'))
					$options['options']=call_user_func($options['callback'],['value'=>$control['value']]+$control,$this);
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

					$options['options']=$f(['value'=>$control['value']]+$control,$this,$other);
					ob_end_clean();
				}

				if(!is_array($options['options']))
				{
					$res=$control['value'];
					break;
				}

				if(is_array($control['value']))
				{
					$res=[];
					foreach($control['value'] as &$v)
						if(isset($options['options'][$v]))
							$res[]=$options['options'][$v];
				}
				else
					$res=isset($options['options'][ $control['value'] ]) ? $options['options'][ $control['value'] ] : null;
			break;
			default:
				if(!isset(self::$controls))
					self::ScanControls();

				class_exists('\CMS\Controls\\'.$control['type'],true);

				/** @var \CMS\Interfaces\Control $cl */
				$cl='\CMS\Controls\\'.$control['type'];
				$res=$cl::Result($control,$this,$other);
		}

		if($res===$control['default'])
			return$res;

		$control['value']=$res;

		if(is_callable($control['result']))
			$res=call_user_func($control['result'],$control,$this,$other);
		elseif($control['result_eval'])
		{
			ob_start();
			$f=create_function('$control,$Controls,$controls',$control['save_eval']);

			if($f===false)
			{
				ob_end_clean();
				$error=error_get_last();
				throw new EE('Error #'.$error['type'].' in save eval: '.$error['message'],EE::DEV,$error);
			}

			$res=$f($control,$this,$other);
			ob_end_clean();
		}

		return$res;
	}

	/** Генерация имени для элемента формы
	 * @param string|array $n Уникальное имя элемента формы
	 * @return string Готовое имя для элемента формы, состоящее из $this->name + $n */
	public function FormName($n)
	{
		$name='';

		if($this->name)
		{
			$name=reset($this->name);
			$a=array_slice($this->name,1);

			foreach($a as &$v)
				if($v!==false)
					$name.='['.$v.']';
		}

		if(is_array($n))
		{
			if(!$this->name)
			{
				$name=reset($n);
				unset($n[key($n)]);
			}

			foreach($n as &$v)
				if($v)
					$name.='['.$v.']';
		}
		elseif($n!==false)
			$name=$name ? $name.'['.$n.']' : $n;

		return$name;
	}

	/** Получение значения элемента формы из POST запроса
	 * @param string|array $n Уникальное имя элемента формы
	 * @param mixed $def Значение по умолчанию
	 * @return mixed */
	public function GetPostVal($n,$def=null)
	{
		$workarr=array_merge($this->name,(array)$n);
		$n=reset($workarr);

		if($pv=isset($this->POST) and !isset($this->POST[$n]) or !$pv and !isset($_POST[$n]))
			return$def;

		$post=isset($this->POST) ? $this->POST[$n] : $_POST[$n];
		array_splice($workarr,0,1);

		foreach($workarr as$v)
		{
			if($v===false)
				continue;

			if(!isset($post[$v]))
				return$def;

			$post=$post[$v];
		}

		return$post;
	}

	/** Получение всех внешних специальных контролов */
	protected static function ScanControls()
	{
		static::$controls=[];

		$controls=glob(DIR.'controls/*.php');

		if($controls)
			foreach($controls as$control)
				self::$controls[]=substr(basename($control),0,-4);
	}
}