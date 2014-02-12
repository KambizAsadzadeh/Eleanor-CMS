<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

class Files extends Eleanor\BaseClass
{
	/**
	 * Преобразования байт в приблизительный читаемый формат
	 * @param int $b Байты (количество)
	 * @return string
	 */
	public static function BytesToSize($b)
	{
		/*
		if($b>1152921504606846976)
			return round($b/1152921504606846976,2).' eb';
		elseif($b>1125899906842624)
			return round($b/1125899906842624,2).' pb';
		elseif($b>1099511627776)
			return round($b/1099511627776,2).' tb';
		else*/if($b>=1073741824)
			return round($b/1073741824,2).' gb';
		elseif($b>=1048576)
			return round($b/1048576,2).' mb';
		elseif($b>=1024)
			return round($b/1024,2).' kb';
		return$b.' b';
	}

	/**
	 * Преобразование приблизительного читаемого формата в байты
	 * @param string $b Приблизительный читаем формат
	 * @return int
	 */
	public static function SizeToBytes($b)
	{
		$bytes=(int)$b;
		if(isset($b[1]))
			switch(preg_match('#([a-z]+)\s*$#i',$b,$m)>0 ? strtolower($m[1]) : '')
			{
				/*case 'eb':
				case 'e':
					return$bytes*1152921504606846976;
				case 'pb':
				case 'p':
					return$bytes*1125899906842624;
				case 'tb':
				case 't':
					return$bytes*1099511627776;*/
				case'gb':
				case'g':
					return$bytes*1073741824;
				case'mb':
				case'm':
					return$bytes*1048576;
				case'kb':
				case'k':
					return$bytes*1024;
			}
		return$bytes;
	}

	/**
	 * Копирование файлов и каталогов
	 * @param string $source Источник: путь откуда будет происходить копирование
	 * @param string $dest Назначение: путь, куда будет происходить копирование
	 * @return bool
	 */
	public static function Copy($source,$dest)
	{
		if(!file_exists($source))
			return false;

		$source=realpath($source);#Путь может быть неполным
		if(is_link($source) or Eleanor\W and readlink($source)!=$source)#Ниже важная информация
			return symlink(readlink($source),$dest);

		if(is_file($source))
		{
			static::MkDir(dirname($dest));
			return copy($source,$dest);
		}

		$f=__FUNCTION__;
		$files=array_diff(scandir($source),['.','..']);

		foreach($files as $entry)
			static::$f($source.DIRECTORY_SEPARATOR.$entry,$dest.DIRECTORY_SEPARATOR.$entry);

		return true;
	}

	/**
	 * Рекурсивное создание симлинков. Каталоги и симлинки копируются, а не симлинкуются
	 * @param string $source Источник: путь откуда будет происходить копирование
	 * @param string $dest Назначение: путь, куда будет происходить копирование
	 * @param bool $deldest Флаг обязательно очистки каталога-приемника
	 * @return bool
	 */
	public static function SymLink($source,$dest,$deldest=true)
	{
		#Очистка значений
		$source=rtrim(realpath($source),'/\\');#Путь может быть неполным
		$dest=rtrim($dest,'/\\');

		/*
			В винде символические ссылки всегда будут с полным путем, а в *nix системах - относительным.
			http://lists.unixcenter.ru/archives/mlug/2004-April/025317.html
		*/

		if(!file_exists($source))
			return false;

		if(is_link($source))
		{
			$source=readlink($source);
			if(!Eleanor\W)
			{
				$source=realpath($source);
				$source=static::ShortPath($dest,$source);
			}
			return symlink($source,$dest);
		}

		if($deldest ? file_exists($dest) : is_file($dest))
			static::Delete($dest);

		if(is_file($source))
		{
			static::MkDir(dirname($dest));
			return symlink(Eleanor\W ? $source : static::ShortPath($dest,$source),$dest);
		}

		$f=__FUNCTION__;
		$files=array_diff(scandir($source),['.','..']);

		foreach($files as $entry)
			static::$f($source.DIRECTORY_SEPARATOR.$entry,$dest.DIRECTORY_SEPARATOR.$entry);

		return true;
	}

