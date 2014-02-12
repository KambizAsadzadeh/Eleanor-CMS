<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Исключение для DB EleanorException
*/
namespace Eleanor\Classes;
use Eleanor;

class EE_DB extends EE
{
	private
		/** @property string $type Тип исключения */
		$type;

	/**
	 * Конструктор
	 * @param string $type Тип исключения: connect - ошибка при подключении, query - ошибка при запросе
	 * @param array $db Данные debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1)
	 * @param array $extra Данные исключения
	 * @param \exception $PO Предыдущее перехваченное исключение, что послужило "родителем" для текущего
	 */
	public function __construct($type,$db,$extra=[],$PO=null)
	{
		$Lang=new Language(__DIR__.'/language/ee-db-*.php');#PhpStorm не видит, что переменная используется ниже :-(

		$db=reset($db);

		$this->file=$db['file'];
		$this->line=$db['line'];
		$this->type=$type;

		switch($type)
		{
			case'connect':
				$mess=$Lang['connect']($extra);
				$code=EE::UNIT;
			break;
			case'query':
				$mess=$Lang['query']($extra);
				$code=EE::DEV;
			break;
			default:
				$mess=$extra['error'];
				$code=EE::UNIT;
		}

		parent::__construct($mess,$code,$extra,$PO);
	}

	/**
	 * Команда залогировать исключение
	 * @param string|bool $logfile Путь к лог-файлу. Без расширения.
	 */
	public function Log($logfile=false)
	{
		$this->LogWriter(
			$logfile ? $logfile : Eleanor\Framework::$logspath.'database',
			md5($this->extra['error'].$this->line.$this->file),
			function($data)
			{
				#Запись в переменные нужна для последующего удобного чтения лог-файла любыми читалками
				$data['n']=isset($data['n']) ? $data['n']+1 : 1;
				$data['d']=date('Y-m-d H:i:s');
				$data['e']=$this->extra['error'];
				$data['l']=$this->getLine();
				$data['t']=$this->type;
				$data['f']=strpos($this->file,\Eleanor\SITEDIR)===0
					? substr($this->file,strlen(\Eleanor\SITEDIR))
					: $this->file;

				$log=$data['error'].PHP_EOL;

				switch($this->type)
				{
					case'connect':
						if(strpos($data['error'],'Access denied for user')===false)
						{
							$data['h']=isset($this->extra['host']) ? $this->extra['host'] : '';
							$data['u']=isset($this->extra['user']) ? $this->extra['user'] : '';
							$data['p']=isset($this->extra['pass']) ? $this->extra['pass'] : '';
							$log.='Host: '.$data['h'].PHP_EOL.'User: '.$data['u'].PHP_EOL;
						}

						$data['db']=isset($this->extra['db']) ? $this->extra['db'] : '';

						$log.='Database: '.$data['db'].PHP_EOL
							.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL
							.'Last happens: '.$data['d'].', total happens: '.$data['n'];
					break;
					case'query':
						$data['q']=$this->extra['query'];
						$log.='Query: '.$data['q'].PHP_EOL
							.'File: '.$data['f'].'['.$data['l'].']'.PHP_EOL
							.'Last happens: '.$data['d'].', total happens: '.$data['n'];
					break;
					default:
						$log.='File: '.$data['f'].'['.$data['l'].']'.PHP_EOL
							.'Last happens: '.$data['d'].', total happens: '.$data['n'];
				}

				return[$data,$log];
			}
		);
	}
}