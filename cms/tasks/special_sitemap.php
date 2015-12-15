<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor, \Eleanor\Classes\EE;

/** Генерация файлов sitemap.xml */
class Sitemap extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	private
		/** @var array Конфигурация (создается в менеджере управления задачами) */
		$opts,

		/** @var array Данные запуска */
		$data=[],

		/** @var int ID модуля для взаимодействия между функциями */
		$mid=0,

		/** @var array Количество необработанных ссылок ID модуля=>Количество ссылок */
		$total=[],

		/** @var resource Идентификатор, возвращаемый функцией fopen */
		$fh,

		/** @var int Количество сгенерированных ссылок */
		$smtotal=0,

		/** @var bool Флаг генерации полной ссылки в sitemap-е */
		$full;

	/** Конструктор
	 * @param array $opts Конфигурация */
	public function __construct($opts)
	{
		$this->opts=$opts;
	}

	/** Запуск задачи
	 * @param mixed $data Данные, возвращенные прошлый раз методом GetNextRunInfo
	 * @return bool Нужно вернуть true, если задание выполнено успешно, и false - если нет */
	public function Run($data)
	{
		$this->data=$data;

		$R=Eleanor::$Db->Query('SELECT `modules`,`file`,`compress`,`limit`,`fulllink`,`send_service` FROM `'
			.CMS\P.'sitemaps` WHERE `id`='.$this->opts['id'].' LIMIT 1');
		if(!$sitemap=$R->fetch_assoc() or !$sitemap['modules'])
			return true;

		$sitemap['modules']=explode(',,',trim($sitemap['modules'],','));
		$apis=[];
		$opts=[ 'limit'=>$sitemap['limit'] ];

		$R=Eleanor::$Db->Query('SELECT `id`,`uris`,`path`,`api` FROM `'.CMS\P.'modules` WHERE `api`!=\'\' AND `id`'
			.Eleanor::$Db->In($sitemap['modules']).' AND `status`=1');
		while($module=$R->fetch_assoc())
		{
			$api=CMS\DIR.$module['path'].$module['api'];
			$class='Api'.basename($module['path']);

			do
			{
				if(class_exists($class,false))
					break;

				if(is_file($api))
				{
					$retapi=\Eleanor\AwareInclude($api);

					if(is_string($retapi))
						$class=$retapi;

					if(class_exists($class,false))
						break;
				}

				continue 2;
			}while(false);

			if(!($class instanceof CMS\Interfaces\SitemapGenerator))
				continue;

			$apis[ $module['id'] ]=new$class([ 'uris'=>json_decode($module['uris'],true), 'id'=>$module['id'] ]);
		}

		if(!$apis)
			return true;

		$f=dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.$sitemap['file'];
		$fx=$f.'.xml';
		$fgz=$f.'.xml.gz';
		$fxex=is_file($fx);

		if(!$this->data and $fxex)
		{
			\Eleanor\Classes\Files::Delete($fx);
			\Eleanor\Classes\Files::Delete($fgz);

			$fxex=false;
		}

		if(!isset($this->data['total'],$this->data['already'],$this->data['modules']))
		{
			foreach($apis as$module=>$Api)
			{/** @var CMS\Interfaces\SitemapGenerator $Api */
				$this->mid=$module;
				$this->total[$this->mid]=$Api->SitemapAmount(
					isset($data['m'][$module]) ? $data['m'][$module] : [],
					isset($this->opts['m'][$module]) ? $this->opts['m'][$module] : null
				);
			}

			$total=array_sum($this->total);

			if($total>0)
			{
				if($sitemap['compress'])
					$sitemap['compress']=function_exists('gzopen') && is_file($fgz) && !$fxex;

				if($sitemap['compress'] and $fh=gzopen($fgz,'r'))
				{
					$this->FOpen($fx,'w');

					while(!gzeof($fh))
						fwrite($this->fh,gzread($fh,1024*64));

					gzclose($fh);
				}

				$this->data['total']=$total;
				$this->data['already']=0;
				$this->data['modules']=[];

				foreach($this->total as $module=>$Api)
					if($Api>0)
						$this->data['modules'][$module]=$Api;
			}
			else
				$this->data['modules']=false;

			$this->data['changed']=false;
		}

		if(!$this->data['modules'])
		{
			if(isset($this->data['sent']))
			{
				switch(array_pop($this->data['sent']))
				{
					case'google':
						$s='http://google.com/webmasters/sitemaps/ping?sitemap=%s';
					break;
					case'yahoo!':
						$s='http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=SitemapWriter&url=%s';
					break;
					case'ask.com':
						$s='http://submissions.ask.com/ping?sitemap=%s';
					break;
					case'bing':
						$s='http://www.bing.com/webmaster/ping.aspx?siteMap=%s';
					break;
					default:
						$s=false;
				}

				if($s)
				{
					$cu=curl_init(sprintf($s,urlencode(\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR
						.$sitemap['file'].($sitemap['compress'] ? '.gz' : '.xml'))));
					curl_setopt_array($cu,[
						CURLOPT_RETURNTRANSFER=>true,
						CURLOPT_TIMEOUT=>10,
						CURLOPT_HEADER=>false,
					]);
					curl_exec($cu);
					curl_close($cu);
				}
			}
			elseif($this->data['changed'])
			{
				if($sitemap['compress'] and function_exists('gzopen') and $hgz=gzopen($fgz,'w9'))
				{
					$this->FOpen($fx,'r');

					while(!feof($this->fh))
						gzwrite($hgz,fread($this->fh,1024*64));

					gzclose($hgz);
					fclose($this->fh);

					#Удаление оригинального файла
					#Files::Delete($fx);
				}
				$this->data['sent']=$sitemap['send_service'] ? explode(',,',trim($sitemap['send_service'],',')) : false;
			}
			else
				$this->data['sent']=false;

			if($this->data['sent'])
				return false;
			else
			{
				unset($this->data['total'],$this->data['already'],$this->data['modules'],$this->data['sent'],
					$this->data['changed']);
				return true;
			}
		}

		$this->FOpen($fx,$fxex ? 'r+' : 'w');

		if(!$fxex)
			fwrite($this->fh,'<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'
				.CMS\Template::$path['3rd']
				.'static/sitemap.xsl"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

		do
		{
			$module=key($this->data['modules']);

			if(isset($apis[$module]))
			{
				$this->full=$sitemap['fulllink'];
				$this->data['m'][$module]=$apis[$module]->SitemapGenerate(
					isset($data['m'][$module]) ? $data['m'][$module] : [],
					isset($this->opts['m'][$module]) ? $this->opts['m'][$module] : [],
					[$this,'Sitemap'],
					$opts
				);

				if($this->smtotal>0)
				{
					$this->data['changed']=true;
					$this->data['already']+=$this->smtotal;
					$this->data['modules'][$module]-=$this->smtotal;

					if($this->data['modules'][$module]<0)
					{
						$this->data['total']-=$this->data['modules'][$module];
						$this->data['modules'][$module]=0;
					}

					break;
				}

				$this->data['total']-=$this->data['modules'][$module];
				$this->data['modules'][$module]=0;
			}

			unset($this->data['modules'][$module]);
		}while(false);

		fclose($this->fh);
		Eleanor::$Db->Update(CMS\P.'sitemaps',['already'=>$this->data['already'],'total'=>$this->data['total']],
			'`id`='.$this->opts['id'].' LIMIT 1');

		return false;
	}

	/** CallBack генератора: сюда передается каждая ссылка, сгенерированная модулем
	 * @param mixed $a Параметры ссылки или массив ссылок
	 * @param bool $isa В случае передачи первым параметром массива ссылок, в этот параметр следует передать true */
	public function Sitemap(array$a=[],$isa=false)
	{
		if($isa)
		{
			foreach($a as &$v)
				$this->Sitemap($v);
			return;
		}

		if(!isset($a['loc']))
			return;

		$a+=[
			'lastmod'=>'',
			'changefreq'=>'',
		];

		if($this->full and preg_match('#^[a-z]{3,6}://#i',$a['loc'])==0)
			$a['loc']=\Eleanor\PROTOCOL.\Eleanor\DOMAIN.\Eleanor\SITEDIR.$a['loc'];

		$a['loc']='<loc>'.$a['loc'].'</loc>';

		if($a['lastmod'])
		{
			if(is_int($a['lastmod']))
			{
				$t=date_offset_get(date_create());
				$s=$t>0 ? '+' : '-';
				$t=abs($t);
				$m=floor($t/3600);
				$s.=(strlen($m)==1 ? '0' : '').$m.':';
				$m=$t%3600;
				$s.=(strlen($m)==1 ? '0' : '').$m;
				$a['lastmod']=date('Y-m-d\TH:i:s').$s;
			}

			$a['lastmod']='<lastmod>'.$a['lastmod'].'</lastmod>';
		}

		if($a['changefreq'])
			$a['changefreq']=in_array($a['changefreq'],['always','hourly','daily','weekly','monthly','yearly','never'])
				? '<changefreq>'.$a['changefreq'].'</changefreq>' : '';

		if(isset($a['priority']))
		{
			$a['priority']=floatval($a['priority']);

			if($a['priority']<=0)
				$a['priority']='0.1';
			elseif($a['priority']>1)
				$a['priority']='1.0';

			$a['priority']='<priority>'.$a['priority'].'</priority>';
		}

		$this->smtotal++;

		fseek($this->fh,-9,SEEK_END);
		fwrite($this->fh,'<url>'.join(',',$a).'</url></urlset>');
	}

	/** Открытие файла */
	private function FOpen($fx,$m)
	{
		if(!$this->fh and !$this->fh=fopen($fx,$m))
			new EE('Unable to access file '.$fx.'.xml',EE::ENV);
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром
	 * @return mixed Данные доложны быть сериализуемыми */
	public function GetNextRunInfo()
	{
		return$this->data;
	}
}