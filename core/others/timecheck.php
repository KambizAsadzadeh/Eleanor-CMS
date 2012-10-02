<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class TimeCheck extends BaseClass
{
	public
		$table,
		$cp='',#Cookie prefix
		$mid,#��� ���������� ������ ��� ������ ������
		$uid;#��� ���������� ������ �� ����� ������� ������������

	public function __construct($mid=0,$table=false,$uid=false)
	{
		$this->uid=$uid===false ? Eleanor::$Login->GetUserValue('id') : $uid;
		$this->mid=$mid;
		if($mid)
			$this->cp=$mid.'-';
		$this->table=$table ? $table : P.'timecheck';
	}

	public function Check($ids,$booly=true)
	{
			return false;
		$r=array();
		if($booly)
		{
			foreach($ids as $k=>&$v)
				if(Eleanor::GetCookie($this->cp.$v))
				{
					unset($ids[$k]);
				}
		}

		if($ids)
		{
			$R=Eleanor::$Db->Query('SELECT `contid`,`author_id`,`ip`,`value`,`timegone`,`date` FROM `'.$this->table.'` WHERE '.($this->mid ? '`mid`='.Eleanor::$Db->Escape($this->mid).' AND ' : '').'`contid`'.Eleanor::$Db->In($ids).' AND `author_id`='.(int)$this->uid.($this->uid ? '' : ' AND `ip`=\''.Eleanor::$ip.'\''));
			while($a=$R->fetch_assoc())
				if($t<$a['_datets']=strtotime($a['date']) or !$a['timegone'])
				{
						Eleanor::SetCookie($this->cp.$a['contid'],1,$a['_datets'].'t');
					$r[$a['contid']]=array_slice($a,1);
				}
		}
		return $isa ? $r : reset($r);
	}

	public function Add($contid,$value='',$timegone=false,$t=3)
	{
		if(!$this->uid)
			$timegone=true;
		{
				return;
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
		Eleanor::SetCookie($this->cp.$contid,1,$t);
		return Eleanor::$Db->Replace(
			$this->table,
			($this->mid ? array('mid'=>$this->mid) : array())
			+array(
				'contid'=>$contid,
				'author_id'=>$this->uid,
				'ip'=>$this->uid ? '' : Eleanor::$ip,
				'value'=>$value,
				'timegone'=>$timegone,#����� ������ ���� �����������?
				'!date'=>'NOW()'.$plus,
			)
		);
	}

	public function Delete($value)
	{
}