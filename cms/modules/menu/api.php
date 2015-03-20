<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

class ApiMenu extends \Eleanor\BaseClass
{
	/** @var array Конфигурация */
	private $config;

	public function __construct($config=array())
	{
		$this->config=$config;
	}

	/** Получение упорядоченного списка меню в виде id=>параметры меню. Подменю не выносится в субмассивы, см parents
	 * @param string|null $lang Язык меню
	 * @param bool $status Флаг учета статуса при выборке меню
	 * @return array */
	public function GetOrderedList($lang=null,$status=true)
	{
		if(!$lang)
			$lang=Language::$main;

		if($db=Eleanor::$Cache->Get($this->config['n'].'_dump'.$status.'_'.$lang))
			return $db;

		$maxlen=0;
		$res=$to1sort=$to2sort=$db=[];

		$R=Eleanor::$Db->Query('SELECT `id`,`url`,`title`,`pos`,`parents`,`status` FROM `'.$this->config['t']
			.'` LEFT JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$lang.'\')'
			.($status ? ' AND `status`=1' : ''));
		while($a=$R->fetch_assoc())
		{
			if($a['parents'])
			{
				$cnt=substr_count($a['parents'],',');
				$to1sort[$a['id']]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}
			$db[$a['id']]=array_slice($a,1);
			$to2sort[$a['id']]=$a['pos'];
		}

		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0)
				if(isset($to2sort[$p[1]]))
					$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
				else
					unset($to1sort[$k],$db[$k],$to2sort[$k]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);

		foreach($to2sort as $k=>&$v)
			$res[$k]=$db[$k];

		Eleanor::$Cache->Put($this->config['n'].'_dump'.$status.'_'.$lang,$res,86400);
		return $res;
	}
}

return ApiMenu::class;