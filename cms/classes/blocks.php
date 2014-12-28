<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\BaseClass;
defined('CMS\STARTED')||die;

/** Блоки на странице */
class Blocks extends BaseClass
{
	/** @var array Кэш блоков */
	public static $blocks;

	/** Получение блоков для определенного места. Метод, выполняя все блоки, возвращает готовый HTML для
	 * непосредственной его вставке на странице.
	 * @param string|array $place Названия мест, например: left, right, top, bottom
	 * @return string|array */
	public static function Get($place)
	{
		$table_id=P.'blocks_ids';
		$table_gr=P.'blocks_groups';
		$service=Eleanor::$service;

		if(!isset(static::$blocks))
		{
			$order=$blocks=[];

			$R=Eleanor::$Db->Query("SELECT `id`, `code`, `blocks` FROM `{$table_id}` INNER JOIN `{$table_gr}` USING(`id`) WHERE `service`='{$service}'");

			while($block=$R->fetch_assoc())
			{
				$order[$block['id']]=(float)static::QuietEval($block['code']);
				$blocks[$block['id']]=$block['blocks'];
			}

			if($order)
			{
				arsort($order,SORT_NUMERIC);
				$p=reset($order);
			}
			else
				$p=0;

			if($p>0)
			{
				$k=key($order);
				static::$blocks=(array)unserialize($blocks[$k]);
			}
			else
			{
				$b=(array)Eleanor::$Cache->Get('blocks-'.Eleanor::$service,true);
				static::$blocks=isset($b['blocks']) ? (array)$b['blocks'] : [];
			}
		}

		$dump=$ids=[];
		$isa=is_array($place);

		if(!$isa)
			$place=(array)$place;

		foreach($place as $pl)
			if(isset(static::$blocks[$pl]))
				$ids=array_merge($ids,static::$blocks[$pl]);

		if($ids)
		{
			$t=time();
			$groups=GetGroups();
			$table=P.'blocks';
			$table_l=P.'blocks_l';
			$in=Eleanor::$Db->In($ids);
			$language=Language::$main;

			$R=Eleanor::$Db->Query("SELECT `id`, `type`, `file`, `user_groups`, `showfrom`, `showto`, `textfile`, `template`,
`notemplate`, `vars`, `title`, `text`, `config` FROM `{$table}` INNER JOIN `{$table_l}` USING(`id`) WHERE `id`{$in} AND `language`IN('','{$language}') AND `status`IN(1,-3)");
			while($block=$R->fetch_assoc())
			{
				if($block['user_groups'] and !array_intersect(explode(',,',trim($block['user_groups'],',')),$groups))
					continue;

				if((int)$block['showfrom'] and $t<strtotime($block['showfrom']))
					continue;

				if((int)$block['showto'] and $t>=strtotime($block['showto']))
				{
					Eleanor::$Db->Update(P.'blocks',['status'=>-2],'`id`='.$block['id'].' LIMIT 1');
					continue;
				}

				if($block['type']=='file')
				{
					$f=DIR.$block['file'];

					if(!$block['file'] or !is_file($f))
						continue;

					if($block['textfile'])
						$block['text']=file_get_contents($f);
					else
					{
						$vars=$block['vars'] ? (array)unserialize($block['vars']) : [];

						if($block['config'])
							$vars['CONFIG']=$block['config'] ? (array)unserialize($block['config']) : [];

						ob_start();

						$block['text']=\Eleanor\AwareInclude(DIR.$block['file'],$vars);

						if(!is_string($block['text']) and (!is_object($block['text']) or
								!($block['text'] instanceof \Eleanor\Abstracts\AppendString)))
							$block['text']='';

						$block['text'].=ob_get_contents();

						ob_end_clean();
					}
				}
				else
					$block['text']=OwnBB::Parse($block['text']);

				if($block['text'])
					$dump[$block['id']]=[
						't'=>$block['title'],
						'c'=>$block['text'],
						'tpl'=>$block['template'] ? $block['template'] : !$block['notemplate'],
					];
			}
		}

		$r=array_fill_keys($place,'');
		$Tpl=Eleanor::$Template;

		foreach($place as $pl)
		{
			$s=new BlocksArray;

			if(isset(static::$blocks[$pl]))
				foreach(static::$blocks[$pl] as $k=>&$v)
				{
					if(!isset($dump[$v]))
						continue;

					$b=$dump[$v];

					if($b['tpl'])
						$s[$k]=$Tpl($b['tpl']===true ? 'Blocks_'.$pl : $b['tpl'],['title'=>$b['t'],'content'=>$b['c']]);
					else
						$s[$k]=$b['c'];
				}

			$r[$pl]=$s;
		}

		return$isa ? $r : reset($r);
	}

	/** Оёбертка для eval, чтобы тот не испортил нам доступные переменные
	 * @param string Неявная переменная, код, который должен быть исполнен
	 * @return mixed */
	protected static function QuietEval()
	{
		return eval(func_get_arg(0));
	}
}

/** Специальная строка для передачи */
class BlocksArray extends BaseClass implements \ArrayAccess, \Countable, \Iterator, \IteratorAggregate
{
	use \Eleanor\Traits\AutoJoin;
}