	/**
	 * Обновление каталога с файлами после внесения изменений. Следующий шах после SymLink
	 * @param string $temp Каталог, в котором происходили изменения
	 * @param string $dest "Рабочий" каталог с файлами, прикрепленный к записи
	 * @throws EE
	 * @return bool
	 */
	public static function UpdateDir($temp,$dest)
	{
		#Очистка значений
		$temp=rtrim(realpath($temp),'/\\');#Путь может быть неполным
		$dest=rtrim($dest,'/\\');

		/*
			Внимание! Функция is_link на винде работает крайне нестабильно!
			Поэтому проверяем через костыли: если readlink($path)!=$path, значит перед нами ссылка,
			но есть ньюанс, в эту функцию в винде ВСЕГДА нужно передавать полные пути, иначе может не срабоать.
		*/

		if(Eleanor\W)
		{#readlink на винде всегда возвращает ссылки с \
			$temp=str_replace('/',DIRECTORY_SEPARATOR,$temp);
			$dest=str_replace('/',DIRECTORY_SEPARATOR,$dest);
		}

		if(!is_dir($temp))
			return false;

		$links=array_diff(scandir($temp),['.','..']);

		#Если $dest не существует или попросту не каталог - это существенно упрощает нам работу
		if(!is_dir($dest))
		{
			if(count($links)==0)
				return static::Delete($temp);

			if(file_exists($dest))
				static::Delete($dest);

			return rename($temp,$dest);
		}

		foreach($links as $k=>$file)
		{
			$fulltemp=$temp.DIRECTORY_SEPARATOR.$file;

			$fulltemp=realpath($fulltemp);#Путь может быть неполным
			if(Eleanor\W ? is_file($fulltemp) && readlink($fulltemp)!=$fulltemp : is_link($fulltemp))
			{
				#Сперва проверим: возможно, мы пытаемся обновить совершенно чужие между собой каталоги
				$orig=readlink($fulltemp);

				if(!is_file($orig) || dirname($orig)!=$dest)
					throw new EE('DISPARATE_DIRS',EE::ENV);
				#Файл переименовали?
				elseif(basename($orig)!=$file)
					rename($orig,dirname($orig).DIRECTORY_SEPARATOR.$file);
			}
			#Переименовали каталог?
			elseif(is_dir($fulltemp) and !is_dir($dest.DIRECTORY_SEPARATOR.$file))
				if(static::FixRenamedDir($fulltemp,$dest)==='d')
					unset($links[$k]);
		}

		$files=array_diff(scandir($dest),['.','..']);

		#Удалим удаленное
		foreach(array_diff($files,$links) as $file)
		{
			$fulldest=$dest.DIRECTORY_SEPARATOR.$file;
			$fulltemp=$temp.DIRECTORY_SEPARATOR.$file;

			#Если удалили или перезалили (файл)
			if(is_dir($fulldest) or Eleanor\W ? !is_file($fulltemp) || readlink($fulltemp)==$fulltemp : !is_link($fulltemp))
				static::Delete($fulldest);
		}

		#Перенесем теперь загруженное
		$f=__FUNCTION__;
		foreach($links as $file)
		{
			$full=$temp.'/'.$file;
			$fulldest=$dest.'/'.$file;

			if(!in_array($file,$files))
				rename($full,$fulldest);
			elseif(is_dir($full))
				static::$f($full,$fulldest);
		}

		static::Delete($temp);
		return true;
	}

