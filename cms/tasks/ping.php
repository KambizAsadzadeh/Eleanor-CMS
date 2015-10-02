<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;

/** Пинг поисковых систем */
class Ping extends \Eleanor\BaseClass implements \CMS\Interfaces\Task
{
	/** Запуск задачи */
	public function Run($d)
	{
		\CMS\LoadOptions('site');
		return \CMS\Seo_Ping::Proccess();
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return'';
	}
}