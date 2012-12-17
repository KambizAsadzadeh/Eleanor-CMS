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

class OwnBbCode extends BaseClass
{	const
		SINGLE=false;

	/**
	 * ����� ��������, � ������ ����� ������������� ���� ��������� ������������� ������
	 */
	public static function RestrictDisplay()
	{
		return Eleanor::$Template->RestrictedSection(Eleanor::$Language['ownbb']['restrict']);
	}

	/**
	 * ��������� ���������� ����� ������� �� ��������
	 *
	 * @param string $t ���, ������� ��������������
	 * @param string $p ��������� ����
	 * @param string $c ���������� ���� [tag...] ��� ��� [/tag]
	 * @param bool $cu ���� ����������� ������������� ����
	 */
	public static function PreDisplay($t,$p,$c,$cu)
	{		return$c;	}

	/**
	 * ��������� ���������� ����� � �������
	 *
	 * @param string $t ���, ������� ��������������
	 * @param string $p ��������� ����
	 * @param string $c ���������� ���� [tag...] ��� ��� [/tag]
	 * @param bool $cu ���� ����������� ������������� ����
	 * @param bool $e ���� ������� ������������ ����
	 */
	public static function PreEdit($t,$p,$c,$cu,$e=self::SINGLE)
	{
		return self::PreSave($t,$p,$c,true,$e);
	}

	/**
	 * ��������� ���������� ����� � �����������
	 *
	 * @param string $t ���, ������� ��������������
	 * @param string $p ��������� ����
	 * @param string $c ���������� ���� [tag...] ��� ��� [/tag]
	 * @param bool $cu ���� ����������� ������������� ����
	 */
	public static function PreSave($t,$p,$c,$cu,$e=self::SINGLE)
	{
		if(!is_array($p))
			$p=Strings::ParseParams($p,$t);
		$tp=isset($p[$t]) ? '' : ' ';
		if(!$cu or isset($p['noparse']))
		{
			unset($p['noparse']);
			$cu=false;
		}
		foreach($p as $k=>$v)
		{
			if($v==$k)
			{
				$tp.=$k.' ';
				continue;
			}

			if(strpos($v,' ')===false)
				$q=$v;
			elseif(strpos($v,'\'')===false)
				$q='"'.$v.'"';
			elseif(strpos($v,'"')===false)
				$q='\''.$v.'\'';
			else
				$q='"'.str_replace('"','&quot;',$v).'"';

			if($k==$t)
				$tp='='.$q.' '.$tp;
			else
				$tp.=$k.'='.$q;
			$tp.=' ';
		}
		return'['.$t.rtrim($tp).($cu ? '' : ' noparse').']'.$c.($e ? '' : '[/'.$t.']');
	}

	/*
		�������, ������� ��� ��������� ���������� �������� ���� ������ ������ - ������ �������� ����� ������ ��������� ��������� ������:
		public static function TotalPreSave($s,$ts,$cu){ return$s; }
		public static function TotalPreEdit($s,$ts,$cu){ return$s; }
		public static function TotalPreDisplay($s,$ts,$cu){ return$s; }

		@param string $s ���� �����
		@param array $ts ������ �����, ������� ���������� ������������
		@param bool $cu ���� ����������� ������������� ����
	*/
}

class OwnBB extends BaseClass
{	const#��������� ���� ��������
		DISPLAY=1,#��������� ����������� ������ ����� �������
		SHOW=2,#��������� ������������� ������ ����� �������: ������� �� DISPLAY ������� � ���, ��� ������������ ���������� �� gr_see, � gr_use
		EDIT=4,#��������� ����������� ������ ����� �������
		SAVE=8;#��������� ����������� (���������� �� ������������) ������ ����� �������

	public static
		$replace=array(),#������ ����� ������� ������������ BB �����. ������: ��� ������ => ��� ������ ������
		$bbs=array(),#������ � ������� �������������� ownbb �����. ����������� � ����� ����� �����
		$opts=array(),#������ � ������� ����������� ��� ������� ������. �������� ���� alt �������� �� ���������� ���� ��������� ��������� alt �� ���������, visual - ���� ����������� ��������� � �������� �������� ������
		$np;#������, ������� ������������ � ������� StoreNotParsed � ParseNotParsed

	/**
	 * ��������� ��������� ownbb �����
	 *
	 * @param string $s ����� ��� ���������, ������ ��������� ownbb ����
	 * @param int $t ��� ��������� (��. ��������� ����)
	 * @param array $codes �������������� ������ ������ ��� �� ���� ����� �������
	 */
	public static function Parse($s,$t=self::DISPLAY,array$c=array())
	{		$s=self::StoreNotParsed($s,$t);
		$s=self::ParseBBCodes($s,$t,$c);
		return self::ParseNotParsed($s,$t);
	}

	/**
	 * ���������������� ��������� ownbb �����. ������� �� Parse � ���, ������ ���� ����� ������������ ownbb ����, � �� ����� ��� Parse ����� ���� ����������
	 *
	 * @param string $s ����� ��� ���������, ������ ��������� ownbb ����
	 * @param int $type ��� ��������� (��. ��������� ����)
	 * @param array $codes �������������� ������ ������ ��� �� ���� ����� �������
	 */
	public static function ParseBBCodes($s,$type,array$codes=array())
	{		switch($type)
		{
			case self::EDIT:
				$mth='PreEdit';
			break;
			case self::SAVE:
				$mth='PreSave';
			break;
			default:
				$mth='PreDisplay';
		}
		$groups=Eleanor::GetUserGroups();
		foreach(self::$bbs as &$bb)
		{
			$ts=explode(',',$bb['tags']);
			if($codes and count(array_intersect($codes,$ts))==0 or !$codes and $bb['special'])
				continue;

			$cu=true;
			if($type&self::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&self::DISPLAY)
				$grs=$bb['gr_see'];
			elseif($type&self::SHOW)
				$grs=array_merge($bb['gr_use'],$bb['gr_see']);
			else
				$grs=false;

			if($grs)
				$cu=(bool)array_intersect($grs,$groups);

			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(self::$replace[$h]))
			{
				$c=self::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{				$c='OwnBbCode_'.$h;
				$cch=true;			}
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}
					$tl=strlen($t);
					#���� �� ����� ������ ��� ��� �.�. i != img (������� ��� ��������� ����� ����� ���������� ���� - )
					if(trim($s{$cp+$tl+1},'=] ')!='')
					{
						++$cp;
						continue;
					}

					$l=false;
					do
					{
						$l=strpos($s,']',$l ? $l+1 : $cp);
						if($l===false)
						{
							++$cp;
							continue 2;
						}
					}while($s{$l-1}=='\\');

					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;

					if(method_exists($c,'Total'.$mth))
					{
						$s=call_user_func(array($c,'Total'.$mth),$s,$ts,$cu);
						continue 3;
					}

					$ps=substr($s,$cp+$tl+1,$l-$cp-$tl-1);
					$ps=str_replace('\\]',']',trim($ps));
					$e=constant($c.'::SINGLE');
					if($e or false===$clpos=stripos($s,'[/'.$t.']',$l+1))
					{
						$l-=$cp-1;#]
						$ct='';
					}
					else
					{
						$ct=substr($s,$l+1,$clpos-$l-1);
						$l=$clpos-$cp+$tl+3;#[/]
					}
					$r=call_user_func(array($c,$mth),$t,$ps,$ct,$cu,$e);
					$s=substr_replace($s,$r,$cp,$l);
					$ocp=$cp;
				}
			}
		}
		return $s;
	}

	/**
	 * ���������� ����������� ����������� ownbb �����, � ������� ������ ����������� ������� �����������
	 *
	 * @param string $s ����� ��� ���������, ������ ��������� ownbb ����
	 * @param int $type ��� ��������� (��. ��������� ����)
	 */
	public static function StoreNotParsed($s,$type)
	{
		$s=str_replace('<!-- NP ','<!-- ',$s);
		$n=0;
		self::$np=array();
		$groups=Eleanor::GetUserGroups();
		foreach(self::$bbs as &$bb)
		{
			if(!$bb['no_parse'])
				continue;

			$cu=true;
			if($type&self::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&self::DISPLAY)
				$grs=$bb['gr_see'];
			elseif($type&self::SHOW)
				$grs=array_merge($bb['gr_use'],$bb['gr_see']);
			else
				$grs=false;

			if($grs and !(bool)array_intersect($grs,$groups))
				continue;

			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(self::$replace[$h]))
			{
				$c=self::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{
				$c='OwnBbCode_'.$h;
				$cch=true;
			}
			$ts=explode(',',$bb['tags']);
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{						++$cp;
						continue;
					}
					$tl=strlen($t);
					#���� �� ����� ������ ��� ��� �.�. i != img (������� ��� ��������� ����� ����� ���������� ���� - )
					if(trim(substr($s,$cp+$tl+1,1),'=] ')!='')
					{
						++$cp;
						continue;
					}

					if(false!==$nop=strpos($s,'noparse]',$cp) and $nop<strpos($s,']',$cp))
					{
						++$cp;
						continue;
					}

					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;

					$e=constant($c.'::SINGLE');
					if($e or false===$l=strpos($s,'[/'.$t.']',$cp))
					{
						$l=strpos($s,']',$cp);
						if($l===false)
						{							++$cp;
							continue;
						}
						$l-=$cp-1;#]
					}
					else
						$l-=$cp-$tl-3;#[/]
					$r='<!-- NP '.$n++.' -->';
					$ct=substr($s,$cp,$l);
					$s=substr_replace($s,$r,$cp,$l);
					self::$np[]=array(
						'r'=>$r,
						't'=>$ct,
						's'=>$bb['sp_tags'] ? $bb['sp_tags']+array(''=>$t) : array($t),
					);
					$ocp=$cp;
				}
			}
		}
		return$s;
	}

	/**
	 * ��������� ����������� ����������� ownbb �����. ���������� ����� �� ���������� ������� StoreNotParsed � ��������� �������� ����� ������� ParseBBCodes
	 *
	 * @param string $s ����� ��� ���������, ������ ��������� ownbb ����
	 * @param int $type ��� ��������� (��. ��������� ����)
	 */
	public static function ParseNotParsed($s,$type)
	{
		if(self::$np)
			if($type)
				foreach(self::$np as &$v)
					$s=str_replace($v['r'],self::ParseBBCodes($v['t'],$type,$v['s']),$s);
			else
				foreach(self::$np as &$v)
					$s=str_replace($v['r'],$v['t'],$s);
		self::$np=array();
		return$s;
	}

	/**
	 * �������� ���� ownbb �����
	 */
	public static function Recache()
	{
		self::$bbs=array();
		$R=Eleanor::$Db->Query('SELECT `handler`,`tags`,`no_parse`,`special`,`sp_tags`,`gr_use`,`gr_see`,`sb` FROM `'.P.'ownbb` WHERE `active`=1 ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
		{			$a['sp_tags']=$a['sp_tags'] ? explode(',',$a['sp_tags']) : array();
			$a['gr_use']=$a['gr_use'] ? explode(',',$a['gr_use']) : array();
			$a['gr_see']=$a['gr_see'] ? explode(',',$a['gr_see']) : array();
			self::$bbs[]=$a;
		}
		Eleanor::$Cache->Put('ownbb',self::$bbs);
	}
}

OwnBB::$bbs=Eleanor::$Cache->Get('ownbb');
if(OwnBB::$bbs===false)
	OwnBB::Recache();