	/**
	 * Реализация действия, когда переименовывается каталог. Часть метода UpdateDir
	 * @param string $path Путь к каталогу, который переименовали
	 * @param string $parent Путь к каталогу-родителю, в котором содержтся непереименовый каталог
	 * @return bool|string d - каталог удален
	 */
	protected static function FixRenamedDir($path,$parent)
	{#Выше важная информация
		$links=array_diff(scandir($path),['.','..']);

		if(!$links)
		{
			ReturnD:
			static::Delete($path);
			return'd';
		}

		$recrsym=false;#Массив ссылок, которые нужно будет пересоздать
		$dirs=[];

		#Redundance for PhpStorm: удалить следующую строку
		$names=$torename=[];

		foreach($links as $k=>$file)
		{
			$full=$path.DIRECTORY_SEPARATOR.$file;
			if(is_dir($full))
				$dirs[$k]=$full;
			elseif(Eleanor\W ? $rl=readlink($full) and $rl!=$full : is_link($full))
			{
				$orig=readlink($full);

				if(strpos($orig,$parent)===0)
				{
					#Что надо переименовать
					$torename=substr(dirname($orig),strlen($parent)+1);
					$torename=explode(DIRECTORY_SEPARATOR,$torename);

					#Во что надо переименовать
					$names=explode(DIRECTORY_SEPARATOR,$path);
					$names=array_slice($names,-count($torename));

					#Получим массив всех удаленных симлинков и путей, куда они вели
					$recrsym=static::GetDelSym($path,$links);
					break;
				}
			}
		}

		if($recrsym===false)
		{
			$f=__FUNCTION__;
			foreach($dirs as $k=>$dir)
				if(static::$f($dir,$parent)==='d')
					unset($links[$k]);

			if(!$links)
				goto ReturnD;
		}
		else
		{
			$from=$to=$parent;
			foreach($names as $k=>$name)
			{
				$from.=DIRECTORY_SEPARATOR.$torename[$k];
				$to.=DIRECTORY_SEPARATOR.$name;

				if(rename($parent.DIRECTORY_SEPARATOR.$torename[$k],$parent.DIRECTORY_SEPARATOR.$name))
					$parent.=DIRECTORY_SEPARATOR.$name;
				else
					return false;
			}

			foreach($recrsym as $k=>$v)
				symlink(str_replace($from,$to,$v),$k);
		}

		return true;
	}

	/**
	 * Получение всех удаленных симлинков и путей, куда они вели
	 * @param string $path Путь к каталогу с линками
	 * @param array $links Если линки уже созданы - их можно перечислить в этом параметре, не сканируя файлы еще раз
	 * @return array
	 */
	public static function GetDelSym($path,array$links=[])
	{#Выше важная информация
		if(!$links)
		{
			$links=array_diff(scandir($path),['.','..']);
			if(!$links)
			{
				static::Delete($path);
				return [];
			}
		}

		$recrsym=[];
		$f=__FUNCTION__;

		foreach($links as $file)
		{
			$full=$path.DIRECTORY_SEPARATOR.$file;

			if(is_dir($full))
				$recrsym+=static::$f($full);
			elseif(Eleanor\W ? $rl=readlink($full) and $rl!=$full : is_link($full))
			{
				$recrsym[ $full ]=readlink($full);
				unlink($full);
			}
		}

		return$recrsym;
	}

	/**
	 * Создание каталога. В отличии от стандартной функции mkdir, метод позволяет создать сразу цепочку каталогов
	 * @param string $path Путь до каталога, который необходимо создать
	 * @return bool
	 */
	public static function MkDir($path)
	{
		$f=__FUNCTION__;

		if(!is_dir($path))
		{
			static::$f(dirname($path));
			return mkdir($path);
		}

		return true;
	}

	/**
	 * Удаление файлов, каталогов и ссылок на файлы
	 * @param string $path Путь к файлу, каталогу или ссылке которые нужно удалить
	 * @param bool $nocheck Флаг отключения проверки существования файла перед unlink
	 * @return bool
	 */
	public static function Delete($path,$nocheck=false)
	{
		if(is_dir($path))
		{
			$f=__FUNCTION__;
			$entries=array_diff(scandir($path),['.','..']);

			foreach($entries as $entry)
				if(!static::$f($path.DIRECTORY_SEPARATOR.$entry,true))
					return true;

			return rmdir($path);
		}

		#Если ссылка битая, file_exists её не определяет, поэтому $check
		return$nocheck||file_exists($path) ? unlink($path) : true;
	}

	/**
	 * Получение размера каталога
	 * @param string $path Путь к каталогу, размер которого необходимо узнать
	 * @param callback|bool $filter Фильтр для случая, если нужно считать размер определенных файлов. return bool
	 * @return int Возвращает СУММУ размеров всех внутрилежащих файлов, а не реальное занимаемое место на диске
	 */
	public static function GetSize($path,$filter=false)
	{
		if(is_link($path))
			return 0;

		if(is_dir($path))
		{
			$size=0;
			$f=__FUNCTION__;
			$entries=array_diff(scandir($path),['.','..']);

			foreach($entries as $entry)
				$size+=static::$f($path.DIRECTORY_SEPARATOR.$entry,$filter);

			return$size;
		}

		return is_file($path) && (!is_callable($filter) or call_user_func($filter,$path)) ? filesize($path) : 0;
	}

