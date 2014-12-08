<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor;

/** Сбор примитивной статистики */
class Statistic extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	/** Запуск задачи */
	public function Run($data)
	{
		#ToDo! Сбор статистики
		$d=date('Y-m-d H:i:s');
		Eleanor::$Db->Delete(CMS\P.'sessions','`expire`<\''.$d.'\'');
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return'';
	}
} 