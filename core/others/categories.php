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

class Categories extends BaseClass
{	public
		$lid='cid',#�������� ��������� ��������� � ������������ ������
		$imgfolder='images/categories/',#������� � ���������� ���������
		$dump;#���� �� ���������, � ������� ������������� ����

	/**
	 * �����������, ����� ������������, ����� �� ��������� �����������. ��� �������� ���������� ���������� ������ Init
	 */
	public function __construct()
	{		$a=func_get_args();
		if($a)			call_user_func_array(array($this,'Init'),$a);	}

	/**
	 * ������������� ������, ����� �������� ��� �������, ������ ����� ������������� ���������
	 *
	 * @param string $t ��� �������� (�� ��������) �������
	 * @param int|FALSE $cache ����, ������������ ����� ����������� ����� �������, �������� FALSE ��������� �����������
	 */
	public function Init($t,$cache=86400)
	{		$r=$cache ? Eleanor::$Cache->Get($t.'_'.Language::$main) : false;
		if($r===false)
		{			$R=Eleanor::$Db->Query('SELECT * FROM `'.$t.'` INNER JOIN `'.$t.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')');
			$r=$this->GetDump($R);			if($cache)
				Eleanor::$Cache->Put($t.'_'.Language::$main,$r,86400,false);
		}
		return$this->dump=$r;
	}

	/**
	 * ������������ ����� ������� � ������� ������������� ����
	 *
	 * @param mysqli_result $R ��������� ���������� ����-������� �� ���� ������
	 */
	public function GetDump($R)
	{		$maxlen=0;
		$r=$to2sort=$to1sort=$db=array();
		while($a=$R->fetch_assoc())
		{			if($a['parents'])
			{				$cnt=substr_count($a['parents'],',');
				$to1sort[$a['id']]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}
			$db[$a['id']]=$a;
			$to2sort[$a['id']]=$a['pos'];
		}
		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($db[$k]['parents'])
				if(isset($to2sort[$db[$k]['parent']]))
					$to2sort[$k]=$to2sort[$db[$k]['parent']].','.$to2sort[$k];
				else
					unset($to2sort[$db[$k]['parent']]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);
		foreach($to2sort as $k=>&$v)
		{			$db[$k]['parents']=rtrim($db[$k]['parents'],',');
			$r[(int)$db[$k]['id']]=$db[$k];
		}

		return$r;
	}

	/**
	 * ������� ������������ ����� �� ����� ��������� ������ �� ����������� ID ��� ������������������ URI ���������
	 *
	 * @param int|array $id �������� ������������� ��������� ���� ������ ������������������ URI
	 */
	public function GetCategory($id)
	{
		if(is_array($id))
		{			$cnt=count($id)-1;
			$parent=0;
			$curr=array_shift($id);
			foreach($this->dump as &$v)
				if($v['parent']==$parent and strcasecmp($v['uri'],$curr)==0)
				{
					if($cnt--==0)
						return $v;
					$curr=array_shift($id);
					$parent=$v['id'];
				}
		}
		elseif(isset($this->dump[$id]))
			return$this->dump[$id];
	}

	/**
	 * ��������� ������ ��������� � ���� option-��, ��� select-a: <option value="ID" selected>VALUE</option>
	 *
	 * @param int|array $sel ������, ������� ����� ��������
	 * @param int|array $no ��� ����������� ��������� (�� ������� � �� ����)
	 */
	public function GetOptions($sel=array(),$no=array())
	{		$opts='';
		$sel=(array)$sel;
		$no=(array)$no;
		foreach($this->dump as &$v)
		{			$p=$v['parents'] ? explode(',',$v['parents']) : array();
			$p[]=$v['id'];
			if(array_intersect($no,$p))
				continue;
			$opts.=Eleanor::Option(($v['parents'] ? str_repeat('&nbsp;',substr_count($v['parents'],',')+1).'�&nbsp;' : '').$v['title'],$v['id'],in_array($v['id'],$sel),array(),2);
		}
		return$opts;	}

	/**
	 * ��������� ������� URI ��� ����������� �������� ��� � ����� URL � ����������� ��������� ������
	 *
	 * @param int $id �������� ������������� ���������
	 */
	public function GetUri($id)
	{		if(!isset($this->dump[$id]))
			return array();
		$params=array();
		$lastu=$this->dump[$id]['uri'];
		if($this->dump[$id]['parents'] and $lastu)
		{			foreach(explode(',',$this->dump[$id]['parents']) as $v)
				if(isset($this->dump[$v]))
					if($this->dump[$v]['uri'])
						$params[]=array($this->dump[$v]['uri']);
					else
					{						$params=array();
						$lastu='';
						break;					}
		}
		$params[]=array($lastu,$this->lid=>$id);
		return$params;
	}
}