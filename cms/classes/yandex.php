<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE;

/** Реализация API от Яндекс.Вебмастера https://tech.yandex.ru/webmaster/doc/dg/concepts/About-docpage/ */
class Yandex extends \Eleanor\Classes\Url
{
	protected
		/** @var array Конфигурация Яндекса /cms/config_yandex.php */
		$config=[],

		/** @var array Данные для авторизации на Яндексе */
		$auth=[],

		/** @var resource Client Url Library */
		$curl;

	/** @param array $config Конфигурация от Яндекс.Вебмастер
	 * @throws EE */
	public function __construct(array$config=[])
	{
		$this->curl=curl_init();
		$this->config=$config ? $config : (array)include DIR.'config_yandex.php';

		if(!isset($this->config['client_id']))
			throw new EE('Missing client_id',EE::DEV);
	}

	public function __destruct()
	{
		curl_close($this->curl);
	}

	/** Получение ссылки на авторизацию приложения
	 * @return string */
	public function GetAuthLink()
	{
		return'https://oauth.yandex.ru/authorize?response_type=code&client_id='.$this->config['client_id'];
	}

	/** Установка данных авторизации на Яндексе
	 * @param array $auth Данные авториазции, необходимые ключи: token, ttl, created, site_id
	 * @return null|string null если ок, string - URL для перехода к авторизации */
	public function SetAuth(array$auth=[])
	{
		if(!is_array($auth) or !isset($auth['token'],$auth['ttl'],$auth['site_id']) or $auth['ttl']+$auth['created']<time())
			return $this->GetAuthLink();

		$this->auth=$auth;

		curl_setopt_array($this->curl,[
			CURLOPT_HEADER=>0,
			CURLOPT_CONNECTTIMEOUT=>2,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_PORT=>443,
			CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER=>['Accept: */*','Authorization: OAuth '.$auth['token']],
		]);
	}

	/** Получение сервисного документа https://tech.yandex.ru/webmaster/doc/dg/tasks/how-to-get-service-document-docpage/
	 * @return \SimpleXMLElement|string string в случае ошибки */
	public function GetServiceDocument()
	{
		curl_setopt($this->curl,CURLOPT_URL,'https://webmaster.yandex.ru/api/v2');
		$xml=curl_exec($this->curl);
		$xml=simplexml_load_string($xml);

		if(!$xml or !isset($xml->workspace,$xml->workspace->collection))
			return'NO_PERMISSION';#У приложения нет нужных разрешений

		return$xml;
	}

	/** Получение списка сайтов пользователя https://tech.yandex.ru/webmaster/doc/dg/reference/hosts-docpage/
	 * @return \SimpleXMLElement|string string в случае ошибки */
	public function GetUserSites()
	{
		$xml=$this->GetServiceDocument();

		if(is_string($xml))
			return$xml;

		$xml=$xml->workspace->collection->attributes();

		#Получение списка сайтов пользователя
		curl_setopt($this->curl,CURLOPT_URL,(string)$xml['href']);
		$xml=curl_exec($this->curl);
		$xml=simplexml_load_string($xml);

		return$xml;
	}

	/** Проверка наличия сайта в Яндекс.Вебмастер
	 * @param string $punycode Домен сайта
	 * @return null|string null в случае наличия, string в случае ошибки */
	public function CheckDomain($punycode=\Eleanor\PUNYCODE)
	{
		$xml=$this->GetUserSites();

		if(is_string($xml))
			return$xml;

		foreach($xml as $host)
		{
			$attrs=$host->attributes();
			$id=preg_match('#(\d+)$#',$attrs['href'],$m)>0 ? $m[1] : false;
			$verify=$host->verification->attributes();

			if($id and $verify['state']=='VERIFIED' and (string)$host->name===$punycode)
				return null;
		}

		return'SITE_NOT_FOUND';#Сайт в Яндекс.Вебмастере не найден
	}

	/** Добавление оригинального текста https://tech.yandex.ru/webmaster/doc/dg/reference/host-original-texts-add-docpage/
	 * @param string $text Оригинальный текст
	 * @param string $punycode Домен сайта
	 * @return \SimpleXMLElement|string string в случае ошибки */
	function AddOriginalText($text,$punycode=\Eleanor\PUNYCODE)
	{
		$url=false;
		$xml=$this->GetUserSites();

		if(is_string($xml))
			return$xml;

		foreach($xml as $host)
		{
			$attrs=$host->attributes();
			$id=preg_match('#(\d+)$#',$attrs['href'],$m)>0 ? $m[1] : false;
			$verify=$host->verification->attributes();

			if($id and $verify['state']=='VERIFIED' and (string)$host->name===$punycode)
			{
				$url=(string)$attrs['href'];
				break;
			}
		}

		if($url===false)
			return'SITE_NOT_FOUND';#Сайт в Яндекс.Вебмастере не найден

		curl_setopt($this->curl,CURLOPT_URL,$url);

		$url=false;
		$data=curl_exec($this->curl);
		$data=simplexml_load_string($data);

		foreach($data->link as $link)
		{
			$attrs=$link->attributes();

			if($attrs['rel']=='original-texts')
			{
				$url=(string)$attrs['href'];
				break;
			}
		}

		if($url===false)
			return'NO_INFO';#Нет информации https://tech.yandex.ru/webmaster/doc/dg/reference/hosts-id-docpage/

		curl_setopt($this->curl,CURLOPT_URL,$url);
		curl_setopt($this->curl,CURLOPT_POST,true);
		curl_setopt($this->curl,CURLOPT_POSTFIELDS,'<original-text><content>'.urlencode($text).'</content></original-text>');

		$xml=curl_exec($this->curl);
		return simplexml_load_string($xml);
	}
}