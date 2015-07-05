<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Пинг поисковых систем о новом контенте */
class Ping extends \Eleanor\BaseClass
{
	const
		/** Количество дней которые хранятся выполненные заявки в БД */
		SAVEDAYS=1,

		/** Лимит пингов за один запуск */
		PROCCESS_LIMIT=50;

	/** @var array Все поисковые системы, которые поддерживают пинг, список берется из файла config_ping.php */
	protected static $services;

	/** Добавление задания для пинга поисковых систем об изменении содержимого сайта
	 * @param array $a Данные, ключи:
	 *  [string id] Уникальный id зписи
	 *  [string main] Ссылка на сайт, на котором изменилась информация
	 *  [string site] Название сайта
	 *  [array services] Поисковые системы, которые нужно пинговать. Если опущено, будут пинговаться все системы.
	 *  [array exclude] Поисковые системы, которые нужно исключить из пинга. Если опущено, исключений не будет.
	 *  [string changes] Страница на которой произошли изменения
	 *  [string rss] RSS сайта
	 *  [array categories] Категории */
	public static function Add(array$a)
	{
		Eleanor::$Db->Replace(P.'ping',
			['id'=>isset($a['id']) ? (string)$a['id'] : uniqid(),
				'pinged'=>0,
				'!date'=>'NOW()',
				'site'=>empty($a['site']) ? '' : join(',',(array)$a['site']),
				'services'=>empty($a['services']) ? '' : join(',',(array)$a['services']),
				'exclude'=>empty($a['exclude']) ? '' : join(',',(array)$a['exclude']),
				'main'=>isset($a['main']) ? (string)$a['main'] : '',
				'changes'=>isset($a['changes']) ? (string)$a['changes'] : '',
				'rss'=>isset($a['rss']) ? (string)$a['rss'] : '',
				'categories'=>empty($a['categories']) ? '' : join('|',(array)$a['categories'])]);

		Eleanor::$Db->Update(P.'tasks',['!nextrun'=>'NOW()'],'`name`=\'ping\'');
		Tasks::UpdateNextRun();
	}

	/** Одиночный пинг поисковых систем
	 * @param array $a Массив входящих параметров, ключи:
	 *  [string main] Ссылка на сайт, на котором изменилась информация
	 *  [string site] Название сайта
	 *  [array services] Поисковые системы, которые нужно пинговать. Если опущено, будут пинговаться все системы.
	 *  [array exclude] Поисковые системы, которые нужно исключить из пинга. Если опущено, исключений не будет.
	 *  [string changes] Страница на которой произошли изменения
	 *  [string rss] RSS сайта
	 *  [array categories] Категории
	 * @return array Результат, возвращенный каждой из поисковых систем */
	public static function Once(array$a)
	{
		if(!isset(static::$services))
			static::$services=include DIR.'config_ping.php';

		if(isset($a['exclude']))
		{
			if(!is_array($a['exclude']))
				$a['exclude']=$a['exclude'] ? explode(',',$a['exclude']) : [];
		}
		else
			$a['exclude']=[];

		if(isset($a['services']))
		{
			if(!is_array($a['services']))
				$a['services']=$a['services'] ? explode(',',$a['services']) : false;
		}
		else
			$a['services']=false;

		$r=[];
		$nech=!empty($a['changes']);
		$nerss=!empty($a['rss']);
		$cu=curl_init();

		if(empty($a['site']))
			$a['site']=Eleanor::$vars['site_name'];

		if(empty($a['main']))
			$a['main']=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;

		foreach(static::$services as $k=>$v)
		{
			if($a['services'] and !in_array($k,$a['services']) or in_array($k,$a['exclude']))
				continue;

			if(!is_array($v['methods']))
				$v['methods']=(array)$v['methods'];

			$f='<?xml version="1.0" encoding="'.\Eleanor\CHARSET.'"?>';
			$categs=isset($a['categories']) ? "<param><value>{$a['categories']}</value></param>" : '';

			if(in_array('weblogUpdates.extendedPing',$v['methods']) and $nech and $nerss)
				$f.=<<<XML
<methodCall>
	<methodName>weblogUpdates.extendedPing</methodName>
	<params>
		<param><value>{$a['site']}</value></param>
		<param><value>{$a['main']}</value></param>
		<param><value>{$a['changes']}</value></param>
		<param><value>{$a['rss']}</value></param>
		{$categs}
	</params>
</methodCall>
XML;
			elseif(in_array('weblogUpdates.ping',$v['methods']))
			{
				$changes=isset($a['changes']) ? '<param><value>'.$a['changes'].'</value></param>' : '';
				$rss=isset($a['rss']) ? '<param><value>'.$a['rss'].'</value></param>' : '';
				$f.=<<<XML
<methodCall>
	<methodName>weblogUpdates.ping</methodName>
	<params>
		<param><value>{$a['site']}</value></param>
		<param><value>{$a['main']}</value></param>
		{$changes}{$rss}{$categs}
	</params>
</methodCall>
XML;
			}
			else
				continue;

			curl_setopt_array($cu,[
				CURLOPT_URL=>$v['url'],
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_TIMEOUT=>10,
				CURLOPT_HEADER=>false,
				CURLOPT_POST=>true,
				CURLOPT_POSTFIELDS=>$f,
				CURLOPT_HTTPHEADER=>['Content-type: text/xml'],
			]);
			$r[$k]=curl_exec($cu);
		}

		curl_close($cu);

		return$r;
	}

	/** Запуск процесса пинга. Запускается через cron */
	public static function Proccess()
	{
		$table=P.'ping';
		$limit=self::PROCCESS_LIMIT;
		$R=Eleanor::$Db->Query("SELECT `id`, `site`, `services`, `exclude`, `main`, `changes`, `rss`, `categories` FROM `{$table}` WHERE `pinged`=0 ORDER BY `date` ASC LIMIT {$limit}");
		while($a=$R->fetch_assoc())
		{
			$res=static::Once($a);
			$id=Eleanor::$Db->Escape($a['id']);

			foreach($res as &$v)
				$v=strpos($v,'Thanks for the ping.')===false ? 'error' : 'ok';

			Eleanor::$Db->Update($table,['pinged'=>1,'result'=>json_encode($res,JSON)],"`id`={$id} LIMIT 1");
		}

		$day=static::SAVEDAYS;
		Eleanor::$Db->Delete($table,"`pinged`=1 AND `date`<NOW()-INTERVAL {$day} DAY");

		return$R->num_rows<static::PROCCESS_LIMIT;
	}
}