<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor;

/** Обновление имен пользователей в таблицах */
class Special_RecoverNames extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	private
		/** @var array Конфигурация (создается в менеджере управления задачами) */
		$opts=[],

		/** @var array Данные запуска */
		$data=[];

	/** Конструктор
	 * @param array $opts Конфигурация */
	public function __construct($opts)
	{
		$this->opts=$opts;
	}

	/** Запуск задачи */
	public function Run($data)
	{
		if(!isset($data['deadids']))
			$data['deadids']=0;

		$this->data=$data;
		$per=$this->opts['per_load'];
		$runned=false;

		foreach($this->opts['tables'] as $table=>$tids)
			foreach($this->opts['ids'] as $k=>$fieldid)
			{
				if(isset($tids[$fieldid]))
					$cnt=$tids[$fieldid];
				else
					continue;

				if(isset($this->data['tables'][$table][$fieldid]))
				{
					if($this->data['tables'][$table][$fieldid]>=$cnt)
						continue;
				}
				else
					$this->data['tables'][$table][$fieldid]=0;

				$fid=Eleanor::$Db->Escape($fieldid,false);
				$fname=Eleanor::$Db->Escape($this->opts['names'][$k],false);

				try
				{
					$R=Eleanor::$Db->Query('SELECT `'.$fid.'` `f`, COUNT(`'.$fid.'`) `cnt` FROM `'.$table.'` WHERE `'
						.$fid.'`!=0  GROUP BY `'.$fid.'` LIMIT '
						.($this->data['tables'][$table][$fieldid]-$data['deadids']).','.$per);
				}
				catch(\Eleanor\Classes\EE_DB$E)
				{
					return true;
				}

				while($res=$R->fetch_assoc())
				{
					$runned=true;
					$R2=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.CMS\USERS_TABLE.'` WHERE `id`='.$res['f']
						.' LIMIT 1');
					if($a=$R2->fetch_assoc())
						$updated=Eleanor::$Db->Update($table,
							[$fname=>htmlspecialchars($a['name'],CMS\ENT,\Eleanor\CHARSET)],
							'`'.$fid.'`='.$res['f']);
					else
					{
						Eleanor::$Db->Update($table,[$fid=>0],'`'.$fid.'`='.$res['f']);

						$updated=$res['cnt'];
						$this->data['deadids']++;
					}

					$this->data['updated']+=$updated;
					$this->data['total']++;
					$this->data['tables'][$table][$fieldid]++;
				}

				break 2;
			}

		if(!$runned or $this->data['total']==$this->opts['total'])
			$this->data['done']=true;

		if($this->data['done'])
			unset($this->data['tables']);

		return$this->data['done'];
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return$this->data;
	}
}