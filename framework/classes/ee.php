<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Системное исключение EleanorException */
class EE extends \Exception
{
	public
		/** @var string|int Код исключения */
		$code=0,

		/** @var array Дополнительные параметры исключения */
		$extra=[];

	const
		/** Размер лог файла, после которого он будет сжат */
		SIZE_TO_COMPRESS=2097152,#2 Mb

		/** Ошибка пользователя, выполнение некорректных действий: ошибка доступа (403, 404 ...), некорректно заполнена
		 * форма и т.п. */
		USER=1,

		/** Ошибки разработчика: обращение к неинициализированной переменной, свойству, методу */
		DEV=2,

		/** Ошибки среды: когда нет доступа для чтения/записи в файл, нет самого файла и т.п. */
		ENV=4,

		/** Ошибка внутри подпрограммы: передача внешним сервисом некорректной информации и т.п. */
		UNIT=8;

	/** Конструктор системных исключений
	 * @param string|array $mess Описание исключения, в случае array - [param in default file], [pathtofile, param]
	 * @param int $code Код исключения
	 * @param array $extra Дополнительные данные исключения
	 *  [string file] Имя файла
	 *  [int line] Строка с исключением
	 *  [string input] Входящие данные, которые вызвали исключение
	 *  [array context] Дам всех переменных в области видимости
	 * @param \exception $PO Предыдущее перехваченное исключение, что послужило "родителем" для текущего */
	public function __construct($mess,$code=self::USER,array$extra=[],$PO=null)
	{
		if(isset($PO))
			$extra+=isset($PO->extra) ? $PO->extra : [ 'file'=>$PO->getFile(), 'line'=>$PO->getLine() ];


		if(is_array($mess))
		{
			$ownlang=isset($mess[0],$mess[1]);
			$param=$ownlang ? $mess[1] : $mess[0];

			try
			{
				$Lang=new Language($ownlang ? $mess[0] : __DIR__.'/translation/ee-*.php');

				if(isset($Lang[$param]))
					$mess=is_callable($Lang[$param]) ? $Lang[$param]($extra) : $Lang[$param];
			}
			catch(EE$E)
			{
				$mess=$param;
			}
		}

		if(isset($extra['file']))
			$this->file=$extra['file'];

		if(isset($extra['line']))
			$this->line=$extra['line'];

		$this->extra=$extra;

		parent::__construct($mess,$code,$PO);
	}

	/** Преобразование в строку */
	public function __toString()
	{
		return$this->getMessage();
	}

	/** Непосредственная запись в лог файл. Лог ошибок состоит из двух файлов: *.log и *.inc Первый представляет собой
	 * текстовый файл для открытия любым удобным способом. Второй - содержит служебную информацию для группировки
	 * идентичных записей.
	 * @param string $pathfile Путь к файла и его имя без расширения (дописывается методом)
	 * @param string $id Уникальный идентификатор записи
	 * @param callback $F Функция для генерации записи в лог файл. Первым параметром получает данные, которые вернула
	 * в прошлый раз. Должна вернуть массив из двух элементов 0 - служебные данные, которые
	 * при следущем исключении будут переданы ей первым параметром, 1 - запись в лог файл.
	 * @return bool */
	protected function LogWriter($pathfile,$id,$F)
	{
		$dir=dirname($pathfile);

		if(!is_dir($dir))
			Files::MkDir($dir);

		$logpath=$pathfile.'.log';
		$incpath=$pathfile.'.inc';

		$isl=is_file($logpath);
		$isi=is_file($incpath);

		if($isl and !is_writeable($logpath) or !$isl and !is_writeable(dirname($logpath)))
		{
			trigger_error('File '.$logpath.' is write-protected!',E_USER_ERROR);
			return false;
		}

		if($isl and filesize($logpath)>static::SIZE_TO_COMPRESS)
		{
			if(static::CompressFile($logpath,substr_replace($logpath,'_'.date('Y-m-d_H-i-s'),strrpos($logpath,'.'),0)))
			{
				unlink($logpath);

				if($isi)
					unlink($incpath);
			}

			clearstatcache();
		}

		if($isi)
		{
			$inc=file_get_contents($incpath);
			$inc=$inc ? (array)unserialize($inc) : [];
		}
		else
			$inc=[];

		$change=isset($inc[$id]);
		$data=$F($change ? $inc[$id]['d'] : []);

		if(!is_array($data) or !isset($data[0],$data[1]))
			return false;

		list($data,$log)=$data;

		if($change and !isset($inc[$id]['o'],$inc[$id]['l']))
		{
			$change=false;

			unset($inc[$id]);
		}

		if($change)
		{
			$offset=$inc[$id]['o'];
			$length=$inc[$id]['l'];

			unset($inc[$id]);
			$size=$isl ? filesize($logpath) : 0;

			if($size<$offset+$length)
			{
				$change=false;

				foreach($inc as &$v)
					if($size<$v['o']+$v['l'])
						unset($v['o'],$v['l']);
			}
		}

		if($change)
		{
			$fh=fopen($logpath,'rb+');

			if(flock($fh,LOCK_EX))
				$diff=Files::FReplace($fh,$log,$offset,$length);
			else
			{
				fclose($fh);
				return false;
			}

			$length+=$diff;

			foreach($inc as &$v)
				if($v['o']>$offset)
					$v['o']+=$diff;
		}
		else
		{
			$fh=fopen($logpath,'a');

			if(flock($fh,LOCK_EX))
			{
				$size=fstat($fh);
				$offset=$size['size'];
				$length=strlen($log);
				fwrite($fh,$log.PHP_EOL.PHP_EOL);
			}
			else
			{
				fclose($fh);

				return false;
			}
		}

		$inc[$id]=['o'=>$offset,'l'=>$length,'d'=>$data];

		flock($fh,LOCK_UN);
		fclose($fh);

		file_put_contents($incpath,serialize($inc));

		return true;
	}

