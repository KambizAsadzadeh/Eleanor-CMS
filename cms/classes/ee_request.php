<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

/** Исключение ошибочного запроса */
class EE_Request extends \Eleanor\Classes\EE
{
	/** Команда залогировать исключение
	 * @param string|bool $logfile Путь к лог-файлу. Без расширения */
	public function Log($logfile=false)
	{
		$this->LogWriter(
			$logfile ? $logfile : \Eleanor\Framework::$logspath.'requests',
			md5($this->extra['code'].$this->line.$this->file),
			function($data)
			{
				$uinfo=Eleanor::$Login->Get(['id','name']);

				#Запись в переменные нужна для последующего удобного чтения лог-файла любыми читалками
				$data['n']=isset($data['n']) ? $data['n']+1 : 1;
				$data['d']=date('Y-m-d H:i:s');
				$data['e']=$this->extra['code'].' - '.$this->getMessage();
				$data['b']=getenv('HTTP_USER_AGENT');
				$data['p']=Url::$current;
				$data['ip']=Eleanor::$ip;

				if($uinfo)
				{
					$data['u']=$uinfo['name'];
					$data['ui']=$uinfo['id'];
				}

				#Ссылающиеся страницы
				if(!isset($data['r']))
					$data['r']=[];

				if($this->extra['back'] and !in_array($this->extra['back'],$data['r']))
					$data['r'][]=$this->extra['back'];

				$dcnt=count($data['r']);

				if($dcnt>50)
					array_splice($data['r'],0,$dcnt-50);
				#/Ссылающиеся страницы

				return[
					$data,
					$data['e'].'('.$data['n'].'): '.($data['p'] ? $data['p'] : '/').PHP_EOL
					.'Date: '.$data['d'].PHP_EOL
					.'IP: '.inet_ntop($data['ip']).PHP_EOL
					.($uinfo ? 'User: '.$data['u'].PHP_EOL : '')
					.'Browser: '.$data['b'].PHP_EOL
					.'Referrers: '.join(', ',$data['r'])
				];
			}
		);
	}
}