<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** API модуля ошибок */
class ApiErrors extends \Eleanor\BaseClass implements Interfaces\NewLangUrl
{
	/** @var array|mixed Конфигурация */
	protected $config=[];

	/** Обычный конструктор
	 * @param array $config Конфигурации */
	public function __construct($config=[])
	{
		$this->config=$config;
		if(!isset($config['t'],$config['tl']))
			$this->config+=include __DIR__.'/config.php';
	}

	/** Получение нового языкового URL
	 * @param string $section Секция модуля
	 * @param array $oldurl Старый URL, разбитый через explode('/')
	 * @param array $oldquery Старые динамические параметры
	 * @param string $oldlang Название старого языка
	 * @param string $newlang Название нового языка
	 * @param Url $Url Объект URL с нужным префиксом
	 * @return string Новый URL относительно корня сайта без начальной / */
	public function GetNewLangUrl($section,$oldurl,$oldquery,$oldlang,$newlang,$Url)
	{
		if(Eleanor::$service=='admin')
			return Eleanor::$services['admin']['file'].'?'.Url::Query($oldquery);

		$id=0;

		if($oldurl)
		{
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->config['t'].'` INNER JOIN `'
				.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$oldlang.'\') AND `uri`'
				.Eleanor::$Db->Escape(end($oldurl)).' LIMIT 1');
			if($R->num_rows>0)
				list($id)=$R->fetch_row();
		}
		elseif(isset($oldquery['id']))
			$id=(int)$oldquery['id'];
		else
			return'';

		$R=Eleanor::$Db->Query("SELECT `uri` FROM `{$this->config['t']}` INNER JOIN `{$this->config['tl']}` USING(`id`) WHERE `language` IN ('','{$newlang}') AND `id`={$id} LIMIT 1");
		if($a=$R->fetch_assoc())
			return$a['uri'] ? $Url($a['uri']) : $Url([],'',['id'=>$id]);

		return'';
	}
}