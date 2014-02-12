<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Шаблонизатор
*/
namespace Eleanor\Classes;
use Eleanor;

class Template extends Eleanor\Abstracts\AppendString
{
	/** Тип обрабатываемых файлов */
	const EXT='.php';

	public
		/** @property array $default Переменные, которые будут переданы во все шаблоны по умолчанию (assign)*/
		$default=[],
		/** @property array $queue Очередь на загрузку. Принимаются: пути в каталоги с файлами - для файловых шаблонов,
		 * пути к файлам возвращающих массив (шаблонизатор на массивах), пути к файлам не возвращающим ничего - шаблоны
		 * на классах */
		$queue=[];

	protected
		/** @property array $loaded Массив загруженных шаблонов */
		$loaded=[];

	/**
	 * Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 * @throws EE
	 * @return string
	 */
	public function _($n,array$p)
	{
		$Loader=function($type,$data)use($n,$p)
		{
			switch($type)
			{
				case'f':#Files Первый параметр - массив имя файла без типа => полный путь к файлу
					if(isset($data[ $n ]))
					{
						try
						{
							ob_start();

							$content=Eleanor\AwareInclude($data[ $n ]);

							if($content==1)
								$content='';

							$content.=ob_get_contents();

							ob_end_clean();

							return$content;
						}
						catch(EE$E)
						{
							throw$E;
						}
						finally
						{
							ob_end_clean();
						}
					}
				break;
				case'c':#Classes
					if(method_exists($data,$n))
						return call_user_func_array([ $data,$n ],$p);

					$c=[$data,$n];

					if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
						return$s;
				break;
				case'a':#Arrays
					if(isset($data[$n]))
						return is_callable($data[$n])
							? call_user_func_array($data[$n],$p)
							: BBCode::ExecLogic($data[$n],$p);
			}

			#Redundance for PhpStorm: убрать следующую строку
			return null;
		};

		#Поиск шаблона среди уже загруженных
		foreach($this->loaded as $source)
			if(null!==$result=$Loader($source[0],$source[1]))
				return$result;

		#Среди загруженных ничего не нашли, жаль, значит будем "шерстить" очередь.
		foreach($this->queue as $k=>$path)
		{
			unset($this->queue[$k]);
			$result=null;

			if(is_array($path))
			{#Шаблонизатор в виде массива
				$this->loaded[$k]=['a',$path];
				$result=$Loader('a',$path);
			}
			if(is_object($path))
			{#Шаблонизатор в виде объекта
				$this->loaded[$k]=['c',$path];
				$result=$Loader('c',$path);
			}
			elseif(is_dir($path))
			{#Нашли каталог, значит перед нами - файловый шаблонизатор
				$files=glob(rtrim($path,'/\\').DIRECTORY_SEPARATOR.'*'.static::EXT);
				$data=[];

				if($files)
					foreach($files as $file)
						$data[ basename($file,static::EXT) ]=$file;

				if($data)
				{
					$this->loaded[$k]=['f',$data];
					$result=$Loader('f',$data);
				}
			}
			elseif(is_file($path))
			{#Шаблонизатор на файле: либо класс, либо массив
				$tryclass=true;
				$class=basename($path);
				$class=__NAMESPACE__.'\\'.substr($class,0,strpos($class,'.'));

				TryClass:

				if(class_exists($class,false))
				{
					$this->loaded[$k]=['c',$class];
					$result=$Loader('c',$class);
					$tryclass=false;
				}

				if($tryclass)
				{
					ob_start();
					$content=Eleanor\AwareInclude($path);
					ob_end_clean();

					if(is_array($content))
					{
						$this->loaded[$k]=['a',$content];
						$result=$Loader('a',$content);
					}
					else
					{
						$tryclass=false;
						goto TryClass;
					}
				}
			}
			elseif(class_exists($path,false))
			{
				$this->loaded[$k]=['c',$path];
				$result=$Loader('c',$path);
			}

			if($result!==null)
				return$result;
		}

		throw new EE('Template '.$n.' was not found',EE::DEV,
			Eleanor\BaseClass::_BT(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1)));
	}
}