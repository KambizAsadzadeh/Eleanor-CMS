<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

/** Категории */
class Categories extends \Eleanor\BaseClass
{
	public
		/** @var array Дамп БД категорий, в удобном упорядоченном виде */
		$dump;

	/** Конструктор. Все входящие переменные передаются методу Init.
	 * @param string
	 * @param int|null */
	public function __construct(...$args)
	{
		if($args)
			$this->Init(...$args);
	}

	/** Инициализация, здесь задается имя таблицы, откуда будут формироваться категории
	 * @param string $t Имя основной (не языковой) таблицы
	 * @param int|null $cache Флаг, определяющий время кэширования дампа таблицы, передача null отключает кэширование
	 * @return array */
	public function Init($t,$cache=86400)
	{
		$r=$cache ? Eleanor::$Cache->Get($t.'_'.Language::$main) : false;

		if($r===false)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$t.'` INNER JOIN `'.$t
				.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')');
			$r=$this->GetDump($R);

			if($cache)
				Eleanor::$Cache->Put($t.'_'.Language::$main,$r,86400,false);
		}

		return$this->dump=$r;
	}

	/** Формирование дампа таблицы в удобном иерархическом виде
	 * @param \mysqli_result $R Результат выполнения дамп-запроса из базы данных
	 * @return array */
	public function GetDump($R)
	{
		$maxlen=0;
		$r=$to2sort=$to1sort=$db=[];

		while($a=$R->fetch_assoc())
		{
			if($a['parents'])
			{
				$cnt=substr_count($a['parents'],',');
				$to1sort[ $a['id'] ]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}

			$db[ $a['id'] ]=$a;
			$to2sort[ $a['id'] ]=$a['pos'];
		}

		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($db[$k]['parents'])
				if(isset($to2sort[$db[$k]['parent']]))
					$to2sort[$k]=(int)$to2sort[$db[$k]['parent']].','.$to2sort[$k];
				else
					unset($to2sort[$db[$k]['parent']]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);

		foreach($to2sort as $k=>&$v)
			$r[ (int)$db[$k]['id'] ]=array_slice($db[$k],1);

		return$r;
	}

	/** Поиск по дампу категорий исходя из переданного ID или последовательности URI категории
	 * @param int|array $id Числовой идентификатор категории либо массив последовательности URI
	 * @return array|null */
	public function GetCategory($id)
	{
		if(is_array($id))
		{
			$cnt=count($id)-1;
			$parent=0;
			$curr=array_shift($id);

			foreach($this->dump as $k=>&$v)
				if($v['parent']==$parent and strcasecmp($v['uri'],$curr)==0)
				{
					if($cnt--==0)
					{
						$id=$k;
						break;
					}

					$curr=array_shift($id);
					$parent=$k;
				}
		}

		return is_scalar($id) && isset($this->dump[$id]) ? $this->dump[$id]+['id'=>$id] : null;
	}

	/** Получение списка категорий в виде option-ов, для select-a: <option value="ID" selected>VALUE</option>
	 * @param int|array $sel Пункты, которые будут отмечены
	 * @param int|array $no ИДы исключаемых категорий (не попадут и их дети)
	 * @return string */
	public function GetOptions($sel=[],$no=[])
	{
		$opts='';
		$sel=(array)$sel;
		$no=(array)$no;

		foreach($this->dump as $k=>&$v)
		{
			$p=$v['parents'] ? explode(',',$v['parents']) : [];
			$p[]=$k;

			if(array_intersect($no,$p))
				continue;

			$opts.=\Eleanor\Classes\Html::Option(
				($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'›&nbsp;' : '').$v['title'],
				$k,in_array($k,$sel),[],2);
		}

		return$opts;
	}

	/** Получение массива URI для дальнейшего генерации ссылки. В случае возврата пустого массива - ЧПУ у категории нет
	 * @param int $id Идентификатор категории
	 * @return array */
	public function GetUri($id)
	{
		if(!isset($this->dump[$id]))
			return[];

		$params=[];
		$lastu=$this->dump[$id]['uri'];

		if($this->dump[$id]['parents'] and $lastu)
		{
			foreach(explode(',',$this->dump[$id]['parents']) as $v)
				if(isset($this->dump[$v]))
					if($this->dump[$v]['uri'])
						$params[]=$this->dump[$v]['uri'];
					else
						return[];
		}

		$params[]=$lastu;
		return$params;
	}
}