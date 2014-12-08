<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Шаблонизатор. Все шаблоны на классах, должны находиться в неймспейсе Eleanor\Templates */
class Template extends Eleanor\Abstracts\AppendString
{
	/** Тип обрабатываемых файлов */
	const EXT='.php';

	public
		/** @var array Переменные, которые будут переданы во все шаблоны по умолчанию (assign)*/
		$default=[],

		/** @var array Очередь на загрузку. Принимаются: пути в каталоги с файлами - для файловых шаблонов,
		 * пути к файлам возвращающих массив (шаблонизатор на массивах), пути к файлам не возвращающим ничего или
		 * возвращающим полное имя класса - шаблоны на классах */
		$queue=[];

	protected
		/** @var array Массив загруженных шаблонов */
		$loaded=[];

	/** @var array Названия свойств, которые при клонирование объектов должны стать ссылками на оригинальны свойства */
	protected static $linking=['default','queue','loaded'];

	/** @param array $queue Очередь на загрузку */
	public function __construct($queue=[])
	{
		$this->queue=(array)$queue;
	}

	/** Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 * @param string $ns Пространство имен
	 * @throws EE
	 * @return string */
	public function _($n,array$p,$ns='Eleanor\Templates\\')
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
							if(isset($p[0]) and count($p)==1 and is_array($p[0]))
								$p=$p[0];

							ob_start();

							$content=Eleanor\AwareInclude($data[ $n ],$p+$this->default);

							if($content==1)
								$content='';

							$eched=ob_get_contents();

							if($eched)
								$content.=$eched;

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
			elseif(is_object($path))
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
				$class=substr($class,0,strpos($class,'.'));
				$nsclass=$ns.$class;

				TryClass:

				if(class_exists($nsclass,false))
				{
					$this->loaded[$k]=['c',$nsclass];
					$result=$Loader('c',$nsclass);
					$tryclass=false;
				}

				if($tryclass)
				{
					ob_start();
					$content=Eleanor\AwareInclude($path,$this->default);
					ob_end_clean();

					if(is_array($content))
					{
						$this->loaded[$k]=['a',$content];
						$result=$Loader('a',$content);
					}
					else
					{
						if(is_string($content) and strcasecmp(ltrim(strrchr($content,'\\'),'\\'),$class)==0)
							$nsclass=$content;#Возможно, просто не угадали с пространством имен

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

		$alldb=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$db=[];

		foreach($alldb as $v)
			if(!isset($v['class']) or $v['class']!=__CLASS__ and !is_subclass_of($v['class'],__CLASS__)
				and !is_subclass_of(__CLASS__,$v['class']))
				break;
			else
				$db=$v;

		throw new EE('Template '.$n.' was not found',EE::DEV,$db);
	}
}