<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Обеспечение кэша в системе. Кэш отключается при включении режима отладки.
*/
namespace Eleanor\Classes;
use Eleanor;

/** Поддержка кэша */
class Cache extends Eleanor\BaseClass
{
	public
		/** @var string Путь к файлам "вечного" хранилища. О то, что такое "вечный кэш", читайте ниже */
		$storage,

		/** @var Eleanor\Interfaces\Cache Объект кэш-машины */
		$Engine;

	/** Конструктор кэширующего класса
	 * @param string|null $uniq Префикс для имен внешней кэш-машины
	 * @param string|null $path Путь для хранения файлов для внутренней кэш-машины (когда отсутствуют внешние)
	 * @param string|null $storage Путь сохранения "вечного" кэша. Вечный отличается тем, что его легко править и он
	 * не удаляется вместе с основным. Как правило, в вечном кэше хранятся сгенерированные данные ключ=>значение при
	 * недоступном генераторе.
	 * @throws EE*/
	public function __construct($uniq=null,$path=null,$storage=null)
	{
		$cachedir=__DIR__.'/cache/';

		if(function_exists('\\apc_store') and is_file($cachedir.'apc.php'))
			$machine=Eleanor\Classes\Cache\Apc::class;
		elseif(class_exists('\\Memcache',false) and is_file($cachedir.'memcache.php'))
			$machine=Eleanor\Classes\Cache\MemCache::class;
		elseif(class_exists('\\Memcached',false) and is_file($cachedir.'memcached.php'))
			$machine=Eleanor\Classes\Cache\MemCached::class;
		elseif(function_exists('\\output_cache_put') and is_file($cachedir.'zend.php'))
			$machine=Eleanor\Classes\Cache\Zend::class;
		else
			$machine=false;

		if($machine)
			$this->Engine=new$machine($uniq ? $uniq : crc32(__DIR__));
		else
			$this->Engine=new Eleanor\Classes\Cache\Serialize($path);

		$this->storage=$storage ? rtrim($storage,'/\\') : $_SERVER['DOCUMENT_ROOT'].Eleanor\SITEDIR.'cache/storage';

		if(!is_dir($this->storage))
			Files::MkDir($this->storage);

		if(!is_writeable($this->storage))
			throw new EE('Folder /cache/storage is write-protected',EE::ENV);
	}

	/** Запись данных в кэш
	 * @param string $key Ключ (имя ячейки хранения кэша
	 * @param mixed $value Хранимые данные
	 * @param int $ttl Время хранения в секундах
	 * @param bool $eternal Запись в качестве "вечного" кэша
	 * @param int|null $insur Время безнадежного устаревания кэша. Используется для предотвращения dog-pile effect.
	 * По умолчанию в два раза больше $ttl.
	 * @throws EE */
	public function Put($key,$value=false,$ttl=0,$eternal=false,$insur=null)
	{
		if(!Eleanor\Framework::$debug)
		{
			if($insur===null)
				$insur=$ttl*2;
			elseif($insur<$ttl or $ttl==0)
				$insur=0;

			if($value===false)
				$this->Delete($key,true);
			else
				$this->Engine->Put($key,$insur>0 ? [$value,$ttl,time()+$ttl] : [$value],$insur>0 ? $insur : $ttl);
		}

		if($eternal)
		{
			if(preg_match('#[\s\#"\'\\\\/:*\?<>|%]+#',$key)>0)
				throw new EE('Invalid eternal key',EE::DEV,[ 'input'=>$key ]);

			file_put_contents($this->storage.'/'.$key.'.inc','<?php return '.var_export($value,true).';');
		}
	}

	/** Получение данных из кэша
	 * @param string $key Имя ячейки хранения кэша
	 * @param bool $eternal Флаг добывания значения из "вечного" кэша
	 * @throws EE
	 * @return mixed */
	public function Get($key,$eternal=false)
	{
		if(Eleanor\Framework::$debug)
			$out=false;
		elseif($out=$this->Engine->Get($key))
		{
			if(is_array($out) and isset($out[1]) and $out[2]<time())
			{
				$this->Put($key,$out[0],$out[1],false,0);

				return false;
			}

			return$out[0];
		}

		if(!$eternal or !$key)
			return$out;

		if(preg_match('#[\s\#"\'\\\\/:*\?<>|%]+#',$key)>0)
			throw new EE('Invalid eternal key',EE::DEV,[ 'input'=>$key ]);

		$file=$this->storage.'/'.$key.'.inc';

		if(!is_file($file))
			return false;

		$out=Eleanor\AwareInclude($file);

		$this->Put($key,$out);

		return$out;
	}

	/** Удаление данных из кэша
	 * @param string $key Имя ячейки хранения кэша
	 * @param bool $eternal Флаг удаления кэша из таблицы "вечного" хранения
	 * @throws EE*/
	public function Delete($key,$eternal=false)
	{
		$this->Engine->Delete($key);

		if($eternal)
		{
			if(preg_match('#[\s\#"\'\\\\/:*\?<>|%]+#',$key)>0)
				throw new EE('Invalid eternal key',EE::DEV,[ 'input'=>$key ]);

			$file=$this->storage.'/'.$key.'.inc';

			if(is_file($file))
				Files::Delete($file);
		}
	}

	/** Пометка кэша устаревшим для его перегенерации. В отличии от Delete, не влечет за собой появление dog-pile effect
	 * @param string $key Имя ячейки хранения кэша */
	public function Obsolete($key)
	{
		if(false!==$out=$this->Engine->Get($key))
			if(is_array($out) and isset($out[1]) and 0<$ttl=($out[2]-time()))
			{
				$out[2]=0;
				$this->Engine->Put($key,$out,$ttl);
			}
			else
				$this->Engine->Delete($key);
	}
}