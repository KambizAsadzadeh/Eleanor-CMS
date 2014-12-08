<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Набор методов для фиксации действий пользователя с целью предотвращения преждевременного их повторения, например для
 * голосования в опросе или выставления оценик */
class TimeCheck extends \Eleanor\BaseClass
{
	public
		/** @var string Имя таблицы */
		$table,

		/** @var string префикс устанавливаемой куки */
		$cp='',

		/** @var int ID модуля */
		$mid,

		/** @var int #ID пользователя */
		$uid;

	/** Конструктор
	 * @param int $mid ID модуля
	 * @param string|null Имя таблицы
	 * @param int|null ID пользователя */
	public function __construct($mid=0,$table=null,$uid=null)
	{
		$this->uid=$uid===null ? Eleanor::$Login->Get('id') : $uid;
		$this->mid=$mid;

		if($mid)
			$this->cp=$mid.'-';

		$this->table=$table ? $table : P.'timecheck';
	}

	/** Проверка истечения времени по для определения возможности повторных действий
	 * @param array|string $ids Идентификаторы контента
	 * @return null|array */
	public function Check($ids)
	{
		if(!$ids)
			return;

		$isa=is_array($ids);
		$r=[];

		$ids=(array)$ids;
		foreach($ids as $k=>&$v)
			if(GetCookie($this->cp.$v))
			{
				$r[$v]=true;
				unset($ids[$k]);
			}

		if($ids)
		{
			$t=time();
			$R=Eleanor::$Db->Query('SELECT `content_id`, `author_id`, `ip`, `value`, `timegone`, `date` FROM `'
				.$this->table.'` WHERE `module_id`='.(int)$this->mid.' AND `content_id`'.Eleanor::$Db->In($ids)
				.' AND `author_id`='.(int)$this->uid.($this->uid ? '' : ' AND `ip`='.Eleanor::$Db->Escape(Eleanor::$ip)));
			while($a=$R->fetch_assoc())
				if($t<$a['_ts']=strtotime($a['date']) or !$a['timegone'])
				{
					if(!isset($r[ $a['content_id'] ]))
						SetCookie($this->cp.$a['content_id'],1,$a['_ts'].'t');

					$r[ $a['content_id'] ]=array_slice($a,1);
				}
		}

		return$isa ? $r : reset($r);
	}

	/** Добавление временной метки для последующей проверки
	 * @param string $id Идентификатор контента
	 * @param string $value Значение, помещаемое в базу
	 * @param bool $timegone Флаг возобновляемости. true - через $t метка истечет и действие можно будет повторить, для
	 * гостей всегд равно true.
	 * @param int|string $t Срок истечения, формат: \d+[mhdMys?] последняя буква определяет длительность: минуты, часы,
	 * дни, месяцы, годы, секунды (в случае секунд букву s можно не указывать) */
	public function Add($id,$value='',$timegone=false,$t=3)
	{
		$plus='';

		if(!$this->uid)
			$timegone=true;

		if($timegone)
		{
			if((int)$t==0)
				return;

			$plus=' + INTERVAL ';

			switch(substr($t,-1))
			{
				case'm':
					$plus.=(int)$t.' MINUTE';
				break;
				case'h':
					$plus.=(int)$t.' HOUR';
				break;
				case'd':
					$plus.=(int)$t.' DAY';
				break;
				case'M':
					$plus.=(int)$t.' MONTH';
				break;
				case'y':
					$plus.=(int)$t.' YEAR';
				break;
				default:
					$plus.=(int)$t.' SECOND';
			}
		}

		SetCookie($this->cp.$id,1,$t);
		Eleanor::$Db->Replace($this->table,[
			'module_id'=>$this->mid,
			'content_id'=>$id,
			'author_id'=>$this->uid,
			'ip'=>$this->uid ? '' : Eleanor::$ip,
			'value'=>$value,
			'timegone'=>$timegone,
			'!date'=>'NOW()'.$plus,
		]);
	}

	/** Удаление записи
	 * @param string|array $ids Идентификаторы контента
	 * @return int */
	public function Delete($ids)
	{
		return Eleanor::$Db->Delete($this->table,'`module_id`='.(int)$this->mid.' AND `content_id`'
			.Eleanor::$Db->In($ids).' AND `author_id`='.(int)$this->uid
			.($this->uid ? '' : ' AND `ip`='.Eleanor::$Db->Escape(Eleanor::$ip)));
	}
}