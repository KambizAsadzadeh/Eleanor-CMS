<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes\Cache;
use Eleanor, Eleanor\Classes;

/** Кэшмашина Serialize */
class Serialize implements Eleanor\Interfaces\Cache
{
	/** @var string Путь файлам кэша */
	protected $path;

	/** @param null $path Путь к файлам кэша
	 * @throws Classes\EE */
	public function __construct($path=null)
	{
		$this->path=$path ? $path : $_SERVER['DOCUMENT_ROOT'].Eleanor\SITEDIR.'cache/';

		if(!is_dir($this->path))
			Classes\Files::MkDir($this->path);

		if(!is_writeable($this->path))
			throw new Classes\EE('Folder /cache is write-protected',Classes\EE::ENV);
	}

	/** Запись значения
	 * @param string $k Ключ. Рекомендуется задавать в виде тег1_тег2 ...
	 * @param mixed $v Значение
	 * @param int $ttl Время жизни этой записи кэша в секундах
	 * @return true */
	public function Put($k,$v,$ttl=0)
	{
		return file_put_contents($this->path.$k.'.php',serialize([$ttl>0 ? (int)$ttl+time() : 0,$v]));
	}

	/** Получение записи из кэша
	 * @param string $k Ключ
	 * @return mixed */
	public function Get($k)
	{
		$f=$this->path.$k.'.php';

		if(!is_file($f))
			return false;

		$d=unserialize(file_get_contents($f));

		if($d[0]>0 and $d[0]<time())
		{
			$this->Delete($k);
			return false;
		}

		return$d[1];
	}

	/** Удаление записи из кэша
	 * @param string $k Ключ
	 * @return bool */
	public function Delete($k)
	{
		$r=Classes\Files::Delete($this->path.$k.'.php');
		clearstatcache();
		return$r;
	}

	/** Удаление записей по тегу. Если имя тега пустое - удаляется вешь кэш
	 * @param string $tag Тег */
	public function DeleteByTag($tag)
	{
		$tag=str_replace('..','',$tag);

		if($tag!='')
			$tag.='*';

		$files=glob($this->path.'*'.$tag.'.php');

		if($files)
			foreach($files as $f)
				Classes\Files::Delete($f);

		clearstatcache();
	}
}