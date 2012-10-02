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

class TaskSpecial_RecoverNames extends BaseClass implements Task
{
		$opts=array(),
		$data=array();

	{

	{
			$data['deadids']=0;
		$per=$this->opts['per_load'];
		$runned=false;
		foreach($this->opts['tables'] as $table=>&$tids)
			foreach($this->opts['ids'] as $k=>&$fieldid)
			{
					$cnt=$tids[$fieldid];
				else
					continue;

				if(isset($this->data['tables'][$table][$fieldid]))
				{
					if($this->data['tables'][$table][$fieldid]>=$cnt)
						continue;
				}
				else
					$this->data['tables'][$table][$fieldid]=0;

				$fid=Eleanor::$Db->Escape($fieldid,false);
				$fname=Eleanor::$Db->Escape($this->opts['names'][$k],false);
				try
				{
				catch(EE_SQL$E)
				{
				}
				while($res=$R->fetch_assoc())
				{
					$R2=Eleanor::$UsersDb->Query('SELECT `name` FROM `'.USERS_TABLE.'` WHERE `id`='.$res['f'].' LIMIT 1');
					if($a=$R2->fetch_assoc())
						$updated=Eleanor::$Db->Update($table,array($fname=>htmlspecialchars($a['name'],ELENT,CHARSET)),'`'.$fid.'`='.$res['f']);
					else
					{
						$this->data['deadids']++;
					$this->data['updated']+=$updated;
					$this->data['total']++;
					$this->data['tables'][$table][$fieldid]++;
				}
				break 2;
			}
		if(!$runned or $this->data['total']==$this->opts['total'])
			$this->data['done']=true;
		if($this->data['done'])
			unset($this->data['tables']);
		return$this->data['done'];
	}

	public function GetNextRunInfo()
	{
}