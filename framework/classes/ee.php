<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Системное исключение EleanorException
*/
namespace Eleanor\Classes;
use Eleanor;

class EE extends \Exception
{
	const
		/** Размер лог файла, после которого он будет сжат */
		SIZE_TO_COMPRESS=2097152;#2 Mb

	public
		/** @property string|int $code Код исключения */
		$code=0,

		/** @property array $extra Дополнительные параметры исключения */
		$extra=[];

	const
		USER=1,#Ошибка пользователя, выполнение некорректных действий: ошибка доступа (403, 404 ...), некорректно заполнена форма и т.п.
		DEV=2,#Ошибки разработчика: обращение к неинициализированной переменной, свойству, методу
		ENV=4,#Ошибки среды: когда нет доступа для чтения/записи в файл, нет самого файла и т.п.
		UNIT=8;#Ошибка внутри подпрограммы: передача внешним сервисом некорректной информации и т.п.

	/**
	 * Конструктор системных исключений
	 * @param string|array $mess Описание исключения, в случае array - [param in default file], [pathtofile, param]
	 * @param int $code Код исключения
	 * @param array $extra Дополнительные данные исключения
	 *   string file Имя файла
	 *   int line Строка с исключением
	 *   string input Входящие данные, которые вызвали исключение
	 *   array context Дам всех переменных в области видимости
	 * @param \exception $PO Предыдущее перехваченное исключение, что послужило "родителем" для текущего
	 */
	public function __construct($mess,$code=self::USER,array$extra=[],$PO=null)
	{
		if(isset($PO,$PO->extra))
			$extra+=$PO->extra;

		if(is_array($mess))
		{
			$ownlf=isset($mess[0],$mess[1]);
			$param=$ownlf ? $mess[1] : $mess[0];

			try
			{
				$Lang=new Language($ownlf ? $mess[0] : __DIR__.'/language/ee-*.php');

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

	public function __toString()
	{
		return$this->getMessage();
	}

	/**
	 * Непосредственная запись в лог файл. Лог ошибок состоит из двух файлов: *.log и *.inc Первый представляет собой
	 * текстовый файл для открытия любым удобным способом. Второй - содержит служебную информацию для группировки
	 * идентичных записей.
	 * @param string $pathfile Путь к файла и его имя без расширения (дописывается методом)
	 * @param string $id Уникальный идентификатор записи
	 * @param callback $F Функция для генерации записи в лог файл. Первым параметром получает данные, которые вернула
	 * в прошлый раз. Должна вернуть массив из двух элементов 0 - служебные данные, которые
	 * при следущем исключении будут переданы ей первым параметром, 1 - запись в лог файл.
	 * @return bool
	 */
	protected function LogWriter($pathfile,$id,$F)
	{
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

		#Redundance for PhpStorm: удалить следующую строку
		$offset=$length=false;

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

	/**
	 * Команда залогировать исключение. Основной наследуемый метод
	 * @param string|bool $logfile Путь к лог-файлу. Без расширения.
	 */
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
					$data['p']=Url::$curpage ? Url::$curpage : '/';
					$data['d']=date('Y-m-d H:i:s');
					$data['l']=$this->line;
					$data['e']=$this->getMessage();
					$data['i']=isset($this->extra['input']) ? $this->extra['input'] : '';#Input: входящие переменные
					$data['f']=strpos($this->file,\Eleanor\SITEDIR)===0
						? substr($this->file,strlen(\Eleanor\SITEDIR))
						: $this->file;

					return[$data,
						$data['e'].PHP_EOL
						.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL
						.'URL: '.$data['p'].PHP_EOL
						.'Last happens: '.$data['d'].', total happens: '.$data['n'].PHP_EOL
						.($data['i'] ? 'Input: '.(is_scalar($data['i']) ? $data['i'] : serialize($data['i'])).PHP_EOL : '')
						.(isset($this->extra['context']) ? 'Context: '.serialize($this->extra['context']) : '-')
					];
				}
			);
	}

	/**
	 * Создание архива лог файла для экономии места.
	 * @param string $source Путь к сжимаемому файлу
	 * @param string $dest Путь с сжатому файлу (результату)
	 * @return bool
	 */
	static function CompressFile($source,$dest)
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