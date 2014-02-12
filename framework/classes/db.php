<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Библиотека для работы с MySQL, с использованием драйвера MySQLi
*/
namespace Eleanor\Classes;
use Eleanor;

class Db extends Eleanor\BaseClass
{
	public
		/** @property \MySQLi $Driver Объект \MySQLi */
		$Driver,
		/** @property string $db Название базы данных, с которой мы работаем */
		$db;

	/**
	 * Соединение с БД
	 * @param array|\MySQLi $p Объект MySQLi или параметры соединения с БД. Ключи массива:
	 *   host Сервер БД.
	 *   user Пользователь БД
	 *   pass Пароль пользоваетля
	 *   db Название базы данных
	 * @throws EE_DB
	 */
	public function __construct($p)
	{
		if(is_object($p) and $p instanceof \MySQLi)
			$this->Driver=$p;
		else
		{
			if(!isset($p['host'],$p['user'],$p['pass'],$p['db']))
				throw new EE_DB('connect',debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1),$p);

			$M=new \MySQLi($p['host'],$p['user'],$p['pass'],$p['db']);

			if($M->connect_errno or !$M->server_version)
				throw new EE_DB('connect',debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1),
								$p+['error'=>$M->connect_error,'errno'=>$M->connect_errno]);

			$M->autocommit(true);
			$M->set_charset(Eleanor\CHARSET=='utf-8' ? 'uft8' : Eleanor\CHARSET);

