<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor;

/** Кодировка файлов */
define('Eleanor\CHARSET','utf-8');
define('Eleanor\UTF8',true);
mb_internal_encoding(CHARSET);

/** Путь к сайту, относительно домена */
defined('Eleanor\SITEDIR')||define('Eleanor\SITEDIR',isset($_SERVER['PHP_SELF']) ? rtrim(dirname($_SERVER['PHP_SELF']),'/\\').'/' : '/');

/** Протокол доступа */
defined('Eleanor\PROTOCOL')||define('Eleanor\PROTOCOL',isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://');

/** Тут есть некоторые глюки... */
define('Eleanor\W',stripos(PHP_OS,'win')===0);

/** Базовый класс, от которого рекомендуется наследовать все остальные: содержит необходимые заглушки, облегчающие поиск
 * багов и их исправление */
abstract class BaseClass
{
	/** Получение местоположения ошибки в коде: файл + строка
	 * @param array $d Дамп стека вызова при помощи функции debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1)
	 * @return array */
	public static function _BT($d)
	{
		return isset($d[0]['file'],$d[0]['line']) ? [ 'file'=>$d[0]['file'],'line'=>$d[0]['line'] ] : [];
	}

	/** Обработка ошибочных вызовов несуществующих статических методов.
	 * Наличие этого метода может показаться странным: ведь если вызвать несуществующий статический метод, будет
	 * сгенерирован Fatal error, который можно отловить и залогировать. Но удобство метода проявляется в классе
	 * наследнике с методом __callStatic который не может выполнить все вызываемые методы.
	 * @param string $n Название несуществующего метода
	 * @param array $p Массив входящих параметров вызываемого метода
	 * @throws \Eleanor\Classes\EE
	 * @return null */
	public static function __callStatic($n,$p)
	{
		$E=new Classes\EE(
			'Called undefined method '.get_called_class().' :: '.$n,
			Classes\EE::DEV,static::_BT(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1))
		);

		if(Framework::$debug)
			throw$E;

		$E->Log();
	}

	/** Обработка ошибочных вызовов несуществующих методов.
	 * Наличие этого метода может показаться странным: ведь если вызвать несуществующий метод объекта, будет
	 * сгенерирован Fatal error, который можно отловить и залогировать. Но удобство метода проявляется в классе
	 * наследнике с методом __call который не может выполнить все вызываемые методы.
	 * @param string $n Название несуществующего метода
	 * @param array $p Массив входящих параметров вызываемого метода
	 * @throws \Eleanor\Classes\EE
	 * @return mixed */
	public function __call($n,$p)
	{
		if(property_exists($this,$n) and is_object($this->$n) and method_exists($this->$n,'__invoke'))
			return call_user_func_array([ $this->$n,'__invoke' ],$p);

		$E=new Classes\EE(
			'Called undefined method '.get_class().' -› '.$n,
			Classes\EE::DEV,static::_BT(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1))
		);

		if(Framework::$debug)
			throw$E;

		$E->Log();
	}

	/** Обработка получения несуществующих свойств
	 * Наличие этого метода может показаться странным: поскольку, при попытке получить неопределенное свойство
	 * генерируется Notice, который можно отловить и залогировать. Но удобство метода проявляется в классе наследнике с
	 * методом __get, который может вернуть не все запрашиваемые свойства.
	 * @param string $n Имя запрашиваемого свойства
	 * @throws \Eleanor\Classes\EE
	 * @return null */
	public function __get($n)
	{
		if(is_array($n))
		{
			$d=$n;
			$n=func_get_arg(1);
		}
		else
			$d=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);

		$E=new Classes\EE(
			'Trying to get value from the unknown variable '.get_class($this).' -› '.$n,	Classes\EE::DEV,
			static::_BT($d)
		);

		if(Framework::$debug)
			throw$E;

		$E->Log();
	}
}

/** Основной класс фреймворка Eleanor */
class Framework extends BaseClass
{
	/** Пространство имен из-под которого будут работать функции __call и __get */
	const NS='\Eleanor\Classes\\';

	public static
		/** @var bool Включить режим отладки */
		$debug=false,

		/** @var callable Предыдущий обработчик ошибок */
		$old_errh,

