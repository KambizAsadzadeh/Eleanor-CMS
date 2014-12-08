<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** API модуля статических страниц */
class ApiStatic extends \Eleanor\BaseClass implements Interfaces\NewLangUrl, Interfaces\SitemapGenerator
{
	/** @var array|mixed Конфигурация */
	protected $config=[];

	/** Обычный конструктор
	 * @param array $config Конфигурации */
	public function __construct(array$config=[])
	{
		$this->config=$config;

		if(!isset($config['t'],$config['tl']))
			$this->config+=include __DIR__.'/config.php';
	}

	/** Получение "содержания" отсортрованного одномерного массива со правильной иерархией страниц
	 * @param string|null $lang Идентификатор языка
	 * @param int|null $parent Родитель
	 * @return array */
	public function GetSubstance($lang=null,$parent=null)
	{
		if(!$lang)
			$lang=Language::$main;

		if($result=Eleanor::$Cache->Get($this->config['n'].'_substance_'.$parent.$lang))
			return$result;

		$maxlen=0;
		$result=$to1sort=$to2sort=$dump=[];

		if($parent)
		{
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$this->config['t'].'` WHERE `id`='.(int)$parent.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return[];

			$rep=$a['parents'].$parent.',';
			$replen=strlen($rep);
			$where=' AND `parents` LIKE \''.$rep.'%\'';
		}
		else
			$replen=(int)$where=$rep='';

		$R=Eleanor::$Db->Query('SELECT `id`, `uri`, `title`, `parents`, `pos` FROM `'.$this->config['t'].'` LEFT JOIN `'
			.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$lang.'\') AND `status`=1'.$where);
		while($a=$R->fetch_assoc())
		{
			if($rep)
				$a['parents']=substr($a['parents'],$replen);

			if($a['parents'])
			{
				$cnt=substr_count($a['parents'],',');
				$to1sort[ $a['id'] ]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}

			$dump[ $a['id'] ]=array_slice($a,1);
			$to2sort[ $a['id'] ]=$a['pos'];
		}
		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($dump[$k]['parents'] and preg_match('#(\d+),$#',$dump[$k]['parents'],$p)>0 and $parent!=$p[1])
				if(isset($to2sort[$p[1]]))
					$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
				else
					unset($to2sort[$k]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($dump[$k]['parents'],','));

		natsort($to2sort);
		foreach($to2sort as $k=>&$v)
			$result[$k]=$dump[$k];

		Eleanor::$Cache->Put($this->config['n'].'_substance_'.$parent.$lang,$result,86400);
		return$result;
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
			return $Url($oldquery);

		$id=0;

		if($oldurl)
		{
			$R=Eleanor::$Db->Query('SELECT `id`, `parents`, `uri` FROM `'.$this->config['t'].'` INNER JOIN `'
				.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$oldlang.'\') AND `uri`'
				.Eleanor::$Db->In($oldurl).' AND `status`=1 ORDER BY `parents` ASC');
			if($R->num_rows>0)
			{
				$parents='';
				$uri=reset($oldurl);

				while($a=$R->fetch_assoc())
					if($parents==$a['parents'] and mb_strtolower($uri)==mb_strtolower($a['uri']))
					{
						$id=$a['id'];
						$parents.=$a['id'].',';

						$uri=next($oldurl);
					}
			}
		}
		elseif(isset($oldquery['id']))
			$id=(int)$oldquery['id'];
		else
			return'';

		return$id>0 ? $this->GetUrl($id,$Url,$newlang) : '';
	}

	/** @var array Дамп ссылок */
	protected $urls;

	/** Получение ссылки на статическую страницу из базы
	 * @param int $id ID страницы
	 * @param Url $Url Объект URL с нужным префиксом
	 * @param null|string $lang Идентифкатор языка
	 * @return mixed */
	public function GetUrl($id,Url$Url,$lang=null)
	{
		if(!$lang)
			$lang=Language::$main;

		$this->urls=Eleanor::$Cache->Get($this->config['n'].'_urls_'.$lang);
		if($this->urls===false)
		{
			$tmp=$this->GetSubstance($lang);
			$this->urls=[];

			foreach($tmp as $k=>$v)
				$this->urls[$k]=[
					'parents'=>$v['parents'],
					'uri'=>$v['uri'],
				];

			Eleanor::$Cache->Put($this->config['n'].'_urls_'.$lang,$this->urls,7200);
		}

		if(!isset($this->urls[$id]))
			return false;

		$params=[];
		$lastu=$this->urls[$id]['uri'];

		if($this->urls[$id]['parents'] and $lastu)
		{
			foreach(explode(',',rtrim($this->urls[$id]['parents'],',')) as $v)
				if(isset($this->urls[$v]))
					if($this->urls[$v]['uri'])
						$params[]=$this->urls[$v]['uri'];
					else
					{
						$params=[];
						$lastu='';

						break;
					}
		}

		if($lastu)
		{
			$params[]=$lastu;
			return$Url($params);
		}

		return rtrim($Url->prefix,'/').'?id='.$id;
	}