			$this->Driver=$M;
			$this->db=$p['db'];
		}
	}

	/**
	 * Обертка для упрощенного доступа к методам объектов MySQLi и результата MySQLi
	 * @param string $n Имя вызываемого метода
	 * @param array $p Параметры вызова
	 * @return mixed
	 */
	public function __call($n,$p)
	{
		if(method_exists($this->Driver,$n))
			return call_user_func_array(array($this->Driver,$n),$p);

		return parent::__call($n,$p);
	}

	/**
	 * Синхронизация времени БД со временем PHP (применение часового пояса). Синхронизируются только поля типа TIMESTAMP.
	 */
	public function SyncTimeZone()
	{
		$t=date_offset_get(date_create());#PhpStorm не знает что date_offset_get возвращает int
		$s=$t>0 ? '+' : '-';
		$t=abs($t);
		$s.=floor($t/3600).':'.($t%3600);
		$this->Driver->query('SET TIME_ZONE=\''.$s.'\'');
	}

	/**
	 * Старт транзакции
	 */
	public function Transaction()
	{
		$this->Driver->autocommit(false);
	}

	/**
	 * Подтверждение транзакции
	 */
	public function Commit()
	{
		$this->Driver->commit();
		$this->Driver->autocommit(true);
	}

	/**
	 * Откат транзакции
	 */
	public function RollBack()
	{
		$this->Driver->rollback();
		$this->Driver->autocommit(true);
	}

	/**
	 * Выполнение SQL запроса в базу
	 * @param string|array $q SQL запрос (в случае array, будет использовано multi_query)
	 * @param int $mode
	 * @throws EE_DB
	 * @return bool|\mysqli_result|\mysqli
	 */
	public function Query($q,$mode=MYSQLI_STORE_RESULT)
	{
		$isa=is_array($q);
		if($isa)
			$q=join(';',$q);

		if($isa)
		{
			$R=$this->Driver->multi_query($q);
			$return_r=false;
		}
		elseif($mode===false)
		{
			$R=$this->Driver->real_query($q);
			$return_r=false;
		}
		else
		{
			$R=$this->Driver->query($q,$mode);
			$return_r=$mode!=MYSQLI_ASYNC;
		}

		if($R===false)
		{
			if($q)
			{
				$e=$this->Driver->error;
				$en=$this->Driver->errno;
			}
			else
			{
				$e='Empty query';
				$en=0;
			}
			throw new EE_DB('query',debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1),
								['error'=>$e,'errno'=>$en,'query'=>$q]);
		}

		return$return_r ? $R : $this->Driver;
	}

	/**
	 * Обертка для удобного осуществления INSERT запросов
	 * @param string $t Имя таблицы, куда необходимо вставить данные
	 * @param array $a Данные. Все данные автоматически экранируются. Для отключения, перед именем поля поставьте !.
	 * Поддерживаются 3 формата вставки:
	 * 1. Одиночная: [ 'field1'=>'value1', 'field2'=>'value2' ]
	 * 2. Множественая: [ 'field1'=>[ 'values11', 'value12' ], 'field2'=>[ 'value21', 'value22' ] ]
	 * 3. Моножественная: [ ['field1'=>'value11', 'field2'=>'value12' ], ['field1'=>'value21', 'field2'=>'value22' ]  ]
	 * При использовании 3го формата, ключи внутренних массивов должны быть ИДЕНТИЧНЫМИ.
	 * @param string $type Тип INSERT запроса
	 * @return int Insert ID
	 */
	public function Insert($t,array$a,$type='IGNORE')
	{
		$this->Query('INSERT '.$type.' INTO `'.$t.'`'.$this->GenerateInsert($a));

		return$this->Driver->insert_id;
	}

	/**
	 * Обертка для удобного осуществления REPLACE запросов
	 * @param string $t Имя таблицы, куда необходимо вставить данные
	 * @param array $a Данные. Идентично методу Insert
	 * @param string $type Тип REPLACE запроса
	 * @return int Affected rows
	 */
	public function Replace($t,array$a,$type='')
	{
		$this->Query('REPLACE '.$type.' INTO `'.$t.'` '.$this->GenerateInsert($a));

		return$this->Driver->affected_rows;
	}

	/**
	 * Генерация INSERT запроса из данных в массиве
	 * @param array $a Массив даных из метода Insert или Replace, например:
	 * [ 'field1'=>'value1', 'field2'=>'value2' ] или
	 * [ 'field1'=>[ 'values11', 'value12' ], 'field2'=>[ 'value21', 'value22' ] ] или
	 * [ ['field1'=>'value11', 'field2'=>'value12' ], ['field1'=>'value21', 'field2'=>'value22' ]  ]
	 * @return string
	 */
	public function GenerateInsert(array$a)
	{
		$rk=$rv='';#result key & result value
		$k=key($a);

		if(is_int($k))
		{
			$ks=[];

			foreach($a as $v)
			{
				if(!$rk)
				{
					foreach($v as $vk=>$_)
					{
						$ks[]=$vk=ltrim($vk,'!');
						$rk.='`'.$vk.'`,';
					}

					$rk='('.rtrim($rk,',').')VALUES';
				}

				$rv='(';

				foreach($ks as $ksk=>$ksv)
					if(isset($v[$ksv]))
						$rv.=$this->Escape($v[$ksv]).',';
					elseif(isset($v['!'.$ksv]))
						$rv.=$v['!'.$ksv].',';
					elseif(isset($v[$ksk]))
						$rv.=$this->Escape($v[$ksk]).',';
					else
						$rv.='NULL,';

				$rk.=rtrim($rv,',').'),';
			}

			return rtrim($rk,',');
		}

		$va=[];#values array
		$isa=true;

		foreach($a as $k=>&$v)
		{
			if($k[0]=='!')
			{
				$safe=false;
				$k=substr($k,1);
			}
			else
				$safe=true;

			if(is_array($v))
			{
				foreach($v as $vk=>$vv)
					$va[$vk][]=$safe ? $this->Escape($vv) : $vv;

				$v='Array';#В этом случае генерируется ошибка. Зато сразу понятно, что идет не так.
			}
			else
			{
				$isa=false;

				if($safe)
					$v=$this->Escape($v);
			}

			$rv.=$v.',';
			$rk.='`'.$k.'`,';
		}

		if($va and $isa)
		{
			foreach($va as &$v)
				$v=join(',',$v);

			$rv='('.join('),(',$va).')';
		}
		else
			$rv='('.rtrim($rv,',').')';

		return'('.rtrim($rk,',').') VALUES '.$rv;
	}

	/**
	 * Обертка для удобного осуществления UPDATE запросов
	 * @param string $t Имя таблицы, где необходимо обновить данные
	 * @param array $a Массив изменямых данных. Все данные автоматически экранируются. Если экранирование не нужно,
	 * перед именем поля поставьте !. Например: array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field5'=>'NOW()')
	 * @param string $w Условие обновления. Секция WHERE, без ключевого слова WHERE.
	 * @param string $type
	 * @return int Affected rows
	 */
	public function Update($t,array$a,$w='',$type='IGNORE')
	{
		$q='UPDATE '.$type.' `'.$t.'` SET ';

		foreach($a as $k=>$v)
			$q.=$k[0]=='!' ? '`'.substr($k,1).'`='.$v.',' : '`'.$k.'`='.$this->Escape($v).',';

		$q=rtrim($q,',');

		if($w)
			$q.=' WHERE '.$w;

		$this->Query($q);

		return$this->Driver->affected_rows;
	}

	/**
	 * Обертка для удобного осуществления DELETE запросов
	 * @param string $t Имя таблицы, откуда необходимо удалить данные
	 * @param string $w Секция WHERE, без ключевого слова WHERE. Если не заполнять - выполнится TRUNCATE запрос.
	 * @return int Affected rows
	 */
	public function Delete($t,$w='')
	{
		$this->Query($w ? 'DELETE FROM `'.$t.'` WHERE '.$w : 'TRUNCATE TABLE `'.$t.'`');

		return$this->Driver->affected_rows;
	}

	/**
	 * Преобразование массива в последовательность для конструкции IN(). Данные автоматически экранируются
	 * @param mixed $a Данные для конструкции IN
	 * @param bool $not Включение конструкции NOT IN. Для оптимизации запросов, по возможности используется = вместо IN
	 * @return string
	 */
	public function In($a,$not=false)
	{
		if(is_array($a) and count($a)==1)
			$a=reset($a);

		if(is_array($a))
		{
			foreach($a as &$v)
				$v=$this->Escape($v);

			return($not ? ' NOT' : '').' IN ('.join(',',$a).')';
		}

		return($not ? '!' : '').'='.$this->Escape($a);
	}

	/**
	 * Экранирование опасных символов в строках
	 * @param string $s Строка для экранирования
	 * @param bool $qs Флаг включения одинарных кавычек в начало и в конец результата
	 * @return mixed
	 */
	public function Escape($s,$qs=true)
	{
		if($s===null)
			return'NULL';

		if(is_int($s) or is_float($s))
			return$s;

		if(is_bool($s))
			return(int)$s;

		$s=$this->Driver->real_escape_string((string)$s);

		return$qs ? '\''.$s.'\'' : $s;
	}
} 