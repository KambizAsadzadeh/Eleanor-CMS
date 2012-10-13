<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������������� ������ ����������� �������.
*/
class TplUserStatic
{
	/*
		�������� ����������� ����������� ��������
		$id - �������� ������������� ����������� �������� ��� �������� �� ���� ������, ������ - ��� �������� �������
		$data - ������ ����������� ��������
			title - �������� ����������� ��������
			text - ����� ����������� ��������
			navi - ������� ������ ��������� � ����������� ��������. ������ ������� - ������ � �������:
				0 - ����� ������
				1 - (���������) ������ ������
			seealso - ������, �������� ��� ��������� (������ ���). ������ ������� - ������ � �������:
				0 - ����� ������
				1 - ������
	*/
	public static function StaticShow($id,$data)
	{
		$see=$navi='';
		if($data['navi'])
		{
			foreach($data['navi'] as &$v)
				$v=$v[1] ? '<a href="'.$v[1].'">'.$v[0].'</a>' : $v[0];
			$navi.=join(' &raquo; ',$data['navi']).'<hr />';
		}
		if($data['seealso'])
		{
			foreach($data['seealso'] as &$v)
				$v='<a href="'.$v[1].'">'.$v[0].'</a>';
			$see='<hr /><b>'.Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['seealso'].'</b><br /><ul><li>'.join('</li><li>',$data['seealso']).'</li></ul>';
		}
		return Eleanor::$Template->OpenTable()
			.$navi
			.$data['text']
			.$see
			.Eleanor::$Template->CloseTable();
	}

	/*
		����� ����������� ������� �� ������� (� ������, ���� ������ ����������� ������� ������������ �� ������� ��������)
		$a - ������ ����������� ������� ��� ������ �� �������. ������ ������� - ������ � �������:
			title - �������� ����������� ��������
			text - ����� ����������� ��������
	*/
	public static function StaticGeneral($a)
	{
		$c='';
		foreach($a as &$v)
			$c.='<h1 style="text-align:center">'.$v['title'].'</h1><br />'.$v['text'].'<br /><br />';
		return$c;
	}

	/*
		����� ���������� ����������� ������� (�������� ���� �������).
		$a - ������ ���� ����������� �������, �������� � ����. ������: id=>array(), ����� ����������� �������:
			uri - ������-������������� ����������� ��������
			title - �������� ����������� ��������
			parents - �������������� ���� ��������� ����������� ��������, ����������� �������� (���� ���, �������, ����)
			pos - ����� �� �������� ������������� ����������� �������� � �������� ������ �������� (�� �������� � �������� ������� � 1)
	*/
	public static function StaticSubstance($a)
	{
		return Eleanor::$Template->Title(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['substance'])
		.($a ? Eleanor::$Template->OpenTable().self::SubstanceItems($a).Eleanor::$Template->CloseTable() : '');
	}

	protected static function SubstanceItems($a)
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$c='<ul>';#Content
		$n=-1;
		$nonp=true;#No new page
		foreach($a as $k=>&$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=self::SubstanceItems(array_slice($a,$n));
					$nonp=false;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Plug->GetUrl($k)).'">'.$v['title'].'</a>';
			$nonp=true;
		}
		return$c.'</li></ul>';
	}
}