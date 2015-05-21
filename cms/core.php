<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Ядро Eleanor CMS
*/
namespace CMS;
use Eleanor\Framework, Eleanor\Classes as FClasses, Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

#Без этого, на некоторых серверах вылазит ошибка "It is not safe to rely on the system's timezone settings."
date_default_timezone_set('Europe/Simferopol');

/** Копирайты системы. Пожалуйста, не удаляйте и не изменяйте их, если, конечно, у вас есть хоть немного уваженияк разработчикам. */
define('CMS\COPYRIGHT','<a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> © '.idate('Y'));

/** Квинтэссенция ENT_* констант  */
define('CMS\ENT',ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED);

/** Полный путь к cms каталогу */
define('CMS\DIR',__DIR__.'/');

/** AJAX запрос к серверу с jQuery */
define('CMS\AJAX',isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');

/** Запрос при помощи AngularJS */
define('CMS\ANGULAR',isset($_SERVER['CONTENT_TYPE']) && strpos((string)$_SERVER['CONTENT_TYPE'],'application/json;')===0);

/** Сокращение полезных опций JSON */
define('CMS\JSON',JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

#Загрузим и настроим фреймворк
require __DIR__.'/../framework/core.php';
Framework::$logspath=__DIR__.'/../trash/logs/';

#Немножко упростим себе жизнь
ignore_user_abort(true);
error_reporting(E_ALL^E_NOTICE);

#Реализация своей автозагрузки: сперто с core.php фреймворка
spl_autoload_register(function($cl){
	if(strpos($cl,__NAMESPACE__)!==0)
		return;

	$lccl=strtolower($cl);#LowerCase class
	$lccl=explode('\\',$lccl,3);

	#Вдруг кто-то из глобальной области пытается зазгрузить класс CMS?
	if(!isset($lccl[1]))
		return;

	#Уберем неймспейс CMS
	array_splice($lccl,0,1);

	#Классы у нас располагаются в каталоге classes, но в неймспейсе CMS (для удобства)
	if(!isset($lccl[1]) or preg_match('#^(traits|interfaces|abstracts|controls|logins|modules|ownbb|parsers|tasks)#',$lccl[0])==0)
		$lccl[0]='classes\\'.$lccl[0];

	$lccl=join('\\',$lccl);

	$trypath=__DIR__.DIRECTORY_SEPARATOR.str_replace('\\','/',$lccl).'.php';

	if($q=is_file($trypath))
		require$trypath;

	if(!class_exists($cl,false) and !interface_exists($cl,false) and !trait_exists($cl,false))
	{
		switch(explode('\\',$lccl)[0])
		{
			case'traits':
				$what='Trait';
			break;
			case'interfaces':
				$what='Interface';
			break;
			case'abstracts':
				$what='Abstract class';
			break;
			case'controls':
				$what='Control';
			break;
			default:
				$what='Class';
		}

		if(class_exists('Eleanor\Classes\EE',false) or include(__DIR__.'/../framework/classes/ee.php'))
		{
			$bt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

			foreach($bt as $db)
				if(isset($db['file'],$db['line']) and (!isset($db['function']) or $db['function']!='class_exists'))
					break;

			throw new EE($what.' not found: '.$cl,EE::DEV,$db);
		}
	}
});

/** Обертка глобальных переменных. Применяется с переменной $_POST для получения безопасных значений */
class GlobalsWrapper implements \ArrayAccess
{
	/** @var string Значение по умолчанию */
	public $default;

	/** @var string Имя глобальной переменной */
	private $varname;

	/** Создание оболочки для глобальной переменной. Глобальная переменная должна быть массивом.
	 * @param string $varname Имя глобальной переменной, для которой создается оболочка */
	public function __construct($varname)
	{
		$this->varname=$varname;
	}

	/** Установка значения элемента глобального массива
	 * @param string $k Имя элемента, ключ массива
	 * @param mixed $v Значение */
	public function offsetSet($k,$v)
	{
		$GLOBALS[$this->varname][$k]=$v;
	}

	/** Проверка существования определенного элемента глобального массива
	 * @param string $k Имя элемента, ключ массива
	 * @return mixed */
	public function offsetExists($k)
	{
		return isset($GLOBALS[$this->varname][$k]);
	}

	/** Удаление элемента глобального массива
	 * @param string $k Имя элемента, ключ массива */
	public function offsetUnset($k)
	{
		unset($GLOBALS[$this->varname][$k]);
	}

	/** Получение элемента глобального массива
	 * @param string $k Имя элемента, ключ массива
	 * @return mixed */
	public function offsetGet($k)
	{
		return isset($GLOBALS[$this->varname][$k]) ? static::Filter($GLOBALS[$this->varname][$k]) : $this->default;
	}

	/** Установка значения по умолчанию
	 * @param mixed $value Значение по умолчанию
	 * @return self */
	public function SetDefault($value)
	{
		$this->default=$value;
		return$this;
	}

	/** Преобразование опасного HTML в безопасный (обертка над функцией htmlspecialchars)
	 * @param string|array $s Строка с опасным HTML
	 * @return mixed */
	public static function Filter($s)
	{
		if(is_array($s))
		{
			foreach($s as &$v)
				$v=static::Filter($v);

			return$s;
		}

		$s=str_replace(["\r\n","\n\r","\r"],"\n",$s);
		$s=trim($s);

		return htmlspecialchars($s,ENT,\Eleanor\CHARSET,false);
	}

	/** Вырезание из $_FILES['file']['name'] опасных символов
	 * @param string $fn Имя файла $_FILES['file']['name']
	 * @return string */
	public static function FileName($fn)
	{
		$fn=basename($fn);
		$fn=Url::Decode($fn);
		$fn=preg_replace('#[\s\'"%\]\[/\\\-]+#','-',$fn);
		return str_replace("\0",'',$fn);
	}
}

/** Реализация специального массива, который бросает исключение в случае попытки доступа к несуществующему ключу */
class ExceptionArray extends \ArrayObject
{
	/** @var string Текст исключения */
	public $except;

	/** @param array|null|object $a Предустановка массива
	 * @param string $except Текст исключения */
	public function __construct($a=[],$except='Index %s was no found in array')
	{
		$this->except=$except;
		parent::__construct($a);
	}

	/** Получение значения
	 * @param mixed $k Ключ, который необходимо получить
	 * @return mixed
	 * @throws EE */
	public function offsetGet($k)
	{
		if($this->offsetExists($k))
			return parent::offsetGet($k);

		throw new EE(sprintf($this->except,$k),EE::DEV,
			[ 'input'=>$k ]+\Eleanor\BaseClass::_BT(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1)));
	}
}

/** Основной класс системы. Частоиспользуемые классы
 * @property Url|DynUrl $Url
 * @property Editor $Editor */
class Eleanor extends Framework
{
	const
		/** @const Версия системы. Огромная просьба: НЕ изменять самостоятельно. */
		VERSION=1,

		/** Пространство имен из-под которого будут работать функции __call и __get */
		NS='\CMS\\';

	public static
		/** @var array Языки, доступные в системе */
		$langs=[],

		/** @var Language Основной языковой объект с автозагрузкой языков из каталога translation */
		$Language,

		/** @var FClasses\Cache Объект системного кэша */
		$Cache,

		/** @var FClasses\MySQL Объект базы данных */
		$Db,

		/** @var FClasses\MySQL Объект пользовательской БД для доступа к таблице пользователей */
		$UsersDb,

		/** @var Template Основной шаблонизатор */
		$Template,

		/** @var Interfaces\Login Объект основного системного логина. Для удобства доступа - именно объект. */
		$Login,

		/** @var Permissions Объект разрешений */
		$Permissions,

		/** @var array Сервисы системы */
		$services,

		/** @var string IP адрес клиента в бинарном формате inet_pton() */
		$ip,

		/** @var array Все возможные IP клиента в бинарном формате, в случае пользования неанонимными проксями и т.п. */
		$ips=[],

		/** @var bool Флаг-идентификатор того, что запрос пришел с нашей страницы */
		$ourquery=true,

		/** @var array Резерв переменных конфигурационных настроек, для которых включена мультиязычность */
		$lvars=[],

		/** @var array Переменные, взятые из конфига */
		$vars=[],

		/** @var string Префикс имен cookie */
		$cookie,

		/** @var string Идентификатор поискового бота */
		$bot,

		/** @var array Идентификация бана, ключи: type (ip|user|group), explain - объяснение, term - срок (не всегда) */
		$ban=[],

		/** @var string Дополнительная строка, которая будет писаться в таблицу сессий. Полезно для создания фичи во
		 * встроенных форумах: эту тему читают N пользователей */
		$extra='',

		/** @var string название сервиса */
		$service,

		/** @var array Отфильтрованный $_POST */
		$POST=[];

	public
		/** @var array Рекомендуемый контейнер для настроек модуля */
		$module=[],

		/** @var array Здесь рекомендуется модули, полученные функцией GetModules */
		$modules;

	/** @param string|array|bool $config Конфигурация системы
	 * @throws EE */
	public function  __construct($config=false)
	{
		if(!$config)
			return;

		if($config===true)
			$config=include DIR.'config.php';
		elseif(is_string($config) and is_file($config))
			$config=include$config;

		if(!is_array($config))
			throw new EE('Unknown config',EE::DEV);

		$config=new ExceptionArray($config);

		parent::$debug=$config['debug'];

		#Настроим языки
		self::$langs=$config['langs'];
		self::$Language->Change($config['language']);

		#База данных
		if(defined('CMS\P'))
			$this->p=$config['prefix'];
		else
			define('CMS\P',$config['prefix']);

		self::$Db=new MySQL([
			'host'=>$config['db-host'],
			'user'=>$config['db-user'],
			'pass'=>$config['db-pass'],
			'db'=>$config['db'],
			'charset'=>$config['db-charset'],
		]);

		if(isset($config['users']))
			self::$UsersDb=new MySQL([
				'host'=>$config['users']['db-host'],
				'user'=>$config['users']['db-user'],
				'pass'=>$config['users']['db-pass'],
				'db'=>$config['users']['db'],
				'charset'=>$config['db_charset'],
			]);
		else
			self::$UsersDb=&self::$Db;

		if(defined('CMS\USERS_TABLE'))
			self::$UsersDb->users_table=$config['users-table'];
		else
			define('CMS\USERS_TABLE',$config['users-table']);

		self::$vars=new ExceptionArray();
		LoadOptions('system');

		if(self::$vars['time_zone'])
			date_default_timezone_set(self::$vars['time_zone']);

		self::$Db->SyncTimeZone();

		if(self::$UsersDb!==self::$Db)
			self::$UsersDb->SyncTimeZone();

		#Загрузка всех сервисов
		if(!isset(self::$services) and false===self::$services=self::$Cache->Get('system-services'))
		{
			self::$services=[];

			$table=P.'services';
			$R=self::$Db->Query("SELECT `name`, `file`, `theme`, `login` FROM `{$table}`");
			while($a=$R->fetch_assoc())
				self::$services[ $a['name'] ]=array_slice($a,1);

			self::$Cache->Put('system-services',self::$services);
		}

		#Определение константы CMS\RUNTASK, которая содержит ссылку на картинку для запуска задач
		if(!defined('CMS\RUNTASK') and isset(self::$services['cron']))
		{
			$task=$config['debug'] ? false : self::$Cache->Get('nextrun',true);
			$t=time();
			define('CMS\RUNTASK',$task===false || $task<=$t ? self::$services['cron']['file'].'?rand='.$t : '');
		}

		$domains=explode(',',self::$vars['trusted_domains']);
		if(!in_array(\Eleanor\PUNYCODE,$domains))
			$domains[]=\Eleanor\PUNYCODE;

		$r=isset($_SERVER['HTTP_ORIGIN']) ? (string)$_SERVER['HTTP_ORIGIN'].'/' : getenv('HTTP_REFERER');
		if($r)
		{
			foreach($domains as &$v)
				$v=preg_quote($v,'#');
			unset($v);

			if(preg_match('#^[a-z]+://('.join('|',$domains).')'.\Eleanor\SITEDIR.'#',$r)==0)
				self::$ourquery=false;
		}

		#Куки
		self::$cookie=$config['cookie-prefix'];

		#Пути
		Template::$http+=[
			'static'=>$config['static'],
			'templates'=>$config['templates'],
			'uploads'=>$config['uploads'],
			'3rd'=>$config['3rd'],
		];
		Template::$path+=[
			'static'=>$config['static-path'],
			'templates'=>$config['templates-path'],
			'uploads'=>$config['uploads-path'],
			'3rd'=>$config['3rd-path'],
		];

		if(isset(self::$vars['blocked_ips']))
		{
			$ips=explode(',',self::$vars['blocked_ips']);

			foreach($ips as &$bip)
			{
				if(strpos($bip,'=')!==false)
				{
					$m=substr($bip,strpos($bip,'=')+1);
					$bip=substr($bip,0,strpos($bip,'='));
				}
				else
					$m=self::$vars['blocked_message'];

				if(IPMatchMask(self::$ip,$bip))
					static::$ban=[ 'type'=>'ip', 'explain'=>$m ];
				else
					foreach(self::$ips as &$ip)
						if(IPMatchMask($ip,$bip))
							static::$ban=[ 'type'=>'ip', 'explain'=>$m ];
			}

			unset(self::$vars['blocked_ips']);
		}

		$ua=getenv('HTTP_USER_AGENT');

		if(isset(self::$vars['bots_list']) and $ua)
		{
			foreach(self::$vars['bots_list'] as $k=>$v)
				if(stripos($ua,$k)!==false)
				{
					self::$bot=$v;
					break;
				}

			unset(self::$vars['bots_list']);
		}
	}
}

if(ANGULAR and !$_POST)
	$_POST=json_decode(trim(file_get_contents('php://input')),true);

if($_SERVER['REQUEST_METHOD']=='POST')
	Eleanor::$POST=new GlobalsWrapper('_POST');

#Detect IP
Eleanor::$ip=filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP) ? inet_pton($_SERVER['REMOTE_ADDR']) : 0;
foreach(['HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_X_COMING_FROM',
		'HTTP_COMING_FROM','HTTP_CLIENT_IP','HTTP_X_CLUSTER_CLIENT_IP','HTTP_PROXY_USER','HTTP_XROXY_CONNECTION',
		'HTTP_PROXY_CONNECTION','HTTP_USERAGENT_VIA','HTTP_X_REAL_IP'] as $v)
{
	$ip=getenv($v);

	if($ip!=Eleanor::$ip and filter_var($ip,FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
		Eleanor::$ips[$v]=inet_pton($ip);
}

Eleanor::$ips=array_unique(Eleanor::$ips);
#/Detect IP
Eleanor::$Cache=new FClasses\Cache(null,DIR.'../cache/',DIR.'../cache/storage/');

/** Шаблонизатор. Не вынесен в отдельный файл, поскольку 99.9% запросов к системе используют этот класс. Так быстрее. */
class Template extends FClasses\Template
{
	public static
		/** @var array Данные отладки для дальнейшего их вывода на страницах. Каждый элемент - массив с ключами:
		 * float t - время выполненния в секундах, string f - имя файла, int l - номер строки, string e - имя события*/
		$debug=[],

		/** @var array Пути к различным каталогам для доступа к файлам по HTTP относительно корня сайта */
		$http=[],

		/** @var array Пути к различным каталогам для доступа из php скриптов */
		$path=[];

	/** @var string Путь к каталогу шаблона с классами оформления */
	public $classes;

	/** @var string Подпространство имен */
	protected $subns;

	/** @param array $queue Очередь на загрузку
	 * @param string $subns Подпространство имен */
	public function __construct($queue=[],$subns='')
	{
		parent::__construct($queue);
		$this->subns=$subns ? $subns.'\\' : '';
	}

	/** Источник шаблонов
	 * @param string $n Название шаблона
	 * @param array $p Параметры шаблона
	 * @return string */
	public function _($n,array$p)
	{
		return parent::_($n,$p,'CMS\Templates\\'.$this->subns);
	}
}

Eleanor::$Template=new Template;

/** Поддержка языков. Здесь находится аналогично шаблонизатору */
class Language extends \Eleanor\Classes\Language
{
	/** Изменение языка. Все языковые файлы будут перезагружены.
	 * @param string $lang Имя нового системного языка
	 * @throws EE */
	public function Change($lang)
	{
		if(!isset(Eleanor::$langs[$lang]))
			throw new EE('Unknown language '.$lang,EE::DEV);

		static::$main=$lang;
		$loc=Eleanor::$langs[$lang]['l'];

		setlocale(LC_TIME,$loc);
		setlocale(LC_COLLATE,$loc);
		setlocale(LC_CTYPE,$loc);

		if($lang!=$this->name)
			parent::Change($lang);
	}
}

Eleanor::$Language=new Language(true);
Eleanor::$Language->source=__DIR__.'/translation/';

/** Локальная версия класса для подключения к базам данных MySQL. Добавлен подсчет запросов и запись в дебаглист. */
class MySQL extends FClasses\MySQL
{
	public
		/** @var int Число запросов */
		$queries=0;

	/** Выполнение SQL запроса в базу
	 * @param string|array $q SQL запрос (в случае array, будет использовано multi_query)
	 * @param int $mode
	 * @throws FClasses\EE_DB
	 * @return bool|\mysqli_result|\mysqli */
	public function Query($q,$mode=MYSQLI_STORE_RESULT)
	{
		if(Eleanor::$debug)
		{
			$d=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);
			$debug=['e'=>$q];

			if(isset($d[0]['file'],$d[0]['line']))
				$debug+=[ 'f'=>$d[0]['file'],'l'=>$d[0]['line'] ];

			$timer=microtime(true);
		}

		$R=parent::Query($q,$mode);
		++$this->queries;

		if(Eleanor::$debug)
		{
			$debug['t']=microtime(true)-$timer;
			Template::$debug[]=$debug;
		}

		return$R;
	}
}

/** Загрузка настроек
 * @param string|array $need Ключевое слово групп настроек, которые должны быть загружены
 * @param bool $ret Флаг возврата настроек. В случае FALSE, настройки будут помещены в массив Eleanor::$vars
 * @param bool $cache Флаг включения кэширования настроек
 * @return array */
function LoadOptions($need,$ret=false,$cache=true)
{
	$need=(array)$need;
	$lgetted=$getted=[];

	if($cache)
		foreach($need as $k=>$key)
			if($cached=Eleanor::$Cache->Get('config-'.$key))
			{
				unset($need[$k]);

				foreach($cached['v'] as $ck=>$cv)
				{
					$getted[$ck]=$cv;
					$lgetted[$ck]=in_array($ck,$cached['m']);
				}
			}

	if($need)
	{
		$regex=[];
		foreach($need as $key)
			$regex[]=','.preg_quote(Eleanor::$Db->Escape($key,false)).',';

		$regex=join('|',$regex);
		$ml=$config=$tocache=[];
		$oldid=0;
		$oldgname='';
		$table=P.'config';
		$table_l=P.'config_l';
		$table_gr=P.'config_groups';

		$R=Eleanor::$Db->Query("SELECT `o`.`id`, `o`.`name`, `l`.`value`, `l`.`json`, `l`.`language`,
`o`.`multilang`, `g`.`name` `gname`, `g`.`keyword` FROM `{$table}` `o` INNER JOIN `{$table_l}` `l` USING(`id`)
INNER JOIN `{$table_gr}` `g` ON `g`.`id`=`o`.`group` WHERE `g`.`keyword` REGEXP '{$regex}' ORDER BY `o`.`id` ASC");
		while($option=$R->fetch_assoc())
		{
			if($option['json'])
				$option['value']=json_decode($option['value'],true);

			if($oldid==$option['id'])
			{
				if($option['multilang'])
					$tocache[ $option['gname'] ][ $option['name'] ][ $option['language'] ]=$option['value'];

				if($option['multilang'] or $option['language']!=Language::$main)
					continue;
			}

			$oldid=$option['id'];

			if($oldgname!=$option['gname'])
			{
				$oldgname=$option['gname'];
				$keyword=$option['keyword'] ? explode(',',trim($option['keyword'],',')) : [];

				foreach($keyword as $key)
					$config[$key][]=$option['gname'];
			}

			$tocache[ $option['gname'] ][ $option['name'] ]=$option['multilang']
				? [ $option['language']=>$option['value'] ]
				: $option['value'];

			if($option['multilang'])
				$ml[ $option['gname'] ][ $option['name'] ]=true;
		}

		foreach($config as $regex=>$key)
		{
			$putcache=['v'=>[],'m'=>[]];

			foreach($key as $grname)
				foreach($tocache[$grname] as $ck=>$cv)
				{
					if(!isset($putcache['v'][$ck]))
					{
						$putcache['v'][$ck]=$cv;

						if(isset($ml[$grname][$ck]))
							$putcache['m'][]=$ck;
					}

					$getted[$ck]=$cv;
					$lgetted[$ck]=isset($ml[$grname][$ck]);
				}

			Eleanor::$Cache->Put('config-'.$regex,$putcache);
		}
	}

	if($ret)
	{
		foreach($getted as $k=>&$key)
			if($lgetted[$k])
				$key=FilterLangValues($key);

		return$getted;
	}

	foreach($getted as $k=>$key)
		if($lgetted[$k])
		{
			Eleanor::$lvars[$k]=$key;
			Eleanor::$vars[$k]=FilterLangValues($key);
		}
		else
			Eleanor::$vars[$k]=$key;
}

/** Установка системных куки
 * @param string $n Имя куки
 * @param string $v Значение куки
 * @param int|null $ttl Время жизни куки в формате \d+[tsmMhd]?, где t - точный TIMESTAMP умирания куки,
 * s - секунды, m - минуты, h - часы, d (по умолчанию) - дни, M - месяцы
 * @param bool $safe Флаг httponly (не обольщайтесь, браузеры дырявые)
 * @return bool */
function SetCookie($n,$v='',$ttl=null,$safe=false)
{
	if($v=='')
	{
		$v=false;
		$ttl=1;
	}
	elseif($ttl===null)
		$ttl=$v ? 31536000+\time() : 0;#1 год
	elseif($ttl)
		do
		{
			switch(substr($ttl,-1))
			{
				case't':
					$ttl=(int)$ttl;
				break 2;
				case'M':
					$ttl=\strtotime('+ '.(int)$ttl.' MONTH');
				break 2;
				case's':
					$ttl=(int)$ttl;
				break;
				case'm':
					$ttl=(int)$ttl*60;
				break;
				case'h':
					$ttl=(int)$ttl*3600;
				break;
				default:#Days...
					$ttl=(int)$ttl*86400;
			}

			$ttl+=time();
		}while(false);

	static$domain;

	if(!isset($domain))
	{
		$domain=\Eleanor\PUNYCODE;

		#Заплатка для браузеров FF & IE, когда они не хотят воспринимать куки с доменов первого уровня аля localhost
		if(strpos($domain,'.')===false)
			$domain='';
		#Заплатка для оперы и ко, которые не хотят воспринимать куки с IP адресов
		elseif(strpos($domain,':')!==false or filter_var($domain, FILTER_VALIDATE_IP))
			$domain='';
	}

	return\setcookie(Eleanor::$cookie.$n,$v,$ttl,\Eleanor\SITEDIR,$domain,\Eleanor\PROTOCOL=='https://',$safe);
}

/** Получение системных куки
 * @param string $n Имя
 * @return mixed */
function GetCookie($n)
{
	$n=Eleanor::$cookie.$n;
	return isset($_COOKIE[$n]) ? $_COOKIE[$n] : false;
}

/** Получение языкового значения из массива со значениями для разных языков
 * @param array $a Языковые значения. В качестве ключей - названия языков и ключ пустую строку для умолчания
 * @param string|null $l Название языка, если передано FALSE, будет использован системный язык
 * @param mixed $d Значение по умолчанию, будет возвращено если значение нужного языка не будет найдено
 * @return mixed */
function FilterLangValues(array$a,$l=null,$d=null)
{
	if(!$l)
		$l=Language::$main;

	if(isset($a[$l]))
		return$a[$l];

	if(isset($a['']))
		return$a[''];

	return isset($a[0]) ? $a[0] : $d;
}

/** Установка шаблона
 * @param string $tpl Название шаблона
 * @throws EE */
function SetTemplate($tpl)
{
	if(!$tpl or !isset(Template::$path['templates']) or !is_dir(Template::$path['templates'].$tpl))
		throw new EE("Template {$tpl} was not found",EE::ENV);

	$T=Template::$path['templates'].$tpl.'/run.php';
	$T=is_file($T) ? include$T : false;

	if(!is_object($T) or !($T instanceof Template))
		throw new EE("Template {$tpl} is not supported",EE::DEV);

	Eleanor::$Template=$T;
}

/** Проверка соответствия IP адреса заданной маске. Поодерживаются IPv4 и IPv6, диапазоны IP адресов и подсетей IP.
 * Примеры: IPMatchMask('192.168.100.100','192.168.100.x'), IPMatchMask('192.168.100.100','192.168.100.50-192.168.100.150'),
 * IPMatchMask('192.168.100.100','192.168.100.0/16')
 * @param string $ip IP адрес, который проверяется
 * @param string $m Маска, диапазон, диапазон с маской, подсеть
 * @return bool */
function IPMatchMask($ip,$m)
{
	$m=trim($m);

	if(strpbrk($ip,':.')===false)
		$ip=inet_ntop($ip);

	if($ipv6=strpos($ip,':')!==false)
	{
		if(strpos($m,':')===false)
			return false;

		$n=substr_count($ip,':')-2;
		$r=str_repeat(':0000',8-$n-2);
		$ip=str_replace('::',$r.':',$ip);
	}

	if(strpos($m,'-')===false)
	{
		if($ipv6 and false!==$p=strpos($m,'::'))
		{
			$n=substr_count($m,':')-2-($p==0);
			$r=str_repeat(':0000',8-$n-2);
			$m=trim(str_replace('::',$r.':',$m),':');
		}

		if(strpos($m,'/')!==false)
		{
			$m=explode('/',$m,2);
			$bm=$bip='';

			if($ipv6)
			{
				$m[0]=explode(':',$m[0]);
				$ip=explode(':',$ip);

				foreach($m[0] as &$v)
					$bm.=sprintf('%08b',hexdec($v));

				foreach($ip as &$v)
					$bip.=sprintf('%08b',hexdec($v));
			}
			else
			{
				$m[0]=explode('.',$m[0]);
				$ip=explode('.',$ip);

				foreach($m[0] as &$v)
					$bm.=sprintf('%08b',$v);

				foreach($ip as &$v)
					$bip.=sprintf('%08b',$v);
			}

			return strncmp($bm,$bip,(int)$m[1])==0;
		}

		$m=str_replace('\*','[a-f0-9]{1,4}',preg_quote($m,'#'));

		return preg_match('#^'.$m.'$#',$ip)>0;
	}
	else
	{
		$m=explode('-',$m,2);

		if($ipv6)
		{
			if(false!==$p=strpos($m[1],'::'))
			{
				$n=substr_count($m[1],':')-2-($p==0);
				$r=str_repeat(':0000',8-$n-2);
				$m[1]=trim(str_replace('::',$r.':',$m[1]),':');
			}

			if(false!==$p=strpos($m[0],'::'))
			{
				$n=substr_count($m[0],':')-2-($p==0);
				$r=str_repeat(':0000',8-$n-2);
				$m[0]=trim(str_replace('::',$r.':',$m[0]),':');
			}

			$mto=explode(':',$m[1],8);
			$m=explode(':',$m[0],8);
			$ip=explode(':',$ip,8);

			foreach($m as &$v)
				$v=hexdec($v);

			$m=join('',$m);

			foreach($mto as &$v)
				$v=hexdec($v);

			$mto=join('',$mto);

			foreach($ip as &$v)
				$v=hexdec($v);

			$ip=implode($ip);
		}
		else
		{
			$mto=explode('.',str_replace('*',255,$m[1]),4);
			$m=explode('.',str_replace('*',0,$m[0]),4);
			$ip=explode('.',$ip,4);

			foreach($m as &$v)
				$v=sprintf('%03d',$v);

			$m=ltrim(join('',$m),'0');

			foreach($mto as &$v)
				$v=sprintf('%03d',$v);

			$mto=ltrim(join('',$mto),'0');

			foreach($ip as &$v)
				$v=sprintf('%03d',$v);

			$ip=ltrim(join('',$ip),'0');
		}
		return bccomp($ip,$m)>=0 && bccomp($mto,$ip)>=0;
	}
}

/** Запись пользовательской активности в таблицу сессии. Используется для списка "кто онлайн" */
function SetSession()
{
	if(!isset(Eleanor::$vars['time_online']))
		LoadOptions('users-on-site');

	$uid=isset(Eleanor::$Login) ? Eleanor::$Login->Get('id') : false;
	$ua=getenv('HTTP_USER_AGENT');

	$info=[
		'r'=>getenv('HTTP_REFERER'),
		'c'=>getenv('HTTP_ACCEPT_CHARSET'),
		'e'=>getenv('HTTP_ACCEPT_ENCODING'),
	];

	if(Eleanor::$ips)
		$info['ips']=Eleanor::$ips;

	$insert=Eleanor::$Db->GenerateInsert([
		'type'=>$uid ? 'user' : (Eleanor::$bot ? 'bot' : 'guest'),
		'user_id'=>(int)$uid,
		'!enter'=>'NOW()',
		'!expire'=>'\''.date('Y-m-d H:i:s').'\' + INTERVAL '
			.(isset(Eleanor::$vars['time_online'][ Eleanor::$service ])
				? (int)Eleanor::$vars['time_online'][ Eleanor::$service ]
				: 900).' SECOND',
		($uid ? 'ip_user' : 'ip_guest')=>Eleanor::$ip,
		'info'=>json_encode($info,JSON),
		'service'=>Eleanor::$service,
		'browser'=>$ua,
		'location'=>Url::Decode(preg_replace('#^'.preg_quote(\Eleanor\SITEDIR,'#').'#','',$_SERVER['REQUEST_URI'])),
		'name'=>(string)Eleanor::$bot,
		'extra'=>Eleanor::$extra,
	]);
	$table=P.'sessions';
	$date=date('Y-m-d H:i:s');

	Eleanor::$Db->Query("INSERT INTO `{$table}` {$insert} ON DUPLICATE KEY UPDATE `enter`=GREATEST(`enter`,'{$date}'), `hits`=`hits`+1,
`expire`=VALUES(`expire`), `info`=VALUES(`info`), `browser`=VALUES(`browser`), `location`=VALUES(`location`),
`extra`=VALUES(`extra`)");
}

/** Установка сервиса. Сервис - это файл, с которого произведен запуск системы: index.php, admin.php и т.п.
 * @param string $name Имя сервиса: index, admin
 * @throws EE */
function SetService($name)
{
	if(!isset(Eleanor::$services[ $name ]))
		throw new EE('Unknown service '.$name,EE::DEV);

	$service=Eleanor::$services[ $name ];
	$runfile=basename($_SERVER['PHP_SELF']);

	if($service['file']!=$runfile)
	{
		Eleanor::$Db->Update(P.'services',['file'=>$runfile],'`name`=\''.$name.'\' LIMIT 1');
		Eleanor::$Cache->Obsolete('system-services');
	}

	if($service['login'])
	{
		if(!isset(Eleanor::$vars['time_online']))
			LoadOptions('users-on-site');

		$class='\CMS\Logins\\'.$service['login'];

		Eleanor::$Login=new$class;
		Eleanor::$Permissions=new Permissions(Eleanor::$Login);
	}
	else
		Eleanor::$Login=Eleanor::$Permissions=null;

	if(isset(Eleanor::$Login) and Eleanor::$Login->IsUser())
	{
		if(Eleanor::$vars['multilang'] and $l=Eleanor::$Login->Get('language') and Language::$main!=$l
			and isset(Eleanor::$langs[$l]))
			Eleanor::$Language->Change($l);

		if($tz=Eleanor::$Login->Get('timezone') and in_array($tz,\timezone_identifiers_list()))
		{
			\date_default_timezone_set($tz);
			Eleanor::$Db->SyncTimeZone();

			if(Eleanor::$UsersDb!==Eleanor::$Db)
				Eleanor::$UsersDb->SyncTimeZone();
		}

		if(!Eleanor::$Permissions->IsAdmin() and $banned=Eleanor::$Login->Get('banned') and 0<strtotime($banned)-time())
		{
			$explain=Eleanor::$Login->Get('ban_explain');
			Eleanor::$ban=[ 'type'=>'user', 'explain'=>$explain, 'term'=>$banned ];
		}
	}

	Eleanor::$service=$name;
}

/** Получение модулей: имен и секций => id модуля
 * @param string|null $s Название сервиса системы
 * @param string|null $l Язык
 * @param bool $hard Флаг регенерации кэша
 * @return array */
function GetModules($s=null,$l=null,$hard=false)
{
	if(!$s)
		$s=Eleanor::$service;

	if(!$l)
		$l=Language::$main;

	$cache='modules_'.$s.(Eleanor::$vars['multilang'] ? '_'.$l : '');

	if($hard or false===$m=Eleanor::$Cache->Get($cache,false))
	{
		$na=$sa=$modules=[];
		$table=P.'modules';
		$R=Eleanor::$Db->Query("SELECT `id`,`services`,`uris`,`title_l` `title`,`path`,`file`,`miniature_type`,`miniature`,`api`,`config` FROM `{$table}` WHERE `status`=1");
		while($module=$R->fetch_assoc())
		{
			$module['uris']=$module['uris'] ? json_decode($module['uris'],true) : [];

			if($s and $module['services'] and strpos($module['services'],','.$s.',')===false or !$module['uris'])
				continue;

			foreach($module['uris'] as $k=>&$lang)
			{
				if(isset($lang['']))
				{
					$na+=array_fill_keys($lang[''],$module['id']);
					$sa+=array_fill_keys($lang[''],$k);
				}

				if(isset($lang[$l]))
				{
					$na+=array_fill_keys($lang[$l],$module['id']);
					$sa+=array_fill_keys($lang[$l],$k);
				}

				if(isset($lang[$l]))
					$lang=reset($lang[$l]);
				elseif(isset($lang['']))
					$lang=reset($lang['']);
				else
					$lang=null;
			}

			$module['title']=$module['title'] ? FilterLangValues(json_decode($module['title'],true),$l,'') : '';
			$modules[ $module['id'] ]=array_slice($module,2);
		}

		$m=['uri2id'=>$na,'uri2section'=>$sa,'id2module'=>$modules];

		Eleanor::$Cache->Put($cache,$m,false);
	}

	return$m;
}

/** Получение групп, в которых состоит пользователь
 * @return array */
function GetGroups()
{
	if(isset(Eleanor::$Login) and Eleanor::$Login->IsUser())
	{
		$groups=Eleanor::$Login->Get('groups');
		return is_array($groups) ? $groups : explode(',',$groups);
	}

	return Eleanor::$bot ? (array)Eleanor::$vars['bot_group'] : (array)Eleanor::$vars['guest_group'];
}