	/**
	 * Преобразование имен файлов в корректную последовательность символов для ОС Windows, где имена файлов задаются
	 * в однобайтовой кодировке.
	 * @param string $f Имя файла
	 * @param bool $dec Флаг декодирования (включение обратного преобразования)
	 * @return string
	 */
	public static function Windows($f,$dec=false)
	{
		if(Eleanor\W and Eleanor\UTF8)
			$f=$dec ? mb_convert_encoding($f,Eleanor\W,'cp1251') : mb_convert_encoding($f,'cp1251');

		return$f;
	}

	/**
	 * Дописывание в средину файла. Метод идентичен функции substr_replace, только для работы с файлом. Для корректно
	 * работы, $fh нужно открывать файлы в режиме rb+. Режим a (дописывание в конец файла) НЕ ПОДДЕРЖИВАЕТСЯ
	 * (особенность PHP)!
	 * @param resource $fh Файловый указатель, возвращаемый функцией fopen
	 * @param string $s Строка на замену (идентично 2му параметру функции substr_replace)
	 * @param int $o Отступ в байтах (идентично 3му параметру функции substr_replace)
	 * @param int $l Длина в байтах (идентично 4му параметру функции substr_replace)
	 * @param int $buf Число байтов, считываемых за раз
	 * @return int|false
	 */
	public static function FReplace($fh,$s,$o,$l=0,$buf=4096)
	{
		$len=strlen($s);

		if(!is_resource($fh) or $len==0 and $l==0)
			return false;

		#PHP 5.4 fstat($fh)['size'];
		$size=fstat($fh);
		$size=$size['size'];

		$diff=$len-$l;

		if($diff==0 and $o<$size)
		{
			fseek($fh,$o,SEEK_SET);
			fwrite($fh,$s);
		}
		elseif($o>=$size)
		{
			fseek($fh,0,SEEK_END);
			fwrite($fh,$s);
		}
		else
		{
			$diff=strlen($s)-$l;

			if($diff>0)
			{
				$step=1;
				$limiter=$o+$l;

				do
				{
					$i=$size-$buf*$step++;

					if($i>$limiter)
					{
						$seek=$i;
						fseek($fh,$seek,SEEK_SET);
						$data=fread($fh,$buf);
					}
					else
					{
						$seek=$limiter;
						fseek($fh,$seek,SEEK_SET);
						$data=fread($fh,$buf+$limiter-$i);
					}

					fseek($fh,$seek+$diff,SEEK_SET);
					fwrite($fh,$data);
				}while($i>$limiter);
			}
			else
			{
				for($i=$o+$l;$i<$size;$i+=$buf)
				{
					fseek($fh,$i,SEEK_SET);
					$data=fread($fh,min($buf,$size-$i));
					fseek($fh,$i+$diff,SEEK_SET);
					fwrite($fh,$data);
				}

				ftruncate($fh,$size+$diff);
			}

			if($len>0)
			{
				fseek($fh,$o,SEEK_SET);
				fwrite($fh,$s);
			}
		}

		return$diff;
	}

	/**
	 * Генерация относительного путь для перехода из одного каталога в другой
	 * @param string $a Путь к первому каталогу
	 * @param string $b Путь ко второму каталогу
	 * @return string Например: ../../aa/bb/cc
	 */
	public static function ShortPath($a,$b)
	{
		$a=preg_split('#[/\\\\]+#',$a);
		$b=preg_split('#[/\\\\]+#',$b);
		$m=min($acnt=count($a),count($b));

		for($i=0;$i<$m;++$i)
		{
			if($i==0 and $a[0]!=$b[0])
				return false;
			if($a[$i]!=$b[$i])
				break;
		}

		$acnt-=$i+1;
		$ret=$acnt>0 ? array_merge(array_fill(0,$acnt,'..'),array_slice($b,$i)) : array_slice($b,$i);

		return join('/',$ret);
	}
}