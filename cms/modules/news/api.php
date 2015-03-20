<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** API модуля новостей */
class ApiNews extends \Eleanor\BaseClass
	implements Interfaces\NewLangUrl, Interfaces\SitemapGenerator, Interfaces\Comments
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
		/*		$puri=false;
		$curls=array();
		$El=Eleanor::getInstance();
		if($El->Url->furl)
		{
			$q=$El->Url->Parse($El->Url->ending ? array() : array('do'),true);
			if($El->Url->ending)
			{
				$curls=isset($q['']) && is_array($q['']) ? $q[''] : array();
				if(Eleanor::$vars['url_static_ending']==$El->Url->ending)
					$puri=array_pop($curls);
			}
		}

		if(Eleanor::$service!='user' or !$q)
			return$El->Url->Construct($q);
		$cid=isset($q['cid']) ? (int)$q['cid'] : 0;
		$nid=isset($q['id']) ? (int)$q['id'] : 0;

		if($nid or $puri)
		{
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->config['tl'].'` WHERE `language` IN (\'\',\''.Language::$main.'\') AND '.($nid ? '`id`='.(int)$nid : '`uri`=\''.Eleanor::$Db->Escape($puri,false).'\'').' AND `lstatus`=1 LIMIT 1');
			if(!list($id)=$R->fetch_row())
				return;

			$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`lcats` FROM `'.$this->config['tl'].'` WHERE `language` IN (\'\',\''.$lang.'\') AND `id`='.$id.' AND `lstatus`=1 LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return;

			$u=array('u'=>array($a['uri'],'id'=>$a['id']));
			if($El->Url->furl)
			{
				Language::$main=$lang;
				Eleanor::$Language->Change();
				$Categs=new Categories($this->config['c']);
				$a['_cat']=$a['lcats'] ? (int)ltrim($a['lcats'],',') : false;
				if($a['_cat'] and $El->Url->furl)
				{
					$cu=$Categs->GetUri($a['_cat']);
					if($cu[0][0])
						$u=$cu+$u;
				}
			}
			return$El->Url->Construct($u);
		}
		elseif($cid or $curls)
		{
			$Categs=new Categories($this->config['c']);
			$category=$Categs->GetCategory($cid ? $cid : $curls);
			if($El->Url->furl)
			{
				Language::$main=$lang;
				Eleanor::$Language->Change();
				$El->Url->ending=$El->Url->delimiter;
				$Categs->Init($this->config['c']);
				$category=$Categs->GetCategory($category['id']);
			}
			return$El->Url->Construct($Categs->GetUri($category['id']));
		}*/
	}

	/** Конфигуратор SiteMap-а
	 * @return array */
	public function SitemapConfigure()
	{
		$lang=isset(Eleanor::$Language[__CLASS__])
			? Eleanor::$Language[__CLASS__]
			: Eleanor::$Language->Load(__DIR__.'/api-*.php',__CLASS__);

		return[
			'np'=>[
				'title'=>$lang['sgnp'],
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
			'cp'=>[
				'title'=>$lang['sgcp'],
				'descr'=>$lang['sgcp_'],
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
			'tp'=>[
				'title'=>$lang['sgtp'],
				'descr'=>$lang['sgtp_'],
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
			'op'=>[
				'title'=>$lang['sgop'],
				'descr'=>$lang['sgop_'],
				'type'=>'input',
				'default'=>'0.3',
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
			'date'=>[
				'title'=>$lang['bdate'],
				'type'=>'date',
				'default'=>'',
				'options'=>[
					'extra'=>[
						'class'=>'need-tabindex',
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
		#Todo!
		/*$conf+=array(
			'np'=>0.7,
			'cp'=>0.5,
			'tp'=>0.5,
			'op'=>0.3,
			'date'=>false,
		);
		$limit=$opts['per_time'];
		$finish=true;
		$vars=Eleanor::LoadOptions(array('site',$this->config['opts']),true);
		$Url=new Url;
		$Url->furl=$vars['furl'];
		$Url->delimiter=$vars['url_static_delimiter'];
		$Url->defis=$vars['url_static_defis'];
		$Url->ending=$vars['url_static_ending'];
		$Url->file=Eleanor::$services['user']['file'];

		foreach(Eleanor::$langs as $lang=>&$_)
		{
			if($limit<1)
				break;
			if(!isset($data[$lang]))
				$data[$lang]=array(
					'stat'=>false,
					'cats'=>false,
					'eqdate'=>true,
					'date'=>$conf['date'],
					'ocats'=>0,
					'o'=>0,
				);

			$qlang=$lang==LANGUAGE ? 'IN (\'\',\''.$lang.'\')' : '=\''.$lang.'\'';
			if($opts['type']=='number')
			{
				if(!$data[$lang]['stat'])
					call_user_func($opts['callback'],5);
				$R=Eleanor::$Db->Query('SELECT COUNT(`lstatus`) `cnt` FROM `'.$this->config['tl'].'` WHERE `language`'.$qlang.' AND `lstatus`=1'.($data[$lang]['date'] ? ' AND `ldate`>'.($data[$lang]['eqdate'] ? '=' : '').'\''.$data[$lang]['date'].'\'' : '').' LIMIT '.$data[$lang]['o'].','.$limit);
				list($cnt)=$R->fetch_row();
				call_user_func($opts['callback'],$cnt);
			}
			else
			{
				$sect=array();
				foreach($opts['uris'] as $k=>$v)
				{
					if(Eleanor::$vars['multilang'] and isset($v[$lang]))
						$v=reset($v[$lang]);
					else
						$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
					$sect[$k]=$v;
				}
				$sect=reset($sect);
				$Url->SetPrefix(Eleanor::$vars['multilang'] && $lang!=LANGUAGE ? array('lang'=>$_['uri'],'module'=>$sect) : array('module'=>$sect));

				if(!$data[$lang]['stat'])
				{
					$a=array(
						$Url->Construct(array()),
						$Url->Construct(array('do'=>'add'),true,''),
						$Url->Construct(array('do'=>'categories'),true,''),
						$Url->Construct(array('do'=>'tags'),true,''),
						$Url->Construct(array('do'=>'search'),true,''),
					);
					foreach($a as &$v)
						call_user_func(
							$opts['callback'],
							array(
								'loc'=>$v,
								'changefreq'=>'never',
								'priority'=>$conf['op'],
							)
						);
					$limit-=5;
					$data[$lang]['stat']=true;
					$finish=false;
				}

				Language::$main=$lang;
				$Categs=new Categories($this->config['c'],true);
				if(!$data[$lang]['cats'])
				{
					$Url->ending=$Url->delimiter;
					#Очень неоптимальный запрос. Да, я знаю. Что делать? Предложите лучше...
					$R=Eleanor::$Db->Query('SELECT `c`.`id`,COUNT(`n`.`lstatus`) `cnt` FROM `'.$this->config['c'].'_l` `c` LEFT JOIN `'.$this->config['tl'].'` `n` ON `n`.`lcats` LIKE CONCAT(\'%,\',`c`.`id`,\',%\') WHERE `c`.`language`'.$qlang.' AND `n`.`lstatus`=1 AND `n`.`ldate`<NOW() AND `n`.`language`'.$qlang.' GROUP BY `c`.`id` LIMIT '.$data[$lang]['ocats'].','.$limit);
					$nums=$R->num_rows;
					if($nums<$limit)
						$data[$lang]['cats']=true;
					$data[$lang]['ocats']+=$nums;
					while($a=$R->fetch_assoc())
					{
						$limit--;
						$finish=false;

						$np=$a['cnt'] % $vars['publ_per_page'];
						$pages=max(ceil($a['cnt']/$vars['publ_per_page'])-($np>0 ? 1 : 0),1);
						if(!isset($data[$lang]['c'.$a['id']]))
							$data[$lang]['c'.$a['id']]=0;
						for($i=$data[$lang]['c'.$a['id']];$i<$pages;$i++)
							call_user_func(
								$opts['callback'],
								array(
									'loc'=>$Url->Construct($Categs->GetUri($a['id'])+array('page'=>array('page'=>$i>0 ? $i : false))),
									'changefreq'=>'monthly',
									'priority'=>$conf['cp'],
								)
							);
						$data[$lang]['c'.$a['id']]=$pages;
					}
				}

				if($data[$lang]['cats'])
				{
					$Url->ending=$vars['url_static_ending'];
					$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`ldate`,`lcats`,UNIX_TIMESTAMP(`last_mod`) `lm` FROM `'.$this->config['tl'].'` WHERE `language`'.$qlang.' AND `lstatus`=1'.($data[$lang]['date'] ? ' AND `ldate`>'.($data[$lang]['eqdate'] ? '=' : '').'\''.$data[$lang]['date'].'\'' : '').' ORDER BY `ldate` ASC LIMIT '.$data[$lang]['o'].','.$limit);
					$data[$lang]['o']+=$R->num_rows;
					while($a=$R->fetch_assoc())
					{
						$u=array('u'=>array($a['uri'],'id'=>$a['id']));
						if($Url->furl)
						{
							$a['lcats']=$a['lcats'] ? (int)ltrim($a['lcats'],',') : false;
							if($a['lcats'])
								$u=$Categs->GetUri($a['lcats'])+$u;
						}
						$data[$lang]['date']=$a['ldate'];
						if($data[$lang]['date'] and strtotime($data[$lang]['date'])<strtotime($a['ldate']))
							$data[$lang]['eqdate']=false;
						$finish=false;
						call_user_func(
							$opts['callback'],
							array(
								'loc'=>$Url->Construct($u),
								'changefreq'=>'monthly',
								'priority'=>$conf['np'],
								'lastmod'=>(int)$a['lm'],
							)
						);
					}
				}
			}
		}
		if($finish)
			foreach(Eleanor::$langs as $lang=>&$_)
			{
				$data[$lang]['o']=$data[$lang]['ocats']=0;
				$data[$lang]['cats']=false;
				$data[$lang]['eqdate']=false;
			}*/

		return$data;
	}

	/** Получение ссылки на комментарий
	 * @param array $ids Идентификатор контента
	 * @return array of [URL,title] */
	public function Link2Comment($ids)
	{
		if(is_array($ids))
		{
			$result=[];
			foreach($ids as $content=>$comments)
				foreach($comments as $v)
					$result[$v]=['#','Test - '.$content];

			return$result;
		}
		#ToDo!
		/*$mc=GetModules('user');
		$mn=array_keys($mc['uris'],'news');
		$mn=reset($mn);
		$r=[];

		$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$this->config['t'].'` INNER JOIN `'.$this->config['tl']
			.'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id`'
			.Eleanor::$Db->In(array_keys($ids)));
		while($a=$R->fetch_assoc())
			foreach($ids[$a['id']] as &$v)
				$r[$v]=[$a['title'],Eleanor::$services['user']['file'].'?'
					.Url::Query(['lang'=>Language::$main==LANGUAGE ? false : Language::$main,'module'=>$mn,'id'=>$a['id'],'cfind'=>$v])];

		return$r;*/
	}

	/** Обновление счетчика комментариев
	 * @param array $changes идентификатор контента=>изменение числа комментариев, например +2 или -4 */
	public function UpdateCommentsCounter($changes)
	{
		foreach($changes as $k=>$v)
		{
			Eleanor::$Db->Update($this->config['t'],
				['!comments'=>'GREATEST(0,`comments`'.($v>=0 ? '+'.$v : $v).')'],
				'`id`='.(int)$k.' LIMIT 1');

			Eleanor::$Db->Update($this->config['tl'],['!last_mod'=>'NOW()'],'`id`='.(int)$k);
		}
	}
}

return ApiNews::class;