	/** Команда залогировать исключение. Основной наследуемый метод
	 * @param string|bool $logfile Путь к лог-файлу. Без расширения */
	public function Log($logfile=false)
	{
		if(Eleanor\Framework::$logs)
			$this->LogWriter(
				$logfile ? $logfile : Eleanor\Framework::$logspath.'errors',
				md5($this->line.$this->file.$this->message),
				function($data)
				{
					#Запись в переменные нужна для последующего удобного чтения лог-файла любыми читалками
					$data['n']=isset($data['n']) ? $data['n']+1 : 1;#Happens counter
					$data['p']=Url::$current ? Url::$current : '/';
					$data['d']=date('Y-m-d H:i:s');
					$data['l']=$this->line;
					$data['e']=$this->getMessage();
					$data['i']=isset($this->extra['input']) ? $this->extra['input'] : '';#Input: входящие переменные
					$data['f']=strpos($this->file,\Eleanor\SITEDIR)===0
						? substr($this->file,strlen(\Eleanor\SITEDIR))
						: $this->file;

					if(isset($this->extra['context']))
						$context=array_map([__CLASS__,'FilterContext'],$this->extra['context']);
					else
						$context=false;

					return[$data,
						$data['e'].PHP_EOL
						.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL
						.'URL: '.$data['p'].PHP_EOL
						.'Last happens: '.$data['d'].', total happens: '.$data['n']
						.($data['i'] ? PHP_EOL.'Input: '.(is_scalar($data['i']) ? $data['i'] : serialize($data['i'])) : '')
						.($context ? PHP_EOL.'Context: '.serialize($context) : '')
					];
				}
			);
	}

	/** Очистка контекста исключения, подавление ошибки Serialization of 'Closure' is not allowed
	 * @param array $value Значение массива
	 * @return array */
	public static function FilterContext($value)
	{
		if(is_scalar($value))
			return$value;

		if(is_object($value))
			return($value instanceof\Closure) ? '[Closure]' : '[Object of '.get_class($value).']';

		if(is_array($value))
		{
			$serial=[];

			foreach($value as$k=>$v)
			{
				if(is_array($v))
					$v='[Array:'.count($v).']';
				elseif(!is_scalar($v))
					$v=($v instanceof \Closure) ? '[Closure]' : '[Object of '.get_class($v).']';

				$serial[$k]=$v;
			}

			return$serial;
		}

		return'[Null]';
	}

	/** Создание архива лог файла для экономии места.
	 * @param string $source Путь к сжимаемому файлу
	 * @param string $dest Путь с сжатому файлу (результату)
	 * @return bool */
	public static function CompressFile($source,$dest)
	{
		if(!is_file($source) or file_exists($dest) or !is_writable(dirname($dest)))
			return false;

		$hf=fopen($source,'r');
		$r=false;

		if(function_exists('bzopen') and $hbz=bzopen($dest.'.bz2','w'))
		{
			while(!feof($hf))
				bzwrite($hbz,fread($hf,1024*16));

			bzclose($hbz);
			$r=true;
		}
		elseif(function_exists('gzopen') and $hgz=gzopen($dest.'.gz','w9'))
		{
			while(!feof($hf))
				gzwrite($hgz,fread($hf,1024*64));

			gzclose($hgz);
			$r=true;
		}

		fclose($hf);

		return$r;
	}
}