		/** @var callable Предыдущий перехватчик исключений */
		$old_exch,

		/** @var string Тип страницы и информацией об ошибке: html, text, json . Своеобразный BSOD*/
		$bsodtype='html',

		/** @var bool Флаг включения логирования всех ошибок */
		$logall=true,

		/** @var bool Флаг включения логирования всех исключений */
		$catchall=true,

		/** @var bool Флаг включения режима логирования */
		$logs=true,

		/** @var string Путь к каталогу, в который будут помещаться логи */
		$logspath;

	/** Упрощенный конструктор встроенных классов
	 * @param string $n Название класса
	 * @param array $p Массив входящих параметров конструктора
	 * @return mixed */
	public function __call($n,$p)
	{
		if(property_exists($this,$n) and is_object($this->$n) and method_exists($this->$n,'__invoke'))
			return call_user_func_array([$this->$n,'__invoke'],$p);

		$nn=static::NS.$n;

		if(class_exists($nn))
		{
			if($p)
			{
				$toeval='';

				foreach($p as $k=>$v)
					$toeval.='$p['.$k.'],';

				return$this->$n=eval('return new $nn('.rtrim($toeval,',').');');
			}

			return$this->$n=new$nn;
		}

		parent::__get(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1),$n);
	}

	/** Метод быстрого создания объектов классов
	 * @param string $n Имя класса
	 * @return mixed */
	public function __get($n)
	{
		$nn=static::NS.$n;
		if(class_exists($nn))
			return$this->$n=new$nn;

		parent::__get(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1),$n);
	}
}

Framework::$logspath=$_SERVER['DOCUMENT_ROOT'].SITEDIR.'logs/';

/** Перехватчик ошибок. Данная функция не является анонимной, поскольку она используется в AwareInclude
 * @param int $num Номер ошибки
 * @param string $error Описание ошибки
 * @param string $f Путь к файлу
 * @param int $l Номер строки
 * @param array|null $context Дамп переменных окружения
 * @throws Classes\EE */
function ErrorHandler($num,$error,$f,$l,$context=null)
{
	#Возможно, ошибку должен был залогировать предыдущий скрипт
	if(!Framework::$logall or Framework::$old_errh and strpos($f,__DIR__.DIRECTORY_SEPARATOR)!==0)
		call_user_func(Framework::$old_errh,$num,$error,$f,$l,$context);

	/* Я на ХУЮ вертел ошибки типа Declaration of ... should be compatible with ... + Заплатка на случай отключенного
	 * автолоадера */
	elseif($num&~E_STRICT and Framework::$logs and class_exists('\Eleanor\Classes\EE'))#
	{
		#Ошибки E_ERROR и E_PARSE могут быть переданы в эту функцию через trigger_error
		$ae=[ E_ERROR=>'Error', E_WARNING=>'Warning', E_NOTICE=>'Notice', E_PARSE=>'Parse error', ];

		$E=new Classes\EE(
			(isset($ae[$num]) ? $ae[$num].': ' : '['.$num.']:').$error,
			Classes\EE::DEV,
			[ 'file'=>$f, 'line'=>$l, 'context'=>$context ]
		);

		if(Framework::$debug and !(E_PARSE&$num))
			throw$E;

		$E->Log();

		if($num&E_USER_ERROR)
			BSOD($error);
	}
}
Framework::$old_errh=set_error_handler('\Eleanor\ErrorHandler',E_ALL);

Framework::$old_exch=set_exception_handler(function($E){
	/** @var Classes\EE $E */
	$m=$E->getMessage();

	if($E instanceof Classes\EE)
		$E->Log();
	elseif((Framework::$catchall or strpos($E->getFile(),__DIR__.DIRECTORY_SEPARATOR)===0)
		#Заплатка на случай отключенного автолоадера
		and (class_exists('\Eleanor\Classes\EE',false) or include(__DIR__.'/classes/ee.php')))
	{
		$E2=new Classes\EE($m,Classes\EE::UNIT,[],$E);
		$E2->Log();
	}

	$extra=isset($E->extra) ? $E->extra : [];
	BSOD($m,$extra+['line'=>$E->getLine(),'file'=>$E->getFile()]);
});