	/** Конфигуратор SiteMap-а
	 * @return array */
	public function SitemapConfigure()
	{
		$lang=isset(Eleanor::$Language['static-api'])
			? Eleanor::$Language['static-api']
			: Eleanor::$Language->Load(__DIR__.'/api-*.php','static-api');

		return [
			'pp'=>[
				'title'=>$lang['pp'],
				'type'=>'input',
				'default'=>'0.7',
				'options'=>[
					'type'=>'number',
					'extra'=>[
						'class'=>'need-tabindex',
						'min'=>0.1,
						'max'=>1,
						'step'=>0.1,
					],
				],
			],
			'ps'=>[
				'title'=>$lang['ps'],
				'type'=>'input',
				'default'=>'0.5',
				'options'=>[
					'type'=>'number',
					'extra'=>[
						'class'=>'need-tabindex',
						'min'=>0.1,
						'max'=>1,
						'step'=>0.1,
					],
				],
			],
		];
	}

	/** Получение потенциального количества генерируемых ссылок (для возможности публикации прогрессбара)
	 * @param mixed $data Данные, полученные от метода SitemapGenerate на предыдущем этапе
	 * @param array $conf Конфигурация от SitemapConfigure
	 * @return number */
	public function SitemapAmount($data,$conf)
	{
		$cnt=0;

		foreach(Eleanor::$langs as$lang=>$langdata)
		{
			if(!Eleanor::$vars['multilang'] and $lang!=Language::$main)
				continue;

			if(empty($data[$lang]['static']))
				++$cnt;

			$lastid=isset($data[$lang]['last_id']) ? (int)$data[$lang]['last_id'] : 0;

			$R=Eleanor::$Db->Query('SELECT COUNT(`id`) `cnt` FROM `'.$this->config['t'].'` INNER JOIN `'
				.$this->config['tl'].'` USING(`id`) WHERE `language`IN(\'\',\''.$lang.'\') AND `status`=1'.($lastid>0
					?' AND `id`>'.$lastid : ''));
			list($add)=$R->fetch_row();
			$cnt+=$add;
		}

		return$cnt;
	}

	/** Генератор карты сайта
	 * @param mixed $data Данные, полученные от этого метода на предыдущем этапе
	 * @param array $conf Конфигурация полученная от метода SitemapConfigure
	 * @param callable $callback Функция, которую следует вызать для отправки результата
	 * @param array $opts Опции, ключи:
	 *  int limit Рекомендуемое количество ссылок для генерации за раз
	 * @return mixed*/
	public function SitemapGenerate($data,$conf,$callback,$opts)
	{
		$conf+=[
			'pp'=>0.7,
			'ps'=>0.5,
		];
		$Url=new Url(false);

		foreach(Eleanor::$langs as$lang=>$langdata)
		{
			if($opts['limit']<1)
				break;

			if(Eleanor::$vars['multilang'])
				$Url->prefix=Url::Encode($langdata['uri']).'/';
			elseif($lang!=Language::$main)
				continue;

			if(!isset($data[$lang]))
				$data[$lang]=[
					'static'=>false,#Статическая часть модуля (содержание)
					'last_id'=>0,
				];

			if(Eleanor::$vars['prefix_free_module']!=$this->config['id'])
				$Url->prefix.=Url::Encode(is_array($this->config['uris']['static'])
					? FilterLangValues($this->config['uris']['static'],$lang,'')
					: $this->config['uris']['static']).'/';

			if(!$data[$lang]['static'])
			{
				$links=[
					Eleanor::$vars['prefix_free_module']==$this->config['id'] ? false : rtrim($Url->prefix,'/'),
				];

				foreach($links as$link)
					call_user_func($callback,[ 'loc'=>$link, 'priority'=>$conf['ps'],]);

				$data[$lang]['static']=true;
			}

			$links=$this->GetSubstance($lang);

			foreach($links as $k=>$link)
				if($k>$data[$lang]['last_id'])
				{
					call_user_func($callback,[ 'loc'=>$this->GetUrl($k,$Url,$lang), 'priority'=>$conf['pp'],]);
					$opts['limit']--;
				}
		}

		return$data;
	}
}

return ApiStatic::class;