<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** API модуля аккаунта */
class ApiAccount extends \Eleanor\BaseClass implements Interfaces\NewLangUrl
{
	/** @var array|mixed Конфигурация */
	protected $config=[];

	/** Обычный конструктор
	 * @param array $config Конфигурации */
	public function __construct($config=[])
	{
		$this->config=$config;
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

		#ToDo!
		return'';
		/*$El=Eleanor::getInstance();
		if(isset($this->module['section']) and in_array($this->module['section'],array('groups','online')))
			return$El->Url->Prefix();
		if(!is_array($q))
		{
			$str=$El->Url->GetEnding(array($El->Url->ending,$El->Url->delimiter),true);
			$q=$El->Url->Parse($str ? array('user','do') : array('do'),true);
		}
		$user=isset($q['user']) ? $q['user'] : 0;
		$id=isset($q['userid']) ? (int)$q['userid'] : 0;
		$do=isset($q['do']) ? preg_replace('#[^a-z0-9\-_]+#','',$q['do']) : false;
		if($user)
			return$El->Url->Construct(array('user'=>$user,'do'=>$do)+$q);
		if($id)
			return$El->Url->Construct(array(array('userid'=>$user),'do'=>$do)+$q);
		if($do)
			return$El->Url->Construct(array('do'=>$do)+$q,true,'');
		return$El->Url->Prefix();*/
	}
}