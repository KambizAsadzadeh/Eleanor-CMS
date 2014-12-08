<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files;

/** Права (разрешения) пользователя на сайте */
final class Permissions extends \Eleanor\BaseClass
{
	/** @var array Значения параметров групп: таблица => ID => поле => значение */
	private static $perms=[];

	/** Получение разрешений исходя из группы
	 * @param array $ids ID групп
	 * @param string $field Название параметра (столбец таблицы групп)
	 * @param string $table Название таблицы с разрешениями групп
	 * @return array */
	public static function ByGroup(array$ids,$field,$table='')
	{
		if(!$table)
			$table=P.'groups';

		if(isset(self::$perms[$table]))
			$groups=self::$perms[$table];
		else
		{
			if(false===$groups=Eleanor::$Cache->Get($table))
			{
				$groups=[];

				$R=Eleanor::$Db->Query('SELECT * FROM `'.$table.'`');
				while($a=$R->fetch_assoc())
				{
					$r=[];
					$id=0;

					foreach($a as $k=>&$v)
						if($k=='id')
							$id=$v;
						elseif($k=='parents')
							$r[$k]=$v ? array_reverse(explode(',',rtrim($v,','))) : [];
						elseif('_l'==substr($k,-2))
							$r[$k]=$v ? json_decode($v,true) : [];
						elseif($v!==null)
							$r[$k]=$v;

					if($id!=0)
						$groups[$id]=$r;
				}

				Eleanor::$Cache->Put($table,$groups,3600);
			}

			self::$perms[$table]=$groups;
		}

		$r=[];
		foreach($ids as &$v)
			if(isset($groups[$v][$field]))
				$r[$v]=$groups[$v][$field];
			else
			{
				$r[$v]=null;

				if(isset($groups[$v]))#Для наследования групп
					foreach($groups[$v]['parents'] as $pv)
						if(isset($groups[$pv][$field]))
							$r[$v]=$groups[$pv][$field];
			}

		return$r;
	}

	/** @var string|Interfaces\Login Объект или названия класса логина (классы логином статичны) */
	private $Login;

	/** Конструктор разрешений, для каждого логина определяются свои разрешения
	 * @param string|Interfaces\Login $Login Объект или названия класса логина (классы логином статичны) */
	public function __construct($Login)
	{
		$this->Login=$Login;
	}

	/** Получение разрешений пользователя с учетом воможного членства в нескольких группах и перезагрузки настроек
	 * индивидуальными параметрами
	 * @param string $field Название параметра (столбец таблицы групп), по которому необходимо получить разрешения
	 * @param string $table Название таблицы с разрешениями
	 * @param string $go Название пользовательского параметра с массивом перегрузки разрешений групп
	 * @return array */
	public function Get($field,$table='',$go='groups_overload')
	{
		$over=$this->Login->Get($go);

		if(!$over or !isset($over['method'][$field],$over['value'][$field]) or $over['method'][$field]=='inherit')
			return$this->ByGroup(GetGroups(),$field,$table);

		$add=$over['method'][$field]=='replace';
		$res=$add ? [$over['value'][$field]] : $this->ByGroup(GetGroups(),$field,$table);

		if(!$add)
			$res[0]=$over['value'][$field];

		return$res;
	}

	/** @var bool Флаг доступа в админ панель */
	protected $is_admin;

	/** Проверка разрешен ли пользователю вход в панель администратора
	 * @return bool */
	public function IsAdmin()
	{
		if(!isset($this->is_admin))
		{
			$v=$this->Get('is_admin');
			$this->is_admin=in_array(1,$v);
		}

		return$this->is_admin;
	}

	protected
		/** @var int Максимальный размер загруженного файла */
		$max_upload,

		/** @var int Теоретический показатель, без учета ограничений сервера */
		$theory;

	/** Определение максимального размера загружаемого файла
	 * @param bool $theory Получение теоретической цифры, без учета ограничений сервера
	 * @return int|bool false - нельзя загружать файлы, true - без ограничений (для $theory) (int) - числов в байтах */
	public function MaxUpload($theory=false)
	{
		if(!isset($this->theory))
		{
			$max=Files::SizeToBytes(ini_get('upload_max_filesize'));
			$v=$this->Get('max_upload');

			if(in_array(1,$v))
			{
				$this->max_upload=$max;
				$this->theory=true;
				return$theory ? true : $max;
			}

			sort($v,SORT_NUMERIC);

			$bytes=(int)end($v);
			$this->theory=$bytes<1 ? false : $bytes*1024;

			if($this->theory!==false)
				$this->theory=min($max,$this->max_upload);

			$this->max_upload=$this->theory;
		}

		return$theory ? $this->theory : $this->max_upload;
	}

	/** @var bool Флаг бана группы */
	protected $banned;

	/** Проверка забанен ли пользователь
	 * @return bool */
	public function IsBanned()
	{
		if($this->IsAdmin())
			return false;

		if(!isset($this->banned))
		{
			$v=$this->Get('banned');
			$this->banned=in_array(1,$v);
		}

		return$this->banned;
	}

	/** @var bool Флаг скрытия капчи */
	protected $captcha;

	/** Проверка возможности отключения капчи для пользователя
	 * @return bool */
	public function HideCaptcha()
	{
		if(!isset($this->captcha))
		{
			$v=$this->Get('captcha');
			$this->captcha=in_array(0,$v);
		}

		return$this->captcha;
	}

	/** @var bool Флаг показа закрытого сайта */
	protected $csa;

	/** Проверка наличия возпожности просматривать закрытый сайт
	 * @return bool */
	public function ClosedSiteAccess()
	{
		if(!isset($this->csa))
		{
			$v=$this->Get('closed_site_access');
			$this->csa=in_array(1,$v);
		}

		return$this->csa;
	}

	/** @var int Flood limit */
	protected $flood;

	/** Минимальное время в секундах между публикацией материалов (новостей, комментариев и т.п.)
	 * @return int */
	public function FloodLimit()
	{
		if(!isset($this->flood))
		{
			$v=$this->Get('flood_limit');
			$this->flood=min($v);
		}

		return$this->flood;
	}

	/** @var int Search limit */
	protected $search;

	/** Минимальный промежуток времени в секундах между поисковыми запросами
	 * @return int */
	public function SearchLimit()
	{
		if(!isset($this->search))
		{
			$v=$this->Get('search_limit');
			$this->search=min($v);
		}

		return$this->search;
	}

	/** @var bool Флаг модерации публикаций */
	protected $moderate;

	/** Проверка наличия возможности публикации материалов без их премодерации
	 * @return bool */
	public function Moderate()
	{
		if(!isset($this->moderate))
		{
			$v=$this->Get('moderate');
			$this->moderate=in_array(1,$v);
		}

		return$this->moderate;
	}
}