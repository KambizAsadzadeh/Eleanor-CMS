<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class Voting extends BaseClass
{
		$mid,#ID ������
		$uid,#uid
		$tpl='Voting',
		$status;#������ ������������ �� ��������� � �����������: [false] - ����� ����������, voted - ��� �������������, refused - ����� �� �������, confirmed - ����� �������, guest - ���������� ������, ������ ��� ����������� ������ ��� �������������, wait - ������� ��������, finished - ����������� ���������

		$TC,
		$table,
		$voting;

	{
		$this->uid=$uid===false ? (int)Eleanor::$Login->GetUserValue('id') : $uid;
		$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'` WHERE `id`='.(int)$id.' LIMIT 1');
		$this->voting=$R->fetch_assoc();
	}

	public function Show(array$request=array())
	{
			return'';

		$qs=array();
		$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
		while($a=$R->fetch_assoc())
		{
			$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
		if(!isset($this->status))
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		return Eleanor::$Template->VotingCover($this->voting,$qs,$this->status,$request);

	public function DoAjax()
	{
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		$data=array(
			'type'=>isset($_POST['voting']['type']) ? (string)$_POST['voting']['type'] : 'vote',
			'data'=>isset($_POST['voting']['data']) ? (array)$_POST['voting']['data'] : array(),
		);
		switch($data['type'])
		{
				if(!$this->status)
				{
					$error=false;
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers` FROM `'.$this->table.'_q` WHERE `id`='.$this->voting['id']);
					while($a=$R->fetch_assoc())
					{
						if(!isset($data['data'][$a['qid']]) or $a['multiple'] and (!is_array($data['data'][$a['qid']]) or count($data['data'][$a['qid']])>$a['maxans'] or array_diff($data['data'][$a['qid']],array_keys($a['answers']))))
						{
							break;
						$qa[$a['qid']]=$a['answers'];
					if($error or count(array_intersect_key($qa,$data['data']))!=count($qa))
					{
					foreach($qa as $k=>&$q)
					{
						{
							$q[$v]++;
							if($this->uid)
								$insqr[]=array(
									'id'=>$this->voting['id'],
									'qid'=>$k,
									'vid'=>$v,
									'uid'=>$this->uid,
								);
						}
						$qs=serialize($q);
						Eleanor::$Db->Update($this->table.'_q',array('answers'=>$qs),'`id`='.$this->voting['id'].' AND `qid`='.$k.' LIMIT 1');
						if($this->uid)
							$insr[]=array(
								'id'=>$this->voting['id'],
								'uid'=>$this->uid,
								'!date'=>'NOW()',
								'answer'=>$qs
							);
					}
					Eleanor::$Db->Update($this->table,array('!votes'=>'`votes`+1'),'`id`='.$this->voting['id'].' LIMIT 1');
					if($this->uid)
					{
					}
					else
					{
							$this->TC=new TimeCheck($this->mid,false,$this->uid);
						$this->TC->Add('v'.$this->voting['id'],serialize($qa),false,$this->voting['againdays'].'d');
					$this->status='confirmed';
					$this->voting['votes']++;
					$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
					while($a=$R->fetch_assoc())
					{
						$a['answers']=$qa[$a['qid']];
						$ques[$a['qid']]=array_slice($a,1);
					}
				else
				{
					while($a=$R->fetch_assoc())
					{
						$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
						$ques[$a['qid']]=array_slice($a,1);
					}
					$this->status='rejected';
				}
				Result(Eleanor::$Template->Voting($this->voting,$ques,$this->status));
				return true;
			break;
			default:
				Error();

	public function Status()
	{
			return $this->status='wait';

		if((int)$this->voting['end']>0 and time()>strtotime($this->voting['end']))
			return $this->status='finished';

			if($this->uid)
			{
				$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'_results` WHERE `id`='.$this->voting['id'].' AND `uid`='.$this->uid.' LIMIT 1');
				$this->status=$R->num_rows==0 ? false : 'voted';
			}
			else
				$this->status='guest';
		else
		{
				$this->TC=new TimeCheck($this->mid,false,$this->uid);
			$this->status=$this->TC->Check('v'.$this->voting['id']) ? 'voted' : false;
		}