#Реализация своей автозагрузки: загружаем только, что напрямую относится к Eleanor Framework
spl_autoload_register(function($cl){
	if(strpos($cl,__NAMESPACE__)===0)
	{
		$lccl=strtolower($cl);#LowerCase class
		$lccl=explode('\\',$lccl,2);

		#Вдруг кто-то из глобальной области пытается зазгрузить класс Eleanor?
		if(!isset($lccl[1]))
			return;

		$lccl=$lccl[1];#Уберем неймспейс Eleanor
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
				default:
					$what='Class';
			}

			if(class_exists('\Eleanor\Classes\EE',false) or include(__DIR__.'/classes/ee.php'))
			{
				$bt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

				foreach($bt as $db)
					if(isset($db['file'],$db['line']) and (!isset($db['function']) or $db['function']!='class_exists'))
						break;

				throw new Classes\EE($what.' not found: '.$cl,Classes\EE::DEV,$db);
			}
		}
	}
});

/** Функция безопасного подключения файла: в случае ParseError-a, будет создан лог
 * @param string $file Полный путь к файлу, который нужно проинклудить
 * @param array $vars Переменные для файла в его области видимости
 * @throws Classes\EE
 * @return mixed */
function AwareInclude($file,array$vars=[])
{
	if(is_file($file))
	{
		#Флаг обработки ошибки
		$skip=false;

		register_shutdown_function(function()use(&$skip){
			if($skip)
				return;

			$e=error_get_last();
			if($e && ($e['type'] & (E_ERROR|E_PARSE|E_COMPILE_ERROR|E_CORE_ERROR)))
			{
				$c=ob_get_contents();
				if($c!==false)
					ob_end_clean();

				ErrorHandler($e['type'],$e['message'],$e['file'],$e['line']);

				BSOD($e['message'],[ 'file'=>$e['file'], 'line'=>$e['line'] ]);
			}
			else
				ob_end_flush();
		},$file,$vars);
		ob_start();

		if($vars)
			extract($vars,EXTR_PREFIX_INVALID|EXTR_OVERWRITE,'var');

		$r=include func_get_arg(0);

		ob_end_flush();
		$skip=true;#Эта переменная используется!

		return$r===null ? true : $r;
	}
	else
		throw new Classes\EE('Missing file '.(strpos($file,SITEDIR)===0 ? substr($file,strlen(SITEDIR)) : $file),Classes\EE::ENV);
}

/** "Тихое" выполнение кода, когда отключается показ ошибок
 * @param callback $Func
 * @return mixed */
function QuitExecute($Func)
{
	set_error_handler(function(){});
	$ret=call_user_func($Func);
	restore_error_handler();
	return$ret;
}

/** Обертка для создания сессии
 * @param string $id Идентификатор сессии, возможно, сессия будет создана наново
 * @param string $n Имя сессии */
function StartSession($id='',$n='')
{
	ini_set('session.use_cookies',0);
	ini_set('session.use_trans_sid',0);

	if(isset($_SESSION))
	{
		if(session_id()==$id and !$n || session_name()==$n)
			return;

		session_write_close();
	}

	if($n and preg_match('#^[a-z0-9]+$#i',$n)>0)
		session_name($n);

	if($id and preg_match('#^[a-z0-9,\-]+$#i',$id)>0)
		session_id($id);

	session_start();
}

/** Системный BSOD
 * @param string $error Текст ошибки
 * @param array $extra Дополнительные параметры */
function BSOD($error,array$extra=[])
{
	$Tpl=new Classes\Template(__DIR__.'/template.php');
	$out=(string)$Tpl->BSOD(Framework::$bsodtype,$error,$extra);

	Classes\OutPut::SendHeaders(Framework::$bsodtype,503);
	Classes\Output::Gzip($out);

	die;
}

#Поддержка IDN
define('Eleanor\PUNYCODE',isset($_SERVER['HTTP_HOST']) && preg_match('#^[a-z0-9\-\.]+$#i',$_SERVER['HTTP_HOST'])>0
	? $_SERVER['HTTP_HOST'] : 'eleanor-cms.ru');
define('Eleanor\DOMAIN',strpos(PUNYCODE,'xn--')===false ? PUNYCODE : Classes\Punycode::Domain(PUNYCODE,false));
