<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Пинг поисковых систем о новом контенте */
class Seo_Ping extends \Eleanor\BaseClass
{
	const
		/** Количество дней которые хранятся выполненные заявки в БД */
		SAVEDAYS=1,

		/** Лимит пингов за один запуск */
		PROCCESS_LIMIT=50;

	/** @var array Все поисковые системы, которые поддерживают пинг, список берется из файла config_seo_ping.php */
	protected static $services;

	/** Добавление задания для пинга поисковых систем об изменении содержимого сайта
	 * @param string $url Ссылка на страницу, где изменилсь информация
	 * @param null|string $id Уникальный id зписи */
	public static function Add($url,$id=null)
	{
		Eleanor::$Db->Replace(P.'seo_ping',['id'=>$id===null ? uniqid() : $id,'pinged'=>0,'!date'=>'NOW()','url'=>$url]);
		Eleanor::$Db->Update(P.'tasks',['!nextrun'=>'NOW()'],'`name`=\'ping\'');

		Tasks::UpdateNextRun();
	}

	/** Пинг по протоколу weblogs http://www.weblogs.com/api.html
	 * @param string $server Ссылка на сервер
	 * @param string|array $url Ссылка на страницу, где изменилась информация
	 * @param string|array|null $title Название страницы
	 * @return string */
	public static function Weblogs($server,$url,$title=null)
	{
		if($title===null)
			$title=[Eleanor::$vars['site_name']];
		else
			$title=(array)$title;

		$isa=is_array($url);
		$result=[];
		$cu=curl_init();
		curl_setopt_array($cu,[
			CURLOPT_URL=>$server,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_TIMEOUT=>10,
			CURLOPT_HEADER=>false,
			CURLOPT_POST=>true,
			CURLOPT_HTTPHEADER=>['Content-type: text/xml'],
		]);
		$charset=\Eleanor\CHARSET;

		foreach((array)$url as $k=>$v)
		{
			$xml=<<<XML
<?xml version="1.0" encoding="{$charset}"?>
<methodCall>
	<methodName>weblogUpdates.ping</methodName>
	<params>
		<param><value>{$title[$k]}</value></param>
		<param><value>{$v}</value></param>
	</params>
</methodCall>
XML;
			curl_setopt($cu,CURLOPT_POSTFIELDS,$xml);
			$result[$k]=curl_exec($cu);
		}

		curl_close($cu);

		return$isa ? $result : reset($result);
	}

	/** Пинг Яндекса для Яндекс.Поиска https://yandex.ru/support/site/optimizing.xml
	 * @param array|string $server URL для выполнения запроса, либо массив с ключами key, login, search_id
	 * @param array|string $url Ссылка на страницу
	 * @return mixed */
	public static function YandexSite($server,$url)
	{
		if(is_array($url))
			$url=join("\n",$url);

		$isa=is_array($server);

		if($isa)
		{
			$server['urls']=$url;
			$post=http_build_query($server);
			$url='/ping.xml';
		}
		else
		{
			$post=http_build_query(['urls'=>$url]);
			$url=$server;
		}


		$host='site.yandex.ru';
		$length=strlen($post);

		$out ="POST {$url} HTTP/1.0\r\n";
		$out.="HOST: {$host}\r\n";
		$out.="Content-Type: application/x-www-form-urlencoded\r\n";
		$out.="Content-Length: {$length}\r\n\r\n";
		$out.=$post."\r\n\r\n";

		$result='';
		$socket=fsockopen($host, 80, $errno, $errstr, 30);

		if($socket)
			if(fwrite($socket, $out))
				while($in=fgets($socket,1024))
					$result.=$in;
			else
			{
				fclose($socket);
				return false;
			}
		else
			return false;

		if(Eleanor::$debug and Eleanor::$logspath)
			file_get_contents(Eleanor::$logspath.'seo_ping_yandex.log',$result."\n",FILE_APPEND);

		if(preg_match('/(<.*>)/u',$result,$result_xml))
		{
			$result=array_pop($result_xml);
			$xml=simplexml_load_string($result);

			if(isset($xml->added,$xml->added['count']) and $xml->added['count']>0)
				return true;
		}

		return false;
	}

	/** Запуск процесса пинга. Запускается через cron */
	public static function Proccess()
	{
		$table=P.'seo_ping';
		$limit=self::PROCCESS_LIMIT;
		$items=[];
		$R=Eleanor::$Db->Query("SELECT `id`, `url` FROM `{$table}` WHERE `pinged`=0 ORDER BY `date` ASC LIMIT {$limit}");
		while($a=$R->fetch_assoc())
			$items[ $a['id'] ]=$a['url'];

		if($items)
		{
			Eleanor::$Db->Update($table, ['pinged'=>1], '`id`'.Eleanor::$Db->In(array_keys($items)));
			$data=(array)include DIR.'config_seo_ping.php';

			foreach($data as $k=>$v)
				switch($v['method'])
				{
					case'weblogUpdates.ping':
						static::Weblogs($v['url'],$items);
					break;
					case'yandex.site':
						static::YandexSite($v['url'] ? $v['url'] : ['key'=>$v['key'],'login'=>$v['login'],'search_id'=>$v['search_id']],$items);
					break;
				}
		}

		$day=static::SAVEDAYS;
		Eleanor::$Db->Delete($table,"`pinged`=1 AND `date`<NOW()-INTERVAL {$day} DAY");

		return$R->num_rows<static::PROCCESS_LIMIT;
	}
}